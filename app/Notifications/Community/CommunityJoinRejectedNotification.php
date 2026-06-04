<?php

namespace App\Notifications\Community;

use App\Models\Community;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommunityJoinRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ?Community $community,
        public string $safeReason
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'community_join_rejected',
            'community_id' => $this->community?->id,
            'community_name' => $this->community?->name,
            'title' => 'Yêu cầu tham gia không được chấp nhận',
            'body' => 'Yêu cầu tham gia cộng đồng '.($this->community?->name ?? '').' không được chấp nhận. '.$this->safeReason,
            'action_url' => route('community.index'),
        ];
    }
}
