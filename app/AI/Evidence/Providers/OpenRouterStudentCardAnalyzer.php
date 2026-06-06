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

class OpenRouterStudentCardAnalyzer implements EvidenceAnalyzer
{
    private const USER_PROMPT = 'Analyze the student card image. Return strict JSON only with keys: document_type_detected (student_card or unknown), document_type_confidence (float), school_name, full_name, student_code, faculty, academic_program, cohort, portrait_present_hint (boolean or null), risk_flags (array), review_summary.';

    public function __construct(
        private readonly StudentCardMatchingService $matcher,
    ) {}

    public function analyze(VerificationEvidence $evidence): EvidenceAnalysisResultData
    {
        if (! config('ai-verification.privacy.allow_external_provider', false)) {
            return EvidenceAnalysisResultData::manualReview(EvidenceRiskFlag::ExternalProviderDisabled);
        }

        $apiKey = config('ai-verification.providers.openrouter.api_key');
        $model = config('ai-verification.providers.openrouter.model');

        if (empty($apiKey) || empty($model)) {
            return EvidenceAnalysisResultData::manualReview(EvidenceRiskFlag::ExternalProviderUnavailable);
        }

        $mediaFile = $evidence->mediaFile;

        if ($mediaFile === null) {
            return EvidenceAnalysisResultData::manualReview(EvidenceRiskFlag::ManualReviewRequired);
        }

        try {
            $diskName = config('media.private_disk', 'private');
            $fileContents = Storage::disk($diskName)->get($mediaFile->path);
            if ($fileContents === null) {
                throw new \Exception("File not found on private storage disk [{$diskName}]: {$mediaFile->path}");
            }
            $imageBase64 = base64_encode($fileContents);
            $mimeType = $mediaFile->mime_type ?? 'image/jpeg';
            $dataUri = "data:{$mimeType};base64,{$imageBase64}";

            $baseUrl = rtrim(config('ai-verification.providers.openrouter.base_url'), '/');
            $timeout = (int) config('ai-verification.providers.openrouter.timeout_seconds', 30);

            $response = Http::timeout($timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$apiKey,
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => config('app.name'),
                ])
                ->post($baseUrl.'/chat/completions', [
                    'model' => $model,
                    'messages' => [[
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => self::USER_PROMPT],
                            ['type' => 'image_url', 'image_url' => ['url' => $dataUri]],
                        ],
                    ]],
                    'response_format' => ['type' => 'json_object'],
                ]);

            if (! $response->successful()) {
                Log::warning('OpenRouterStudentCardAnalyzer: API error.', ['status' => $response->status()]);

                return EvidenceAnalysisResultData::manualReview(EvidenceRiskFlag::ExternalProviderUnavailable);
            }

            $body = $response->json();
            $rawText = $body['choices'][0]['message']['content'] ?? null;

            if (! is_string($rawText)) {
                return EvidenceAnalysisResultData::manualReview(EvidenceRiskFlag::ExternalProviderUnavailable);
            }

            $decoded = json_decode($rawText, true);

            if (! is_array($decoded)) {
                return EvidenceAnalysisResultData::manualReview(EvidenceRiskFlag::ExternalProviderUnavailable);
            }

            $extracted = ExtractedStudentCardFieldsData::fromArray($decoded);
            $matchResult = $this->matcher->match($extracted, $evidence->verificationRequest);

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
                riskFlags: $matchResult['flags'],
                reviewSummary: $decoded['review_summary'] ?? null,
                provider: 'openrouter',
                modelName: $model,
            );

        } catch (\Throwable $e) {
            Log::warning('OpenRouterStudentCardAnalyzer: Failed.', ['error' => $e->getMessage()]);

            return EvidenceAnalysisResultData::manualReview(EvidenceRiskFlag::ExternalProviderUnavailable);
        }
    }
}
