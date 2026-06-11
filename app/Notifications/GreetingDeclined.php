<?php

namespace App\Notifications;

use App\Channels\Messages\WebPushMessage;
use App\Channels\WebPushChannel;
use App\Models\Greeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GreetingDeclined extends Notification
{
    use Queueable;

    public function __construct(public Greeting $greeting) {}

    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
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

    public function toWebPush(object $notifiable): WebPushMessage
    {
        $receiver = $this->greeting->receiver;

        return (new WebPushMessage)
            ->title('Lời chào kết nối bị từ chối')
            ->body($receiver->name.' đã từ chối lời mời kết nối của bạn.')
            ->url(route('connections.index'))
            ->icon($receiver->avatar_url ?? '/img/default-avatar.png')
            ->tag('greeting_declined_'.$this->greeting->id)
            ->category('push_greetings_enabled');
    }
}
