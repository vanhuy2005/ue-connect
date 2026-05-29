<?php

namespace Tests\Unit\AI;

use App\AI\Evidence\DTO\EvidenceAnalysisResultData;
use App\AI\Evidence\Providers\GeminiFlashStudentCardAnalyzer;
use App\AI\Evidence\Providers\LocalHybridStudentCardAnalyzer;
use App\AI\Evidence\Providers\OpenRouterStudentCardAnalyzer;
use App\AI\Evidence\Services\EvidenceAnalyzerManager;
use App\Enums\EvidenceAnalysisRecommendation;
use App\Enums\EvidenceCaptureMethod;
use App\Enums\EvidenceRiskFlag;
use App\Models\VerificationEvidence;
use Tests\TestCase;

class EvidenceAnalyzerManagerTest extends TestCase
{
    protected EvidenceAnalyzerManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(EvidenceAnalyzerManager::class);
    }

    public function test_blocks_upload_evidence_for_ai_analysis(): void
    {
        config(['ai-verification.camera_capture_required_for_ai' => true]);

        $evidence = new VerificationEvidence([
            'capture_method' => EvidenceCaptureMethod::UploadFallback,
            'evidence_type' => 'student_card',
        ]);

        $result = $this->manager->analyze($evidence);

        $this->assertContains(EvidenceRiskFlag::NotCameraCapture, $result->riskFlags);
        $this->assertEquals(0.0, $result->confidenceScore);
    }

    public function test_blocks_gemini_when_allow_external_provider_is_false(): void
    {
        config([
            'ai-verification.provider' => 'gemini_flash',
            'ai-verification.privacy.allow_external_provider' => false,
            'ai-verification.camera_capture_required_for_ai' => false,
        ]);

        $evidence = new VerificationEvidence([
            'capture_method' => EvidenceCaptureMethod::Camera,
            'evidence_type' => 'student_card',
        ]);

        $result = $this->manager->analyze($evidence);

        $this->assertContains(EvidenceRiskFlag::ExternalProviderDisabled, $result->riskFlags);
    }

    public function test_fallback_chain_is_called_when_confidence_is_low(): void
    {
        config([
            'ai-verification.provider' => 'local_hybrid',
            'ai-verification.camera_capture_required_for_ai' => false,
            'ai-verification.fallback.enabled' => true,
            'ai-verification.fallback.providers' => ['gemini_flash'],
            'ai-verification.fallback.min_confidence_to_skip' => 0.75,
            'ai-verification.privacy.allow_external_provider' => true,
        ]);

        $evidence = new VerificationEvidence([
            'capture_method' => EvidenceCaptureMethod::Camera,
            'evidence_type' => 'student_card',
        ]);

        // Mock Local Hybrid to return low confidence (0.4)
        $mockLocal = $this->createMock(LocalHybridStudentCardAnalyzer::class);
        $mockLocal->expects($this->once())
            ->method('analyze')
            ->willReturn(new EvidenceAnalysisResultData(
                recommendation: EvidenceAnalysisRecommendation::ManualReview,
                confidenceScore: 0.4,
                riskFlags: [],
                provider: 'local_hybrid',
                modelName: 'tesseract'
            ));
        $this->app->instance(LocalHybridStudentCardAnalyzer::class, $mockLocal);

        // Mock Gemini to return high confidence (0.85)
        $mockGemini = $this->createMock(GeminiFlashStudentCardAnalyzer::class);
        $mockGemini->expects($this->once())
            ->method('analyze')
            ->willReturn(new EvidenceAnalysisResultData(
                recommendation: EvidenceAnalysisRecommendation::LikelyMatch,
                confidenceScore: 0.85,
                riskFlags: [],
                provider: 'gemini_flash',
                modelName: 'gemini-2.0-flash'
            ));
        $this->app->instance(GeminiFlashStudentCardAnalyzer::class, $mockGemini);

        $manager = app(EvidenceAnalyzerManager::class);
        $result = $manager->analyze($evidence);

        // Assert that the result was overridden by the fallback analyzer
        $this->assertEquals(0.85, $result->confidenceScore);
        $this->assertEquals('gemini_flash', $result->provider);
    }

    public function test_fallback_chain_is_not_called_when_confidence_is_high(): void
    {
        config([
            'ai-verification.provider' => 'local_hybrid',
            'ai-verification.camera_capture_required_for_ai' => false,
            'ai-verification.fallback.enabled' => true,
            'ai-verification.fallback.providers' => ['gemini_flash'],
            'ai-verification.fallback.min_confidence_to_skip' => 0.75,
            'ai-verification.privacy.allow_external_provider' => true,
        ]);

        $evidence = new VerificationEvidence([
            'capture_method' => EvidenceCaptureMethod::Camera,
            'evidence_type' => 'student_card',
        ]);

        // Mock Local Hybrid to return high confidence (0.8)
        $mockLocal = $this->createMock(LocalHybridStudentCardAnalyzer::class);
        $mockLocal->expects($this->once())
            ->method('analyze')
            ->willReturn(new EvidenceAnalysisResultData(
                recommendation: EvidenceAnalysisRecommendation::LikelyMatch,
                confidenceScore: 0.8,
                riskFlags: [],
                provider: 'local_hybrid',
                modelName: 'tesseract+ollama'
            ));
        $this->app->instance(LocalHybridStudentCardAnalyzer::class, $mockLocal);

        // Mock Gemini should NOT be called
        $mockGemini = $this->createMock(GeminiFlashStudentCardAnalyzer::class);
        $mockGemini->expects($this->never())->method('analyze');
        $this->app->instance(GeminiFlashStudentCardAnalyzer::class, $mockGemini);

        $manager = app(EvidenceAnalyzerManager::class);
        $result = $manager->analyze($evidence);

        $this->assertEquals(0.8, $result->confidenceScore);
        $this->assertEquals('local_hybrid', $result->provider);
    }

    public function test_fallback_stops_early_if_crosses_skip_threshold(): void
    {
        config([
            'ai-verification.provider' => 'local_hybrid',
            'ai-verification.camera_capture_required_for_ai' => false,
            'ai-verification.fallback.enabled' => true,
            'ai-verification.fallback.providers' => ['gemini_flash', 'openrouter'],
            'ai-verification.fallback.min_confidence_to_skip' => 0.75,
            'ai-verification.privacy.allow_external_provider' => true,
        ]);

        $evidence = new VerificationEvidence([
            'capture_method' => EvidenceCaptureMethod::Camera,
            'evidence_type' => 'student_card',
        ]);

        // Local: 0.3
        $mockLocal = $this->createMock(LocalHybridStudentCardAnalyzer::class);
        $mockLocal->expects($this->once())
            ->method('analyze')
            ->willReturn(new EvidenceAnalysisResultData(
                recommendation: EvidenceAnalysisRecommendation::ManualReview,
                confidenceScore: 0.3,
                riskFlags: [],
                provider: 'local_hybrid',
                modelName: 'tesseract'
            ));
        $this->app->instance(LocalHybridStudentCardAnalyzer::class, $mockLocal);

        // Gemini: 0.8 (crosses 0.75 skip threshold)
        $mockGemini = $this->createMock(GeminiFlashStudentCardAnalyzer::class);
        $mockGemini->expects($this->once())
            ->method('analyze')
            ->willReturn(new EvidenceAnalysisResultData(
                recommendation: EvidenceAnalysisRecommendation::LikelyMatch,
                confidenceScore: 0.8,
                riskFlags: [],
                provider: 'gemini_flash',
                modelName: 'gemini-2.0-flash'
            ));
        $this->app->instance(GeminiFlashStudentCardAnalyzer::class, $mockGemini);

        // OpenRouter: should NOT be called
        $mockOpenRouter = $this->createMock(OpenRouterStudentCardAnalyzer::class);
        $mockOpenRouter->expects($this->never())->method('analyze');
        $this->app->instance(OpenRouterStudentCardAnalyzer::class, $mockOpenRouter);

        $manager = app(EvidenceAnalyzerManager::class);
        $result = $manager->analyze($evidence);

        $this->assertEquals(0.8, $result->confidenceScore);
        $this->assertEquals('gemini_flash', $result->provider);
    }
}
