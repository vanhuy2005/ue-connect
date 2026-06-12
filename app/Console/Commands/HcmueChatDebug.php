<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Chat\AnswerComposerService;
use App\AI\HcmueChatbot\Chat\CohortMajorCatalogService;
use App\AI\HcmueChatbot\Chat\ConversationContextService;
use App\AI\HcmueChatbot\Chat\QueryRouterService;
use App\AI\HcmueChatbot\Chat\QuestionNormalizerService;
use App\AI\HcmueChatbot\Chat\StructuredQueryPlannerService;
use App\AI\HcmueChatbot\Retrieval\AcademicQueryAnalyzer;
use App\AI\HcmueChatbot\Retrieval\RagRetrievalService;
use App\AI\HcmueChatbot\Retrieval\StructuredRetrievalService;
use App\Models\Major;
use App\Models\SourceDocument;
use App\Models\TrainingProgram;
use Illuminate\Console\Command;

class HcmueChatDebug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:chat:debug {question : The user academic question to debug} {--conversation-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trace and debug HCMUE chatbot retrieval routing, structured query mapping, RAG results, and composed answers';

    /**
     * Execute the console command.
     */
    public function handle(
        QuestionNormalizerService $normalizer,
        QueryRouterService $router,
        StructuredQueryPlannerService $planner,
        StructuredRetrievalService $structuredRetrieval,
        RagRetrievalService $ragRetrieval,
        AnswerComposerService $composer,
        AcademicQueryAnalyzer $queryAnalyzer,
        ConversationContextService $contextService,
        CohortMajorCatalogService $catalogService
    ): int {
        $question = $this->argument('question');
        $conversationId = $this->option('conversation-id') ?: 'default_debug_session';

        $this->info('=================== HCMUE CHAT PIPELINE TRACE ===================');
        $this->line("Original Question: \"{$question}\"");
        $this->newLine();

        // Step 0: Check context for follow-up
        $contextResult = $contextService->resolveFollowUp($question, $conversationId, $normalizer);
        $isFollowUp = $contextResult['is_follow_up'];

        $this->info('Conversation Context Trace:');
        $this->line(' - is_follow_up:            '.($isFollowUp ? 'TRUE' : 'FALSE'));
        $this->line(' - inherited_intent:        '.($contextResult['intent'] ?: 'N/A'));
        $this->line(' - inherited_policy_topic: '.($contextResult['policy_topic'] ?: 'N/A'));
        $this->line(' - overridden_cohort:       '.($contextResult['overridden_cohort'] ?: 'N/A'));
        $this->line(' - resolved_question:       '.($isFollowUp ? "\"{$contextResult['resolved_question']}\"" : 'N/A'));
        $this->line(' - inherited_context:       '.($isFollowUp ? json_encode([
            'intent' => $contextResult['intent'],
            'knowledge_type' => $contextResult['knowledge_type'],
            'policy_topic' => $contextResult['policy_topic'],
            'major' => $contextResult['major'],
            'cohort' => $contextResult['cohort'],
        ], JSON_UNESCAPED_UNICODE) : 'N/A'));
        $this->newLine();

        if ($isFollowUp) {
            $question = $contextResult['resolved_question'];
        }

        // Step 1: Normalize
        $norm = $normalizer->normalize($question);
        $normalizedQuestion = $norm['normalized_question'];
        $detected = $norm['detected_terms'];

        if ($isFollowUp) {
            $detected['cohort'] = $contextResult['cohort'];
            $detected['canonical_cohort'] = $contextResult['cohort'];
            $detected['detected_cohort'] = $contextResult['overridden_cohort'] ?: $contextResult['cohort'];
            $detected['cohort_alias'] = $contextResult['overridden_cohort'] ?: $contextResult['cohort'];
            $detected['major'] = $contextResult['major'];
            $detected['canonical_major'] = $contextResult['major'];
            $detected['detected_major'] = $contextResult['overridden_major'] ?: $contextResult['major'];
            $detected['policy_topic'] = $contextResult['policy_topic'];
            if ($contextResult['knowledge_type'] === 'student_handbook') {
                $detected['document_type'] = 'student_handbook';
            }
        }

        $cohort = $detected['canonical_cohort'] ?? $detected['cohort'] ?? null;
        $major = $detected['canonical_major'] ?? $detected['major'] ?? null;
        $cohortAlias = $detected['cohort_alias'] ?? $detected['detected_cohort'] ?? null;
        $matchedAlias = $detected['matched_alias'] ?? null;

        $majorExistsInCohort = ($cohort && $major) ? $catalogService->hasMajorInCohort($cohort, $major) : null;
        $catalogSource = $catalogService->getCatalogSource();
        $availableMajorsCount = $cohort ? count($catalogService->getMajorsForCohort($cohort)) : 0;
        $availableCohortsCount = $major ? count($catalogService->getCohortsForMajor($major)) : 0;

        $this->line("Normalized Question: \"{$normalizedQuestion}\"");
        $this->newLine();

        $this->info('Detected Entities:');
        $this->line(' - cohort:           '.($detected['cohort'] ?: 'N/A'));
        $this->line(' - detected_cohort:  '.($detected['detected_cohort'] ?? 'N/A'));
        $this->line(' - canonical_cohort: '.($detected['canonical_cohort'] ?? 'N/A'));
        $this->line(' - cohort_alias:     '.($detected['cohort_alias'] ?? 'N/A'));
        $this->line(' - major (canonical):'.($detected['major'] ?: 'N/A'));
        $this->line(' - detected_major:   '.($detected['detected_major'] ?? 'N/A'));
        $this->line(' - canonical_major:  '.($detected['canonical_major'] ?? 'N/A'));
        $this->line(' - matched_alias:    '.($detected['matched_alias'] ?: 'N/A'));
        $this->line(' - faculty:          '.($detected['faculty'] ?: 'N/A'));
        $this->line(' - course:           '.($detected['course'] ?: 'N/A'));
        $this->line(' - policy_topic:     '.($detected['policy_topic'] ?: 'N/A'));
        $this->line(' - semester:         '.($detected['semester'] ?: 'N/A'));
        $this->line(' - course_name:      '.($detected['course_name'] ?: 'N/A'));
        $this->newLine();

        $this->info('Catalog Debug Output:');
        $this->line('detected_cohort: '.($detected['detected_cohort'] ?? 'N/A'));
        $this->line('canonical_cohort: '.($detected['canonical_cohort'] ?? 'N/A'));
        $this->line('cohort_alias: '.($detected['cohort_alias'] ?? 'N/A'));
        $this->newLine();
        $this->line('detected_major: '.($detected['detected_major'] ?? 'N/A'));
        $this->line('canonical_major: '.($detected['canonical_major'] ?? 'N/A'));
        $this->line('matched_alias: '.($detected['matched_alias'] ?? 'N/A'));
        $this->newLine();
        $this->line('major_exists_in_cohort: '.($majorExistsInCohort === null ? 'N/A' : ($majorExistsInCohort ? 'true' : 'false')));
        $this->line('catalog_source: '.$catalogSource);
        $this->line('available_majors_count: '.$availableMajorsCount);
        $this->line('available_cohorts_count: '.$availableCohortsCount);
        $this->newLine();

        // Special Query: Majors of a Cohort
        if ($cohort && ! $major && (preg_match('/(ngành\s+nào|ngành\s+gì|mở\s+ngành|tuyển\s+sinh\s+ngành|có\s+những\s+ngành|có\s+các\s+ngành|danh\s+sách\s+ngành|gồm\s+những\s+ngành|gồm\s+các\s+ngành)/ui', $normalizedQuestion))) {
            $this->info('=== SPECIAL QUERY: MAJORS OF COHORT ===');
            $majors = $catalogService->getMajorsForCohort($cohort);
            $majorsStr = implode("\n", array_map(fn ($m) => "- {$m}", $majors));
            $this->comment("{$cohort} có những ngành học sau:\n\n{$majorsStr}");

            return self::SUCCESS;
        }

        // Special Query: Cohorts of a Major
        if ($major && ! $cohort && (preg_match('/(khóa\s+nào|khoá\s+nào|năm\s+nào|mở\s+khóa|mở\s+khoá|có\s+ở\s+khóa|có\s+ở\s+khoá|tuyển\s+ở\s+khóa|tuyển\s+ở\s+khoá|những\s+khóa\s+nào|những\s+khoá\s+nào|các\s+khóa\s+nào|các\s+khoá\s+nào)/ui', $normalizedQuestion))) {
            $this->info('=== SPECIAL QUERY: COHORTS OF MAJOR ===');
            $cohorts = $catalogService->getCohortsForMajor($major);
            $cohortsStr = implode("\n", array_map(fn ($c) => "- {$c}", $cohorts));
            $this->comment("Ngành {$major} có ở những khóa học sau:\n\n{$cohortsStr}");

            return self::SUCCESS;
        }

        // Validation Cohort <-> Major Mismatch
        if ($cohort && $major && ! $majorExistsInCohort) {
            $this->warn('=== VALIDATION FAILED: MAJOR DOES NOT EXIST IN COHORT ===');
            $majors = $catalogService->getMajorsForCohort($cohort);
            $majorsStr = implode("\n", array_map(fn ($m) => "* {$m}", $majors));
            $this->comment("Mình không tìm thấy ngành {$major} trong dữ liệu của {$cohort}.\n\nCác ngành hiện có:\n\n{$majorsStr}");

            return self::SUCCESS;
        }

        // Analyze query to calculate knowledge_type and rewritten_query for debug trace
        $analysis = $queryAnalyzer->analyze($normalizedQuestion);
        $isStudentPolicyIntent = ($analysis['intent'] ?? 'general') === 'student_policy';
        $isTotalCreditsIntent = ($analysis['intent'] ?? 'general') === 'total_credits';

        $knowledgeType = 'curriculum';
        $docTypeVal = $detected['document_type'] ?? $analysis['document_type'] ?? 'unknown';
        $queryLower = mb_strtolower($normalizedQuestion, 'UTF-8');

        if ($isStudentPolicyIntent) {
            $knowledgeType = 'student_handbook';
        } elseif ($docTypeVal === 'student_handbook' || $docTypeVal === 'academic_regulation') {
            $knowledgeType = 'student_handbook';
        } elseif ($docTypeVal === 'training_program' || $docTypeVal === 'learning_outcome') {
            $knowledgeType = 'curriculum';
        } else {
            $handbookKeywords = [
                'sổ tay', 'student handbook', 'sotaysinhvien', 'quy chế', 'quy định',
                'quyche', 'quydinh', 'học vụ', 'gpa', 'học phí', 'học bổng', 'rèn luyện',
                'tốt nghiệp', 'ra trường', 'cảnh báo', 'buộc thôi học', 'học lại', 'học cải thiện',
                'đăng ký học phần', 'hủy học phần', 'miễn giảm', 'kỷ luật', 'thôi học',
                'rớt môn', 'nợ môn',
            ];
            $hasHandbook = false;
            foreach ($handbookKeywords as $kw) {
                if (str_contains($queryLower, $kw)) {
                    $hasHandbook = true;
                    break;
                }
            }
            if ($hasHandbook) {
                $knowledgeType = 'student_handbook';
            }
        }

        $rewrittenQuery = $normalizedQuestion;
        if ($isTotalCreditsIntent) {
            $rewrittenQuery = 'Tổng số tín chỉ toàn khóa học chương trình đào tạo tốt nghiệp';
        } elseif ($isStudentPolicyIntent) {
            if (str_contains($queryLower, '5%') || str_contains($queryLower, '5 phần trăm')) {
                $rewrittenQuery = 'quy định học lại quá 5 phần trăm số tín chỉ';
            } elseif (str_contains($queryLower, 'hạ bằng')) {
                $rewrittenQuery = 'điều kiện hạ xếp loại tốt nghiệp do học lại';
            }
        }

        // Step 2: Route
        if ($isFollowUp) {
            $route = [
                'intent' => $contextResult['intent'],
                'source' => $contextResult['source'] ?: 'rag',
                'confidence' => 1.0,
                'entities' => $detected,
                'missing_required_fields' => [],
                'reason' => 'Bypassed router: Kế thừa từ ngữ cảnh (Follow-up)',
            ];
        } else {
            $route = $router->route($normalizedQuestion, $detected);
        }
        $this->info('Router Decision:');
        $this->line(' - expected route:   (determined by query properties)');
        $this->line(' - actual route:     '.$route['source']);
        $this->line(' - intent matched:   '.$route['intent']);
        $this->line(' - knowledge_type:   '.$knowledgeType);
        $this->line(' - rewritten_query:  '.($rewrittenQuery !== $normalizedQuestion ? "\"$rewrittenQuery\"" : 'N/A'));
        $this->line(' - confidence score: '.number_format($route['confidence'], 2));
        $this->line(' - reason:           '.$route['reason']);
        $this->line(' - detected_course_name: '.($detected['course_name'] ?: 'N/A'));
        $this->line(' - detected_semester:    '.($detected['semester'] ?: 'N/A'));

        $curriculumSignals = [
            'môn', 'học phần', 'subject', 'course', 'mã học phần', 'tiên quyết',
            'song hành', 'nâng cao', 'cơ sở ngành', 'chuyên ngành', 'chương trình khung',
            'ctdt', 'ctkh', 'học kỳ', 'học kì', 'semester', 'kì', 'hk',
        ];
        $curriculumSignalFound = 'FALSE';
        foreach ($curriculumSignals as $sig) {
            if (str_contains(mb_strtolower($question), $sig)) {
                $curriculumSignalFound = "TRUE (matched '{$sig}')";
                break;
            }
        }
        $this->line(' - curriculum_signal_found: '.$curriculumSignalFound);
        $this->line(' - router_reason:           '.$route['reason']);
        $this->newLine();

        $structuredDbResult = null;
        $ragChunks = [];

        // Step 3: Structured Query Planner & Retrieval
        if (in_array($route['source'], ['structured_db', 'hybrid'])) {
            $this->info('Structured Query Plan:');
            $plan = $planner->plan($route, $normalizedQuestion);
            $this->line(' - query_type:       '.$plan['query_type']);
            $this->line(' - filters:          '.json_encode($plan['filters'], JSON_UNESCAPED_UNICODE));

            if ($plan['requires_clarification']) {
                $this->warn('  -> Clarification Required: '.$plan['clarification_question']);

                return self::FAILURE;
            }

            $structuredDbResult = $structuredRetrieval->retrieve($plan);

            $this->newLine();
            $this->info('Structured Database Retrieval Result:');
            if ($structuredDbResult['success']) {
                $this->line(' - status:           SUCCESS');
                $this->line(' - metadata:         '.json_encode($structuredDbResult['metadata'], JSON_UNESCAPED_UNICODE));

                $data = $structuredDbResult['data'];
                if (is_array($data) && isset($data['total_credits'])) {
                    $this->line(' - total_credits:    '.$data['total_credits']);
                } elseif (method_exists($data, 'toArray')) {
                    $this->line(' - records count:    '.count($data));
                } else {
                    $this->line(' - data payload:     '.json_encode($data, JSON_UNESCAPED_UNICODE));
                }
            } else {
                $this->error(' - status:           FAILED');
                $this->error(' - message:          '.$structuredDbResult['message']);
                $this->printStructuredSuggestions($detected);
            }
            $this->newLine();
        }

        // Step 4: RAG Retrieval
        $shouldFallbackToRag = ($route['source'] === 'structured_db') &&
            (! $structuredDbResult || ! ($structuredDbResult['success'] ?? false));

        if (in_array($route['source'], ['rag', 'hybrid']) || $shouldFallbackToRag) {
            $this->info('RAG Retrieval (Qdrant):');
            $ragFilters = [];
            if (! empty($detected['cohort'])) {
                $ragFilters['cohort'] = $detected['cohort'];
            }
            if (! empty($detected['major'])) {
                $ragFilters['major'] = $detected['major'];
            }

            $ragChunks = $ragRetrieval->retrieve($normalizedQuestion, $ragFilters);

            // Log fallback attempts if present
            if (! empty($ragRetrieval->fallbackAttemptsLogs)) {
                $this->info('RAG Fallback Sequence Attempts:');
                foreach ($ragRetrieval->fallbackAttemptsLogs as $idx => $attempt) {
                    $filterStr = json_encode($attempt['filter'], JSON_UNESCAPED_UNICODE);
                    $this->line('   ['.($idx + 1)."] Attempt: {$attempt['attempt_name']} | Filters: {$filterStr} | Results: {$attempt['result_count']} | Top Score: ".number_format($attempt['top_score'], 2));
                }
                $this->newLine();
            }

            $this->line(' - points retrieved: '.count($ragChunks));
            foreach ($ragChunks as $index => $chunk) {
                $score = number_format($chunk['score'] ?? 0.0, 2);
                $rerank = isset($chunk['rerank_score']) ? number_format($chunk['rerank_score'], 2) : 'N/A';
                $this->line('   ['.($index + 1)."] Document: {$chunk['document_name']} | Score: {$score} | Rerank: {$rerank}");
                $this->line('       Text: '.mb_substr(trim($chunk['chunk_text']), 0, 100).'...');
            }
            $this->newLine();
        }

        // Step 5: Compose Answer
        $this->info('Answer Composition:');
        $composed = $composer->compose(
            $question,
            $normalizedQuestion,
            $route,
            $structuredDbResult,
            $ragChunks
        );

        $this->line(' - provider used:    '.$composed['model_provider'].' ('.$composed['model_name'].')');
        $this->line(' - latency:          '.$composed['latency_ms'].' ms');
        $this->newLine();

        $this->info('Final Answer Output:');
        $this->comment($composed['answer_text']);
        $this->newLine();

        $this->info('=================================================================');

        // Save conversation context
        if ($route['source'] !== 'none') {
            $knowledgeType = null;
            $loaiTaiLieu = null;
            $policyTopic = $detected['policy_topic'] ?? null;
            $cohort = $detected['cohort'] ?? null;
            $major = $detected['major'] ?? null;

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

            $structured_found = ! empty($structuredDbResult) && ($structuredDbResult['success'] ?? false);
            $rag_found = ! empty($ragChunks);
            $lastSuccess = $structured_found || $rag_found;

            $contextService->setContext($conversationId, [
                'last_intent' => $route['intent'],
                'last_knowledge_type' => $knowledgeType,
                'last_loai_tai_lieu' => $loaiTaiLieu,
                'last_khoa_hoc' => $cohort,
                'last_nganh' => $major,
                'last_policy_topic' => $policyTopic,
                'last_rewritten_query' => $ragRetrieval->lastRewrittenQuery ?? $normalizedQuestion,
                'last_question' => $normalizedQuestion,
                'last_source' => $route['source'],
                'updated_at' => time(),
                'last_success' => $lastSuccess,
            ]);
        }

        return self::SUCCESS;
    }

