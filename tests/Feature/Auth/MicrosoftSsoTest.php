<?php

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Models\User;
use App\Models\UserIdentityProvider;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
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
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        // Enable SSO for tests that need it
        config(['services.microsoft.enabled' => true]);
        config(['services.microsoft.tenant' => 'b1a9fdc0-1d56-4c3d-a481-809fff8a26db']);
        config(['services.microsoft.allowed_domains' => ['student.hcmue.edu.vn', 'teacher.hcmue.edu.vn']]);
        config(['services.microsoft.client_id' => 'mock-client-id']);
        config(['services.microsoft.client_secret' => 'mock-client-secret']);
        config(['services.microsoft.redirect' => 'http://ue-connect.test/login/microsoft/callback']);
    }

    /**
     * @param  array<string, mixed>  $userAttributes
     */
    private function mockSocialiteUser(array $userAttributes = []): void
    {
        $microsoftUser = array_merge([
            'tid' => config('services.microsoft.tenant', 'b1a9fdc0-1d56-4c3d-a481-809fff8a26db'),
        ], $userAttributes['user'] ?? []);

        $mockUser = mock(\Laravel\Socialite\Two\User::class);
        $mockUser->shouldReceive('getId')->andReturn($userAttributes['id'] ?? 'ms-user-test');
        $mockUser->shouldReceive('getEmail')->andReturn($userAttributes['email'] ?? 'test@student.hcmue.edu.vn');
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
            'email' => 'student@student.hcmue.edu.vn',
            'name' => 'Bad Tenant Student',
            'user' => ['tid' => 'wrong-tenant-guid'],
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['sso']);
        $this->assertDatabaseMissing('users', ['email' => 'student@student.hcmue.edu.vn']);
    }

    public function test_sso_callback_succeeds_when_tenant_id_matches(): void
    {
        $correctTenant = 'correct-tenant-guid';
        config(['services.microsoft.tenant' => $correctTenant]);

        $this->mockSocialiteUser([
            'id' => 'ms-user-good-tenant',
            'email' => 'goodtenant@student.hcmue.edu.vn',
            'name' => 'Good Tenant Student',
            'user' => ['tid' => $correctTenant],
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'goodtenant@student.hcmue.edu.vn']);
    }

    public function test_sso_callback_fails_for_tenant_mismatch_with_organizations_tenant(): void
    {
        config(['services.microsoft.tenant' => 'organizations']);

        $this->mockSocialiteUser([
            'id' => 'ms-user-multi',
            'email' => 'multi@student.hcmue.edu.vn',
            'name' => 'Multi Tenant User',
            'user' => ['tid' => 'any-tenant-id'],
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['sso']);
        $this->assertDatabaseMissing('users', ['email' => 'multi@student.hcmue.edu.vn']);
    }

    // -------------------------------------------------------------------------
    // New user registration — no role assigned (P0-2)
    // -------------------------------------------------------------------------

    public function test_sso_callback_registers_new_hcmue_user_without_role(): void
    {
        try {
            $this->mockSocialiteUser([
                'id' => 'ms-user-456',
                'email' => 'newstudent@student.hcmue.edu.vn',
                'name' => 'Nguyen Van Student',
            ]);

            $response = $this->get(route('auth.microsoft.callback'));

            $response->assertRedirect(route('verification.status'));
            $this->assertDatabaseHas('users', [
                'email' => 'newstudent@student.hcmue.edu.vn',
                'name' => 'Nguyen Van Student',
                'account_status' => AccountStatus::REGISTERED->value,
            ]);

            $user = User::where('email', 'newstudent@student.hcmue.edu.vn')->first();

            // P0-2: New SSO users must NOT be auto-assigned any role at registration
            $this->assertFalse($user->hasRole('student'));
            $this->assertFalse($user->hasRole('alumni'));
            $this->assertFalse($user->hasRole('teacher'));

            $this->assertDatabaseHas('user_identity_providers', [
                'user_id' => $user->id,
                'provider_name' => 'microsoft',
                'provider_user_id' => 'ms-user-456',
            ]);
        } catch (\Throwable $e) {
            dump('TRACEBACK MESSAGE: '.$e->getMessage());
            dump('TRACEBACK: '.$e->getTraceAsString());
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Existing linked user
    // -------------------------------------------------------------------------

    public function test_sso_callback_authenticates_existing_linked_user(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@student.hcmue.edu.vn',
            'account_status' => AccountStatus::ACTIVE,
        ]);

        UserIdentityProvider::create([
            'user_id' => $user->id,
            'provider_name' => 'microsoft',
            'provider_user_id' => 'ms-user-789',
            'provider_email' => 'existing@student.hcmue.edu.vn',
            'linked_at' => now(),
        ]);

        $this->mockSocialiteUser([
            'id' => 'ms-user-789',
            'email' => 'existing@student.hcmue.edu.vn',
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
            'email' => 'suspended@student.hcmue.edu.vn',
            'account_status' => AccountStatus::SUSPENDED,
        ]);

        UserIdentityProvider::create([
            'user_id' => $user->id,
            'provider_name' => 'microsoft',
            'provider_user_id' => 'ms-suspended',
            'provider_email' => 'suspended@student.hcmue.edu.vn',
            'linked_at' => now(),
        ]);

        $this->mockSocialiteUser([
            'id' => 'ms-suspended',
            'email' => 'suspended@student.hcmue.edu.vn',
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        // MicrosoftAuthController explicitly redirects suspended users to account-restricted
        $response->assertRedirect(route('system.account-restricted'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_sso_callback_blocks_banned_user(): void
    {
        $user = User::factory()->create([
            'email' => 'banned@student.hcmue.edu.vn',
            'account_status' => AccountStatus::BANNED,
        ]);

        UserIdentityProvider::create([
            'user_id' => $user->id,
            'provider_name' => 'microsoft',
            'provider_user_id' => 'ms-banned',
            'provider_email' => 'banned@student.hcmue.edu.vn',
            'linked_at' => now(),
        ]);

        $this->mockSocialiteUser([
            'id' => 'ms-banned',
            'email' => 'banned@student.hcmue.edu.vn',
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
        $mockUser->user = ['tid' => 'b1a9fdc0-1d56-4c3d-a481-809fff8a26db'];

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

    public function test_sso_callback_accepts_student_domain(): void
    {
        $this->mockSocialiteUser([
            'id' => 'ms-user-student-domain',
            'email' => 'student@student.hcmue.edu.vn',
            'name' => 'HCMUE Student',
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'student@student.hcmue.edu.vn']);
    }

    public function test_sso_callback_fails_when_tenant_config_is_missing(): void
    {
        config(['services.microsoft.tenant' => '']);

        $this->mockSocialiteUser([
            'id' => 'ms-user-no-tenant-config',
            'email' => 'student@student.hcmue.edu.vn',
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['sso']);
    }

    public function test_login_page_renders_sso_session_errors(): void
    {
        // Disable SSO to trigger validation exception on redirect
        config(['services.microsoft.enabled' => false]);

        $response = $this->followingRedirects()
            ->get(route('auth.microsoft.redirect'));

        $response->assertOk();
        $response->assertSee('Đăng nhập bằng Microsoft hiện chưa được cấu hình hoặc kích hoạt trên môi trường này.');
    }

    public function test_sso_callback_fails_when_tenant_config_is_common_or_organizations(): void
    {
        config(['services.microsoft.tenant' => 'common']);

        $this->mockSocialiteUser([
            'id' => 'ms-user-common-config',
            'email' => 'student@student.hcmue.edu.vn',
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['sso']);

        config(['services.microsoft.tenant' => 'organizations']);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['sso']);
    }

    public function test_sso_callback_fails_when_actual_tenant_is_common_or_organizations(): void
    {
        // Allowed tenant is valid GUID
        config(['services.microsoft.tenant' => 'b1a9fdc0-1d56-4c3d-a481-809fff8a26db']);

        $this->mockSocialiteUser([
            'id' => 'ms-user-common-actual',
            'email' => 'student@student.hcmue.edu.vn',
            'user' => ['tid' => 'common'],
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['sso']);

        $this->mockSocialiteUser([
            'id' => 'ms-user-orgs-actual',
            'email' => 'student@student.hcmue.edu.vn',
            'user' => ['tid' => 'organizations'],
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['sso']);
    }

    public function test_sso_redirect_fails_when_tenant_config_is_common_or_organizations(): void
    {
        config(['services.microsoft.tenant' => 'common']);

        $response = $this->get(route('auth.microsoft.redirect'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['sso']);

        config(['services.microsoft.tenant' => 'organizations']);

        $response = $this->get(route('auth.microsoft.redirect'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['sso']);
    }

    public function test_config_parses_microsoft_allowed_domains(): void
    {
        config(['services.microsoft.allowed_domains' => ['student.hcmue.edu.vn', 'teacher.hcmue.edu.vn']]);

        $this->assertEquals(
            ['student.hcmue.edu.vn', 'teacher.hcmue.edu.vn'],
            config('services.microsoft.allowed_domains')
        );
    }

    public function test_sso_callback_accepts_teacher_domain(): void
    {
        config(['services.microsoft.allowed_domains' => ['student.hcmue.edu.vn', 'teacher.hcmue.edu.vn']]);

        $this->mockSocialiteUser([
            'id' => 'ms-user-teacher-domain',
            'email' => 'teacher@teacher.hcmue.edu.vn',
            'name' => 'HCMUE Teacher',
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'teacher@teacher.hcmue.edu.vn']);
    }

    public function test_sso_callback_succeeds_when_tid_missing_in_profile_but_present_in_id_token(): void
    {
        // 1. Build an id_token with tid claim
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'tid' => 'b1a9fdc0-1d56-4c3d-a481-809fff8a26db',
            'oid' => 'ms-user-id-token',
            'email' => 'student@student.hcmue.edu.vn',
        ]));
        $idToken = "{$header}.{$payload}.signature";

        // 2. Mock Socialite user with missing tid in profile, but with id_token in accessTokenResponseBody
        $mockUser = mock(\Laravel\Socialite\Two\User::class);
        $mockUser->shouldReceive('getId')->andReturn('ms-user-id-token');
        $mockUser->shouldReceive('getEmail')->andReturn('student@student.hcmue.edu.vn');
        $mockUser->shouldReceive('getName')->andReturn('HCMUE ID Token User');

        // tid is missing in profile
        $mockUser->user = [
            'oid' => 'ms-user-id-token',
        ];

        $mockUser->accessTokenResponseBody = [
            'id_token' => $idToken,
        ];

        $mockProvider = mock(AbstractProvider::class);
        $mockProvider->shouldReceive('user')->andReturn($mockUser);

        Socialite::shouldReceive('driver')->with('microsoft')->andReturn($mockProvider);

        config(['services.microsoft.tenant' => 'b1a9fdc0-1d56-4c3d-a481-809fff8a26db']);
        config(['services.microsoft.allowed_domains' => ['student.hcmue.edu.vn', 'teacher.hcmue.edu.vn']]);

        $response = $this->get(route('auth.microsoft.callback'));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'student@student.hcmue.edu.vn']);
    }

    public function test_sso_callback_auto_verifies_email_for_new_users(): void
    {
        $this->mockSocialiteUser([
            'id' => 'ms-new-verify',
            'email' => 'newverify@student.hcmue.edu.vn',
            'name' => 'Verify Student',
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $user = User::where('email', 'newverify@student.hcmue.edu.vn')->firstOrFail();
        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function test_sso_callback_auto_verifies_email_for_existing_unverified_users_when_linked(): void
    {
        $user = User::factory()->create([
            'email' => 'existingunverified@student.hcmue.edu.vn',
            'email_verified_at' => null,
            'account_status' => AccountStatus::ACTIVE,
        ]);

        $this->mockSocialiteUser([
            'id' => 'ms-existing-unverified',
            'email' => 'existingunverified@student.hcmue.edu.vn',
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function test_sso_callback_auto_verifies_email_for_existing_linked_unverified_users(): void
    {
        $user = User::factory()->create([
            'email' => 'linkedunverified@student.hcmue.edu.vn',
            'email_verified_at' => null,
            'account_status' => AccountStatus::ACTIVE,
        ]);

        UserIdentityProvider::create([
            'user_id' => $user->id,
            'provider_name' => 'microsoft',
            'provider_user_id' => 'ms-linked-unverified',
            'provider_email' => 'linkedunverified@student.hcmue.edu.vn',
            'linked_at' => now(),
        ]);

        $this->mockSocialiteUser([
            'id' => 'ms-linked-unverified',
            'email' => 'linkedunverified@student.hcmue.edu.vn',
        ]);

        $response = $this->get(route('auth.microsoft.callback'));

        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
    }
}
