<?php

namespace App\Notifications\Community;

use App\Models\Community;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommunitySuspendedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Community $community,
        public string $safeReason
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'community_suspended',
            'community_id' => $this->community->id,
            'community_name' => $this->community->name,
            'title' => 'Cộng đồng tạm thời bị khóa',
            'body' => 'Cộng đồng '.$this->community->name.' đã tạm thời bị khóa. '.$this->safeReason,
            'action_url' => route('community.index'),
        ];
    }
}
