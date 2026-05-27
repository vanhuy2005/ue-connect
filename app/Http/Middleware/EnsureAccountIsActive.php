<?php

namespace App\Http\Middleware;

use App\Enums\AccountStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
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
            if ($user->isSuspended() || $user->isBanned() || $user->account_status === AccountStatus::DELETED) {
                if (! $request->is('system/account-restricted')) {
                    return redirect()->route('system.account-restricted');
                }
            }
        }

        return $next($request);
    }
}
