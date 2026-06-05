<?php

namespace Database\Seeders\Testing;

use Database\Seeders\Reference\AcademicStructureSeeder;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Database\Seeder;

class MinimalTestingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AccessControlReferenceSeeder::class,
            AcademicStructureSeeder::class,
        ]);
    }
}
