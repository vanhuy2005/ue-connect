<?php

namespace App\Notifications;

use App\Channels\Messages\WebPushMessage;
use App\Channels\WebPushChannel;
use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemAnnouncementNotification extends Notification
{
    use Queueable;

    public function __construct(public Announcement $announcement) {}

    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'system_announcement',
            'announcement_id' => $this->announcement->id,
            'sender_name' => 'UEConnect',
            'title' => $this->announcement->title,
            'body' => $this->announcement->body,
            'action_url' => route('dashboard'),
        ];
    }

    public function toWebPush(object $notifiable): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->announcement->title)
            ->body(str($this->announcement->body)->limit(50)->toString())
            ->url(route('dashboard'))
            ->icon('/icons/icon-192x192.png')
            ->tag('announcement_'.$this->announcement->id)
            ->category('push_admin_announcements_enabled');
    }
}
