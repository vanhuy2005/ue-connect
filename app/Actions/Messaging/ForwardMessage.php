<?php

namespace App\Actions\Messaging;

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageReceived;
use App\Support\Navigation\UserNavigationMetrics;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ForwardMessage
{
    /**
     * Forward a message to a target conversation.
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, Message $sourceMessage, Conversation $targetConversation): Message
    {
        // 1. Authorize forward source and send to target
        Gate::forUser($user)->authorize('forward', [$sourceMessage, $targetConversation]);

        return DB::transaction(function () use ($user, $sourceMessage, $targetConversation) {
            // 2. Create the forwarded message
            $message = Message::create([
                'conversation_id' => $targetConversation->id,
                'sender_id' => $user->id,
                'body' => $sourceMessage->body,
                'message_type' => MessageType::TEXT,
                'status' => MessageStatus::SENT,
                'forwarded_from_message_id' => $sourceMessage->id,
            ]);

            // 3. Update target conversation
            $targetConversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => now(),
            ]);

            // 4. Notify other participants in the target conversation
            $recipient = $targetConversation->getRecipientFor($user);
            if ($recipient) {
                $recipient->notify(new MessageReceived($message));
            }

            app(UserNavigationMetrics::class)->forgetForUser($user);
            app(UserNavigationMetrics::class)->forgetForUser($recipient);

            return $message;
        });
    }
}
