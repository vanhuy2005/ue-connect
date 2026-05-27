<?php

namespace App\Actions\Auth;

use App\Enums\AccountStatus;
use App\Models\User;
use App\Models\UserIdentityProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        try {
            $socialiteUser = Socialite::driver('microsoft')->user();
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'sso' => ['Không thể kết nối với dịch vụ Microsoft: '.$e->getMessage()],
            ]);
        }

        $email = $socialiteUser->getEmail();
        if (empty($email)) {
            throw ValidationException::withMessages([
                'email' => ['Không tìm thấy địa chỉ email trong tài khoản Microsoft của bạn.'],
            ]);
        }

        // Normalize email
        $normalizedEmail = Str::lower(trim($email));

        // Strict HCMUE email domain constraint
        if (! Str::endsWith($normalizedEmail, '@hcmue.edu.vn')) {
            throw ValidationException::withMessages([
                'email' => ['Tài khoản Microsoft phải thuộc hệ sinh thái email @hcmue.edu.vn của Trường Đại học Sư phạm TP.HCM.'],
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
                'provider_tenant_id' => $socialiteUser->user['tenantId'] ?? null,
                'provider_email' => $normalizedEmail,
                'linked_at' => now(),
                'last_login_at' => now(),
            ]);

            $user->update(['last_login_at' => now()]);
            Auth::login($user, true);

            return $user;
        }

        // 3. Register a new user
        $user = User::create([
            'name' => $socialiteUser->getName() ?? Str::title(explode('@', $normalizedEmail)[0]),
            'email' => $normalizedEmail,
            'password' => Hash::make(Str::random(24)),
            'account_status' => AccountStatus::REGISTERED,
            'last_login_at' => now(),
        ]);

        // Assign default student role
        $user->assignRole('student');

        // Create identity provider mapping
        UserIdentityProvider::create([
            'user_id' => $user->id,
            'provider_name' => 'microsoft',
            'provider_user_id' => $socialiteUser->getId(),
            'provider_tenant_id' => $socialiteUser->user['tenantId'] ?? null,
            'provider_email' => $normalizedEmail,
            'linked_at' => now(),
            'last_login_at' => now(),
        ]);

        Auth::login($user, true);

        return $user;
    }
}
