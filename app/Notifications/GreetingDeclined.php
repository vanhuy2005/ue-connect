<?php

namespace App\Notifications;

use App\Models\Greeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GreetingDeclined extends Notification
{
    use Queueable;

    public function __construct(public Greeting $greeting) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $receiver = $this->greeting->receiver;

        return [
            'type' => 'greeting_declined',
            'greeting_id' => $this->greeting->id,
            'receiver_id' => $receiver->id,
            'receiver_name' => $receiver->name,
            'title' => 'Lời chào kết nối bị từ chối',
            'body' => $receiver->name.' đã từ chối lời mời kết nối của bạn.',
            'action_url' => route('connections.index'),
        ];
    }
}
