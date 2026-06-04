<?php

namespace App\Notifications\Community;

use App\Models\Community;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommunityJoinApprovedNotification extends Notification
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
            'type' => 'community_join_approved',
            'community_id' => $this->community->id,
            'community_name' => $this->community->name,
            'title' => 'Yêu cầu tham gia được chấp nhận',
            'body' => 'Yêu cầu tham gia cộng đồng '.$this->community->name.' của bạn đã được chấp nhận.',
            'action_url' => route('community.show', ['community' => $this->community->id]),
        ];
    }
}
