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

        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);

        // Enable SSO for tests that need it
        config(['services.microsoft.enabled' => true]);
        config(['services.microsoft.tenant' => 'organizations']);
        config(['services.microsoft.allowed_domain' => 'hcmue.edu.vn']);
    }

    /**
     * @param  array<string, mixed>  $userAttributes
     */
    private function mockSocialiteUser(array $userAttributes = []): void
    {
        $microsoftUser = array_merge([
            'tid' => null,
        ], $userAttributes['user'] ?? []);

        $mockUser = mock(\Laravel\Socialite\Two\User::class);
        $mockUser->shouldReceive('getId')->andReturn($userAttributes['id'] ?? 'ms-user-test');
        $mockUser->shouldReceive('getEmail')->andReturn($userAttributes['email'] ?? 'test@hcmue.edu.vn');
        $mockUser->shouldReceive('getName')->andReturn($userAttributes['name'] ?? 'Test User');
        $mockUser->user = $microsoftUser;

        $mockProvider = mock(AbstractProvider::class);
        $mockProvider->shouldReceive('user')->andReturn($mockUser);

        Socialite::shouldReceive('driver')->with('microsoft')->andReturn($mockProvider);
    }

    // -------------------------------------------------------------------------
    // Redirect tests
    // -------------------------------------------------------------------------

    public function test_sso_redirects_to_microsoft(): void
    {
        $response = $this->get(route('auth.microsoft.redirect'));
        $response->assertRedirect();
        $this->assertStringContainsString('login.microsoftonline.com', $response->getTargetUrl());
    }

    // -------------------------------------------------------------------------
    // Feature gate: MICROSOFT_LOGIN_ENABLED=false
    // -------------------------------------------------------------------------

    public function test_sso_callback_fails_when_sso_is_disabled(): void
    {
        config(['services.microsoft.enabled' => false]);

        // Even without a valid OAuth response, we should hit the gate first
        $mockProvider = mock(AbstractProvider::class);
        $mockProvider->shouldReceive('user')->andThrow(new \Exception('should not reach socialite'));

        Socialite::shouldReceive('driver')->with('microsoft')->andReturn($mockProvider);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['sso']);
    }

    // -------------------------------------------------------------------------
    // Domain validation
    // -------------------------------------------------------------------------

    public function test_sso_callback_with_invalid_domain_fails(): void
    {
        $this->mockSocialiteUser([
            'id' => 'ms-user-123',
            'email' => 'attacker@gmail.com',
            'name' => 'Attacker User',
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseMissing('users', ['email' => 'attacker@gmail.com']);
    }

    // -------------------------------------------------------------------------
    // Tenant ID validation (P0-1)
    // -------------------------------------------------------------------------

    public function test_sso_callback_fails_when_tenant_id_mismatches(): void
    {
        config(['services.microsoft.tenant' => 'correct-tenant-guid']);

        $this->mockSocialiteUser([
            'id' => 'ms-user-bad-tenant',
            'email' => 'student@hcmue.edu.vn',
            'name' => 'Bad Tenant Student',
            'user' => ['tid' => 'wrong-tenant-guid'],
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['sso']);
        $this->assertDatabaseMissing('users', ['email' => 'student@hcmue.edu.vn']);
    }

    public function test_sso_callback_succeeds_when_tenant_id_matches(): void
    {
        $correctTenant = 'correct-tenant-guid';
        config(['services.microsoft.tenant' => $correctTenant]);

        $this->mockSocialiteUser([
            'id' => 'ms-user-good-tenant',
            'email' => 'goodtenant@hcmue.edu.vn',
            'name' => 'Good Tenant Student',
            'user' => ['tid' => $correctTenant],
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'goodtenant@hcmue.edu.vn']);
    }

    public function test_sso_callback_skips_tenant_check_for_organizations_tenant(): void
    {
        config(['services.microsoft.tenant' => 'organizations']);

        $this->mockSocialiteUser([
            'id' => 'ms-user-multi',
            'email' => 'multi@hcmue.edu.vn',
            'name' => 'Multi Tenant User',
            'user' => ['tid' => 'any-tenant-id'],
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'multi@hcmue.edu.vn']);
    }

    // -------------------------------------------------------------------------
    // New user registration — no role assigned (P0-2)
    // -------------------------------------------------------------------------

    public function test_sso_callback_registers_new_hcmue_user_without_role(): void
    {
        $this->mockSocialiteUser([
            'id' => 'ms-user-456',
            'email' => 'newstudent@hcmue.edu.vn',
            'name' => 'Nguyen Van Student',
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('verification.status'));
        $this->assertDatabaseHas('users', [
            'email' => 'newstudent@hcmue.edu.vn',
            'name' => 'Nguyen Van Student',
            'account_status' => AccountStatus::REGISTERED->value,
        ]);

        $user = User::where('email', 'newstudent@hcmue.edu.vn')->first();

        // P0-2: New SSO users must NOT be auto-assigned any role at registration
        $this->assertFalse($user->hasRole('student'));
        $this->assertFalse($user->hasRole('alumni'));
        $this->assertFalse($user->hasRole('advisor'));

        $this->assertDatabaseHas('user_identity_providers', [
            'user_id' => $user->id,
            'provider_name' => 'microsoft',
            'provider_user_id' => 'ms-user-456',
        ]);
    }

    // -------------------------------------------------------------------------
    // Existing linked user
    // -------------------------------------------------------------------------

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

        $this->mockSocialiteUser([
            'id' => 'ms-user-789',
            'email' => 'existing@hcmue.edu.vn',
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    // -------------------------------------------------------------------------
    // Blocked user statuses — P1-3
    // -------------------------------------------------------------------------

    public function test_sso_callback_blocks_suspended_user(): void
    {
        $user = User::factory()->create([
            'email' => 'suspended@hcmue.edu.vn',
            'account_status' => AccountStatus::SUSPENDED,
        ]);

        UserIdentityProvider::create([
            'user_id' => $user->id,
            'provider_name' => 'microsoft',
            'provider_user_id' => 'ms-suspended',
            'provider_email' => 'suspended@hcmue.edu.vn',
            'linked_at' => now(),
        ]);

        $this->mockSocialiteUser([
            'id' => 'ms-suspended',
            'email' => 'suspended@hcmue.edu.vn',
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        // MicrosoftAuthController explicitly redirects suspended users to account-restricted
        $response->assertRedirect(route('system.account-restricted'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_sso_callback_blocks_banned_user(): void
    {
        $user = User::factory()->create([
            'email' => 'banned@hcmue.edu.vn',
            'account_status' => AccountStatus::BANNED,
        ]);

        UserIdentityProvider::create([
            'user_id' => $user->id,
            'provider_name' => 'microsoft',
            'provider_user_id' => 'ms-banned',
            'provider_email' => 'banned@hcmue.edu.vn',
            'linked_at' => now(),
        ]);

        $this->mockSocialiteUser([
            'id' => 'ms-banned',
            'email' => 'banned@hcmue.edu.vn',
        ]);

        // MicrosoftAuthController explicitly redirects banned users to account-restricted
        $response = $this->get(route('auth.microsoft.callback'));
        $response->assertRedirect(route('system.account-restricted'));
        $this->assertAuthenticatedAs($user);
    }

    // -------------------------------------------------------------------------
    // Missing email fallback — P1-3
    // -------------------------------------------------------------------------

    public function test_sso_callback_fails_when_email_is_empty(): void
    {
        $mockUser = mock(\Laravel\Socialite\Two\User::class);
        $mockUser->shouldReceive('getId')->andReturn('ms-no-email');
        $mockUser->shouldReceive('getEmail')->andReturn('');
        $mockUser->shouldReceive('getName')->andReturn('No Email User');
        $mockUser->user = [];

        $mockProvider = mock(AbstractProvider::class);
        $mockProvider->shouldReceive('user')->andReturn($mockUser);

        Socialite::shouldReceive('driver')->with('microsoft')->andReturn($mockProvider);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
    }

    // -------------------------------------------------------------------------
    // Socialite failure — P1-3 (obfuscated error message)
    // -------------------------------------------------------------------------

    public function test_sso_callback_obfuscates_socialite_exception_message(): void
    {
        $mockProvider = mock(AbstractProvider::class);
        $mockProvider->shouldReceive('user')->andThrow(
            new \Exception('internal server error with sensitive GUID abc-123-def')
        );

        Socialite::shouldReceive('driver')->with('microsoft')->andReturn($mockProvider);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['sso']);

        // The raw exception message must not leak to the session
        $errors = session('errors');
        $ssoError = $errors?->first('sso') ?? '';
        $this->assertStringNotContainsString('abc-123-def', $ssoError);
        $this->assertStringNotContainsString('sensitive GUID', $ssoError);
    }
}
