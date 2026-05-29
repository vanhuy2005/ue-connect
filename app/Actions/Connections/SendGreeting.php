<?php

namespace App\Actions\Connections;

use App\Enums\GreetingStatus;
use App\Models\Connection;
use App\Models\Greeting;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class SendGreeting
{
    /**
     * Send a connection greeting request.
     *
     * @param  array{message?: string}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $sender, User $receiver, array $data = []): Greeting
    {
        Gate::forUser($sender)->authorize('send', [Connection::class, $receiver]);

        return Greeting::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message' => $data['message'] ?? 'Xin chào! Mình muốn kết nối học tập/cộng đồng với bạn.',
            'status' => GreetingStatus::PENDING,
            'expires_at' => now()->addDays(7),
        ]);
    }
}
