<?php

namespace Tests\Feature\App;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountStatusRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspended_user_is_redirected_to_restricted_page(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::SUSPENDED,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertRedirect(route('system.account-restricted'));
    }

    public function test_banned_user_is_redirected_to_restricted_page(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::BANNED,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertRedirect(route('system.account-restricted'));
    }

    public function test_deleted_user_is_redirected_to_restricted_page(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::DELETED,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertRedirect(route('system.account-restricted'));
    }

    public function test_active_user_is_not_redirected_to_restricted_page(): void
    {
        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);

        $user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $user->assignRole('student');

        // Note: visiting dashboard also requires a profile to exist in verified.identity check.
        // Let's create a profile so they pass EnsureIdentityIsVerified as well.
        $user->profile()->create([
            'display_name' => 'Active User',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_restricted_page_displays_correct_suspended_state_copy(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::SUSPENDED,
            'account_status_reason' => 'Spamming community boards',
        ]);

        $response = $this->actingAs($user)->get(route('system.account-restricted'));

        $response->assertOk();
        $response->assertSee('Tài khoản đang bị tạm khóa');
        $response->assertSee('Spamming community boards');
        $response->assertSee('Liên hệ hỗ trợ');
    }

    public function test_restricted_page_displays_correct_banned_state_copy(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::BANNED,
            'account_status_reason' => 'Severe policy violation',
        ]);

        $response = $this->actingAs($user)->get(route('system.account-restricted'));

        $response->assertOk();
        $response->assertSee('Tài khoản bị cấm vĩnh viễn');
        $response->assertSee('Severe policy violation');
    }
}
