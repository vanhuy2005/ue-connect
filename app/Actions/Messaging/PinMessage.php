<?php

namespace App\Actions\Messaging;

use App\Models\ConversationPinnedMessage;
use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PinMessage
{
    /**
     * Pin a message in the conversation.
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(User $user, Message $message): ConversationPinnedMessage
    {
        Gate::forUser($user)->authorize('pin', $message);

        $conversation = $message->conversation;

        // Same conversation integrity
        if ((int) $message->conversation_id !== (int) $conversation->id) {
            throw new ValidationException(
                Validator::make([], [])
                    ->errors()
                    ->add('message_id', 'Không thể ghim tin nhắn từ cuộc trò chuyện khác.')
            );
        }

        // Limit to max 3 pins per conversation
        $pinnedCount = $conversation->pinnedMessages()->count();
        if ($pinnedCount >= 3) {
            throw new ValidationException(
                Validator::make([], [])
                    ->errors()
                    ->add('pin_limit', 'Bạn chỉ có thể ghim tối đa 3 tin nhắn trong mỗi cuộc trò chuyện.')
            );
        }

        return DB::transaction(function () use ($conversation, $message, $user) {
            return ConversationPinnedMessage::firstOrCreate([
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
            ], [
                'pinned_by' => $user->id,
            ]);
        });
    }
}
