<?php

namespace App\Notifications;

use App\Channels\Messages\WebPushMessage;
use App\Channels\WebPushChannel;
use App\Models\Greeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GreetingAccepted extends Notification
{
    use Queueable;

    public function __construct(public Greeting $greeting, public int $conversationId) {}

    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
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

    public function toWebPush(object $notifiable): WebPushMessage
    {
        $receiver = $this->greeting->receiver;

        return (new WebPushMessage)
            ->title('Đã đồng ý kết nối')
            ->body($receiver->name.' đã đồng ý lời mời kết nối của bạn. Hai bạn có thể nhắn tin cho nhau ngay bây giờ.')
            ->url(route('messages.index', ['conversation' => $this->conversationId]))
            ->icon($receiver->avatar_url ?? '/img/default-avatar.png')
            ->tag('greeting_accepted_'.$this->greeting->id)
            ->category('push_greetings_enabled');
    }
}
