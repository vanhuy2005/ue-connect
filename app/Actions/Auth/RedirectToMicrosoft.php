<?php

namespace App\Actions\Auth;

use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectToMicrosoft
{
    /**
     * Redirect the user to the Microsoft authentication page.
     */
    public function execute(): RedirectResponse
    {
        $tenant = config('services.microsoft.tenant');
        $enabled = config('services.microsoft.enabled')
            && ! empty(config('services.microsoft.client_id'))
            && ! empty(config('services.microsoft.client_secret'))
            && ! empty(config('services.microsoft.redirect'))
            && ! empty($tenant)
            && ! in_array($tenant, ['common', 'organizations']);

        if (! $enabled) {
            return redirect()->route('login')->withErrors([
                'sso' => ['Đăng nhập bằng Microsoft hiện chưa được cấu hình hoặc kích hoạt trên môi trường này.'],
            ]);
        }

        return Socialite::driver('microsoft')
            ->scopes(['openid', 'profile', 'email', 'User.Read'])
            ->redirect();
    }
}
