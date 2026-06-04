<?php

namespace Database\Seeders;

use Database\Seeders\Reference\AcademicStructureSeeder;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Database\Seeders\Testing\MinimalTestingSeeder;
use Database\Seeders\Uat\UatScenarioSeeder;
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
        if (app()->environment('testing')) {
            $this->call(MinimalTestingSeeder::class);

            return;
        }

        $this->call([
            AccessControlReferenceSeeder::class,
            AcademicStructureSeeder::class,
        ]);

        if (app()->environment(['local', 'staging'])) {
            $this->call(UatScenarioSeeder::class);
        } else {
            $this->command->warn('Skipped UAT seeders outside local/staging. Reference data only was seeded.');
        }
    }
}
