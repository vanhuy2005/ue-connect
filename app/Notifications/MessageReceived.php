<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MessageReceived extends Notification
{
    use Queueable;

    public function __construct(public Message $message) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $sender = $this->message->sender;

        return [
            'type' => 'message_received',
            'message_id' => $this->message->id,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
            'conversation_id' => $this->message->conversation_id,
            'title' => 'Tin nhắn mới',
            'body' => 'Bạn có tin nhắn mới.',
            'action_url' => route('messages.index', ['conversation' => $this->message->conversation_id]),
        ];
    }
}
