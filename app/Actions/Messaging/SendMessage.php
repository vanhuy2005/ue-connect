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

class SendMessage
{
    /**
     * Send a text or image message inside a conversation.
     *
     * @param  array{body?: ?string, media_id?: ?int}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $sender, Conversation $conversation, array $data): Message
    {
        Gate::forUser($sender)->authorize('sendMessage', $conversation);

        // Validate body and media_id
        Validator::make($data, [
            'body' => ['nullable', 'string', 'max:2000'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
        ])->validate();

        $trimmedBody = isset($data['body']) ? trim($data['body']) : '';
        $mediaId = $data['media_id'] ?? null;

        if (empty($trimmedBody) && ! $mediaId) {
            throw ValidationException::withMessages([
                'body' => ['Tin nhắn không được để trống.'],
            ]);
        }

        $messageType = $mediaId ? MessageType::IMAGE : MessageType::TEXT;

        return DB::transaction(function () use ($sender, $conversation, $trimmedBody, $messageType, $mediaId) {
            // 1. Create message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'body' => $trimmedBody ?: null,
                'message_type' => $messageType,
                'status' => MessageStatus::SENT,
            ]);

            // 2. Attach media if provided
            if ($mediaId) {
                app(AttachMediaToModelAction::class)->execute($sender, $message, [$mediaId], 'message_attachment');
            }

            // 3. Update conversation
            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => now(),
            ]);

            // 4. Notify other participants
            $recipient = $conversation->getRecipientFor($sender);
            if ($recipient) {
                $recipient->notify(new MessageReceived($message));
            }

            return $message;
        });
    }
}
