<?php

namespace App\Notifications;

use App\Models\Greeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GreetingReceived extends Notification
{
    use Queueable;

    public function __construct(public Greeting $greeting) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $sender = $this->greeting->sender;

        return [
            'type' => 'greeting_received',
            'greeting_id' => $this->greeting->id,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
            'title' => 'Lời chào kết nối mới',
            'body' => $sender->name.' đã gửi cho bạn một lời chào kết nối.',
            'action_url' => route('connections.index'),
        ];
    }
}
