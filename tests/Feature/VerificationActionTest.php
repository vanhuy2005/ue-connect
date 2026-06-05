<?php

namespace Tests\Feature;

use App\Actions\Admin\ReviewVerificationAction;
use App\Enums\AccountStatus;
use App\Enums\VerificationStatus;
use App\Models\AcademicProgram;
use App\Models\Faculty;
use App\Models\Profile;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Services\AuditService;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_verification_action_creates_audit_and_updates_status(): void
    {
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        $admin = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $admin->assignRole('admin');

        $user = User::factory()->create(['account_status' => AccountStatus::PENDING_VERIFICATION]);
        $faculty = Faculty::create([
            'name' => 'Khoa Công nghệ Thông tin',
            'slug' => 'cntt',
            'status' => 'active',
        ]);
        $program = AcademicProgram::create([
            'faculty_id' => $faculty->id,
            'name' => 'Sư phạm Tin học',
            'slug' => 'sp-tin-hoc',
            'status' => 'active',
        ]);

        $verificationRequest = VerificationRequest::create([
            'user_id' => $user->id,
            'role_requested' => 'student',
            'status' => VerificationStatus::PENDING_REVIEW,
            'submitted_name' => 'Test Student',
            'submitted_student_code' => 'S12345',
            'submitted_email' => $user->email,
            'submitted_faculty_id' => $faculty->id,
            'submitted_academic_program_id' => $program->id,
            'submitted_cohort' => 'K48',
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin);

        app(ReviewVerificationAction::class)->execute($verificationRequest, [
            'action' => 'approve',
            'reason' => 'Looks good for approval.',
            'notify_user' => false,
        ], app(AuditService::class), $admin);

        $this->assertDatabaseHas('verification_requests', [
            'id' => $verificationRequest->id,
            'status' => VerificationStatus::APPROVED->value,
            'reviewed_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('verification_review_actions', [
            'verification_request_id' => $verificationRequest->id,
            'admin_id' => $admin->id,
            'action_key' => 'approve',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action_key' => 'verification.approve',
            'target_type' => 'verification_requests',
            'target_id' => $verificationRequest->id,
        ]);

        $user->refresh();
        $this->assertEquals(AccountStatus::PROFILE_INCOMPLETE, $user->account_status);
        $this->assertTrue($user->hasRole('student'));
        $this->assertNotNull(Profile::where('user_id', $user->id)->first());
    }
}
