<?php

namespace Tests\Feature\Verification;

use App\Enums\AccountStatus;
use App\Enums\IdentityType;
use App\Enums\VerificationStatus;
use App\Models\AcademicProgram;
use App\Models\Faculty;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Tests\TestCase;

class VerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Faculty $faculty;

    protected AcademicProgram $program;

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
            'email' => 'student@student.hcmue.edu.vn',
            'account_status' => AccountStatus::REGISTERED,
            'intended_identity_type' => IdentityType::CURRENT_STUDENT,
        ]);
        $this->user->assignRole('student');
    }

    public function test_unverified_user_is_redirected_to_status(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $response->assertRedirect(route('verification.status'));
    }

    public function test_verification_wizard_submits_successfully(): void
    {
        Storage::fake('private');

        $file = UploadedFile::fake()->image('card.jpg', 800, 600)->size(1024); // 1MB

        $component = Volt::actingAs($this->user)
            ->test('pages.verification.start')
            ->set('role_requested', 'student')
            ->call('nextStep')
            ->assertSet('step', 2)
            ->set('submitted_name', 'Nguyen Van A')
            ->set('submitted_student_code', '48.01.103.001')
            ->set('submitted_faculty_id', $this->faculty->id)
            ->set('submitted_academic_program_id', $this->program->id)
            ->set('submitted_cohort', 'K48')
            ->call('nextStep')
            ->assertSet('step', 3)
            ->set('evidence_files', [$file])
            ->set('evidence_types', ['student_card'])
            ->set('evidence_notes', ['Thẻ sinh viên của tôi'])
            ->call('submit')
            ->assertRedirect(route('verification.status'));

        $this->user->refresh();
        $this->assertEquals(AccountStatus::PENDING_VERIFICATION, $this->user->account_status);

        $this->assertDatabaseHas('verification_requests', [
            'user_id' => $this->user->id,
            'submitted_name' => 'Nguyen Van A',
            'submitted_student_code' => '48.01.103.001',
            'submitted_faculty_id' => $this->faculty->id,
            'submitted_cohort' => 'K48',
            'status' => VerificationStatus::PENDING_REVIEW->value,
        ]);
    }

    public function test_teacher_verification_role_is_locked_and_accepts_advising_classes(): void
    {
        Storage::fake('private');

        $teacher = User::factory()->create([
            'email' => 'teacher@teacher.hcmue.edu.vn',
            'account_status' => AccountStatus::REGISTERED,
            'intended_identity_type' => IdentityType::TEACHER_ADVISOR,
        ]);

        $file = UploadedFile::fake()->image('staff-card.jpg', 800, 600)->size(1024);

        Volt::actingAs($teacher)
            ->test('pages.verification.start')
            ->set('role_requested', 'alumni')
            ->call('nextStep')
            ->assertSet('role_requested', 'teacher')
            ->assertSet('step', 2)
            ->set('submitted_name', 'Nguyen Thai Nguyen')
            ->set('submitted_faculty_id', $this->faculty->id)
            ->set('submitted_position', 'Giảng viên')
            ->set('submitted_is_academic_advisor', true)
            ->set('submitted_advised_class_codes', "49.cnttd\n50.CNTTA")
            ->call('nextStep')
            ->assertSet('step', 3)
            ->set('evidence_files', [$file])
            ->set('evidence_types', ['staff_card'])
            ->set('evidence_notes', ['Thẻ viên chức'])
            ->call('submit')
            ->assertRedirect(route('verification.status'));

        $this->assertDatabaseHas('verification_requests', [
            'user_id' => $teacher->id,
            'role_requested' => 'teacher',
            'submitted_is_academic_advisor' => true,
            'submitted_advised_class_codes' => "49.CNTTD\n50.CNTTA",
            'status' => VerificationStatus::PENDING_REVIEW->value,
        ]);
    }
}
