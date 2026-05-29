<?php

namespace App\AI\Evidence\Services;

use App\AI\Evidence\Contracts\EvidenceAnalyzer;
use App\AI\Evidence\DTO\EvidenceAnalysisResultData;
use App\AI\Evidence\Providers\GeminiFlashStudentCardAnalyzer;
use App\AI\Evidence\Providers\LocalHybridStudentCardAnalyzer;
use App\AI\Evidence\Providers\LocalOcrStudentCardAnalyzer;
use App\AI\Evidence\Providers\MockStudentCardAnalyzer;
use App\AI\Evidence\Providers\OpenRouterStudentCardAnalyzer;
use App\Enums\EvidenceCaptureMethod;
use App\Enums\EvidenceRiskFlag;
use App\Models\VerificationEvidence;
use Illuminate\Support\Facades\Log;

class EvidenceAnalyzerManager
{
    public function __construct(
        private readonly MockStudentCardAnalyzer $mockAnalyzer,
        private readonly LocalOcrStudentCardAnalyzer $localOcrAnalyzer,
        private readonly LocalHybridStudentCardAnalyzer $localHybridAnalyzer,
        private readonly GeminiFlashStudentCardAnalyzer $geminiAnalyzer,
        private readonly OpenRouterStudentCardAnalyzer $openRouterAnalyzer,
    ) {}

    public function analyze(VerificationEvidence $evidence): EvidenceAnalysisResultData
    {
        // AI only runs for camera-captured student_card evidence
        if (config('ai-verification.camera_capture_required_for_ai', true)) {
            $method = $evidence->capture_method;

            if ($method !== EvidenceCaptureMethod::Camera) {
                return EvidenceAnalysisResultData::manualReview(
                    EvidenceRiskFlag::NotCameraCapture,
                    EvidenceRiskFlag::ManualReviewRequired,
                );
            }
        }

        $provider = config('ai-verification.provider', 'mock');
        $analyzer = $this->resolveProvider($provider);

        try {
            return $analyzer->analyze($evidence);
        } catch (\Throwable $e) {
            Log::warning('EvidenceAnalyzerManager: Provider failed, falling back to manual review.', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return EvidenceAnalysisResultData::manualReview(
                EvidenceRiskFlag::ManualReviewRequired,
            );
        }
    }

    private function resolveProvider(string $provider): EvidenceAnalyzer
    {
        return match ($provider) {
            'local_ocr' => $this->localOcrAnalyzer,
            'local_hybrid' => $this->localHybridAnalyzer,
            'gemini_flash' => $this->resolveExternalProvider('gemini_flash'),
            'openrouter' => $this->resolveExternalProvider('openrouter'),
            default => $this->mockAnalyzer,
        };
    }

    private function resolveExternalProvider(string $provider): EvidenceAnalyzer
    {
        if (! config('ai-verification.privacy.allow_external_provider', false)) {
            Log::warning('EvidenceAnalyzerManager: External provider requested but AI_ALLOW_EXTERNAL_PROVIDER is false.', [
                'provider' => $provider,
            ]);

            return new class($this->mockAnalyzer) implements EvidenceAnalyzer
            {
                public function __construct(private readonly MockStudentCardAnalyzer $mock) {}

                public function analyze(VerificationEvidence $evidence): EvidenceAnalysisResultData
                {
                    return EvidenceAnalysisResultData::manualReview(
                        EvidenceRiskFlag::ExternalProviderDisabled,
                    );
                }
            };
        }

        return match ($provider) {
            'gemini_flash' => $this->geminiAnalyzer,
            'openrouter' => $this->openRouterAnalyzer,
            default => $this->mockAnalyzer,
        };
    }
}
