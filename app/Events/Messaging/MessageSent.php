<?php

namespace App\Events\Messaging;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Message $message)
    {
        if (! $this->message->relationLoaded('sender')) {
            $this->message->load('sender.profile');
        }
        if (! $this->message->relationLoaded('replyTo')) {
            $this->message->load('replyTo.sender');
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.'.$this->message->conversation_id),
        ];
    }

    /**
     * Custom broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'body' => $this->message->body,
            'message_type' => $this->message->message_type?->value ?? $this->message->message_type,
            'client_message_id' => $this->message->client_message_id,
            'created_at' => $this->message->created_at->toIso8601String(),
            'sender' => [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->name,
                'profile' => [
                    'avatar_url' => $this->message->sender->profile?->avatar_url,
                ],
            ],
            'reply_to' => $this->message->replyTo ? [
                'id' => $this->message->replyTo->id,
                'body' => $this->message->replyTo->body,
                'is_recalled' => $this->message->replyTo->isRecalled(),
                'sender' => [
                    'name' => $this->message->replyTo->sender?->name,
                ],
            ] : null,
        ];
    }
}
