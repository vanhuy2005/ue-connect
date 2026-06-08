<?php

namespace App\Notifications\Community;

use App\Models\Community;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClubManagerGrantedNotification extends Notification
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
            'type' => 'club_manager_granted',
            'community_id' => $this->community->id,
            'community_name' => $this->community->name,
            'title' => 'Bạn được cấp quyền Quản lý cộng đồng',
            'body' => 'Bạn đã được cấp quyền Quản lý trong cộng đồng '.$this->community->name.'.',
            'action_url' => route('community.show', ['community' => $this->community->id]),
        ];
    }
}
