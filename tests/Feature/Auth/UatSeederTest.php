<?php

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Models\User;
use Database\Seeders\FacultyAndAcademicProgramSeeder;
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
        $this->artisan('db:seed', ['--class' => FacultyAndAcademicProgramSeeder::class]);
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
        $this->assertTrue(Hash::check('password', $admin->password));
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->can('review_verification'));

        $this->assertNotNull(User::where('email', 'superadmin@hcmue.edu.vn')->first());
        $this->assertNotNull(User::where('email', 'mentor.manager@hcmue.edu.vn')->first());
        $this->assertNotNull(User::where('email', 'moderator@hcmue.edu.vn')->first());
        $this->assertNotNull(User::where('email', 'verification.reviewer@hcmue.edu.vn')->first());

        // 4. Verify legacy unverified student user
        $student = User::where('email', 'student.test@hcmue.edu.vn')->first();
        $this->assertNotNull($student);
        $this->assertEquals(AccountStatus::REGISTERED, $student->account_status);
        $this->assertTrue(Hash::check('password', $student->password));
        $this->assertCount(0, $student->roles);
        $this->assertNotNull($student->profile?->studentProfile);

        $verifiedStudent = User::where('email', 'student@hcmue.edu.vn')->first();
        $this->assertNotNull($verifiedStudent);
        $this->assertTrue($verifiedStudent->hasRole('student'));
        $this->assertNotNull($verifiedStudent->profile?->studentProfile);

        $alumni = User::where('email', 'alumni.mentor@hcmue.edu.vn')->first();
        $this->assertNotNull($alumni);
        $this->assertTrue($alumni->hasRole('alumni'));
        $this->assertNotNull($alumni->profile?->alumniProfile);

        $advisor = User::where('email', 'advisor.mentor@hcmue.edu.vn')->first();
        $this->assertNotNull($advisor);
        $this->assertTrue($advisor->hasRole('advisor'));
        $this->assertNotNull($advisor->profile?->advisorProfile);
    }
}
