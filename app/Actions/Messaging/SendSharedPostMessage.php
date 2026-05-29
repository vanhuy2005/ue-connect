<?php

namespace App\Actions\Messaging;

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class SendSharedPostMessage
{
    /**
     * Share a post into a conversation.
     *
     * @param  array{body?: string}  $data
     *
     * @throws AuthorizationException|\Exception
     */
    public function execute(User $sender, Conversation $conversation, Post $post, array $data = []): Message
    {
        // 1. Authorize conversation
        Gate::forUser($sender)->authorize('sendMessage', $conversation);

        // 2. Retrieve recipient
        $recipient = $conversation->getRecipientFor($sender);
        if (! $recipient) {
            throw new \Exception('Không tìm thấy người nhận trong cuộc trò chuyện.');
        }

        // 3. Verify recipient can view the post (privacy check)
        $canRecipientView = Gate::forUser($recipient)->allows('view', $post);
        if (! $canRecipientView) {
            throw new \Exception('Người nhận không có quyền xem bài viết này.');
        }

        return DB::transaction(function () use ($sender, $conversation, $post, $data) {
            // 4. Create shared_post message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'body' => $data['body'] ?? null,
                'message_type' => MessageType::SHARED_POST,
                'status' => MessageStatus::SENT,
                'shared_post_id' => $post->id,
                'metadata_json' => [
                    'author_name' => $post->user->name,
                    'body_excerpt' => Str::limit($post->body, 120),
                ],
            ]);

            // 5. Update conversation
            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => now(),
            ]);

            return $message;
        });
    }
}
