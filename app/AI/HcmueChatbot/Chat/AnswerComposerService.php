<?php

namespace App\AI\HcmueChatbot\Chat;

use App\AI\HcmueChatbot\LLM\LlmGateway;
use App\AI\HcmueChatbot\Prompts\AnswerComposerPrompt;
use App\AI\HcmueChatbot\Prompts\OllamaLocalSystemPrompt;
use Illuminate\Support\Facades\Log;

class AnswerComposerService
{
    /**
     * Compose an answer from structured DB result and RAG context.
     *
     * Automatically uses prompt compaction when the active provider is Ollama
     * to avoid OOM on constrained hardware. Falls back to a cloud provider
     * if Ollama fails and OLLAMA_FALLBACK_ENABLED=true.
     *
     * @return array{
     *   answer_text: string,
     *   model_provider: string,
     *   model_name: string,
     *   latency_ms: int,
     *   input_tokens: int,
     *   output_tokens: int,
     *   total_tokens: int,
     *   fallback_used: bool,
     *   fallback_provider: ?string
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

        $gateway = LlmGateway::driverWithFallback();
        $primaryProvider = $gateway['primary'];
        $isOllama = $primaryProvider === 'ollama';

        // Apply prompt compaction for Ollama to prevent OOM on low-RAM machines
        if ($isOllama) {
            $ragChunks = $this->compactRagChunks($ragChunks);
        }

        $dbResultText = $this->formatStructuredDbResult($structuredDbResult, compact: $isOllama);
        $ragContextText = $this->formatRagContext($ragChunks);

        $prompt = AnswerComposerPrompt::render([
            'user_question' => $userQuestion,
            'normalized_question' => $normalizedQuestion,
            'structured_db_result' => $dbResultText,
            'rag_context' => $ragContextText,
        ]);

        // Truncate combined context for Ollama if needed
        if ($isOllama) {
            $prompt = $this->truncatePrompt($prompt);
        }

        $systemInstruction = $isOllama
            ? OllamaLocalSystemPrompt::render()
            : null;

        $generateOptions = ['temperature' => 0.3];
        if ($systemInstruction !== null) {
            $generateOptions['system_instruction'] = $systemInstruction;
        }

        $usedProvider = $primaryProvider;
        $usedModel = LlmGateway::activeModelName($primaryProvider);
        $fallbackUsed = false;
        $fallbackProvider = null;

        try {
            $llm = $gateway['provider'];
            $response = $llm->generate($prompt, $generateOptions);
        } catch (\Exception $e) {
            if ($gateway['fallback_enabled']) {
                $fallbackProvider = $gateway['fallback_provider'];

                Log::warning('OllamaProvider failed; falling back to cloud provider.', [
                    'primary' => $primaryProvider,
                    'fallback' => $fallbackProvider,
                    'error' => $e->getMessage(),
                ]);

                try {
                    $fallbackLlm = LlmGateway::driver($fallbackProvider);
                    $response = $fallbackLlm->generate($prompt, ['temperature' => 0.3]);
                    $usedProvider = $fallbackProvider;
                    $usedModel = LlmGateway::activeModelName($fallbackProvider);
                    $fallbackUsed = true;
                } catch (\Exception $fallbackException) {
                    Log::error('Fallback provider also failed: '.$fallbackException->getMessage());

                    return $this->safeErrorResult($startTime, $primaryProvider, $usedModel);
                }
            } else {
                Log::error('AnswerComposerService failed (no fallback): '.$e->getMessage());

                return $this->safeErrorResult($startTime, $primaryProvider, $usedModel);
            }
        }

        $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

        return [
            'answer_text' => $response['text'],
            'model_provider' => $usedProvider,
            'model_name' => $usedModel,
            'latency_ms' => $latencyMs,
            'input_tokens' => $response['usage']['input_tokens'] ?? 0,
            'output_tokens' => $response['usage']['output_tokens'] ?? 0,
            'total_tokens' => $response['usage']['total_tokens'] ?? 0,
            'fallback_used' => $fallbackUsed,
            'fallback_provider' => $fallbackProvider,
        ];
    }

    /**
     * Build a safe error response when all providers fail.
     *
     * @return array{answer_text: string, model_provider: string, model_name: string, latency_ms: int, input_tokens: int, output_tokens: int, total_tokens: int, fallback_used: bool, fallback_provider: null}
     */
    private function safeErrorResult(float $startTime, string $provider, string $model): array
    {
        return [
            'answer_text' => 'Xin lỗi, có lỗi xảy ra khi tạo câu trả lời. Vui lòng thử lại sau.',
            'model_provider' => $provider,
            'model_name' => $model,
            'latency_ms' => (int) round((microtime(true) - $startTime) * 1000),
            'input_tokens' => 0,
            'output_tokens' => 0,
            'total_tokens' => 0,
            'fallback_used' => false,
            'fallback_provider' => null,
        ];
    }

    /**
     * Limit RAG chunks to OLLAMA_RAG_TOP_K for prompt compaction.
     *
     * @param  array<int, array<string, mixed>>  $chunks
     * @return array<int, array<string, mixed>>
     */
    private function compactRagChunks(array $chunks): array
    {
        $limit = config('ai.ollama.rag_top_k', 4);

        return array_slice($chunks, 0, $limit);
    }

    /**
     * Truncate prompt to OLLAMA_MAX_CONTEXT_CHARS to avoid OOM.
     */
    private function truncatePrompt(string $prompt): string
    {
        $maxChars = config('ai.ollama.max_context_chars', 12000);

        if (mb_strlen($prompt) <= $maxChars) {
            return $prompt;
        }

        return mb_substr($prompt, 0, $maxChars).'\n\n[Context tự động rút gọn do giới hạn bộ nhớ]';
    }

    /**
     * Format structured DB result into text for the prompt.
     *
     * @param  bool  $compact  When true, limits course list to 20 to save tokens (Ollama mode).
     */
    private function formatStructuredDbResult(?array $result, bool $compact = false): string
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
            $displayData = $compact ? array_slice($dataArray, 0, 20) : $dataArray;
            $lines[] = sprintf('Danh sách học phần (%d học phần):', count($dataArray));
            foreach ($displayData as $course) {
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