    /**
     * Print useful fallback tips when structured retrieval fails.
     */
    protected function printStructuredSuggestions(array $detected): void
    {
        $this->newLine();
        $this->info('=== DB DIAGNOSTIC SUGGESTIONS ===');

        $cohort = $detected['cohort'];
        $major = $detected['major'];

        if ($cohort) {
            $programsCount = TrainingProgram::whereHas('cohort', function ($q) use ($cohort) {
                $q->where('cohort_name', 'like', "%{$cohort}%");
            })->count();
            $this->line("- Training programs with cohort '{$cohort}': {$programsCount} records.");
        }

        if ($major) {
            $matchingMajors = Major::where('name', 'like', "%{$major}%")
                ->orWhere('normalized_name', 'like', "%{$major}%")
                ->pluck('name')
                ->toArray();
            if (empty($matchingMajors)) {
                $this->warn("- No matching Majors found in database for term: '{$major}'.");
            } else {
                $this->line('- Matching Majors in DB: '.implode(', ', $matchingMajors));
            }
        }

        if ($cohort && $major) {
            $this->line('- Checking general cohort/major training documents in Source Documents...');
            $docs = SourceDocument::where('document_type', 'training_program')
                ->where('cohort', 'like', "%{$cohort}%")
                ->get();
            $this->line('  Found '.$docs->count()." source documents matching document_type=training_program and cohort='{$cohort}':");
            foreach ($docs as $d) {
                $this->line("   * ID: {$d->id} | Title: {$d->title} | Path: {$d->file_path}");
            }
        }
    }
}
