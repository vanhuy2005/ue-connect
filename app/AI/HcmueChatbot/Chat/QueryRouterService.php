<?php

namespace App\AI\HcmueChatbot\Chat;

use App\AI\HcmueChatbot\LLM\LlmGateway;
use App\AI\HcmueChatbot\Prompts\QueryRouterPrompt;
use Illuminate\Support\Facades\Log;

class QueryRouterService
{
    /**
     * Route a normalized question to the appropriate data source.
     *
     * @return array{
     *   intent: string,
     *   source: string,
     *   confidence: float,
     *   entities: array,
     *   missing_required_fields: array,
     *   reason: string
     * }
     */
    public function route(string $normalizedQuestion, array $detectedTerms = []): array
    {
        // Fast-path rule-based routing for clear-cut cases (avoids LLM latency)
        $fastRoute = $this->fastPathRoute($normalizedQuestion, $detectedTerms);
        if ($fastRoute !== null) {
            Log::debug('QueryRouter: fast-path route applied.', ['source' => $fastRoute['source']]);

            return $fastRoute;
        }

        // LLM-based routing for ambiguous cases
        return $this->llmRoute($normalizedQuestion);
    }

    /**
     * Fast-path rule-based routing — avoids LLM for obvious patterns.
     *
     * @return array|null Returns a route array or null if uncertain.
     */
    private function fastPathRoute(string $question, array $detectedTerms): ?array
    {
        $lower = mb_strtolower($question);

        // Clearly out of scope
        $outOfScopeKeywords = [
            'ăn gì', 'thời tiết', 'bóng đá', 'phim', 'nhạc', 'game',
            'chứng khoán', 'cryptocurrency', 'nấu ăn', 'du lịch', 'mua sắm',
        ];
        foreach ($outOfScopeKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return $this->buildRoute('unsupported', 'none', 0.99, [], [], 'Câu hỏi ngoài phạm vi học vụ HCMUE.');
            }
        }

        // Policy / regulation topics → RAG
        $ragKeywords = [
            'học lại', 'học cải thiện', 'cảnh báo học tập', 'điều kiện tốt nghiệp',
            'tốt nghiệp', 'học bổng', 'rèn luyện', 'quy chế', 'quy định',
            'đăng ký học phần', 'hủy học phần', 'miễn giảm', 'sổ tay',
            'rớt môn', 'trượt môn', 'nợ môn', 'kỷ luật', 'thôi học',
            'xét học vụ', 'cảnh báo', 'không đạt', 'rớt',
        ];
        foreach ($ragKeywords as $kw) {
            if (mb_strpos($lower, $kw) !== false) {
                // If ALSO mentions specific program context → hybrid
                if ($detectedTerms['cohort'] && $detectedTerms['major']) {
                    return $this->buildRoute('hybrid', 'hybrid', 0.85, $detectedTerms, [], 'Câu hỏi kết hợp dữ liệu CTĐT và quy định học vụ.');
                }

                return $this->buildRoute('academic_policy', 'rag', 0.92, $detectedTerms, [], 'Câu hỏi về quy định/học vụ, dùng RAG.');
            }
        }

        // Missing cohort/major for structured queries
        $structuredKeywords = [
            'tín chỉ', 'học phần', 'môn học', 'chương trình đào tạo',
            'ngành', 'học kỳ', 'mã môn', 'danh sách môn', 'môn bắt buộc', 'môn tự chọn',
        ];
        $isStructuredQuestion = false;
        foreach ($structuredKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                $isStructuredQuestion = true;
                break;
            }
        }

        if ($isStructuredQuestion) {
            if (empty($detectedTerms['cohort']) || empty($detectedTerms['major'])) {
                $missing = [];
                if (empty($detectedTerms['cohort'])) {
                    $missing[] = 'cohort';
                }
                if (empty($detectedTerms['major'])) {
                    $missing[] = 'major';
                }

                return $this->buildRoute('clarification', 'none', 0.90, $detectedTerms, $missing, 'Thiếu thông tin khóa hoặc ngành để tra cứu CTĐT.');
            }

            return $this->buildRoute('curriculum_course_lookup', 'structured_db', 0.88, $detectedTerms, [], 'Câu hỏi về CTĐT, dùng structured DB.');
        }

        return null; // Let LLM decide
    }

    /**
     * Use LLM for routing when fast-path is inconclusive.
     */
    private function llmRoute(string $normalizedQuestion): array
    {
        try {
            $prompt = QueryRouterPrompt::render(['user_question' => $normalizedQuestion]);
            $llm = LlmGateway::driver();

            $response = $llm->generate($prompt, ['json_mode' => true, 'temperature' => 0.1]);
            $text = trim($response['text']);

            // Strip markdown code fences if present
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
            $text = preg_replace('/\s*```$/', '', $text);

            $parsed = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($parsed)) {
                Log::warning('QueryRouterService: LLM returned invalid JSON, falling back.', ['raw' => $text]);

                return $this->fallbackRoute();
            }

            return [
                'intent' => $parsed['intent'] ?? 'unsupported',
                'source' => $parsed['source'] ?? 'none',
                'confidence' => (float) ($parsed['confidence'] ?? 0.5),
                'entities' => $parsed['entities'] ?? [],
                'missing_required_fields' => $parsed['missing_required_fields'] ?? [],
                'reason' => $parsed['reason'] ?? '',
            ];
        } catch (\Exception $e) {
            Log::error('QueryRouterService LLM call failed: '.$e->getMessage());

            return $this->fallbackRoute();
        }
    }

    /**
     * Build a routing result array.
     */
    private function buildRoute(
        string $intent,
        string $source,
        float $confidence,
        array $entities,
        array $missingFields,
        string $reason
    ): array {
        return [
            'intent' => $intent,
            'source' => $source,
            'confidence' => $confidence,
            'entities' => $entities,
            'missing_required_fields' => $missingFields,
            'reason' => $reason,
        ];
    }

    /**
     * Safe fallback when routing fails.
     */
    private function fallbackRoute(): array
    {
        return $this->buildRoute('unsupported', 'none', 0.0, [], [], 'Không thể phân loại câu hỏi.');
    }
}
