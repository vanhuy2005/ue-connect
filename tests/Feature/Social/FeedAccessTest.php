<?php

namespace Tests\Feature\Social;

use App\Enums\AccountStatus;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);
    }

    public function test_guests_cannot_access_feed(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_suspended_users_cannot_access_feed(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::SUSPENDED,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('system.account-restricted'));
    }

    public function test_banned_users_cannot_access_feed(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::BANNED,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('system.account-restricted'));
    }

    public function test_incomplete_profile_users_are_redirected_to_profile_setup(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::PROFILE_INCOMPLETE,
        ]);
        $user->assignRole('student');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('profile.setup'));
    }

    public function test_active_verified_users_with_completed_profiles_can_access_feed(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $user->assignRole('student');

        $user->profile()->create([
            'display_name' => 'Verified Student',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('app.home');
    }
}
