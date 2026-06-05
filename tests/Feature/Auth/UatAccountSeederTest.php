<?php

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Models\User;
use Database\Seeders\Reference\AcademicStructureSeeder;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Database\Seeders\Uat\UatAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UatAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);
        $this->artisan('db:seed', ['--class' => AcademicStructureSeeder::class]);
    }

    /**
     * Test that UatAccountSeeder populates correct users, roles, and credentials.
     */
    public function test_uat_seeder_creates_expected_users_roles_and_permissions(): void
    {
        $this->artisan('db:seed', ['--class' => UatAccountSeeder::class]);

        $this->assertFalse(Role::where('name', 'advisor')->exists());

        $admin = User::where('email', 'admin@teacher.hcmue.edu.vn')->first();
        $this->assertNotNull($admin);
        $this->assertEquals(AccountStatus::ACTIVE, $admin->account_status);
        $this->assertTrue(Hash::check('password', $admin->password));
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->can('review_verification'));

        $this->assertNotNull(User::where('email', 'superadmin@teacher.hcmue.edu.vn')->first());
        $this->assertNotNull(User::where('email', 'mentor.manager@teacher.hcmue.edu.vn')->first());
        $this->assertNotNull(User::where('email', 'moderator@teacher.hcmue.edu.vn')->first());
        $this->assertNotNull(User::where('email', 'verification.reviewer@teacher.hcmue.edu.vn')->first());

        $student = User::where('email', 'student.test@student.hcmue.edu.vn')->first();
        $this->assertNotNull($student);
        $this->assertEquals(AccountStatus::REGISTERED, $student->account_status);
        $this->assertTrue(Hash::check('password', $student->password));
        $this->assertCount(0, $student->roles);
        $this->assertNotNull($student->profile?->studentProfile);

        $verifiedStudent = User::where('email', 'student@student.hcmue.edu.vn')->first();
        $this->assertNotNull($verifiedStudent);
        $this->assertTrue($verifiedStudent->hasRole('student'));
        $this->assertNotNull($verifiedStudent->profile?->studentProfile);

        $alumni = User::where('email', 'alumni.mentor@gmail.com')->first();
        $this->assertNotNull($alumni);
        $this->assertTrue($alumni->hasRole('alumni'));
        $this->assertNotNull($alumni->profile?->alumniProfile);

        $teacher = User::where('email', 'teacher.mentor@teacher.hcmue.edu.vn')->first();
        $this->assertNotNull($teacher);
        $this->assertTrue($teacher->hasRole('teacher'));
        $this->assertFalse($teacher->hasRole('advisor'));
        $this->assertNotNull($teacher->profile?->advisorProfile);
    }
}
