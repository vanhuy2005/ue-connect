<?php

namespace Tests\Feature\Social;

use App\Enums\AccountStatus;
use App\Models\AcademicProgram;
use App\Models\BlockedUser;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class DiscoveryUpgradeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Faculty $facultyCs;

    protected AcademicProgram $programCs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'FacultyAndAcademicProgramSeeder']);

        $this->facultyCs = Faculty::where('slug', 'cntt')->first();
        $this->programCs = AcademicProgram::where('slug', 'cong-nghe-thong-tin')->first();

        // Main User
        $this->user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->user->assignRole('student');
        $profile = $this->user->profile()->create([
            'display_name' => 'John Doe',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
            'bio' => 'An enthusiastic developer',
        ]);
        $profile->studentProfile()->create([
            'student_code' => '49.01.104.001',
            'faculty_id' => $this->facultyCs->id,
            'academic_program_id' => $this->programCs->id,
            'cohort' => 'K49',
        ]);
    }

    public function test_only_active_verified_discoverable_users_are_visible(): void
    {
        $this->actingAs($this->user);

        // Visible User
        $visibleUser = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $visibleUser->assignRole('student');
        $visibleProfile = $visibleUser->profile()->create([
            'display_name' => 'Visible Student',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);
        $visibleProfile->studentProfile()->create([
            'student_code' => '49.01.104.002',
            'faculty_id' => $this->facultyCs->id,
            'academic_program_id' => $this->programCs->id,
        ]);

        // Non-active User
        $inactiveUser = User::factory()->create(['account_status' => AccountStatus::SUSPENDED]);
        $inactiveUser->assignRole('student');
        $inactiveProfile = $inactiveUser->profile()->create([
            'display_name' => 'Suspended Student',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);

        // Non-discoverable User
        $privateUser = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $privateUser->assignRole('student');
        $privateUser->profile()->create([
            'display_name' => 'Private Student',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => false,
        ]);

        Volt::test('pages.app.discovery')
            ->assertSee('Visible Student')
            ->assertDontSee('Suspended Student')
            ->assertDontSee('Private Student');
    }

    public function test_mutual_blocks_are_excluded_from_discovery(): void
    {
        $this->actingAs($this->user);

        // Blocked by current user
        $blockedUser = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $blockedUser->assignRole('student');
        $blockedProfile = $blockedUser->profile()->create([
            'display_name' => 'Blocked Student',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);
        $blockedProfile->studentProfile()->create([
            'student_code' => '49.01.104.003',
            'faculty_id' => $this->facultyCs->id,
            'academic_program_id' => $this->programCs->id,
        ]);

        BlockedUser::create([
            'blocker_id' => $this->user->id,
            'blocked_id' => $blockedUser->id,
        ]);

        // User blocking current user
        $blockerUser = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $blockerUser->assignRole('student');
        $blockerProfile = $blockerUser->profile()->create([
            'display_name' => 'Blocker Student',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);
        $blockerProfile->studentProfile()->create([
            'student_code' => '49.01.104.004',
            'faculty_id' => $this->facultyCs->id,
            'academic_program_id' => $this->programCs->id,
        ]);

        BlockedUser::create([
            'blocker_id' => $blockerUser->id,
            'blocked_id' => $this->user->id,
        ]);

        Volt::test('pages.app.discovery')
            ->assertDontSee('Blocked Student')
            ->assertDontSee('Blocker Student');
    }

    public function test_search_by_faculty_major_bio_and_display_name(): void
    {
        $this->actingAs($this->user);

        // Faculty Tieng Anh User
        $englishFaculty = Faculty::where('slug', 'tieng-anh')->first();
        $englishUser = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $englishUser->assignRole('student');
        $englishProfile = $englishUser->profile()->create([
            'display_name' => 'Alice English',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
            'bio' => 'Teaching enthusiast',
        ]);
        $englishProfile->studentProfile()->create([
            'student_code' => '49.01.104.005',
            'faculty_id' => $englishFaculty->id,
            'academic_program_id' => AcademicProgram::where('slug', 'ngon-ngu-anh')->first()->id,
        ]);

        Volt::test('pages.app.discovery')
            ->set('search', 'Alice')
            ->assertSee('Alice English')
            ->set('search', 'Tiếng Anh')
            ->assertSee('Alice English')
            ->set('search', 'Ngôn ngữ Anh')
            ->assertSee('Alice English')
            ->set('search', 'Teaching')
            ->assertSee('Alice English')
            ->set('search', 'Nonexistent')
            ->assertDontSee('Alice English');
    }

    public function test_role_type_filtering(): void
    {
        $this->actingAs($this->user);

        // Advisor User
        $advisorUser = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $advisorUser->assignRole('advisor');
        $advisorProfile = $advisorUser->profile()->create([
            'display_name' => 'Professor Smith',
            'role_type' => 'advisor',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);
        $advisorProfile->advisorProfile()->create([
            'faculty_id' => $this->facultyCs->id,
        ]);

        // Student User
        $studentUser = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $studentUser->assignRole('student');
        $studentProfile = $studentUser->profile()->create([
            'display_name' => 'Bob Student',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);
        $studentProfile->studentProfile()->create([
            'student_code' => '49.01.104.006',
            'faculty_id' => $this->facultyCs->id,
            'academic_program_id' => $this->programCs->id,
        ]);

        Volt::test('pages.app.discovery')
            ->set('roleFilter', 'advisor')
            ->assertSee('Professor Smith')
            ->assertDontSee('Bob Student')
            ->set('roleFilter', 'student')
            ->assertSee('Bob Student')
            ->assertDontSee('Professor Smith');
    }

    public function test_shared_context_resolver_returns_commonalities(): void
    {
        $this->actingAs($this->user);

        // Shared cohort student
        $studentUser = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $studentUser->assignRole('student');
        $studentProfile = $studentUser->profile()->create([
            'display_name' => 'Bob Scholar',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);
        $studentProfile->studentProfile()->create([
            'student_code' => '49.01.104.007',
            'faculty_id' => $this->facultyCs->id,
            'academic_program_id' => $this->programCs->id,
            'cohort' => 'K49',
        ]);

        Volt::test('pages.app.discovery')
            ->assertSee('Cùng ngành')
            ->assertSee('Cùng khóa')
            ->assertSee('Cùng khoa');
    }
}
