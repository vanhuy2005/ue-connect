<?php

namespace Tests\Feature\Verification;

use App\AI\Evidence\Services\EvidenceAnalyzerManager;
use App\Enums\AccountStatus;
use App\Enums\EvidenceAnalysisRecommendation;
use App\Enums\EvidenceAnalysisStatus;
use App\Enums\EvidenceCaptureMethod;
use App\Enums\EvidenceRiskFlag;
use App\Enums\VerificationStatus;
use App\Jobs\AnalyzeStudentCardEvidenceJob;
use App\Models\AcademicProgram;
use App\Models\EvidenceAnalysisJob;
use App\Models\EvidenceAnalysisResult;
use App\Models\Faculty;
use App\Models\MediaFile;
use App\Models\User;
use App\Models\VerificationEvidence;
use App\Models\VerificationRequest;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCardAiVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $admin;

    protected Faculty $faculty;

    protected AcademicProgram $program;

    protected VerificationRequest $request;

    protected VerificationEvidence $evidence;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        $this->faculty = Faculty::create([
            'name' => 'Khoa Công nghệ Thông tin',
            'slug' => 'cntt',
            'status' => 'active',
        ]);

        $this->program = AcademicProgram::create([
            'faculty_id' => $this->faculty->id,
            'name' => 'Sư phạm Tin học',
            'slug' => 'sp-tin-hoc',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'name' => 'Nguyen Van A',
            'email' => 'student@student.hcmue.edu.vn',
            'account_status' => AccountStatus::REGISTERED,
        ]);
        $this->user->assignRole('student');

        $this->admin = User::factory()->create([
            'email' => 'admin@teacher.hcmue.edu.vn',
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->admin->assignRole('admin');

        $this->request = VerificationRequest::create([
            'user_id' => $this->user->id,
            'role_requested' => 'student',
            'status' => VerificationStatus::PENDING_REVIEW,
            'submitted_name' => 'Nguyen Van A',
            'submitted_student_code' => '48.01.103.001',
            'submitted_faculty_id' => $this->faculty->id,
            'submitted_academic_program_id' => $this->program->id,
            'submitted_cohort' => 'K48',
            'submitted_email' => 'student@student.hcmue.edu.vn',
            'submitted_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $mediaFile = MediaFile::create([
            'owner_id' => $this->user->id,
            'disk' => 'private',
            'path' => 'verifications/1/captures/test.jpg',
            'original_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => 1024,
            'visibility' => 'private',
            'file_category' => 'verification_evidence',
        ]);

        $this->evidence = VerificationEvidence::create([
            'verification_request_id' => $this->request->id,
            'media_file_id' => $mediaFile->id,
            'evidence_type' => 'student_card',
            'user_note' => 'My card',
            'status' => 'uploaded',
            'capture_method' => EvidenceCaptureMethod::Camera,
            'captured_at' => now(),
        ]);
    }

    public function test_non_student_card_evidence_is_skipped(): void
    {
        $this->evidence->update(['evidence_type' => 'transcript']);

        $job = new AnalyzeStudentCardEvidenceJob($this->evidence->id);
        $job->handle(app(EvidenceAnalyzerManager::class));

        $analysisJob = EvidenceAnalysisJob::where('verification_evidence_id', $this->evidence->id)->first();
        $this->assertEquals(EvidenceAnalysisStatus::Skipped, $analysisJob->status);

        $result = $analysisJob->result;
        $this->assertContains(EvidenceRiskFlag::UnsupportedDocumentType->value, $result->risk_flags_json);
    }

    public function test_upload_fallback_is_skipped_by_manager(): void
    {
        $this->evidence->update(['capture_method' => EvidenceCaptureMethod::UploadFallback]);

        $job = new AnalyzeStudentCardEvidenceJob($this->evidence->id);
        $job->handle(app(EvidenceAnalyzerManager::class));

        $analysisJob = EvidenceAnalysisJob::where('verification_evidence_id', $this->evidence->id)->first();
        $this->assertEquals(EvidenceAnalysisStatus::Skipped, $analysisJob->status);

        $result = $analysisJob->result;
        $this->assertContains(EvidenceRiskFlag::NotCameraCapture->value, $result->risk_flags_json);
        $this->assertContains(EvidenceRiskFlag::ManualReviewRequired->value, $result->risk_flags_json);
    }

    public function test_ai_never_auto_approves_request(): void
    {
        config(['ai-verification.provider' => 'mock']);

        $job = new AnalyzeStudentCardEvidenceJob($this->evidence->id);
        $job->handle(app(EvidenceAnalyzerManager::class));

        $this->request->refresh();
        // Request status remains under review/pending review, never becomes approved automatically
        $this->assertNotEquals(VerificationStatus::APPROVED, $this->request->status);
    }

    public function test_admin_can_see_ai_results_in_detail_page(): void
    {
        config(['ai-verification.provider' => 'mock']);

        $job = new AnalyzeStudentCardEvidenceJob($this->evidence->id);
        $job->handle(app(EvidenceAnalyzerManager::class));

        $response = $this->actingAs($this->admin)->get(route('admin.verifications.detail', ['id' => $this->request->id]));
        $response->assertStatus(200)
            ->assertSee('HCMUE AI Assist')
            ->assertSee('Nguyen Van A')
            ->assertSee('48.01.103.001');
    }

    public function test_external_provider_blocked_when_privacy_setting_disabled(): void
    {
        config([
            'ai-verification.provider' => 'gemini_flash',
            'ai-verification.privacy.allow_external_provider' => false,
        ]);

        $job = new AnalyzeStudentCardEvidenceJob($this->evidence->id);
        $job->handle(app(EvidenceAnalyzerManager::class));

        $analysisJob = EvidenceAnalysisJob::where('verification_evidence_id', $this->evidence->id)->first();
        $this->assertEquals(EvidenceAnalysisStatus::ManualReviewRequired, $analysisJob->status);

        $result = $analysisJob->result;
        $this->assertContains(EvidenceRiskFlag::ExternalProviderDisabled->value, $result->risk_flags_json);
        $this->assertEquals(EvidenceAnalysisRecommendation::ManualReview, $result->recommendation);
    }

    public function test_job_is_idempotent_and_prevents_duplicate_records(): void
    {
        config(['ai-verification.provider' => 'mock']);

        // First execution
        $job = new AnalyzeStudentCardEvidenceJob($this->evidence->id);
        $job->handle(app(EvidenceAnalyzerManager::class));

        $initialJobCount = EvidenceAnalysisJob::where('verification_evidence_id', $this->evidence->id)->count();
        $initialResultCount = EvidenceAnalysisResult::where('verification_evidence_id', $this->evidence->id)->count();

        $this->assertEquals(1, $initialJobCount);
        $this->assertEquals(1, $initialResultCount);

        // Second execution (retry/double dispatch simulation)
        $job->handle(app(EvidenceAnalyzerManager::class));

        $secondJobCount = EvidenceAnalysisJob::where('verification_evidence_id', $this->evidence->id)->count();
        $secondResultCount = EvidenceAnalysisResult::where('verification_evidence_id', $this->evidence->id)->count();

        $this->assertEquals(1, $secondJobCount);
        $this->assertEquals(1, $secondResultCount);
    }

    public function test_local_provider_resolves_configured_model_name(): void
    {
        config([
            'ai-verification.provider' => 'local_hybrid',
            'ai-verification.local_hybrid.ocr_engine' => 'tesseract',
            'ai-verification.local_hybrid.ollama_model' => 'qwen2.5:1.5b',
        ]);

        // Make the evidence ineligible so it skips analyzer execution, keeping the initial model_name
        $this->evidence->update(['evidence_type' => 'transcript']);

        $job = new AnalyzeStudentCardEvidenceJob($this->evidence->id);
        $job->handle(app(EvidenceAnalyzerManager::class));

        $analysisJob = EvidenceAnalysisJob::where('verification_evidence_id', $this->evidence->id)->first();
        $this->assertNotNull($analysisJob);
        $this->assertEquals('tesseract+qwen2.5:1.5b', $analysisJob->model_name);
    }
}
