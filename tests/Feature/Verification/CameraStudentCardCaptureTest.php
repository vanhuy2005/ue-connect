<?php

namespace Tests\Feature\Verification;

use App\Enums\AccountStatus;
use App\Enums\EvidenceCaptureMethod;
use App\Enums\EvidenceCaptureStatus;
use App\Jobs\AnalyzeStudentCardEvidenceJob;
use App\Models\AcademicProgram;
use App\Models\EvidenceCaptureSession;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Tests\TestCase;

class CameraStudentCardCaptureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Faculty $faculty;

    protected AcademicProgram $program;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);

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
            'email' => 'student@hcmue.edu.vn',
            'account_status' => AccountStatus::REGISTERED,
        ]);
        $this->user->assignRole('student');
    }

    private function generateValidImageBase64(int $width = 640, int $height = 360): string
    {
        $img = imagecreatetruecolor($width, $height);
        ob_start();
        imagejpeg($img);
        $jpgContent = ob_get_clean();
        imagedestroy($img);

        return 'data:image/jpeg;base64,'.base64_encode($jpgContent);
    }

    public function test_user_can_create_capture_session_on_selecting_camera_method(): void
    {
        $component = Volt::actingAs($this->user)
            ->test('pages.verification.start')
            ->set('role_requested', 'student')
            ->call('nextStep')
            ->set('submitted_name', 'Nguyen Van A')
            ->set('submitted_student_code', '48.01.103.001')
            ->set('submitted_faculty_id', $this->faculty->id)
            ->set('submitted_academic_program_id', $this->program->id)
            ->set('submitted_cohort', 'K48')
            ->call('nextStep')
            ->set('evidenceMethod', 'camera');

        $this->assertNotNull($component->get('captureSessionToken'));
        $this->assertDatabaseHas('evidence_capture_sessions', [
            'user_id' => $this->user->id,
            'status' => EvidenceCaptureStatus::Started->value,
            'required_evidence_type' => 'student_card',
        ]);
    }

    public function test_expired_session_rejects_submission(): void
    {
        Storage::fake('private');

        $component = Volt::actingAs($this->user)
            ->test('pages.verification.start')
            ->set('role_requested', 'student')
            ->call('nextStep')
            ->set('submitted_name', 'Nguyen Van A')
            ->set('submitted_student_code', '48.01.103.001')
            ->set('submitted_faculty_id', $this->faculty->id)
            ->set('submitted_academic_program_id', $this->program->id)
            ->set('submitted_cohort', 'K48')
            ->call('nextStep')
            ->set('evidenceMethod', 'camera');

        $token = $component->get('captureSessionToken');
        $session = EvidenceCaptureSession::where('session_token_hash', hash('sha256', $token))->first();
        $session->update(['expires_at' => now()->subMinute()]);

        $validBase64 = $this->generateValidImageBase64();

        $component->set('capturedImageData', $validBase64)
            ->call('submit')
            ->assertHasErrors(['evidence']);
    }

    public function test_too_many_attempts_rejects_submission(): void
    {
        Storage::fake('private');

        $component = Volt::actingAs($this->user)
            ->test('pages.verification.start')
            ->set('role_requested', 'student')
            ->call('nextStep')
            ->set('submitted_name', 'Nguyen Van A')
            ->set('submitted_student_code', '48.01.103.001')
            ->set('submitted_faculty_id', $this->faculty->id)
            ->set('submitted_academic_program_id', $this->program->id)
            ->set('submitted_cohort', 'K48')
            ->call('nextStep')
            ->set('evidenceMethod', 'camera');

        $token = $component->get('captureSessionToken');
        $session = EvidenceCaptureSession::where('session_token_hash', hash('sha256', $token))->first();
        $session->update(['attempt_count' => config('ai-verification.capture.max_attempts', 5)]);

        $validBase64 = $this->generateValidImageBase64();

        $component->set('capturedImageData', $validBase64)
            ->call('submit')
            ->assertHasErrors(['evidence']);
    }

    public function test_camera_evidence_creates_private_media_file_and_sets_capture_method(): void
    {
        Storage::fake('private');
        Bus::fake();
        config(['ai-verification.enabled' => true]);

        $component = Volt::actingAs($this->user)
            ->test('pages.verification.start')
            ->set('role_requested', 'student')
            ->call('nextStep')
            ->set('submitted_name', 'Nguyen Van A')
            ->set('submitted_student_code', '48.01.103.001')
            ->set('submitted_faculty_id', $this->faculty->id)
            ->set('submitted_academic_program_id', $this->program->id)
            ->set('submitted_cohort', 'K48')
            ->call('nextStep')
            ->set('evidenceMethod', 'camera');

        $validBase64 = $this->generateValidImageBase64();

        $component->set('capturedImageData', $validBase64)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('verification.status'));

        $this->assertDatabaseHas('verification_evidences', [
            'capture_method' => EvidenceCaptureMethod::Camera->value,
            'evidence_type' => 'student_card',
        ]);

        $this->assertDatabaseHas('evidence_capture_sessions', [
            'user_id' => $this->user->id,
            'status' => EvidenceCaptureStatus::Completed->value,
        ]);

        Bus::assertDispatched(AnalyzeStudentCardEvidenceJob::class);
    }

    public function test_camera_student_card_does_not_dispatch_ai_job_when_disabled(): void
    {
        Storage::fake('private');
        Bus::fake();
        config(['ai-verification.enabled' => false]);

        $component = Volt::actingAs($this->user)
            ->test('pages.verification.start')
            ->set('role_requested', 'student')
            ->call('nextStep')
            ->set('submitted_name', 'Nguyen Van A')
            ->set('submitted_student_code', '48.01.103.001')
            ->set('submitted_faculty_id', $this->faculty->id)
            ->set('submitted_academic_program_id', $this->program->id)
            ->set('submitted_cohort', 'K48')
            ->call('nextStep')
            ->set('evidenceMethod', 'camera');

        $validBase64 = $this->generateValidImageBase64();

        $component->set('capturedImageData', $validBase64)
            ->call('submit')
            ->assertHasNoErrors();

        Bus::assertNotDispatched(AnalyzeStudentCardEvidenceJob::class);
    }

    public function test_invalid_base64_payload_is_rejected(): void
    {
        Storage::fake('private');

        $component = Volt::actingAs($this->user)
            ->test('pages.verification.start')
            ->set('role_requested', 'student')
            ->call('nextStep')
            ->set('submitted_name', 'Nguyen Van A')
            ->set('submitted_student_code', '48.01.103.001')
            ->set('submitted_faculty_id', $this->faculty->id)
            ->set('submitted_academic_program_id', $this->program->id)
            ->set('submitted_cohort', 'K48')
            ->call('nextStep')
            ->set('evidenceMethod', 'camera');

        $fakeBase64 = 'data:image/jpeg;base64,'.base64_encode('fake-image-content');

        $component->set('capturedImageData', $fakeBase64)
            ->call('submit')
            ->assertHasErrors(['evidence']);
    }

    public function test_too_small_image_payload_is_rejected(): void
    {
        Storage::fake('private');

        $component = Volt::actingAs($this->user)
            ->test('pages.verification.start')
            ->set('role_requested', 'student')
            ->call('nextStep')
            ->set('submitted_name', 'Nguyen Van A')
            ->set('submitted_student_code', '48.01.103.001')
            ->set('submitted_faculty_id', $this->faculty->id)
            ->set('submitted_academic_program_id', $this->program->id)
            ->set('submitted_cohort', 'K48')
            ->call('nextStep')
            ->set('evidenceMethod', 'camera');

        // 100x100px is too small
        $tooSmallBase64 = $this->generateValidImageBase64(100, 100);

        $component->set('capturedImageData', $tooSmallBase64)
            ->call('submit')
            ->assertHasErrors(['evidence']);
    }
}
