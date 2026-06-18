<?php

namespace Tests\Feature;

use App\Models\PermissionGrant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionGrantGateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An active, non-scoped PermissionGrant should grant access via Gate.
     */
    public function test_active_non_scoped_grant_passes_gate(): void
    {
        $user = User::factory()->create();
        $granter = User::factory()->create();

        PermissionGrant::factory()->create([
            'user_id' => $user->id,
            'permission_key' => 'manage_users',
            'scope_type' => null,
            'scope_id' => null,
            'granted_by' => $granter->id,
            'status' => 'active',
            'starts_at' => null,
            'expires_at' => null,
        ]);

        $this->assertTrue($user->can('manage_users'));
    }

    /**
     * A revoked grant must NOT grant access.
     */
    public function test_revoked_grant_does_not_pass_gate(): void
    {
        $user = User::factory()->create();
        $granter = User::factory()->create();

        PermissionGrant::factory()->create([
            'user_id' => $user->id,
            'permission_key' => 'manage_users',
            'scope_type' => null,
            'scope_id' => null,
            'granted_by' => $granter->id,
            'status' => 'revoked',
            'starts_at' => null,
            'expires_at' => null,
        ]);

        $this->assertFalse($user->can('manage_users'));
    }

    /**
     * An expired grant must NOT grant access.
     */
    public function test_expired_grant_does_not_pass_gate(): void
    {
        $user = User::factory()->create();
        $granter = User::factory()->create();

        PermissionGrant::factory()->create([
            'user_id' => $user->id,
            'permission_key' => 'manage_users',
            'scope_type' => null,
            'scope_id' => null,
            'granted_by' => $granter->id,
            'status' => 'active',
            'starts_at' => null,
            'expires_at' => now()->subMinute(),
        ]);

        $this->assertFalse($user->can('manage_users'));
    }

    /**
     * A grant with a future starts_at must NOT grant access yet.
     */
    public function test_future_grant_does_not_pass_gate_before_start(): void
    {
        $user = User::factory()->create();
        $granter = User::factory()->create();

        PermissionGrant::factory()->create([
            'user_id' => $user->id,
            'permission_key' => 'manage_users',
            'scope_type' => null,
            'scope_id' => null,
            'granted_by' => $granter->id,
            'status' => 'active',
            'starts_at' => now()->addHour(),
            'expires_at' => null,
        ]);

        $this->assertFalse($user->can('manage_users'));
    }

    /**
     * A scoped grant must NOT apply system-wide; it is handled by policies.
     */
    public function test_scoped_grant_does_not_pass_global_gate(): void
    {
        $user = User::factory()->create();
        $granter = User::factory()->create();

        PermissionGrant::factory()->create([
            'user_id' => $user->id,
            'permission_key' => 'manage_community',
            'scope_type' => 'community',
            'scope_id' => 99,
            'granted_by' => $granter->id,
            'status' => 'active',
            'starts_at' => null,
            'expires_at' => null,
        ]);

        // The Gate::before hook excludes scoped grants, so ->can() returns false
        // (no Spatie permission either, since this user has no role granting it).
        $this->assertFalse($user->can('manage_community'));
    }

    /**
     * Granting via Gate::before must not interfere with users who have the
     * permission through their Spatie role.
     */
    public function test_role_based_permission_still_works_alongside_grants(): void
    {
        $admin = User::factory()->create();

        Role::findOrCreate('admin', 'web')
            ->givePermissionTo(
                Permission::findOrCreate('manage_users', 'web')
            );

        $admin->assignRole('admin');

        $this->assertTrue($admin->can('manage_users'));
    }
}
