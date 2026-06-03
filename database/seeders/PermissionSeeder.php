<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Deprecated compatibility seeder.
     *
     * RoleAndPermissionSeeder is the canonical owner for all roles and
     * permissions. Keep this delegating seeder so old commands remain safe.
     */
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);
    }
}
