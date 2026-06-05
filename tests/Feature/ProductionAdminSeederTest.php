<?php

namespace Tests\Feature;

use App\Models\AdvisorProfile;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\Reference\AcademicStructureSeeder;
use Database\Seeders\Reference\ProductionAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionAdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_admin_seeder_wipes_other_users_and_seeds_super_admin(): void
    {
        // 1. Seed base academic structure (needed for faculty association)
        $this->seed(AcademicStructureSeeder::class);

        // 2. Insert dummy transactional data that should be wiped
        $dummyUser = User::create([
            'name' => 'Dummy Student',
            'email' => 'student.dummy@hcmue.edu.vn',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'student.dummy@hcmue.edu.vn',
        ]);

        // 3. Run ProductionAdminSeeder
        $this->seed(ProductionAdminSeeder::class);

        // 4. Assert the dummy user is deleted
        $this->assertDatabaseMissing('users', [
            'email' => 'student.dummy@hcmue.edu.vn',
        ]);

        // 5. Assert the Super Admin is created and configured properly
        $this->assertDatabaseHas('users', [
            'email' => 'admin@teacher.hcmue.edu.vn',
        ]);

        $adminUser = User::where('email', 'admin@teacher.hcmue.edu.vn')->firstOrFail();
        $this->assertTrue($adminUser->hasRole('admin'));

        // 6. Assert Profile exists
        $profile = Profile::where('user_id', $adminUser->id)->firstOrFail();
        $this->assertEquals('UEConnect Admin', $profile->display_name);

        // 7. Assert AdvisorProfile exists
        $advisorProfile = AdvisorProfile::where('profile_id', $profile->id)->firstOrFail();
        $this->assertNotNull($advisorProfile->faculty_id);
    }
}
