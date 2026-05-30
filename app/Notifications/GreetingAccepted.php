<?php

namespace App\Notifications;

use App\Models\Greeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GreetingAccepted extends Notification
{
    use Queueable;

    public function __construct(public Greeting $greeting, public int $conversationId) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $receiver = $this->greeting->receiver;

        return [
            'type' => 'greeting_accepted',
            'greeting_id' => $this->greeting->id,
            'receiver_id' => $receiver->id,
            'receiver_name' => $receiver->name,
            'conversation_id' => $this->conversationId,
            'title' => 'Đã đồng ý kết nối',
            'body' => $receiver->name.' đã đồng ý lời mời kết nối của bạn. Hai bạn có thể nhắn tin cho nhau ngay bây giờ.',
            'action_url' => route('messages.index', ['conversation' => $this->conversationId]),
        ];
    }
}
