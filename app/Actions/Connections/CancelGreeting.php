<?php

namespace App\Actions\Connections;

use App\Enums\GreetingStatus;
use App\Models\Connection;
use App\Models\Greeting;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class CancelGreeting
{
    /**
     * Cancel a sent greeting request.
     *
     * @throws AuthorizationException
     */
    public function execute(User $sender, Greeting $greeting): Greeting
    {
        Gate::forUser($sender)->authorize('cancel', [Connection::class, $greeting]);

        $greeting->update([
            'status' => GreetingStatus::CANCELLED,
        ]);

        return $greeting;
    }
}
