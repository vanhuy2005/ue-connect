<?php

namespace App\Actions\Connections;

use App\Enums\GreetingStatus;
use App\Models\Connection;
use App\Models\Greeting;
use App\Models\User;
use App\Notifications\GreetingDeclined;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class DeclineGreeting
{
    /**
     * Decline a connection greeting request.
     *
     * @param  array{reason?: string}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $receiver, Greeting $greeting, array $data = []): Greeting
    {
        Gate::forUser($receiver)->authorize('decline', [Connection::class, $greeting]);

        $greeting->update([
            'status' => GreetingStatus::DECLINED,
            'decline_reason' => $data['reason'] ?? null,
            'declined_at' => now(),
        ]);

        $greeting->sender->notify(new GreetingDeclined($greeting));

        return $greeting;
    }
}
