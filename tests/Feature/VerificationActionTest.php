<?php

namespace Tests\Feature;

use App\Actions\Admin\ReviewVerificationAction;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VerificationActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_verification_action_creates_audit_and_updates_status()
    {
        $admin = User::factory()->create();
        // ensure admin role exists and assign
        if (class_exists(Role::class) && method_exists($admin, 'assignRole')) {
            Role::firstOrCreate(['name' => 'admin']);
            $admin->assignRole('admin');
        }

        $user = User::factory()->create();

        $vr = VerificationRequest::create([
            'user_id' => $user->id,
            'role_requested' => 'student',
            'status' => 'pending_review',
            'submitted_name' => 'Test Student',
            'submitted_student_code' => 'S12345',
            'submitted_email' => $user->email,
        ]);

        $this->actingAs($admin);

        $audit = app(AuditService::class);
        $action = new ReviewVerificationAction;

        $action->execute($vr, ['action' => 'approve', 'reason' => 'Looks good', 'notify_user' => false], $audit);

        $this->assertDatabaseHas('verification_requests', [
            'id' => $vr->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'target_type' => 'verification_request',
            'target_id' => $vr->id,
        ]);
    }
}
