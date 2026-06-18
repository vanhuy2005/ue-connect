<?php

namespace Tests\Feature\Verification;

use App\Actions\Admin\BuildAdminDashboardAction;
use App\Enums\AccountStatus;
use App\Enums\VerificationStatus;
use App\Models\AcademicProgram;
use App\Models\Faculty;
use App\Models\Profile;
use App\Models\StudentProfile;
use App\Models\User;
use App\Models\VerificationRequest;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AdminVerificationReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $studentUser;

    protected Faculty $faculty;

    protected AcademicProgram $program;

    protected VerificationRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        // Create reference data
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

        // Create Admin
        $this->admin = User::factory()->create([
            'email' => 'admin@teacher.hcmue.edu.vn',
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->admin->assignRole('admin');

        // Create Student user and request
        $this->studentUser = User::factory()->create([
            'email' => 'student@student.hcmue.edu.vn',
            'account_status' => AccountStatus::PENDING_VERIFICATION,
        ]);
        $this->studentUser->assignRole('student');

        $this->request = VerificationRequest::create([
            'user_id' => $this->studentUser->id,
            'role_requested' => 'student',
            'status' => VerificationStatus::PENDING_REVIEW,
            'submitted_name' => 'Nguyen Van Student',
            'submitted_student_code' => '48.01.103.001',
            'submitted_faculty_id' => $this->faculty->id,
            'submitted_academic_program_id' => $this->program->id,
            'submitted_cohort' => 'K48',
            'submitted_email' => 'student@student.hcmue.edu.vn',
            'submitted_at' => now(),
        ]);
    }

    public function test_non_admin_cannot_access_review_pages(): void
    {
        $this->actingAs($this->studentUser);

        $this->get(route('admin.verifications.queue'))->assertStatus(403);
        $this->get(route('admin.verifications.detail', ['id' => $this->request->id]))->assertStatus(403);
    }

    public function test_admin_can_access_review_queue(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.verifications.queue'));
        $response->assertOk();
    }

    public function test_opening_details_transitions_request_to_under_review(): void
    {
        $this->actingAs($this->admin);

        // Before visiting, request is pending
        $this->assertEquals(VerificationStatus::PENDING_REVIEW, $this->request->status);

        Volt::test('pages.admin.verification-detail', ['id' => $this->request->id]);

        $this->request->refresh();
        $this->assertEquals(VerificationStatus::UNDER_REVIEW, $this->request->status);
        $this->assertEquals($this->admin->id, $this->request->assigned_admin_id);

        $this->assertDatabaseHas('verification_review_actions', [
            'verification_request_id' => $this->request->id,
            'admin_id' => $this->admin->id,
            'action_key' => 'start_review',
        ]);
    }

    public function test_admin_can_approve_request(): void
    {
        $this->actingAs($this->admin);

        // Trigger under_review first by test mount
        $component = Volt::test('pages.admin.verification-detail', ['id' => $this->request->id])
            ->set('action', 'approve')
            ->set('reason', 'Thông tin hợp lệ và chính xác.')
            ->call('processReview')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.verifications.queue'));

        $this->request->refresh();
        $this->assertEquals(VerificationStatus::APPROVED, $this->request->status);

        $this->studentUser->refresh();
        $this->assertEquals(AccountStatus::PROFILE_INCOMPLETE, $this->studentUser->account_status);
        $this->assertTrue($this->studentUser->hasRole('student'));

        // Profile created
        $profile = Profile::where('user_id', $this->studentUser->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('Nguyen Van Student', $profile->display_name);

        $this->assertDatabaseHas('student_profiles', [
            'profile_id' => $profile->id,
            'student_code' => '48.01.103.001',
            'faculty_id' => $this->faculty->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $this->admin->id,
            'action_key' => 'verification.approve',
        ]);
    }

    public function test_admin_can_reject_request(): void
    {
        $this->actingAs($this->admin);

        $component = Volt::test('pages.admin.verification-detail', ['id' => $this->request->id])
            ->set('action', 'reject')
            ->set('reason', 'Thiếu thẻ sinh viên mặt sau.')
            ->call('processReview')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.verifications.queue'));

        $this->request->refresh();
        $this->assertEquals(VerificationStatus::REJECTED, $this->request->status);

        $this->studentUser->refresh();
        $this->assertEquals(AccountStatus::REGISTERED, $this->studentUser->account_status);

        $this->assertDatabaseHas('verification_review_actions', [
            'verification_request_id' => $this->request->id,
            'action_key' => 'reject',
            'reason' => 'Thiếu thẻ sinh viên mặt sau.',
        ]);
    }

    public function test_admin_can_request_more_information(): void
    {
        $this->actingAs($this->admin);

        $component = Volt::test('pages.admin.verification-detail', ['id' => $this->request->id])
            ->set('action', 'need_more_information')
            ->set('instruction', 'Vui lòng tải lại ảnh thẻ SV rõ hơn.')
            ->call('processReview')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.verifications.queue'));

        $this->request->refresh();
        $this->assertEquals(VerificationStatus::NEEDS_MORE_INFORMATION, $this->request->status);

        $this->studentUser->refresh();
        $this->assertEquals(AccountStatus::REGISTERED, $this->studentUser->account_status);

        $this->assertDatabaseHas('verification_review_actions', [
            'verification_request_id' => $this->request->id,
            'action_key' => 'need_more_information',
            'instruction' => 'Vui lòng tải lại ảnh thẻ SV rõ hơn.',
        ]);
    }

    public function test_admin_can_mark_request_suspicious_with_state_machine_status(): void
    {
        $this->actingAs($this->admin);

        Volt::test('pages.admin.verification-detail', ['id' => $this->request->id])
            ->set('action', 'suspicious')
            ->set('reason', 'Minh chứng có dấu hiệu chỉnh sửa nghiêm trọng.')
            ->call('processReview')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.verifications.queue'));

        $this->request->refresh();
        $this->assertEquals(VerificationStatus::SUSPICIOUS, $this->request->status);
        $this->assertNotSame('suspended_by_admin', $this->request->status->value);

        $this->studentUser->refresh();
        $this->assertEquals(AccountStatus::SUSPENDED, $this->studentUser->account_status);

        $this->assertDatabaseHas('verification_review_actions', [
            'verification_request_id' => $this->request->id,
            'action_key' => 'suspend_suspicious',
        ]);
    }

    public function test_admin_cannot_approve_duplicate_mssv(): void
    {
        // First create a verified user with the same MSSV
        $otherUser = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $otherProfile = Profile::create(['user_id' => $otherUser->id, 'display_name' => 'Other Student', 'role_type' => 'student']);
        StudentProfile::create([
            'profile_id' => $otherProfile->id,
            'student_code' => '48.01.103.001',
            'faculty_id' => $this->faculty->id,
            'academic_program_id' => $this->program->id,
        ]);

        $this->actingAs($this->admin);

        $component = Volt::test('pages.admin.verification-detail', ['id' => $this->request->id])
            ->set('action', 'approve')
            ->set('reason', 'Thông tin hợp lệ.')
            ->call('processReview')
            ->assertHasErrors('general'); // Generates MSSV duplicate block!

        $this->request->refresh();
        $this->assertNotEquals(VerificationStatus::APPROVED, $this->request->status);
    }

    public function test_queue_defaults_to_pending_and_includes_under_review_and_resubmitted(): void
    {
        $this->actingAs($this->admin);

        // Create requests with different statuses
        $underReviewRequest = VerificationRequest::create([
            'user_id' => User::factory()->create()->id,
            'role_requested' => 'student',
            'status' => VerificationStatus::UNDER_REVIEW,
            'submitted_name' => 'Nguyen Van Under Review',
            'submitted_email' => 'underreview@student.hcmue.edu.vn',
            'submitted_at' => now(),
        ]);

        $resubmittedRequest = VerificationRequest::create([
            'user_id' => User::factory()->create()->id,
            'role_requested' => 'student',
            'status' => VerificationStatus::RESUBMITTED,
            'submitted_name' => 'Nguyen Van Resubmitted',
            'submitted_email' => 'resubmitted@student.hcmue.edu.vn',
            'submitted_at' => now(),
        ]);

        $approvedRequest = VerificationRequest::create([
            'user_id' => User::factory()->create()->id,
            'role_requested' => 'student',
            'status' => VerificationStatus::APPROVED,
            'submitted_name' => 'Nguyen Van Approved',
            'submitted_email' => 'approved@student.hcmue.edu.vn',
            'submitted_at' => now(),
        ]);

        $component = Volt::test('pages.admin.verification-queue')
            ->assertSet('status', 'pending');

        $requests = $component->get('requests');
        $requestIds = collect($requests->items())->pluck('id')->all();

        // Should contain pending_review (this->request), under_review, and resubmitted requests
        $this->assertContains($this->request->id, $requestIds);
        $this->assertContains($underReviewRequest->id, $requestIds);
        $this->assertContains($resubmittedRequest->id, $requestIds);

        // Should not contain approved request
        $this->assertNotContains($approvedRequest->id, $requestIds);
    }

    public function test_admin_dashboard_counts_all_pending_under_review_and_resubmitted(): void
    {
        $this->actingAs($this->admin);

        // Create extra requests with under_review and resubmitted statuses
        VerificationRequest::create([
            'user_id' => User::factory()->create()->id,
            'role_requested' => 'student',
            'status' => VerificationStatus::UNDER_REVIEW,
            'submitted_name' => 'Nguyen Van Under Review 2',
            'submitted_email' => 'underreview2@student.hcmue.edu.vn',
            'submitted_at' => now(),
        ]);

        VerificationRequest::create([
            'user_id' => User::factory()->create()->id,
            'role_requested' => 'student',
            'status' => VerificationStatus::RESUBMITTED,
            'submitted_name' => 'Nguyen Van Resubmitted 2',
            'submitted_email' => 'resubmitted2@student.hcmue.edu.vn',
            'submitted_at' => now(),
        ]);

        // Clear cache so dashboard gets fresh data
        Cache::forget('admin_dashboard_data');
        Cache::forget('admin_dashboard_data_v2');

        $dashboardData = app(BuildAdminDashboardAction::class)->execute();

        // Count should be 3: 1 pending_review (from setUp), 1 under_review, 1 resubmitted
        $this->assertEquals(3, $dashboardData['snapshot']['pending_verification']);
    }
}
