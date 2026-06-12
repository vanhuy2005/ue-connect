<?php

namespace App\AI\HcmueChatbot\Chat;

use Illuminate\Support\Facades\Cache;

class ConversationContextService
{
    private const CACHE_PREFIX = 'hcmue_chat_context_';

    private const TTL_SECONDS = 3600; // 1 hour

    /**
     * Get the conversation context.
     *
     * @return array{
     *   last_intent: ?string,
     *   last_knowledge_type: ?string,
     *   last_loai_tai_lieu: ?string,
     *   last_khoa_hoc: ?string,
     *   last_nganh: ?string,
     *   last_policy_topic: ?string,
     *   last_rewritten_query: ?string,
     *   last_question: ?string,
     *   last_source: ?string
     * }
     */
    public function getContext(int|string $sessionId): array
    {
        $key = self::CACHE_PREFIX.$sessionId;

        return Cache::get($key, [
            'last_intent' => null,
            'last_knowledge_type' => null,
            'last_loai_tai_lieu' => null,
            'last_khoa_hoc' => null,
            'last_nganh' => null,
            'last_policy_topic' => null,
            'last_rewritten_query' => null,
            'last_question' => null,
            'last_source' => null,
            'updated_at' => 0,
            'last_success' => false,
        ]);
    }

    /**
     * Set the conversation context.
     */
    public function setContext(int|string $sessionId, array $context): void
    {
        $key = self::CACHE_PREFIX.$sessionId;
        Cache::put($key, $context, self::TTL_SECONDS);
    }

    /**
     * Clear the conversation context.
     */
    public function clearContext(int|string $sessionId): void
    {
        $key = self::CACHE_PREFIX.$sessionId;
        Cache::forget($key);
    }

    /**
     * Detect if a user message is a follow-up and return resolved question and entities.
     *
     * @return array{
     *   is_follow_up: bool,
     *   resolved_question: string,
     *   intent: ?string,
     *   knowledge_type: ?string,
     *   policy_topic: ?string,
     *   major: ?string,
     *   cohort: ?string,
     *   source: ?string,
     *   overridden_cohort: ?string,
     *   overridden_major: ?string
     * }
     */
    public function resolveFollowUp(string $message, string $sessionId, QuestionNormalizerService $normalizer): array
    {
        $context = $this->getContext($sessionId);

        $isFollowUp = false;
        $resolvedQuestion = $message;
        $intent = null;
        $knowledgeType = null;
        $policyTopic = null;
        $major = null;
        $cohort = null;
        $source = null;
        $overriddenCohort = null;
        $overriddenMajor = null;

        $now = time();
        $lastUpdatedAt = (int) ($context['updated_at'] ?? 0);
        $lastSuccess = (bool) ($context['last_success'] ?? false);
        $timeDiff = $now - $lastUpdatedAt;

        // Context must be under 10 minutes (600s) and previous answer must be successful
        if ($context['last_intent'] && $timeDiff < 600 && $lastSuccess === true) {
            $isFollowUp = $this->detectFollowUp($message);
            if ($isFollowUp) {
                $intent = $context['last_intent'];
                $knowledgeType = $context['last_knowledge_type'];
                $policyTopic = $context['last_policy_topic'];
                $major = $context['last_nganh'];
                $cohort = $context['last_khoa_hoc'];
                $source = $context['last_source'];

                // Normalize message to extract new entities
                $norm = $normalizer->normalize($message);
                $detected = $norm['detected_terms'];

                $overriddenCohort = $detected['cohort'] ?? null;
                $overriddenMajor = $detected['major'] ?? null;

                // Handle "Khóa trước" decrement
                if (preg_match('/kh[oó]a\s+trước/iu', $message)) {
                    if ($cohort && preg_match('/(\d{4})\s*-\s*Kh[oó]a\s*(\d+)/iu', $cohort, $cohortM)) {
                        $prevNum = (int) $cohortM[2] - 1;
                        $prevYear = 1974 + $prevNum;

                        // Check exact spelling in Qdrant catalog if available
                        $cohortsCatalogList = app(CohortMajorCatalogService::class)->getCatalog()['cohorts'] ?? [];
                        $matchedFromCatalog = null;
                        foreach ($cohortsCatalogList as $cItem) {
                            if (preg_match('/\b'.$prevNum.'\b/u', $cItem)) {
                                $matchedFromCatalog = $cItem;
                                break;
                            }
                        }
                        $overriddenCohort = $matchedFromCatalog ?: "{$prevYear} - Khóa {$prevNum}";
                    }
                }

                $cohort = $overriddenCohort ?? $cohort;
                $major = $overriddenMajor ?? $major;

                // Reconstruct resolved question
                $lastQuestion = $context['last_question'] ?? $message;
                $resolvedQuestion = $lastQuestion;

                if ($overriddenCohort) {
                    $resolvedQuestion = $this->replaceCohortInQuestion($resolvedQuestion, $overriddenCohort);
                }
                if ($overriddenMajor) {
                    $resolvedQuestion = $this->replaceMajorInQuestion($resolvedQuestion, $overriddenMajor);
                }
            }
        }

        return [
            'is_follow_up' => $isFollowUp,
            'resolved_question' => $resolvedQuestion,
            'intent' => $intent,
            'knowledge_type' => $knowledgeType,
            'policy_topic' => $policyTopic,
            'major' => $major,
            'cohort' => $cohort,
            'source' => $source,
            'overridden_cohort' => $overriddenCohort,
            'overridden_major' => $overriddenMajor,
        ];
    }

