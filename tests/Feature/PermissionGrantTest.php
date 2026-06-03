<?php

namespace Tests\Feature;

use App\Models\PermissionGrant;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionGrantTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_grant_and_revoke_permission()
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $managePermissions = Permission::findOrCreate('manage_permissions', 'web');
        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->givePermissionTo($managePermissions);

        $admin = User::factory()->create();
        if (method_exists($admin, 'assignRole')) {
            $admin->assignRole('admin');
        }

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.permission-grants.store'), [
            'user_id' => $user->id,
            'permission_key' => 'manage_users',
            'reason' => 'Testing grant',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('permission_grants', [
            'user_id' => $user->id,
            'permission_key' => 'manage_users',
            'status' => 'active',
        ]);

        $grant = PermissionGrant::where('user_id', $user->id)->first();

        $revokeResponse = $this->actingAs($admin)->post(route('admin.permission-grants.revoke', ['grant' => $grant->id]), [
            'reason' => 'No longer needed',
        ]);

        $revokeResponse->assertStatus(302);

        $this->assertDatabaseHas('permission_grants', [
            'id' => $grant->id,
            'status' => 'revoked',
        ]);
    }
}
