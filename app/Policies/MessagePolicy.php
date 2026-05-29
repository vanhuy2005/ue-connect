<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * Determine whether the user can delete the message.
     */
    public function deleteOwn(User $user, Message $message): bool
    {
        return $user->isActive() && $message->sender_id === $user->id;
    }
}
