<?php

namespace App\Actions\Messaging;

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SendMessage
{
    /**
     * Send a text message inside a conversation.
     *
     * @param  array{body: string}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $sender, Conversation $conversation, array $data): Message
    {
        Gate::forUser($sender)->authorize('sendMessage', $conversation);

        return DB::transaction(function () use ($sender, $conversation, $data) {
            // 1. Create message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'body' => $data['body'],
                'message_type' => MessageType::TEXT,
                'status' => MessageStatus::SENT,
            ]);

            // 2. Update conversation
            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => now(),
            ]);

            return $message;
        });
    }
}
