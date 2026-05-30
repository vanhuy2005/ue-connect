<?php

namespace App\Actions\Messaging;

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageReceived;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReplyToMessage
{
    /**
     * Reply to a message inside a conversation.
     *
     * @param  array{body: string}  $data
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(User $sender, Conversation $conversation, Message $replyToMessage, array $data): Message
    {
        // 1. Authorize reply ability
        Gate::forUser($sender)->authorize('reply', [$replyToMessage]);

        // 2. Validate same conversation integrity
        if ((int) $replyToMessage->conversation_id !== (int) $conversation->id) {
            throw new ValidationException(
                Validator::make([], [])
                    ->errors()
                    ->add('reply_to_message_id', 'Không thể trả lời tin nhắn từ cuộc trò chuyện khác.')
            );
        }

        // 3. Validate body
        Validator::make($data, [
            'body' => ['required', 'string', 'max:2000'],
        ])->validate();

        $trimmedBody = trim($data['body']);
        if (empty($trimmedBody)) {
            throw new ValidationException(
                Validator::make([], [])
                    ->errors()
                    ->add('body', 'Nội dung tin nhắn không được để trống.')
            );
        }

        return DB::transaction(function () use ($sender, $conversation, $replyToMessage, $trimmedBody) {
            // 4. Create message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'body' => $trimmedBody,
                'message_type' => MessageType::TEXT,
                'status' => MessageStatus::SENT,
                'reply_to_message_id' => $replyToMessage->id,
            ]);

            // 5. Update conversation
            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => now(),
            ]);

            // 6. Notify other participants
            $recipient = $conversation->getRecipientFor($sender);
            if ($recipient) {
                // If muted/restricted is handled by notification policy, it will suppress it
                $recipient->notify(new MessageReceived($message));
            }

            return $message;
        });
    }
}
