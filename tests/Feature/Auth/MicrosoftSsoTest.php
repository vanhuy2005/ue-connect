<?php

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Models\User;
use App\Models\UserIdentityProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Tests\TestCase;

class MicrosoftSsoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions since HandleMicrosoftCallback assigns 'student' role
        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
    }

    public function test_sso_redirects_to_microsoft(): void
    {
        $response = $this->get(route('auth.microsoft.redirect'));
        $response->assertRedirect();
        $this->assertStringContainsString('login.microsoftonline.com', $response->getTargetUrl());
    }

    public function test_sso_callback_with_invalid_domain_fails(): void
    {
        $mockUser = mock(\Laravel\Socialite\Two\User::class);
        $mockUser->shouldReceive('getId')->andReturn('ms-user-123');
        $mockUser->shouldReceive('getEmail')->andReturn('attacker@gmail.com');
        $mockUser->shouldReceive('getName')->andReturn('Attacker User');

        $mockProvider = mock(AbstractProvider::class);
        $mockProvider->shouldReceive('user')->andReturn($mockUser);

        Socialite::shouldReceive('driver')->with('microsoft')->andReturn($mockProvider);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseMissing('users', ['email' => 'attacker@gmail.com']);
    }

    public function test_sso_callback_registers_new_hcmue_user(): void
    {
        $mockUser = mock(\Laravel\Socialite\Two\User::class);
        $mockUser->shouldReceive('getId')->andReturn('ms-user-456');
        $mockUser->shouldReceive('getEmail')->andReturn('newstudent@hcmue.edu.vn');
        $mockUser->shouldReceive('getName')->andReturn('Nguyen Van Student');

        $mockProvider = mock(AbstractProvider::class);
        $mockProvider->shouldReceive('user')->andReturn($mockUser);

        Socialite::shouldReceive('driver')->with('microsoft')->andReturn($mockProvider);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('verification.status'));
        $this->assertDatabaseHas('users', [
            'email' => 'newstudent@hcmue.edu.vn',
            'name' => 'Nguyen Van Student',
            'account_status' => AccountStatus::REGISTERED->value,
        ]);

        $user = User::where('email', 'newstudent@hcmue.edu.vn')->first();
        $this->assertTrue($user->hasRole('student'));

        $this->assertDatabaseHas('user_identity_providers', [
            'user_id' => $user->id,
            'provider_name' => 'microsoft',
            'provider_user_id' => 'ms-user-456',
        ]);
    }

    public function test_sso_callback_authenticates_existing_linked_user(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@hcmue.edu.vn',
            'account_status' => AccountStatus::ACTIVE,
        ]);

        UserIdentityProvider::create([
            'user_id' => $user->id,
            'provider_name' => 'microsoft',
            'provider_user_id' => 'ms-user-789',
            'provider_email' => 'existing@hcmue.edu.vn',
            'linked_at' => now(),
        ]);

        $mockUser = mock(\Laravel\Socialite\Two\User::class);
        $mockUser->shouldReceive('getId')->andReturn('ms-user-789');
        $mockUser->shouldReceive('getEmail')->andReturn('existing@hcmue.edu.vn');

        $mockProvider = mock(AbstractProvider::class);
        $mockProvider->shouldReceive('user')->andReturn($mockUser);

        Socialite::shouldReceive('driver')->with('microsoft')->andReturn($mockProvider);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
