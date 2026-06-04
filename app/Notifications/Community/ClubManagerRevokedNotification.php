<?php

namespace App\Notifications\Community;

use App\Models\Community;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClubManagerRevokedNotification extends Notification
{
    use Queueable;

    public function __construct(public Community $community) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'club_manager_revoked',
            'community_id' => $this->community->id,
            'community_name' => $this->community->name,
            'title' => 'Quyền Quản lý CLB đã bị thu hồi',
            'body' => 'Quyền Quản lý trong cộng đồng '.$this->community->name.' của bạn đã bị thu hồi.',
            'action_url' => route('community.show', ['community' => $this->community->id]),
        ];
    }
}
