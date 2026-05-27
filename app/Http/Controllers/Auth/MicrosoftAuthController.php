<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\HandleMicrosoftCallback;
use App\Actions\Auth\RedirectToMicrosoft;
use App\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class MicrosoftAuthController extends Controller
{
    /**
     * Redirect the user to the Microsoft authentication provider.
     */
    public function redirect(RedirectToMicrosoft $action): RedirectResponse
    {
        return $action->execute();
    }

    /**
     * Handle the callback from the Microsoft authentication provider.
     */
    public function callback(HandleMicrosoftCallback $action): RedirectResponse
    {
        try {
            $user = $action->execute();

            // Redirect logic based on account status
            if ($user->isSuspended() || $user->isBanned()) {
                return redirect()->route('system.account-restricted');
            }

            if (! $user->isVerified()) {
                return redirect()->route('verification.status');
            }

            if ($user->account_status === AccountStatus::PROFILE_INCOMPLETE) {
                return redirect()->route('profile.setup');
            }

            return redirect()->intended(route('dashboard'));
        } catch (ValidationException $e) {
            return redirect()->route('login')->withErrors($e->errors());
        }
    }
}
