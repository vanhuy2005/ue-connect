<?php

namespace App\AI\HcmueChatbot\Chat;

use App\AI\HcmueChatbot\LLM\LlmGateway;
use App\AI\HcmueChatbot\Prompts\StructuredQueryPlannerPrompt;
use Illuminate\Support\Facades\Log;

class StructuredQueryPlannerService
{
    /**
     * Build a structured query plan from a router result.
     *
     * @param  array  $routerResult  The result from QueryRouterService.
     * @param  string  $normalizedQuestion  The normalized question text.
     * @return array{
     *   query_type: string,
     *   filters: array,
     *   include: array,
     *   sort: array,
     *   requires_clarification: bool,
     *   clarification_question: ?string
     * }
     */
    public function plan(array $routerResult, string $normalizedQuestion): array
    {
        // If already flagged as clarification by router, propagate immediately
        if ($routerResult['source'] === 'none' && $routerResult['intent'] === 'clarification') {
            return $this->buildClarificationPlan($routerResult['missing_required_fields'] ?? []);
        }

        // Fast-path plan from detected entities (avoids extra LLM call)
        $fastPlan = $this->fastPathPlan($routerResult, $normalizedQuestion);
        if ($fastPlan !== null) {
            return $fastPlan;
        }

        // Use LLM to generate structured plan for complex cases
        return $this->llmPlan($routerResult, $normalizedQuestion);
    }

    /**
     * Fast-path plan builder using router entities directly.
     */
    private function fastPathPlan(array $routerResult, string $question): ?array
    {
        $entities = $routerResult['entities'] ?? [];
        $lower = mb_strtolower($question);

        // Determine query type from intent + question content
        $intent = $routerResult['intent'] ?? '';

        $filters = [
            'cohort' => $entities['cohort'] ?? null,
            'admission_year' => $entities['admission_year'] ?? null,
            'faculty' => $entities['faculty'] ?? null,
            'major' => $entities['major'] ?? null,
            'semester' => isset($entities['semester']) && $entities['semester'] ? (int) $entities['semester'] : null,
            'course_code' => $entities['course'] ?? $entities['course_code'] ?? null,
            'course_name' => $entities['course_name'] ?? null,
            'course_type' => null,
        ];

        if (! empty($filters['course_code']) || ! empty($filters['course_name'])) {
            return $this->buildPlan('find_course_detail', $filters);
        }

        // Determine specific query type from question content
        if (str_contains($lower, 'tổng tín chỉ') || str_contains($lower, 'bao nhiêu tín chỉ') || str_contains($lower, 'tổng số tín chỉ')) {
            return $this->buildPlan('get_program_total_credits', $filters);
        }

        if (str_contains($lower, 'học kỳ') && preg_match('/học kỳ\s*(\d+)/u', $lower, $m)) {
            $filters['semester'] = (int) $m[1];

            return $this->buildPlan('list_courses_by_semester', $filters);
        }

        if (str_contains($lower, 'môn tự chọn') || str_contains($lower, 'học phần tự chọn')) {
            $filters['course_type'] = 'elective';

            return $this->buildPlan('list_elective_courses', $filters);
        }

        if (str_contains($lower, 'môn bắt buộc') || str_contains($lower, 'học phần bắt buộc')) {
            $filters['course_type'] = 'required';

            return $this->buildPlan('list_required_courses', $filters);
        }

        if (str_contains($lower, 'chuẩn đầu ra') || str_contains($lower, 'learning outcomes')) {
            return $this->buildPlan('get_learning_outcomes', $filters);
        }

        if (str_contains($lower, 'so sánh') || str_contains($lower, 'khác nhau')) {
            return $this->buildPlan('compare_programs', $filters);
        }

        if ($intent === 'curriculum_course_lookup' || str_contains($lower, 'danh sách môn') || str_contains($lower, 'học những môn')) {
            return $this->buildPlan('list_curriculum_courses', $filters);
        }

        if ($intent === 'training_program_lookup') {
            return $this->buildPlan('find_training_program', $filters);
        }

        return null;
    }

    /**
     * Use LLM to generate a structured query plan.
     */
    private function llmPlan(array $routerResult, string $normalizedQuestion): array
    {
        try {
            $prompt = StructuredQueryPlannerPrompt::render([
                'router_json' => json_encode($routerResult, JSON_UNESCAPED_UNICODE),
                'normalized_question' => $normalizedQuestion,
            ]);

            $llm = LlmGateway::driver();
            $response = $llm->generate($prompt, ['json_mode' => true, 'temperature' => 0.1]);
            $text = trim($response['text']);

            // Strip markdown code fences
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
            $text = preg_replace('/\s*```$/', '', $text);

            $parsed = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($parsed)) {
                Log::warning('StructuredQueryPlannerService: LLM returned invalid JSON.', ['raw' => $text]);

                return $this->buildClarificationPlan(['unknown']);
            }

            return [
                'query_type' => $parsed['query_type'] ?? 'find_training_program',
                'filters' => $parsed['filters'] ?? [],
                'include' => $parsed['include'] ?? [],
                'sort' => $parsed['sort'] ?? [],
                'requires_clarification' => (bool) ($parsed['requires_clarification'] ?? false),
                'clarification_question' => $parsed['clarification_question'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('StructuredQueryPlannerService LLM call failed: '.$e->getMessage());

            return $this->buildClarificationPlan(['error']);
        }
    }

    /**
     * Build a standard query plan.
     */
    private function buildPlan(string $queryType, array $filters): array
    {
        return [
            'query_type' => $queryType,
            'filters' => $filters,
            'include' => [],
            'sort' => [],
            'requires_clarification' => false,
            'clarification_question' => null,
        ];
    }

    /**
     * Build a clarification plan when required info is missing.
     */
    private function buildClarificationPlan(array $missingFields): array
    {
        $fieldLabels = [
            'cohort' => 'khóa tuyển sinh (ví dụ: K48, K50, K51)',
            'major' => 'ngành học (ví dụ: Công nghệ thông tin, Sư phạm Toán)',
        ];

        $missingLabels = array_map(fn ($f) => $fieldLabels[$f] ?? $f, $missingFields);
        $questionText = count($missingLabels) > 0
            ? 'Bạn có thể cho mình biết thêm về '.implode(' và ', $missingLabels).' không?'
            : 'Bạn có thể cung cấp thêm thông tin về khóa học và ngành để mình tra chính xác hơn không?';

        return [
            'query_type' => 'clarification',
            'filters' => [],
            'include' => [],
            'sort' => [],
            'requires_clarification' => true,
            'clarification_question' => $questionText,
        ];
    }
}
