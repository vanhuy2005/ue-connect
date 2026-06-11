<?php

namespace App\Notifications;

use App\Channels\Messages\WebPushMessage;
use App\Channels\WebPushChannel;
use App\Models\Greeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GreetingReceived extends Notification
{
    use Queueable;

    public function __construct(public Greeting $greeting) {}

    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
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

    public function toWebPush(object $notifiable): WebPushMessage
    {
        $sender = $this->greeting->sender;

        return (new WebPushMessage)
            ->title('Lời chào kết nối mới')
            ->body($sender->name.' đã gửi cho bạn một lời chào kết nối.')
            ->url(route('connections.index'))
            ->icon($sender->avatar_url ?? '/img/default-avatar.png')
            ->tag('greeting_'.$this->greeting->id)
            ->category('push_greetings_enabled');
    }
}
