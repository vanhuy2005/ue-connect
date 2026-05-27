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
        return Socialite::driver('microsoft')
            ->scopes(['User.Read'])
            ->redirect();
    }
}
