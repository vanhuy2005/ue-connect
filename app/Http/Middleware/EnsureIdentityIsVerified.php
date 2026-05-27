<?php

namespace App\Http\Middleware;

use App\Enums\AccountStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdentityIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // If the user has not been verified, redirect them to the verification status page.
            if (! $user->isVerified()) {
                if (! $request->is('verification/*') && ! $request->is('auth/*') && ! $request->is('system/*')) {
                    return redirect()->route('verification.status');
                }
            }

            // If verified but profile is incomplete, redirect them to onboarding/profile setup.
            if ($user->account_status === AccountStatus::PROFILE_INCOMPLETE) {
                if (! $request->is('app/profile/setup') && ! $request->is('verification/*') && ! $request->is('auth/*') && ! $request->is('system/*')) {
                    return redirect()->route('profile.setup');
                }
            }
        }

        return $next($request);
    }
}
