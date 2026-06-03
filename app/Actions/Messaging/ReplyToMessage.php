<?php

namespace App\Actions\Messaging;

use App\Actions\Media\AttachMediaToModelAction;
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
     * @param  array{body?: ?string, media_id?: ?int}  $data
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
            throw ValidationException::withMessages([
                'reply_to_message_id' => ['Không thể trả lời tin nhắn từ cuộc trò chuyện khác.'],
            ]);
        }

        // 3. Validate body and media_id
        Validator::make($data, [
            'body' => ['nullable', 'string', 'max:2000'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
        ])->validate();

        $trimmedBody = isset($data['body']) ? trim($data['body']) : '';
        $mediaId = $data['media_id'] ?? null;

        if (empty($trimmedBody) && ! $mediaId) {
            throw ValidationException::withMessages([
                'body' => ['Nội dung tin nhắn không được để trống.'],
            ]);
        }

        $messageType = $mediaId ? MessageType::IMAGE : MessageType::TEXT;

        return DB::transaction(function () use ($sender, $conversation, $replyToMessage, $trimmedBody, $messageType, $mediaId) {
            // 4. Create message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'body' => $trimmedBody ?: null,
                'message_type' => $messageType,
                'status' => MessageStatus::SENT,
                'reply_to_message_id' => $replyToMessage->id,
            ]);

            // 5. Attach media if provided
            if ($mediaId) {
                app(AttachMediaToModelAction::class)->execute($sender, $message, [$mediaId], 'message_attachment');
            }

            // 6. Update conversation
            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => now(),
            ]);

            // 7. Notify other participants
            $recipient = $conversation->getRecipientFor($sender);
            if ($recipient) {
                // If muted/restricted is handled by notification policy, it will suppress it
                $recipient->notify(new MessageReceived($message));
            }

            return $message;
        });
    }
}
