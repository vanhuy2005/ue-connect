<?php

namespace App\Actions\Connections;

use App\Actions\Messaging\FindOrCreateDirectConversation;
use App\Enums\ConnectionStatus;
use App\Enums\GreetingStatus;
use App\Models\Connection;
use App\Models\Greeting;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AcceptGreeting
{
    /**
     * Accept a connection greeting request.
     *
     * @throws AuthorizationException
     */
    public function execute(User $receiver, Greeting $greeting): Connection
    {
        Gate::forUser($receiver)->authorize('accept', [Connection::class, $greeting]);

        return DB::transaction(function () use ($greeting) {
            $sender = $greeting->sender;
            $receiver = $greeting->receiver;

            // 1. Update greeting status
            $greeting->update([
                'status' => GreetingStatus::ACCEPTED,
                'accepted_at' => now(),
            ]);

            // 2. Create connection normalized pair
            $userOneId = min($sender->id, $receiver->id);
            $userTwoId = max($sender->id, $receiver->id);

            $connection = Connection::updateOrCreate(
                [
                    'user_one_id' => $userOneId,
                    'user_two_id' => $userTwoId,
                ],
                [
                    'status' => ConnectionStatus::ACTIVE,
                    'source_greeting_id' => $greeting->id,
                    'connected_at' => now(),
                    'disconnected_at' => null,
                ]
            );

            // 3. Initialize 1:1 direct conversation immediately
            $findConversation = new FindOrCreateDirectConversation;
            $findConversation->execute($sender, $receiver);

            return $connection;
        });
    }
}
