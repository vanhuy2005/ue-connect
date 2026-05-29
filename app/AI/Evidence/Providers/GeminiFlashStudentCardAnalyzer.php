<?php

namespace App\AI\Evidence\Providers;

use App\AI\Evidence\Contracts\EvidenceAnalyzer;
use App\AI\Evidence\DTO\EvidenceAnalysisResultData;
use App\AI\Evidence\DTO\ExtractedStudentCardFieldsData;
use App\AI\Evidence\Services\StudentCardMatchingService;
use App\Enums\DetectedDocumentType;
use App\Enums\EvidenceRiskFlag;
use App\Models\VerificationEvidence;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiFlashStudentCardAnalyzer implements EvidenceAnalyzer
{
    private const SYSTEM_PROMPT = 'You are assisting UEConnect identity verification. Analyze the student card image. Extract only visible information. Do not guess. If unclear, return null. Return strict JSON only, no markdown.';

    private const USER_PROMPT = 'Extract student card fields. Return JSON with these keys: document_type_detected (student_card or unknown), document_type_confidence (float 0-1), school_name, full_name, student_code, faculty, academic_program, cohort, portrait_present_hint (boolean or null), risk_flags (array of strings), review_summary (string).';

    public function __construct(
        private readonly StudentCardMatchingService $matcher,
    ) {}

    public function analyze(VerificationEvidence $evidence): EvidenceAnalysisResultData
    {
        if (! config('ai-verification.privacy.allow_external_provider', false)) {
            return EvidenceAnalysisResultData::manualReview(EvidenceRiskFlag::ExternalProviderDisabled);
        }

        $apiKey = config('ai-verification.providers.gemini_flash.api_key');

        if (empty($apiKey)) {
            return EvidenceAnalysisResultData::manualReview(EvidenceRiskFlag::ExternalProviderUnavailable);
        }

        $mediaFile = $evidence->mediaFile;

        if ($mediaFile === null) {
            return EvidenceAnalysisResultData::manualReview(EvidenceRiskFlag::ManualReviewRequired);
        }

        try {
            $imageBase64 = base64_encode(Storage::disk('private')->get($mediaFile->path));
            $mimeType = $mediaFile->mime_type ?? 'image/jpeg';

            $model = config('ai-verification.providers.gemini_flash.model', 'gemini-2.0-flash');
            $baseUrl = rtrim(config('ai-verification.providers.gemini_flash.base_url'), '/');
            $timeout = (int) config('ai-verification.providers.gemini_flash.timeout_seconds', 30);

            $response = Http::timeout($timeout)
                ->post("{$baseUrl}/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [[
                        'parts' => [
                            ['text' => self::SYSTEM_PROMPT.'\n\n'.self::USER_PROMPT],
                            ['inline_data' => ['mime_type' => $mimeType, 'data' => $imageBase64]],
                        ],
                    ]],
                    'generationConfig' => [
                        'response_mime_type' => 'application/json',
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('GeminiFlashStudentCardAnalyzer: API error.', ['status' => $response->status()]);

                return EvidenceAnalysisResultData::manualReview(EvidenceRiskFlag::ExternalProviderUnavailable);
            }

            $body = $response->json();
            $rawText = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (! is_string($rawText)) {
                return EvidenceAnalysisResultData::manualReview(EvidenceRiskFlag::ExternalProviderUnavailable);
            }

            $decoded = json_decode($rawText, true);

            if (! is_array($decoded)) {
                return EvidenceAnalysisResultData::manualReview(EvidenceRiskFlag::ExternalProviderUnavailable);
            }

            $extracted = ExtractedStudentCardFieldsData::fromArray($decoded);
            $matchResult = $this->matcher->match($extracted, $evidence->verificationRequest);
            $allFlags = $matchResult['flags'];

            $docType = ($decoded['document_type_detected'] ?? 'unknown') === 'student_card'
                ? DetectedDocumentType::StudentCard
                : DetectedDocumentType::Unknown;

            return new EvidenceAnalysisResultData(
                recommendation: $matchResult['recommendation'],
                confidenceScore: $matchResult['score'],
                documentTypeDetected: $docType,
                documentTypeConfidence: (float) ($decoded['document_type_confidence'] ?? 0.0),
                ocrText: null,
                extractedFields: $extracted,
                matchResult: $matchResult['details'],
                riskFlags: $allFlags,
                reviewSummary: $decoded['review_summary'] ?? null,
                provider: 'gemini_flash',
                modelName: $model,
            );

        } catch (\Throwable $e) {
            Log::warning('GeminiFlashStudentCardAnalyzer: Failed.', ['error' => $e->getMessage()]);

            return EvidenceAnalysisResultData::manualReview(EvidenceRiskFlag::ExternalProviderUnavailable);
        }
    }
}
