<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Run reference seeders
        $this->call([
            RoleAndPermissionSeeder::class,
            FacultyAndAcademicProgramSeeder::class,
        ]);

        // 2. Create default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@hcmue.edu.vn'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'account_status' => AccountStatus::ACTIVE,
            ]
        );
        $admin->assignRole('admin');

        // 3. Create default Student User
        $student = User::firstOrCreate(
            ['email' => 'student@hcmue.edu.vn'],
            [
                'name' => 'Student User',
                'password' => bcrypt('password'),
                'account_status' => AccountStatus::REGISTERED,
            ]
        );
        $student->assignRole('student');
    }
}
