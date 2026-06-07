<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemAnnouncementNotification extends Notification
{
    use Queueable;

    public function __construct(public Announcement $announcement) {}

    public function via(object $notifiable): array
    {
        return ['database'];
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
}
