<?php

namespace Database\Seeders;

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
        // 1. Reference data (roles, permissions, faculties, programs)
        $this->call([
            RoleAndPermissionSeeder::class,
            FacultyAndAcademicProgramSeeder::class,
        ]);

        // 2. UAT test accounts (admin + unverified student)
        if (app()->environment(['local', 'testing'])) {
            $this->call([
                UatSeeder::class,
                UatFeedSeeder::class,
                DemoMentorSeeder::class,
            ]);
        } else {
            $this->command->warn('Skipped UAT/demo seeders: Not in local/testing environment.');
        }
    }
}
