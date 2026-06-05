<?php

namespace App\Actions\Auth;

use App\Enums\AccountStatus;
use App\Models\User;
use App\Models\UserIdentityProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class HandleMicrosoftCallback
{
    /**
     * Create a new callback handler instance.
     */
    public function __construct(
        protected ValidateMicrosoftIdentity $validator
    ) {}

    /**
     * Handle the Microsoft authentication callback and return the authenticated user.
     *
     * @throws ValidationException
     */
    public function execute(): User
    {
        // Gate: SSO must be explicitly enabled in configuration
        if (! config('services.microsoft.enabled')) {
            throw ValidationException::withMessages([
                'sso' => ['Đăng nhập bằng Microsoft hiện chưa được kích hoạt.'],
            ]);
        }

        try {
            $socialiteUser = Socialite::driver('microsoft')->user();
        } catch (\Exception $e) {
            Log::warning('Microsoft SSO provider error', [
                'exception' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'sso' => ['Không thể đăng nhập bằng Microsoft HCMUE. Vui lòng kiểm tra tài khoản hoặc thử lại.'],
            ]);
        }

        // Validate and resolve Microsoft identity data
        $identityData = $this->validator->execute($socialiteUser);
        $normalizedEmail = $identityData['email'];
        $providerUserId = $identityData['provider_user_id'];
        $actualTenantId = $identityData['tenant_id'];

        // 1. Check if identity provider mapping already exists
        $identity = UserIdentityProvider::where('provider_name', 'microsoft')
            ->where('provider_user_id', $providerUserId)
            ->first();

        if ($identity) {
            $user = $identity->user;

            if (! $user) {
                // Orphaned identity provider record
                $identity->delete();
            } else {
                // Update login timestamps
                $now = now();
                $identity->update(['last_login_at' => $now]);

                if (! $user->hasVerifiedEmail()) {
                    $user->markEmailAsVerified();
                }

                $user->update(['last_login_at' => $now]);

                Auth::login($user, true);

                return $user;
            }
        }

        // 2. Check if a user with the same email already exists
        $user = User::where('email', $normalizedEmail)->first();

        if ($user) {
            // Link existing user
            UserIdentityProvider::create([
                'user_id' => $user->id,
                'provider_name' => 'microsoft',
                'provider_user_id' => $providerUserId,
                'provider_tenant_id' => $actualTenantId,
                'provider_email' => $normalizedEmail,
                'linked_at' => now(),
                'last_login_at' => now(),
            ]);

            $user->update([
                'last_login_at' => now(),
                'intended_identity_type' => $user->intended_identity_type ?? ($identityData['intended_identity_type'] ?? null),
            ]);

            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            Auth::login($user, true);

            return $user;
        }

        $user = User::create([
            'name' => $socialiteUser->getName() ?? Str::title(explode('@', $normalizedEmail)[0]),
            'email' => $normalizedEmail,
            'password' => Hash::make(Str::random(24)),
            'account_status' => AccountStatus::REGISTERED,
            'intended_identity_type' => $identityData['intended_identity_type'] ?? null,
            'last_login_at' => now(),
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        // Create identity provider mapping
        UserIdentityProvider::create([
            'user_id' => $user->id,
            'provider_name' => 'microsoft',
            'provider_user_id' => $providerUserId,
            'provider_tenant_id' => $actualTenantId,
            'provider_email' => $normalizedEmail,
            'linked_at' => now(),
            'last_login_at' => now(),
        ]);

        Auth::login($user, true);

        return $user;
    }
}
