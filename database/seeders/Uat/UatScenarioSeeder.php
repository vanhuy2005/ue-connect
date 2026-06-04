<?php

namespace Database\Seeders\Uat;

use Illuminate\Database\Seeder;

class UatScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UatAccountSeeder::class,
            UatVerificationSeeder::class,
            UatFeedSeeder::class,
            UatCommunitySeeder::class,
            UatMentorSeeder::class,
        ]);
    }
}
