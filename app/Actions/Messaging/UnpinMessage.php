<?php

namespace App\Actions\Messaging;

use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UnpinMessage
{
    /**
     * Unpin a message from the conversation.
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, Message $message): void
    {
        Gate::forUser($user)->authorize('unpin', $message);

        DB::transaction(function () use ($message) {
            $message->conversation->pinnedMessages()
                ->where('message_id', $message->id)
                ->delete();
        });
    }
}
