<?php

namespace App\AI\HcmueChatbot\Chat;

use App\AI\HcmueChatbot\LLM\LlmGateway;
use App\AI\HcmueChatbot\Prompts\AnswerComposerPrompt;
use App\AI\HcmueChatbot\Prompts\OllamaLocalSystemPrompt;
use App\Models\SourceDocument;
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

        $structured_found = $structuredDbResult['success'] ?? false;
        $rag_found = ! empty($ragChunks);
        $structured_error_message = (! $structured_found && ! empty($structuredDbResult)) ? ($structuredDbResult['message'] ?? null) : null;
        $rag_context_count = count($ragChunks);

        Log::info('AnswerComposerService: Context status', [
            'structured_found' => $structured_found,
            'rag_found' => $rag_found,
            'structured_error_message' => $structured_error_message,
            'structured_result_length' => $structured_found ? mb_strlen(json_encode($structuredDbResult)) : 0,
            'rag_context_count' => $rag_context_count,
        ]);

        // 1. Direct total credits query formatting
        if (! empty($structuredDbResult) && ($structuredDbResult['success'] ?? false)) {
            $meta = $structuredDbResult['metadata'] ?? [];
            if (($meta['type'] ?? '') === 'total_credits' && isset($structuredDbResult['data']['total_credits'])) {
                $credits = $structuredDbResult['data']['total_credits'];
                $cohort = $routerResult['entities']['cohort'] ?? 'K49';
                $major = $routerResult['entities']['major'] ?? 'Công nghệ thông tin';

                // Try to resolve the exact source document
                $sourceDoc = null;
                try {
                    $sourceDoc = SourceDocument::where('document_type', 'training_program')
                        ->where('cohort', 'like', "%{$cohort}%")
                        ->where('title', 'like', "%{$major}%")
                        ->first();
                    if (! $sourceDoc) {
                        $sourceDoc = SourceDocument::where('document_type', 'training_program')
                            ->where('cohort', 'like', "%{$cohort}%")
                            ->first();
                    }
                } catch (\Exception $e) {
                    Log::debug('AnswerComposerService: Could not query source_documents: '.$e->getMessage());
                }

                $sourceFileText = $sourceDoc
                    ? "\n\nNguồn trích dẫn: ".basename($sourceDoc->file_path ?: $sourceDoc->source_url)
                    : '';

                $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

                return [
                    'answer_text' => "Theo dữ liệu chương trình đào tạo đã lập chỉ mục, ngành {$major} khóa {$cohort} cần tích lũy {$credits} tín chỉ để tốt nghiệp.{$sourceFileText}",
                    'model_provider' => 'structured_db',
                    'model_name' => 'Database Query Engine',
                    'latency_ms' => $latencyMs,
                    'input_tokens' => 0,
                    'output_tokens' => 0,
                    'total_tokens' => 0,
                    'fallback_used' => false,
                    'fallback_provider' => null,
                ];
            }
        }

        // Grounding rule: Return static Vietnamese fallback response immediately if no context is found

        if (! $structured_found && ! $rag_found && $routerResult['source'] === 'structured_db') {
            $cohort = $routerResult['entities']['cohort'] ?? 'K49';
            $major = $routerResult['entities']['major'] ?? 'Công nghệ thông tin';
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            return [
                'answer_text' => "Mình chưa tìm thấy chương trình đào tạo {$cohort} ngành {$major} trong dữ liệu đã lập chỉ mục.",
                'model_provider' => $primaryProvider,
                'model_name' => LlmGateway::activeModelName($primaryProvider),
                'latency_ms' => $latencyMs,
                'input_tokens' => 0,
                'output_tokens' => 0,
                'total_tokens' => 0,
                'fallback_used' => false,
                'fallback_provider' => null,
            ];
        }

        if (! $structured_found && ! $rag_found) {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            return [
                'answer_text' => 'Xin lỗi, dữ liệu hiện tại không đề cập đến nội dung bạn đang tìm kiếm.',
                'model_provider' => $primaryProvider,
                'model_name' => LlmGateway::activeModelName($primaryProvider),
                'latency_ms' => $latencyMs,
                'input_tokens' => 0,
                'output_tokens' => 0,
                'total_tokens' => 0,
                'fallback_used' => false,
                'fallback_provider' => null,
            ];
        }

        // Apply prompt compaction for Ollama to prevent OOM on low-RAM machines
        if ($isOllama) {
            $ragChunks = $this->compactRagChunks($ragChunks);
        }

        $dbResultText = $this->formatStructuredDbResult($structuredDbResult, compact: $isOllama, ragFound: $rag_found);
        $ragContextText = $this->formatRagContext($ragChunks);

        Log::debug('AnswerComposerService: ===== PROMPT DEBUG =====', [
            'structured_db_result' => $dbResultText ?: '(empty)',
            'rag_context_length' => mb_strlen($ragContextText),
            'rag_context_preview' => mb_substr($ragContextText, 0, 300),
        ]);

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

                    return $this->safeErrorResult($startTime, $primaryProvider, $usedModel, $fallbackException);
                }
            } else {
                Log::error('AnswerComposerService failed (no fallback): '.$e->getMessage());

                return $this->safeErrorResult($startTime, $primaryProvider, $usedModel, $e);
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
    private function safeErrorResult(float $startTime, string $provider, string $model, ?\Throwable $exception = null): array
    {
        $message = 'Xin lỗi, có lỗi xảy ra khi tạo câu trả lời. Vui lòng thử lại sau.';

        if ($exception) {
            $errText = $exception->getMessage();
            if (str_contains($errText, '429') || str_contains($errText, 'RESOURCE_EXHAUSTED') || str_contains($errText, 'quota')) {
                $message = 'Lỗi kết nối AI (HTTP 429): Tài khoản của bạn đã vượt quá giới hạn tài nguyên (Quota Exceeded) hoặc bị giới hạn vùng/quốc gia. Vui lòng kiểm tra lại hạn mức tài khoản Google AI Studio hoặc đổi sang API Key khác.';
            } elseif (str_contains($errText, '400') || str_contains($errText, 'API_KEY_INVALID') || str_contains($errText, 'key not valid')) {
                $message = 'Lỗi kết nối AI (HTTP 400): API Key không hợp lệ hoặc thiếu. Vui lòng điền đúng GEMINI_API_KEY trong file .env và clear cache config.';
            } elseif (str_contains($errText, '503') || str_contains($errText, 'high demand') || str_contains($errText, 'temporary')) {
                $message = 'Lỗi kết nối với AI (HTTP 503): This model is currently experiencing high demand. Spikes in demand are usually temporary. Please try again later.';
            }
        }

        return [
            'answer_text' => $message,
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

    private function compactRagChunks(array $chunks): array
    {
        $limit = config('ai.ollama.rag_top_k', 4);

        $sliced = array_slice($chunks, 0, $limit);
        foreach ($sliced as &$chunk) {
            if (isset($chunk['chunk_text'])) {
                $maxLen = config('ai.ollama.chunk_max_chars', 1500);
                if (mb_strlen($chunk['chunk_text'], 'UTF-8') > $maxLen) {
                    $chunk['chunk_text'] = mb_substr($chunk['chunk_text'], 0, $maxLen, 'UTF-8')."\n...[Nội dung đoạn trích được cắt bớt để tối ưu hóa bộ nhớ local model]";
                }
            }
            if (isset($chunk['metadata'])) {
                $chunk['metadata'] = array_intersect_key($chunk['metadata'], array_flip([
                    'document_name', 'document_type', 'cohort', 'page_start', 'page_end', 'article', 'chapter',
                ]));
            }
        }

        return $sliced;
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
    private function formatStructuredDbResult(?array $result, bool $compact = false, bool $ragFound = false): string
    {
        if (empty($result) || ! ($result['success'] ?? false)) {
            if ($ragFound) {
                return '';
            }
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
            $page = ($chunk['page_start'] ?? null) ? 'tr.'.$chunk['page_start'] : '';
            $score = isset($chunk['score']) ? sprintf('%.2f', $chunk['score']) : '';

            $meta = $chunk['metadata'] ?? [];
            $cohort = $meta['cohort'] ?? $chunk['cohort'] ?? null;
            $major = $meta['major'] ?? null;

            $metaParts = [];
            if ($cohort) {
                $metaParts[] = "Khóa: {$cohort}";
            }
            if ($major) {
                $metaParts[] = "Ngành: {$major}";
            }
            $metaStr = ! empty($metaParts) ? ' ('.implode(', ', $metaParts).')' : '';

            $source = trim("{$docName}".($article ? ", {$article}" : '').($page ? ", {$page}" : ''));
            $lines[] = '--- Đoạn '.($i + 1)." [{$source}]{$metaStr} (độ tương đồng: {$score}) ---";
            $lines[] = $chunk['chunk_text'] ?? '';
        }

        return implode("\n", $lines);
    }
}
