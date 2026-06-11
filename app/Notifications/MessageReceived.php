<?php

namespace App\Notifications;

use App\Channels\Messages\WebPushMessage;
use App\Channels\WebPushChannel;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MessageReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Message $message) {}

    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
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

    public function toWebPush(object $notifiable): WebPushMessage
    {
        $sender = $this->message->sender;

        return (new WebPushMessage)
            ->title($sender->name)
            ->body(str($this->message->body)->limit(50)->toString())
            ->url(route('messages.index', ['conversation' => $this->message->conversation_id]))
            ->icon($sender->avatar_url ?? '/img/default-avatar.png')
            ->tag('message_'.$this->message->conversation_id)
            ->category('push_messages_enabled');
    }
}
