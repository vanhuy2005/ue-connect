<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * UAT Seeder — creates well-known test accounts for manual testing.
 *
 * Accounts created:
 *   admin@hcmue.edu.vn   / Password@123  — admin role, ACTIVE status
 *   student.test@hcmue.edu.vn / Password@123  — no role, REGISTERED status (needs verification)
 *
 * Idempotent: safe to run multiple times (uses updateOrCreate).
 * NOT for production — only called from DatabaseSeeder in local/testing environments.
 */
class UatSeeder extends Seeder
{
    public function run(): void
    {
        // Output the active DB
        $this->command->info('Database: '.DB::connection()->getDatabaseName());

        // Reset permission cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Ensure admin role exists
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // UAT 1 — Admin account
        $admin = User::updateOrCreate(
            ['email' => 'admin@hcmue.edu.vn'],
            [
                'name' => 'UEConnect Admin',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
                'account_status' => AccountStatus::ACTIVE,
                'last_login_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        // UAT 2 — Unverified student account
        $student = User::updateOrCreate(
            ['email' => 'student.test@hcmue.edu.vn'],
            [
                'name' => 'Nguyễn Văn Test',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
                'account_status' => AccountStatus::REGISTERED,
                'last_login_at' => now(),
            ]
        );
        // Roles are NOT assigned at registration — only upon admin verification approval
        $student->syncRoles([]);

        $this->command->info('✅ UAT accounts seeded:');
        $this->command->table(
            ['Email', 'Password', 'Role', 'Status'],
            [
                ['admin@hcmue.edu.vn', 'Password@123', 'admin', 'active'],
                ['student.test@hcmue.edu.vn', 'Password@123', '(none — pending verification)', 'registered'],
            ]
        );
    }
}
