<?php

namespace Tests\Feature\Settings;

use App\Actions\Settings\EnsureUserSettingsExistAction;
use App\Enums\AccountStatus;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\AcademicProgram;
use App\Models\Faculty;
use App\Models\User;
use Database\Seeders\Reference\AcademicStructureSeeder;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class SettingsPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Faculty $faculty;

    protected AcademicProgram $program;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);
        $this->artisan('db:seed', ['--class' => AcademicStructureSeeder::class]);

        $this->faculty = Faculty::first();
        $this->program = AcademicProgram::first();

        $this->user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
            'email' => 'myemail@hcmue.edu.vn',
            'name' => 'Main Student',
        ]);
        $this->user->assignRole('student');

        $profile = $this->user->profile()->create([
            'display_name' => 'Main Student Display',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);
        $profile->studentProfile()->create([
            'student_code' => '49.01.104.001',
            'faculty_id' => $this->faculty->id,
            'academic_program_id' => $this->program->id,
        ]);

        // Provision settings
        app(EnsureUserSettingsExistAction::class)->execute($this->user);
    }

    public function test_updating_privacy_persists_in_database(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.settings.privacy')
            ->set('show_faculty', false)
            ->set('show_major', false)
            ->call('savePrivacy')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('profile_privacy_settings', [
            'user_id' => $this->user->id,
            'show_faculty' => false,
            'show_major' => false,
        ]);
    }

    public function test_user_cannot_set_restricted_visibility_states(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.settings.privacy')
            ->set('profile_visibility', 'hidden_by_moderation')
            ->set('discovery_visibility', 'forced_hidden')
            ->call('savePrivacy');

        // Verify that administrative values are rejected and database remains unchanged or clean
        $this->assertDatabaseMissing('profile_privacy_settings', [
            'user_id' => $this->user->id,
            'profile_visibility' => 'hidden_by_moderation',
        ]);
        $this->assertDatabaseMissing('profile_privacy_settings', [
            'user_id' => $this->user->id,
            'discovery_visibility' => 'forced_hidden',
        ]);
    }

    public function test_full_mssv_and_email_never_public_to_strangers(): void
    {
        $otherUser = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
            'email' => 'otherstudent@hcmue.edu.vn',
            'name' => 'Other Student',
        ]);
        $otherUser->assignRole('student');

        $otherProfile = $otherUser->profile()->create([
            'display_name' => 'Other Student Display',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);
        $otherProfile->studentProfile()->create([
            'student_code' => '49.01.104.999',
            'faculty_id' => $this->faculty->id,
            'academic_program_id' => $this->program->id,
        ]);

        $this->actingAs($this->user);

        // View other user's profile
        Volt::test('pages.app.profile', ['user' => $otherUser])
            ->set('activeTab', 'about')
            ->assertDontSee('otherstudent@hcmue.edu.vn') // Email hidden by default
            ->assertDontSee('49.01.104.999') // Full MSSV never public
            ->assertSee('49.01'); // Masked MSSV is visible
    }

    public function test_discovery_excludes_users_with_disabled_discovery(): void
    {
        $otherUser = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $otherUser->assignRole('student');

        $otherProfile = $otherUser->profile()->create([
            'display_name' => 'Private Person',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);
        $otherProfile->studentProfile()->create([
            'student_code' => '49.01.104.555',
            'faculty_id' => $this->faculty->id,
            'academic_program_id' => $this->program->id,
        ]);

        app(EnsureUserSettingsExistAction::class)->execute($otherUser);
        $otherUser->profilePrivacySetting()->update(['discovery_visibility' => 'disabled']);

        $this->actingAs($this->user);

        Volt::test('pages.app.connections', ['activeTab' => 'discovery'])
            ->assertDontSee('Private Person');
    }

    public function test_private_profile_hides_content_for_non_connections(): void
    {
        $otherUser = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
            'name' => 'Private Student',
        ]);
        $otherUser->assignRole('student');

        $otherProfile = $otherUser->profile()->create([
            'display_name' => 'Private Student Display',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);
        $otherProfile->studentProfile()->create([
            'student_code' => '49.01.104.777',
            'faculty_id' => $this->faculty->id,
            'academic_program_id' => $this->program->id,
        ]);

        app(EnsureUserSettingsExistAction::class)->execute($otherUser);
        $otherUser->profilePrivacySetting()->update(['profile_visibility' => 'connections_only']);

        // Create a post for other user
        $otherUser->posts()->create([
            'body' => 'Secret message for friends only',
            'visibility' => PostVisibility::VERIFIED_USERS,
            'status' => PostStatus::PUBLISHED,
        ]);

        $this->actingAs($this->user);

        // Render target profile as a stranger (not connected)
        Volt::test('pages.app.profile', ['user' => $otherUser])
            ->assertSee('Trang cá nhân riêng tư')
            ->assertDontSee('Secret message for friends only');
    }

    public function test_updating_new_privacy_preferences_persists_in_database(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.settings.privacy')
            ->set('mentions_preference', 'connections')
            ->set('tags_preference', 'nobody')
            ->set('online_status_visibility', 'nobody')
            ->call('savePrivacy')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('profile_privacy_settings', [
            'user_id' => $this->user->id,
            'mentions_preference' => 'connections',
            'tags_preference' => 'nobody',
            'online_status_visibility' => 'nobody',
        ]);
    }
}
