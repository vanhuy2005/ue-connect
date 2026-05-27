<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DebugUatUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ueconnect:debug-uat-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safe local debugging of seeded UAT user accounts and their permissions/roles.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('==================================================');
        $this->info('UEConnect UAT Users Debugger');
        $this->info('==================================================');

        $dbName = DB::connection()->getDatabaseName();
        $this->line('Current Database: '.$dbName);

        // 1. Verify Admin User
        $adminEmail = 'admin@hcmue.edu.vn';
        $admin = User::where('email', $adminEmail)->first();

        if ($admin) {
            $this->info("Admin user '{$adminEmail}' exists: yes");
            $this->line('Admin account status: '.$admin->account_status->value);
            $this->line('Admin intended identity type: '.($admin->intended_identity_type?->value ?? 'null'));
            $hasAdminRole = $admin->hasRole('admin') ? 'yes' : 'no';
            $this->line('Admin has admin role: '.$hasAdminRole);

            // Check review_verification permission
            $canReview = $admin->can('review_verification') ? 'yes' : 'no';
            $this->line('Admin can review_verification: '.$canReview);

            // Check password
            $passwordOk = Hash::check('Password@123', $admin->password) ? 'yes' : 'no';
            $this->line('Password check (Password@123): '.$passwordOk);
        } else {
            $this->error("Admin user '{$adminEmail}' exists: NO");
        }

        $this->line('--------------------------------------------------');

        // 2. Verify Student User
        $studentEmail = 'student.test@hcmue.edu.vn';
        $student = User::where('email', $studentEmail)->first();

        if ($student) {
            $this->info("Student user '{$studentEmail}' exists: yes");
            $this->line('Student account status: '.$student->account_status->value);
            $this->line('Student intended identity type: '.($student->intended_identity_type?->value ?? 'null'));
            $hasRoles = $student->roles()->count() > 0 ? 'yes ('.implode(', ', $student->getRoleNames()->toArray()).')' : 'no';
            $this->line('Student has roles: '.$hasRoles);

            // Check password
            $passwordOk = Hash::check('Password@123', $student->password) ? 'yes' : 'no';
            $this->line('Password check (Password@123): '.$passwordOk);
        } else {
            $this->error("Student user '{$studentEmail}' exists: NO");
        }

        $this->info('==================================================');

        return Command::SUCCESS;
    }
}
