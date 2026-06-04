<?php

namespace Tests\Feature\App;

use App\Enums\AccountStatus;
use App\Enums\VerificationStatus;
use App\Models\AcademicProgram;
use App\Models\Faculty;
use App\Models\User;
use App\Models\VerificationRequest;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ProfileSetupTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Faculty $faculty;

    protected AcademicProgram $program;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        $this->faculty = Faculty::create([
            'name' => 'Khoa Công nghệ Thông tin',
            'slug' => 'cntt',
            'status' => 'active',
        ]);

        $this->program = AcademicProgram::create([
            'faculty_id' => $this->faculty->id,
            'name' => 'Sư phạm Tin học',
            'slug' => 'sp-tin-hoc',
            'status' => 'active',
        ]);
    }

    public function test_registered_user_cannot_access_profile_setup(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::REGISTERED,
        ]);

        $response = $this->actingAs($user)->get(route('profile.setup'));
        $response->assertRedirect(route('verification.status'));
    }

    public function test_pending_verification_user_cannot_access_profile_setup(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::PENDING_VERIFICATION,
        ]);

        $response = $this->actingAs($user)->get(route('profile.setup'));
        $response->assertRedirect(route('verification.status'));
    }

    public function test_active_user_is_redirected_to_dashboard(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);

        $response = $this->actingAs($user)->get(route('profile.setup'));
        $response->assertRedirect(route('dashboard'));
    }

    public function test_profile_incomplete_user_can_access_profile_setup(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::PROFILE_INCOMPLETE,
        ]);

        $response = $this->actingAs($user)->get(route('profile.setup'));
        $response->assertOk();
    }

    public function test_profile_setup_loads_approved_verification_data(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::PROFILE_INCOMPLETE,
        ]);

        $request = VerificationRequest::create([
            'user_id' => $user->id,
            'role_requested' => 'student',
            'status' => VerificationStatus::APPROVED,
            'submitted_name' => 'Nguyen Van Verified',
            'submitted_student_code' => '48.01.103.999',
            'submitted_faculty_id' => $this->faculty->id,
            'submitted_academic_program_id' => $this->program->id,
            'submitted_cohort' => 'K48',
            'submitted_email' => $user->email,
        ]);

        Volt::actingAs($user)
            ->test('pages.app.profile-setup')
            ->assertSet('role_type', 'student')
            ->assertSet('display_name', 'Nguyen Van Verified')
            ->assertSet('student_code', '48.01.103.999')
            ->assertSet('faculty_id', $this->faculty->id)
            ->assertSet('academic_program_id', $this->program->id)
            ->assertSet('cohort', 'K48');
    }

    public function test_profile_setup_completes_student_profile_successfully(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::PROFILE_INCOMPLETE,
        ]);

        Volt::actingAs($user)
            ->test('pages.app.profile-setup')
            ->set('role_type', 'student')
            ->set('display_name', 'Student Test')
            ->set('bio', 'My student bio')
            ->set('student_code', '12345678')
            ->set('cohort', 'K47')
            ->set('faculty_id', $this->faculty->id)
            ->set('academic_program_id', $this->program->id)
            ->call('save')
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertEquals(AccountStatus::ACTIVE, $user->account_status);
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'display_name' => 'Student Test',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);
        $this->assertDatabaseHas('student_profiles', [
            'student_code' => '12345678',
            'cohort' => 'K47',
            'faculty_id' => $this->faculty->id,
            'academic_program_id' => $this->program->id,
        ]);
    }

    public function test_profile_setup_completes_alumni_profile_successfully(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::PROFILE_INCOMPLETE,
        ]);

        Volt::actingAs($user)
            ->test('pages.app.profile-setup')
            ->set('role_type', 'alumni')
            ->set('display_name', 'Alumni Test')
            ->set('bio', 'My alumni bio')
            ->set('graduation_year', 2020)
            ->set('willing_to_mentor', true)
            ->set('faculty_id', $this->faculty->id)
            ->set('academic_program_id', $this->program->id)
            ->call('save')
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertEquals(AccountStatus::ACTIVE, $user->account_status);
        $this->assertDatabaseHas('alumni_profiles', [
            'graduation_year' => 2020,
            'willing_to_mentor' => true,
            'faculty_id' => $this->faculty->id,
            'academic_program_id' => $this->program->id,
        ]);
    }

    public function test_profile_setup_completes_advisor_profile_successfully(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::PROFILE_INCOMPLETE,
        ]);

        Volt::actingAs($user)
            ->test('pages.app.profile-setup')
            ->set('role_type', 'advisor')
            ->set('display_name', 'Advisor Test')
            ->set('bio', 'My advisor bio')
            ->set('department', 'Software Engineering')
            ->set('title', 'Lecturer')
            ->set('faculty_id', $this->faculty->id)
            ->call('save')
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertEquals(AccountStatus::ACTIVE, $user->account_status);
        $this->assertDatabaseHas('advisor_profiles', [
            'department' => 'Software Engineering',
            'title' => 'Lecturer',
            'faculty_id' => $this->faculty->id,
        ]);
    }

    public function test_profile_setup_overwrites_existing_profile_successfully(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::PROFILE_INCOMPLETE,
        ]);

        // Create an existing profile for the user that will be soft-deleted and overwritten
        $existingProfile = $user->profile()->create([
            'display_name' => 'Old Name',
            'role_type' => 'student',
            'profile_status' => 'incomplete',
        ]);

        // Soft-delete the profile first to test the restore capability
        $existingProfile->delete();
        $this->assertTrue($existingProfile->trashed());

        Volt::actingAs($user)
            ->test('pages.app.profile-setup')
            ->set('role_type', 'student')
            ->set('display_name', 'Overwrite Test')
            ->set('bio', 'My overwrite bio')
            ->set('student_code', '87654321')
            ->set('cohort', 'K48')
            ->set('faculty_id', $this->faculty->id)
            ->set('academic_program_id', $this->program->id)
            ->call('save')
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertEquals(AccountStatus::ACTIVE, $user->account_status);

        // Assert that the profile is still present (restored) and updated
        $profile = $user->profile;
        $this->assertNotNull($profile);
        $this->assertFalse($profile->trashed());
        $this->assertEquals('Overwrite Test', $profile->display_name);
        $this->assertEquals('complete', $profile->profile_status);
    }
}
