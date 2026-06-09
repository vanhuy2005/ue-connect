<?php

namespace App\AI\HcmueChatbot\Chat;

use App\AI\HcmueChatbot\LLM\LlmGateway;
use App\AI\HcmueChatbot\Prompts\AnswerComposerPrompt;
use Illuminate\Support\Facades\Log;

class AnswerComposerService
{
    /**
     * Compose an answer from structured DB result and RAG context.
     *
     * @return array{
     *   answer_text: string,
     *   model_provider: string,
     *   model_name: string,
     *   latency_ms: int,
     *   input_tokens: int,
     *   output_tokens: int,
     *   total_tokens: int
     * }
     */
    public function compose(
        string $userQuestion,
        string $normalizedQuestion,
        array $routerResult,
        ?array $structuredDbResult,
        array $ragChunks
    ): array {
        $startTime = microtime(true);

        $dbResultText = $this->formatStructuredDbResult($structuredDbResult);
        $ragContextText = $this->formatRagContext($ragChunks);

        $prompt = AnswerComposerPrompt::render([
            'user_question' => $userQuestion,
            'normalized_question' => $normalizedQuestion,
            'structured_db_result' => $dbResultText,
            'rag_context' => $ragContextText,
        ]);

        try {
            $llm = LlmGateway::driver();
            $response = $llm->generate($prompt, ['temperature' => 0.3]);

            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            return [
                'answer_text' => $response['text'],
                'model_provider' => env('AI_LLM_DRIVER', 'gemini'),
                'model_name' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
                'latency_ms' => $latencyMs,
                'input_tokens' => $response['usage']['input_tokens'] ?? 0,
                'output_tokens' => $response['usage']['output_tokens'] ?? 0,
                'total_tokens' => $response['usage']['total_tokens'] ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('AnswerComposerService failed: '.$e->getMessage());
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            return [
                'answer_text' => 'Xin lỗi, có lỗi xảy ra khi tạo câu trả lời. Vui lòng thử lại.',
                'model_provider' => env('AI_LLM_DRIVER', 'gemini'),
                'model_name' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
                'latency_ms' => $latencyMs,
                'input_tokens' => 0,
                'output_tokens' => 0,
                'total_tokens' => 0,
            ];
        }
    }

    /**
     * Format structured DB result into text for the prompt.
     */
    private function formatStructuredDbResult(?array $result): string
    {
        if (empty($result) || ! ($result['success'] ?? false)) {
            $message = $result['message'] ?? 'Không có dữ liệu CTĐT.';

            return "[Structured DB] {$message}";
        }

        $data = $result['data'];
        $meta = $result['metadata'] ?? [];

        if (is_null($data)) {
            return '[Structured DB] Không tìm thấy dữ liệu.';
        }

        $lines = ['[Structured DB]'];

        if (! empty($meta['program_title'])) {
            $lines[] = 'Chương trình đào tạo: '.$meta['program_title'];
        }

        // Handle Eloquent Collection or array
        if (is_iterable($data) && ! is_array($data)) {
            $dataArray = $data->toArray();
        } elseif (is_array($data)) {
            $dataArray = $data;
        } else {
            // Single model — convert to array
            $dataArray = method_exists($data, 'toArray') ? $data->toArray() : (array) $data;
        }

        // Handle scalar total_credits
        if (isset($dataArray['total_credits'])) {
            $lines[] = 'Tổng tín chỉ: '.$dataArray['total_credits'];

            return implode("\n", $lines);
        }

        // List of courses
        if (! empty($dataArray) && isset($dataArray[0])) {
            $lines[] = sprintf('Danh sách học phần (%d học phần):', count($dataArray));
            foreach ($dataArray as $course) {
                $courseArr = is_array($course) ? $course : (array) $course;
                $code = $courseArr['course_code'] ?? '';
                $name = $courseArr['course_name'] ?? '';
                $credits = $courseArr['credits'] ?? '';
                $type = $courseArr['course_type'] ?? '';
                $semester = $courseArr['semester'] ?? '';
                $lines[] = "  - [{$code}] {$name} | {$credits} TC | {$type} | Học kỳ {$semester}";
            }
        } else {
            // Single program or object
            foreach ($dataArray as $key => $val) {
                if (is_scalar($val)) {
                    $lines[] = "{$key}: {$val}";
                }
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Format RAG chunks into context text for the prompt.
     */
    private function formatRagContext(array $chunks): string
    {
        if (empty($chunks)) {
            return '[RAG Context] Không tìm thấy quy định liên quan trong tài liệu đã lập chỉ mục.';
        }

        $lines = ['[RAG Context]'];
        foreach ($chunks as $i => $chunk) {
            $docName = $chunk['document_name'] ?? 'Tài liệu không tên';
            $article = $chunk['article'] ?? null;
            $page = $chunk['page_start'] ? 'tr.'.$chunk['page_start'] : '';
            $score = isset($chunk['score']) ? sprintf('%.2f', $chunk['score']) : '';

            $source = trim("{$docName}".($article ? ", {$article}" : '').($page ? ", {$page}" : ''));
            $lines[] = '--- Đoạn '.($i + 1)." [{$source}] (độ tương đồng: {$score}) ---";
            $lines[] = $chunk['chunk_text'] ?? '';
        }

        return implode("\n", $lines);
    }
}
