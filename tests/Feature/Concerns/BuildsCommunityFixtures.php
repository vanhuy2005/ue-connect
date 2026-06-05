<?php

namespace Tests\Feature\Concerns;

use App\Enums\AccountStatus;
use App\Models\User;
use Spatie\Permission\Models\Role;

trait BuildsCommunityFixtures
{
    protected function createActiveUser(string $role = 'student'): User
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);

        $user->profile()->create([
            'display_name' => $user->name,
            'role_type' => $role,
            'profile_status' => 'complete',
            'profile_completed_at' => now(),
        ]);

        Role::findOrCreate($role, 'web');
        $user->assignRole($role);

        return $user;
    }

    protected function createAdminUser(): User
    {
        Role::findOrCreate('admin', 'web');
        $admin = $this->createActiveUser('teacher');
        $admin->assignRole('admin');

        return $admin;
    }
}