    /**
     * Detect if a message is a follow-up query.
     */
    private function detectFollowUp(string $message): bool
    {
        $lower = mb_strtolower($message, 'UTF-8');

        if (mb_strlen($message) > 60) {
            return false;
        }

        // transition prefix/suffix patterns
        $isTransition = false;
        $transitionPatterns = [
            '/^còn\s+/iu',
            '/^thế\s+/iu',
            '/^vậy\s+/iu',
            '/^thế\s+còn/iu',
            '/sao$/iu',
            '/thì sao/iu',
        ];
        foreach ($transitionPatterns as $pattern) {
            if (preg_match($pattern, $lower)) {
                $isTransition = true;
                break;
            }
        }

        // demonstratives or context-dependent phrases
        $isContextDependent = false;
        $contextDependentPhrases = [
            'cái đó', 'điều này', 'áp dụng cho', 'như thế', 'như vậy', 'thì thế nào',
            'có áp dụng', 'áp dụng không',
        ];
        foreach ($contextDependentPhrases as $phrase) {
            if (str_contains($lower, $phrase)) {
                $isContextDependent = true;
                break;
            }
        }

        // check if it clearly introduces a new topic keyword
        $newTopicKeywords = [
            'đăng ký học phần', 'nghiên cứu khoa học', 'học bổng', 'học phí',
            'quyết định', 'văn bản', 'tin chỉ tốt nghiệp', 'môn học', 'học phần',
            'chuẩn đầu ra', 'rèn luyện', 'cảnh báo học vụ', 'buộc thôi học',
        ];

        if ($isTransition || $isContextDependent) {
            foreach ($newTopicKeywords as $kw) {
                if (str_contains($lower, $kw)) {
                    return false;
                }
            }

            return true;
        }

        // Also check if extremely short and contains cohort or major
        $words = preg_split('/\s+/u', trim($lower));
        if (count($words) <= 4) {
            if (preg_match('/k\d{2}/i', $lower) || preg_match('/khóa\s*\d{2}/iu', $lower) || preg_match('/khoá\s*\d{2}/iu', $lower) || str_contains($lower, 'ngành')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Replace cohort in query.
     */
    private function replaceCohortInQuestion(string $question, string $newCohort): string
    {
        $pattern = '/(?:20\d{2}\s*-\s*)?(?:khóa|khoá|k)\s*\d{2}/iu';
        if (preg_match($pattern, $question)) {
            return preg_replace($pattern, $newCohort, $question);
        }

        return $question.' đối với '.$newCohort;
    }

    /**
     * Replace major in query.
     */
    private function replaceMajorInQuestion(string $question, string $newMajor): string
    {
        return $question.' ngành '.$newMajor;
    }
}
