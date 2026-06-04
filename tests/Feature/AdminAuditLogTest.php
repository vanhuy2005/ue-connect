<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_audit_logs()
    {
        $this->seed(AccessControlReferenceSeeder::class);

        Permission::findOrCreate('manage_permissions', 'web');
        Permission::findOrCreate('view_audit_log', 'web');
        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->givePermissionTo(['manage_permissions', 'view_audit_log']);

        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $user->assignRole('admin');

        AuditLog::create([
            'actor_id' => $user->id,
            'actor_type' => 'user',
            'action' => 'test_action',
            'target_type' => 'user',
            'target_id' => (string) $user->id,
            'reason' => 'unit test',
        ]);

        $response = $this->actingAs($user)->get(route('admin.audit-logs.index'));

        $response->assertStatus(200);
        $response->assertSeeText('test_action');
    }
}
