<?php

namespace App\Jobs;

use App\AI\Evidence\Services\EvidenceAnalyzerManager;
use App\Enums\EvidenceAnalysisRecommendation;
use App\Enums\EvidenceAnalysisStatus;
use App\Enums\EvidenceCaptureMethod;
use App\Enums\EvidenceRiskFlag;
use App\Models\EvidenceAnalysisJob;
use App\Models\EvidenceAnalysisResult;
use App\Models\VerificationEvidence;
use App\Services\AuditLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyzeStudentCardEvidenceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $evidenceId) {}

    public function handle(EvidenceAnalyzerManager $analyzerManager): void
    {
        $evidence = VerificationEvidence::with(['verificationRequest', 'mediaFile'])->find($this->evidenceId);

        if (! $evidence) {
            Log::error('AnalyzeStudentCardEvidenceJob: VerificationEvidence not found.', [
                'evidence_id' => $this->evidenceId,
            ]);

            return;
        }

        $request = $evidence->verificationRequest;
        if (! $request) {
            Log::error('AnalyzeStudentCardEvidenceJob: VerificationRequest not found.', [
                'evidence_id' => $this->evidenceId,
            ]);

            return;
        }

        $cameraRequired = config('ai-verification.camera_capture_required_for_ai', true);
        
        $isEligible = $evidence->evidence_type === 'student_card'
            && (!$cameraRequired || $evidence->capture_method === EvidenceCaptureMethod::Camera);

        $provider = config('ai-verification.provider', 'mock');

        // Check if an analysis job is already completed/processing to ensure idempotency
        $existingJob = EvidenceAnalysisJob::where('verification_evidence_id', $evidence->id)->first();

        if ($existingJob) {
            if (in_array($existingJob->status, [
                EvidenceAnalysisStatus::Succeeded,
                EvidenceAnalysisStatus::ManualReviewRequired,
                EvidenceAnalysisStatus::Skipped,
            ])) {
                Log::info('AnalyzeStudentCardEvidenceJob: Evidence already has a completed analysis job.', [
                    'evidence_id' => $evidence->id,
                    'job_id' => $existingJob->id,
                    'status' => $existingJob->status->value ?? $existingJob->status,
                ]);

                return;
            }

            // If it failed or is in processing, we retry / update it
            $analysisJob = $existingJob;
            $analysisJob->update([
                'status' => EvidenceAnalysisStatus::Processing,
                'attempt_count' => $analysisJob->attempt_count + 1,
                'started_at' => now(),
            ]);
        } else {
            $analysisJob = EvidenceAnalysisJob::create([
                'verification_request_id' => $request->id,
                'verification_evidence_id' => $evidence->id,
                'media_file_id' => $evidence->media_file_id,
                'provider' => $provider,
                'model_name' => $this->resolveConfiguredModelName($provider),
                'status' => EvidenceAnalysisStatus::Processing,
                'attempt_count' => 1,
                'queued_at' => now(),
                'started_at' => now(),
            ]);
        }

        if (! $isEligible) {
            $analysisJob->update([
                'status' => EvidenceAnalysisStatus::Skipped,
                'finished_at' => now(),
            ]);

            $riskFlags = [EvidenceRiskFlag::UnsupportedDocumentType->value];
            $summary = 'Không hỗ trợ phân tích AI cho hình thức này hoặc loại minh chứng này.';

            if ($evidence->capture_method === EvidenceCaptureMethod::UploadFallback) {
                $riskFlags = [
                    EvidenceRiskFlag::NotCameraCapture->value,
                    EvidenceRiskFlag::ManualReviewRequired->value,
                ];
                $summary = 'Minh chứng tải lên thủ công. Không hỗ trợ phân tích AI. Cần duyệt thủ công.';
            }

            EvidenceAnalysisResult::updateOrCreate(
                [
                    'verification_evidence_id' => $evidence->id,
                    'analysis_job_id' => $analysisJob->id,
                ],
                [
                    'verification_request_id' => $request->id,
                    'document_type_detected' => 'unknown',
                    'document_type_confidence' => 0.0,
                    'ocr_text' => null,
                    'extracted_fields_json' => [],
                    'match_result_json' => [],
                    'risk_flags_json' => $riskFlags,
                    'confidence_score' => 0.0,
                    'recommendation' => EvidenceAnalysisRecommendation::ManualReview,
                    'review_summary' => $summary,
                ]
            );

            return;
        }

        try {
            // Run analysis
            $resultData = $analyzerManager->analyze($evidence);

            // Determine status from recommendation
            $status = match ($resultData->recommendation) {
                EvidenceAnalysisRecommendation::LikelyMatch => EvidenceAnalysisStatus::Succeeded,
                default => EvidenceAnalysisStatus::ManualReviewRequired,
            };

            DB::transaction(function () use ($analysisJob, $request, $evidence, $resultData, $status) {
                $analysisJob->update([
                    'status' => $status,
                    'finished_at' => now(),
                    'provider' => $resultData->provider ?? $analysisJob->provider,
                    'model_name' => $resultData->modelName ?? $analysisJob->model_name,
                ]);

                EvidenceAnalysisResult::updateOrCreate(
                    [
                        'verification_evidence_id' => $evidence->id,
                        'analysis_job_id' => $analysisJob->id,
                    ],
                    [
                        'verification_request_id' => $request->id,
                        'document_type_detected' => $resultData->documentTypeDetected?->value ?? 'unknown',
                        'document_type_confidence' => $resultData->documentTypeConfidence ?? 0.0,
                        'ocr_text' => config('ai-verification.privacy.store_raw_ocr_text', true) ? $resultData->ocrText : null,
                        'extracted_fields_json' => $resultData->extractedFields ? $resultData->extractedFields->toArray() : [],
                        'match_result_json' => $resultData->matchResult,
                        'risk_flags_json' => $resultData->riskFlagValues(),
                        'confidence_score' => $resultData->confidenceScore,
                        'recommendation' => $resultData->recommendation,
                        'review_summary' => $resultData->reviewSummary,
                    ]
                );

                // Log audit action
                AuditLogService::log(
                    actorId: null, // System-executed
                    actorType: 'system',
                    actionKey: 'verification.ai_analysis_completed',
                    targetType: 'VerificationRequest',
                    targetId: $request->id,
                    contextType: 'VerificationEvidence',
                    contextId: $evidence->id,
                    beforeSnapshot: null,
                    afterSnapshot: [
                        'recommendation' => $resultData->recommendation->value,
                        'confidence_score' => $resultData->confidenceScore,
                        'risk_flags' => $resultData->riskFlagValues(),
                    ],
                    reason: 'AI Student Card Analysis completed'
                );
            });

        } catch (\Throwable $e) {
            Log::error('AnalyzeStudentCardEvidenceJob: Analysis failed.', [
                'evidence_id' => $this->evidenceId,
                'error' => $e->getMessage(),
            ]);

            $analysisJob->update([
                'status' => EvidenceAnalysisStatus::Failed,
                'failed_at' => now(),
                'error_code' => 'ANALYSIS_ERROR',
                'error_message' => $e->getMessage(),
            ]);

            // Save fallback result safely
            EvidenceAnalysisResult::updateOrCreate(
                [
                    'verification_evidence_id' => $evidence->id,
                    'analysis_job_id' => $analysisJob->id,
                ],
                [
                    'verification_request_id' => $request->id,
                    'document_type_detected' => 'unknown',
                    'document_type_confidence' => 0.0,
                    'ocr_text' => null,
                    'extracted_fields_json' => [],
                    'match_result_json' => [],
                    'risk_flags_json' => [EvidenceRiskFlag::ManualReviewRequired->value],
                    'confidence_score' => 0.0,
                    'recommendation' => EvidenceAnalysisRecommendation::ManualReview,
                    'review_summary' => 'Lỗi xảy ra trong quá trình phân tích AI. Cần xem xét thủ công.',
                ]
            );
        }
    }

    private function resolveConfiguredModelName(string $provider): ?string
    {
        return match ($provider) {
            'mock' => config('ai-verification.providers.mock.model'),
            'local_ocr' => config('ai-verification.local_hybrid.ocr_engine', 'tesseract'),
            'local_hybrid' => config('ai-verification.local_hybrid.ocr_engine', 'tesseract').'+'.config('ai-verification.local_hybrid.ollama_model', 'qwen2.5:1.5b'),
            'gemini_flash' => config('ai-verification.providers.gemini_flash.model'),
            'openrouter' => config('ai-verification.providers.openrouter.model'),
            default => config("ai-verification.providers.{$provider}.model"),
        };
    }
}
