<?php

namespace App\AI\Evidence\Services;

use App\AI\Evidence\DTO\ExtractedStudentCardFieldsData;
use App\Enums\EvidenceRiskFlag;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaStudentCardNormalizer
{
    private const PROMPT_TEMPLATE = <<<'PROMPT'
Bạn là hệ thống hỗ trợ xác thực thẻ sinh viên UEConnect.

Chỉ sử dụng OCR_TEXT được cung cấp.
Không bịa thông tin.
Nếu không chắc, trả null.
Chỉ trả JSON hợp lệ, không có markdown hay giải thích.

OCR_TEXT:
"""
{ocr_text}
"""

JSON schema (trả đúng định dạng này):
{
  "document_type_detected": "student_card" hoặc "unknown",
  "school_name": "string hoặc null",
  "full_name": "string hoặc null",
  "student_code": "string hoặc null",
  "faculty": "string hoặc null",
  "academic_program": "string hoặc null",
  "cohort": "string hoặc null",
  "portrait_present_hint": true/false/null,
  "risk_flags": [],
  "review_summary": "string"
}
PROMPT;

    /**
     * Normalize OCR text using Ollama. Never sends image.
     *
     * @return array{data: ?ExtractedStudentCardFieldsData, flags: list<EvidenceRiskFlag>, raw: ?array<string, mixed>}
     */
    public function normalize(string $ocrText): array
    {
        if (! config('ai-verification.local_hybrid.ollama_enabled', false)) {
            return ['data' => null, 'flags' => [], 'raw' => null];
        }

        $baseUrl = rtrim(config('ai-verification.local_hybrid.ollama_base_url', 'http://127.0.0.1:11434'), '/');
        $model = config('ai-verification.local_hybrid.ollama_model', 'qwen2.5:1.5b');
        $timeout = (int) config('ai-verification.local_hybrid.ollama_timeout_seconds', 20);

        $prompt = str_replace('{ocr_text}', $ocrText, self::PROMPT_TEMPLATE);

        try {
            $response = Http::timeout($timeout)
                ->post($baseUrl.'/api/generate', [
                    'model' => $model,
                    'prompt' => $prompt,
                    'stream' => false,
                    'format' => 'json',
                ]);

            if (! $response->successful()) {
                Log::warning('OllamaStudentCardNormalizer: Non-success response from Ollama.', [
                    'status' => $response->status(),
                ]);

                return ['data' => null, 'flags' => [EvidenceRiskFlag::OllamaUnavailable], 'raw' => null];
            }

            $body = $response->json();
            $rawResponse = $body['response'] ?? null;

            if (! is_string($rawResponse)) {
                return ['data' => null, 'flags' => [EvidenceRiskFlag::OllamaUnavailable], 'raw' => null];
            }

            $decoded = json_decode($rawResponse, true);

            if (! is_array($decoded)) {
                // Try to parse direct response body as JSON
                $decoded = json_decode(json_encode($body), true);
            }

            if (! is_array($decoded)) {
                return ['data' => null, 'flags' => [], 'raw' => null];
            }

            $extracted = ExtractedStudentCardFieldsData::fromArray($decoded);

            return ['data' => $extracted, 'flags' => [], 'raw' => $decoded];

        } catch (\Throwable $e) {
            Log::warning('OllamaStudentCardNormalizer: Ollama unavailable.', [
                'error' => $e->getMessage(),
            ]);

            return ['data' => null, 'flags' => [EvidenceRiskFlag::OllamaUnavailable], 'raw' => null];
        }
    }
}
