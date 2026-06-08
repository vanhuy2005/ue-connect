<?php

namespace Database\Seeders\Uat;

use Database\Seeders\SeedPotentialMentors;
use Illuminate\Database\Seeder;

class UatScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UatAccountSeeder::class,
            SeedPotentialMentors::class,
            UatVerificationSeeder::class,
            UatFeedSeeder::class,
            UatCommunitySeeder::class,
            UatMentorSeeder::class,
        ]);
    }
}
