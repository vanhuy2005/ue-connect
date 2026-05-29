<?php

namespace Tests\Unit\AI;

use App\AI\Evidence\Services\EvidenceAnalyzerManager;
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
}
