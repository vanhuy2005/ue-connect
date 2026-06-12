<?php

namespace Tests\Feature\Settings;

use App\Actions\Settings\EnsureUserSettingsExistAction;
use App\Enums\AccountStatus;
use App\Models\AcademicProgram;
use App\Models\BlockedUser;
use App\Models\Faculty;
use App\Models\User;
use Database\Seeders\Reference\AcademicStructureSeeder;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class BlockedUsersTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $target;

    protected Faculty $faculty;

    protected AcademicProgram $program;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);
        $this->artisan('db:seed', ['--class' => AcademicStructureSeeder::class]);

        $this->faculty = Faculty::first();
        $this->program = AcademicProgram::first();

        // Main User
        $this->user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
            'name' => 'John Doe',
        ]);
        $this->user->assignRole('student');
        $profile = $this->user->profile()->create([
            'display_name' => 'John Doe Display',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);
        $profile->studentProfile()->create([
            'student_code' => '49.01.104.001',
            'faculty_id' => $this->faculty->id,
            'academic_program_id' => $this->program->id,
        ]);

        // Target User
        $this->target = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
            'name' => 'Jane Victim',
        ]);
        $this->target->assignRole('student');
        $targetProfile = $this->target->profile()->create([
            'display_name' => 'Jane Victim Display',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);
        $targetProfile->studentProfile()->create([
            'student_code' => '49.01.104.002',
            'faculty_id' => $this->faculty->id,
            'academic_program_id' => $this->program->id,
        ]);

        app(EnsureUserSettingsExistAction::class)->execute($this->user);
    }

    public function test_graceful_view_when_i_blocked_them(): void
    {
        $this->actingAs($this->user);

        // Block Jane
        BlockedUser::create([
            'blocker_id' => $this->user->id,
            'blocked_id' => $this->target->id,
        ]);

        // Try viewing Jane's profile
        Volt::test('pages.app.profile', ['user' => $this->target])
            ->assertSee('Bạn đã chặn tài khoản này')
            ->assertSee('Bỏ chặn tài khoản')
            ->assertDontSee('Bài viết');
    }

    public function test_graceful_view_when_they_blocked_me(): void
    {
        $this->actingAs($this->user);

        // Jane blocks John
        BlockedUser::create([
            'blocker_id' => $this->target->id,
            'blocked_id' => $this->user->id,
        ]);

        // Try viewing Jane's profile
        Volt::test('pages.app.profile', ['user' => $this->target])
            ->assertSee('Hồ sơ không khả dụng')
            ->assertDontSee('Bạn đã chặn tài khoản này');
    }

    public function test_unblock_removes_relationship(): void
    {
        $this->actingAs($this->user);

        // Block Jane
        BlockedUser::create([
            'blocker_id' => $this->user->id,
            'blocked_id' => $this->target->id,
        ]);

        Volt::test('pages.app.settings.privacy', ['subSection' => 'blocked'])
            ->call('confirmUnblock', $this->target->id, $this->target->name)
            ->call('executeUnblock')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('blocked_users', [
            'blocker_id' => $this->user->id,
            'blocked_id' => $this->target->id,
        ]);
    }
}
