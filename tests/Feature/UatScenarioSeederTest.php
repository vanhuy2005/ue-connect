<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\MentorProfile;
use App\Models\PermissionGrant;
use App\Models\User;
use App\Models\VerificationRequest;
use Database\Seeders\Reference\AcademicStructureSeeder;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Database\Seeders\Uat\UatScenarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UatScenarioSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_uat_scenario_seeder_creates_comprehensive_uat_data(): void
    {
        $this->seed(AccessControlReferenceSeeder::class);
        $this->seed(AcademicStructureSeeder::class);
        $this->seed(UatScenarioSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'student@hcmue.edu.vn',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@hcmue.edu.vn',
        ]);
        $this->assertDatabaseHas('verification_requests', [
            'status' => 'pending_review',
        ]);
        $this->assertDatabaseHas('verification_requests', [
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('communities', [
            'slug' => 'clb-cong-nghe-giao-duc',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('permission_grants', [
            'permission_key' => 'manage_community',
            'scope_type' => 'community',
            'status' => 'active',
        ]);

        $this->assertGreaterThanOrEqual(6, VerificationRequest::count());
        $this->assertGreaterThanOrEqual(4, Community::count());
        $this->assertGreaterThanOrEqual(3, PermissionGrant::where('scope_type', 'community')->count());
        $this->assertTrue(
            User::where('email', 'alumni.mentor@hcmue.edu.vn')->firstOrFail()->can('mentor_access'),
            'Approved alumni mentor UAT account should be able to access mentor features.'
        );
        $this->assertTrue(
            MentorProfile::where('mentor_visibility', true)->where('is_public_ready', true)->exists(),
            'UAT mentor seed should include at least one visible and public-ready mentor profile.'
        );
    }
}
