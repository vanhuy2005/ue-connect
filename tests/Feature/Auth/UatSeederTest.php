<?php

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\UatSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UatSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Run prerequisite RoleAndPermissionSeeder
        $this->artisan('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
    }

    /**
     * Test that UatSeeder populates correct users, roles, and credentials.
     */
    public function test_uat_seeder_creates_expected_users_roles_and_permissions(): void
    {
        // 2. Run the UatSeeder
        $this->artisan('db:seed', ['--class' => UatSeeder::class]);

        // 3. Verify Admin User
        $admin = User::where('email', 'admin@hcmue.edu.vn')->first();
        $this->assertNotNull($admin);
        $this->assertEquals(AccountStatus::ACTIVE, $admin->account_status);
        $this->assertTrue(Hash::check('Password@123', $admin->password));
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->can('review_verification'));

        // 4. Verify Student User
        $student = User::where('email', 'student.test@hcmue.edu.vn')->first();
        $this->assertNotNull($student);
        $this->assertEquals(AccountStatus::REGISTERED, $student->account_status);
        $this->assertTrue(Hash::check('Password@123', $student->password));
        $this->assertCount(0, $student->roles);
    }
}
