<?php

namespace App\Actions\Messaging;

use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RecallMessage
{
    /**
     * Recall a message.
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, Message $message): Message
    {
        Gate::forUser($user)->authorize('recall', $message);

        return DB::transaction(function () use ($user, $message) {
            $message->update([
                'recalled_at' => now(),
                'recalled_by' => $user->id,
            ]);
            $message->delete();

            return $message;
        });
    }
}
