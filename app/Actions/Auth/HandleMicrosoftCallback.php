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
                'sso' => ['Không thể kết nối với dịch vụ xác thực. Vui lòng thử lại sau.'],
            ]);
        }

        // P0-1: Validate tenant ID (tid claim) against configured single-tenant
        $allowedTenantId = config('services.microsoft.tenant');
        $actualTenantId = $socialiteUser->user['tid'] ?? null;

        if (! empty($allowedTenantId) && $allowedTenantId !== 'organizations' && $allowedTenantId !== 'common') {
            if ($actualTenantId !== $allowedTenantId) {
                Log::warning('Microsoft SSO tenant mismatch', [
                    'expected' => $allowedTenantId,
                    'received' => $actualTenantId,
                ]);

                throw ValidationException::withMessages([
                    'sso' => ['Tài khoản Microsoft của bạn không thuộc tổ chức được phép.'],
                ]);
            }
        }

        $email = $socialiteUser->getEmail();
        if (empty($email)) {
            throw ValidationException::withMessages([
                'email' => ['Không tìm thấy địa chỉ email trong tài khoản Microsoft của bạn.'],
            ]);
        }

        // Normalize email
        $normalizedEmail = Str::lower(trim($email));

        // Strict allowed-domain constraint (configurable, defaults to hcmue.edu.vn)
        $allowedDomain = config('services.microsoft.allowed_domain', 'hcmue.edu.vn');
        if (! Str::endsWith($normalizedEmail, '@'.$allowedDomain)) {
            throw ValidationException::withMessages([
                'email' => ['Tài khoản Microsoft phải thuộc hệ sinh thái email @'.$allowedDomain.' của Trường Đại học Sư phạm TP.HCM.'],
            ]);
        }

        // 1. Check if identity provider mapping already exists
        $identity = UserIdentityProvider::where('provider_name', 'microsoft')
            ->where('provider_user_id', $socialiteUser->getId())
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
                'provider_user_id' => $socialiteUser->getId(),
                'provider_tenant_id' => $actualTenantId,
                'provider_email' => $normalizedEmail,
                'linked_at' => now(),
                'last_login_at' => now(),
            ]);

            $user->update(['last_login_at' => now()]);
            Auth::login($user, true);

            return $user;
        }

        // 3. Register a new user — no role assigned here.
        // Roles (student/alumni/advisor) are assigned only upon admin verification approval.
        $user = User::create([
            'name' => $socialiteUser->getName() ?? Str::title(explode('@', $normalizedEmail)[0]),
            'email' => $normalizedEmail,
            'password' => Hash::make(Str::random(24)),
            'account_status' => AccountStatus::REGISTERED,
            'last_login_at' => now(),
        ]);

        // Create identity provider mapping
        UserIdentityProvider::create([
            'user_id' => $user->id,
            'provider_name' => 'microsoft',
            'provider_user_id' => $socialiteUser->getId(),
            'provider_tenant_id' => $actualTenantId,
            'provider_email' => $normalizedEmail,
            'linked_at' => now(),
            'last_login_at' => now(),
        ]);

        Auth::login($user, true);

        return $user;
    }
}
