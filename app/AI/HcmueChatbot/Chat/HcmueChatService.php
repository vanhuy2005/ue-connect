<?php

namespace App\AI\HcmueChatbot\Chat;

use App\AI\HcmueChatbot\Retrieval\RagRetrievalService;
use App\AI\HcmueChatbot\Retrieval\StructuredRetrievalService;
use App\Models\AiAnswer;
use App\Models\AiQuestion;
use App\Models\AiRetrievedChunk;
use App\Models\AiStructuredQuery;
use App\Models\ChatSession;
use App\Models\DocumentChunk;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class HcmueChatService
{
    /**
     * Messages for special routing outcomes.
     */
    private const CLARIFICATION_PREFIX = 'Để mình tìm chính xác hơn, ';

    private const UNSUPPORTED_MESSAGE = 'Xin lỗi, câu hỏi này nằm ngoài phạm vi hỗ trợ của HCMUE Academic Chatbot. Mình chỉ có thể hỗ trợ các câu hỏi về chương trình đào tạo, học phần, quy chế học vụ và sổ tay sinh viên của Trường ĐHSP TPHCM.';

    public function __construct(
        protected QuestionNormalizerService $normalizer,
        protected QueryRouterService $router,
        protected StructuredQueryPlannerService $planner,
        protected StructuredRetrievalService $structuredRetrieval,
        protected RagRetrievalService $ragRetrieval,
        protected AnswerComposerService $composer,
        protected CitationVerifierService $citationVerifier,
        protected HallucinationGuardService $guard,
        protected ConversationContextService $contextService
    ) {}

    /**
     * Process a chat message and return a structured response.
     *
     * @return array{
     *   answer: string,
     *   sources: array,
     *   route: string,
     *   intent: string,
     *   requires_clarification: bool,
     *   question_id: ?int,
     *   answer_id: ?int
     * }
     */
    public function chat(
        string $userMessage,
        ChatSession $session,
        ?User $user = null
    ): array {
        Log::info('HcmueChatService: Processing message.', [
            'session_id' => $session->id,
            'message_preview' => mb_substr($userMessage, 0, 100),
        ]);

        // === Step 0: Check conversation context for follow-up ===
        $contextResult = $this->contextService->resolveFollowUp($userMessage, $session->id, $this->normalizer);
        $isFollowUp = $contextResult['is_follow_up'];

        if ($isFollowUp) {
            $normalizedQuestion = $contextResult['resolved_question'];
            $intent = $contextResult['intent'];
            $source = $contextResult['source'] ?: 'rag';
            $detectedTerms = [
                'cohort' => $contextResult['cohort'],
                'canonical_cohort' => $contextResult['cohort'],
                'detected_cohort' => $contextResult['overridden_cohort'] ?: $contextResult['cohort'],
                'cohort_alias' => $contextResult['overridden_cohort'] ?: $contextResult['cohort'],
                'major' => $contextResult['major'],
                'canonical_major' => $contextResult['major'],
                'detected_major' => $contextResult['overridden_major'] ?: $contextResult['major'],
                'matched_alias' => null,
                'faculty' => null,
                'course' => null,
                'policy_topic' => $contextResult['policy_topic'],
                'document_type' => $contextResult['knowledge_type'] === 'student_handbook' ? 'student_handbook' : 'unknown',
            ];

            $routerResult = [
                'intent' => $intent,
                'source' => $source,
                'confidence' => 1.0,
                'entities' => $detectedTerms,
                'missing_required_fields' => [],
                'reason' => 'Kế thừa từ ngữ cảnh hội thoại (Follow-up)',
            ];
        } else {
            // === Step 1: Normalize question ===
            $normResult = $this->normalizer->normalize($userMessage);
            $normalizedQuestion = $normResult['normalized_question'];
            $detectedTerms = $normResult['detected_terms'];

            // === Step 2: Route question ===
            $routerResult = $this->router->route($normalizedQuestion, $detectedTerms);
            $source = $routerResult['source'];
            $intent = $routerResult['intent'];
        }

        // Initialize extra debug tracing variables
        $extraDebug = [
            'is_follow_up' => $isFollowUp,
            'inherited_intent' => $isFollowUp ? $contextResult['intent'] : null,
            'inherited_policy_topic' => $isFollowUp ? $contextResult['policy_topic'] : null,
            'overridden_cohort' => $isFollowUp ? $contextResult['overridden_cohort'] : null,
            'resolved_question' => $isFollowUp ? $normalizedQuestion : null,
            'inherited_context' => $isFollowUp ? [
                'intent' => $contextResult['intent'],
                'knowledge_type' => $contextResult['knowledge_type'],
                'policy_topic' => $contextResult['policy_topic'],
                'major' => $contextResult['major'],
                'cohort' => $contextResult['cohort'],
            ] : null,
            'detected_cohort' => $detectedTerms['detected_cohort'] ?? null,
            'canonical_cohort' => $detectedTerms['canonical_cohort'] ?? null,
            'cohort_alias' => $detectedTerms['cohort_alias'] ?? null,
            'detected_major' => $detectedTerms['detected_major'] ?? null,
            'canonical_major' => $detectedTerms['canonical_major'] ?? null,
            'matched_alias' => $detectedTerms['matched_alias'] ?? null,
            'fallback_attempts' => [],
        ];

        // === Step 3: Log the question ===
        $aiQuestion = AiQuestion::create([
            'session_id' => $session->id,
            'user_id' => $user?->id,
            'original_question' => $userMessage,
            'normalized_question' => $normalizedQuestion,
            'intent' => $intent,
            'source_route' => $source,
            'confidence' => $routerResult['confidence'],
            'created_at' => now(),
        ]);

        // === Step 4: Handle special routes immediately ===
        if ($source === 'none' && $intent === 'clarification') {
            $clarificationQ = $routerResult['clarification_question']
                ?? 'Bạn có thể cho mình biết thêm về ngành học và khóa tuyển sinh không?';

            $answerText = self::CLARIFICATION_PREFIX.$clarificationQ;

            $this->logAnswer($aiQuestion->id, $answerText, 0);

            return $this->buildResponse($answerText, [], $source, $intent, true, $aiQuestion->id, null, $extraDebug);
        }

        if ($source === 'none' && $intent === 'unsupported') {
            $this->logAnswer($aiQuestion->id, self::UNSUPPORTED_MESSAGE, 0);

            return $this->buildResponse(self::UNSUPPORTED_MESSAGE, [], $source, $intent, false, $aiQuestion->id, null, $extraDebug);
        }

        // === Step 5: Retrieve data based on route ===
        $structuredResult = null;
        $ragChunks = [];

        if (in_array($source, ['structured_db', 'hybrid'])) {
            $queryPlan = $this->planner->plan($routerResult, $normalizedQuestion);

            // If planner requests clarification
            if ($queryPlan['requires_clarification']) {
                $clarificationText = self::CLARIFICATION_PREFIX.($queryPlan['clarification_question'] ?? '');
                $this->logAnswer($aiQuestion->id, $clarificationText, 0);

                return $this->buildResponse($clarificationText, [], $source, $intent, true, $aiQuestion->id, null, $extraDebug);
            }

            $structuredResult = $this->structuredRetrieval->retrieve($queryPlan);

            // Log structured query
            $this->logStructuredQuery($aiQuestion->id, $queryPlan, $structuredResult);
        }

        // Fall back to RAG if structured DB query failed to find data
        $shouldFallbackToRag = ($source === 'structured_db') &&
            (! $structuredResult || ! ($structuredResult['success'] ?? false));

        if (in_array($source, ['rag', 'hybrid']) || $shouldFallbackToRag) {
            if ($shouldFallbackToRag) {
                $source = 'rag';
            }
            $ragFilters = [];
            if (! empty($detectedTerms['cohort'])) {
                $ragFilters['cohort'] = $detectedTerms['cohort'];
            }
            if (! empty($detectedTerms['major'])) {
                $ragFilters['major'] = $detectedTerms['major'];
            }

            $ragChunks = $this->ragRetrieval->retrieve($normalizedQuestion, $ragFilters);

            // Log retrieved chunks
            $this->logRetrievedChunks($aiQuestion->id, $ragChunks);
        }

        // === Step 6: Compose answer ===
        $composedResult = $this->composer->compose(
            $userMessage,
            $normalizedQuestion,
            $routerResult,
            $structuredResult,
            $ragChunks
        );

        $draftAnswer = $composedResult['answer_text'];

        // === Step 7: Verify citations ===
        $hasAnySource = ! empty($ragChunks) || (! empty($structuredResult) && ($structuredResult['success'] ?? false));
        $citationResult = $this->citationVerifier->verify($draftAnswer, $ragChunks, $structuredResult);

        // === Step 8: Guard against hallucinations ===
        $finalAnswer = $this->guard->guard($draftAnswer, $citationResult, $hasAnySource);

        // === Step 9: Log the answer ===
        $aiAnswer = $this->logAnswer($aiQuestion->id, $finalAnswer, $composedResult['latency_ms'], [
            'model_provider' => $composedResult['model_provider'],
            'model_name' => $composedResult['model_name'],
            'input_tokens' => $composedResult['input_tokens'],
            'output_tokens' => $composedResult['output_tokens'],
            'total_tokens' => $composedResult['total_tokens'],
        ]);

        // === Step 10: Build sources for frontend ===
        $sources = $this->buildSources($ragChunks, $structuredResult);

        // === Step 11: Update conversation context ===
        $knowledgeType = null;
        $loaiTaiLieu = null;
        $policyTopic = $detectedTerms['policy_topic'] ?? null;
        $cohort = $detectedTerms['cohort'] ?? null;
        $major = $detectedTerms['major'] ?? null;

        if (! empty($ragChunks)) {
            $topChunk = $ragChunks[0];
            $knowledgeType = $topChunk['metadata']['knowledge_type'] ?? $topChunk['document_type'] ?? null;
            if ($knowledgeType === 'so_tay_sinh_vien' || $knowledgeType === 'quyet_dinh_ban_hanh' || $knowledgeType === 'student_handbook') {
                $knowledgeType = 'student_handbook';
            }
            $loaiTaiLieu = $topChunk['document_type'] ?? $topChunk['metadata']['loai_tai_lieu'] ?? null;

            if (empty($cohort)) {
                $cohort = $topChunk['cohort'] ?? $topChunk['metadata']['khoa_hoc'] ?? null;
            }
            if (empty($major)) {
                $major = $topChunk['metadata']['nganh'] ?? null;
            }
        }

        $structured_found = ! empty($structuredResult) && ($structuredResult['success'] ?? false);
        $rag_found = ! empty($ragChunks);
        $lastSuccess = $structured_found || $rag_found;

        $this->contextService->setContext($session->id, [
            'last_intent' => $intent,
            'last_knowledge_type' => $knowledgeType,
            'last_loai_tai_lieu' => $loaiTaiLieu,
            'last_khoa_hoc' => $cohort,
            'last_nganh' => $major,
            'last_policy_topic' => $policyTopic,
            'last_rewritten_query' => $this->ragRetrieval->lastRewrittenQuery ?? $normalizedQuestion,
            'last_question' => $normalizedQuestion,
            'last_source' => $source,
            'updated_at' => time(),
            'last_success' => $lastSuccess,
        ]);

        $extraDebug['fallback_attempts'] = $this->ragRetrieval->fallbackAttemptsLogs ?? [];

        return $this->buildResponse($finalAnswer, $sources, $source, $intent, false, $aiQuestion->id, $aiAnswer->id ?? null, $extraDebug);
    }

    /**
     * Get or create a chat session for a user.
     */
    public function resolveSession(User $user, ?int $sessionId = null): ChatSession
    {
        if ($sessionId) {
            $session = ChatSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->first();

            if ($session) {
                return $session;
            }
        }

        return ChatSession::create([
            'user_id' => $user->id,
            'title' => 'Cuộc hội thoại mới',
        ]);
    }

    /**
     * Log the AI answer to the database.
     */
    private function logAnswer(int $questionId, string $answerText, int $latencyMs, array $extra = []): AiAnswer
    {
        return AiAnswer::create([
            'question_id' => $questionId,
            'answer_text' => $answerText,
            'model_provider' => $extra['model_provider'] ?? config('ai.llm_provider', 'gemini'),
            'model_name' => $extra['model_name'] ?? config('ai.gemini.model', 'gemini-2.0-flash'),
            'prompt_version' => '1.0',
            'latency_ms' => $latencyMs,
            'input_tokens' => $extra['input_tokens'] ?? 0,
            'output_tokens' => $extra['output_tokens'] ?? 0,
            'total_tokens' => $extra['total_tokens'] ?? 0,
            'status' => 'success',
            'created_at' => now(),
        ]);
    }

    /**
     * Log retrieved RAG chunks to the database.
     */
    private function logRetrievedChunks(int $questionId, array $chunks): void
    {
        foreach ($chunks as $chunk) {
            // Only log if chunk has a valid DB-backed id (real ingested chunks)
            $chunkDbId = isset($chunk['id']) ? (int) $chunk['id'] : null;
            if (! $chunkDbId || ! DocumentChunk::find($chunkDbId)) {
                continue; // Skip mock/external chunks that don't exist in DB
            }

            AiRetrievedChunk::create([
                'question_id' => $questionId,
                'document_chunk_id' => $chunkDbId,
                'score' => $chunk['score'] ?? null,
                'rerank_score' => $chunk['rerank_score'] ?? null,
                'metadata_json' => [
                    'document_name' => $chunk['document_name'] ?? null,
                    'document_type' => $chunk['document_type'] ?? null,
                    'article' => $chunk['article'] ?? null,
                    'page_start' => $chunk['page_start'] ?? null,
                ],
                'created_at' => now(),
            ]);
        }
    }

    /**
     * Log the structured query plan to the database.
     */
    private function logStructuredQuery(int $questionId, array $queryPlan, ?array $result): void
    {
        $resultCount = 0;
        if (! empty($result['data'])) {
            $data = $result['data'];
            if (is_countable($data)) {
                $resultCount = count($data);
            } elseif (is_array($data)) {
                $resultCount = 1;
            }
        }

        AiStructuredQuery::create([
            'question_id' => $questionId,
            'query_type' => $queryPlan['query_type'] ?? 'unknown',
            'filters_json' => $queryPlan['filters'] ?? [],
            'result_count' => $resultCount,
            'metadata_json' => $result['metadata'] ?? [],
            'created_at' => now(),
        ]);
    }

    /**
     * Build sources array for the API response.
     */
    private function buildSources(array $ragChunks, ?array $structuredResult): array
    {
        $sources = [];

        foreach ($ragChunks as $chunk) {
            $sources[] = [
                'type' => 'rag',
                'document_name' => $chunk['document_name'] ?? 'Tài liệu',
                'document_type' => $chunk['document_type'] ?? null,
                'article' => $chunk['article'] ?? null,
                'page_start' => $chunk['page_start'] ?? null,
                'score' => round($chunk['score'] ?? 0, 3),
            ];
        }

        if (! empty($structuredResult['success']) && ! empty($structuredResult['metadata'])) {
            $sources[] = [
                'type' => 'structured_db',
                'document_name' => $structuredResult['metadata']['program_title']
                    ?? $structuredResult['metadata']['title']
                    ?? 'Dữ liệu CTĐT',
                'document_type' => $structuredResult['metadata']['type'] ?? 'training_program',
                'article' => null,
                'page_start' => null,
                'score' => 1.0,
            ];
        }

        return $sources;
    }

    /**
     * Build the final response array.
     */
    private function buildResponse(
        string $answer,
        array $sources,
        string $route,
        string $intent,
        bool $requiresClarification,
        ?int $questionId,
        ?int $answerId = null,
        array $extraDebug = []
    ): array {
        return array_merge([
            'answer' => $answer,
            'sources' => $sources,
            'route' => $route,
            'intent' => $intent,
            'requires_clarification' => $requiresClarification,
            'question_id' => $questionId,
            'answer_id' => $answerId,
        ], $extraDebug);
    }
}
