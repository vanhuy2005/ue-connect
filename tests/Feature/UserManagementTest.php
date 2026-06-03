<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_suspend_ban_and_reactivate_user(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        Permission::findOrCreate('manage_users', 'web');
        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->givePermissionTo('manage_users');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $target = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.users.suspend', $target), [
            'reason' => 'Testing suspend action',
        ])->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'account_status' => 'suspended',
        ]);

        $this->actingAs($admin)->post(route('admin.users.ban', $target), [
            'reason' => 'Testing ban action',
        ])->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'account_status' => 'banned',
        ]);

        $this->actingAs($admin)->post(route('admin.users.reactivate', $target), [
            'reason' => 'Testing reactivate action',
        ])->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'account_status' => 'active',
        ]);
    }
}
