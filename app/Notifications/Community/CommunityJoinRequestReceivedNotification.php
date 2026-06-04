<?php

namespace App\Notifications\Community;

use App\Models\CommunityJoinRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommunityJoinRequestReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(public CommunityJoinRequest $joinRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $community = $this->joinRequest->community;
        $requester = $this->joinRequest->user;

        return [
            'type' => 'community_join_request_received',
            'community_id' => $community?->id,
            'community_name' => $community?->name,
            'join_request_id' => $this->joinRequest->id,
            'requester_id' => $requester?->id,
            'requester_name' => $requester?->name,
            'title' => 'Yêu cầu tham gia cộng đồng',
            'body' => ($requester?->name ?? 'Ai đó').' muốn tham gia '.($community?->name ?? 'cộng đồng của bạn').'.',
            'action_url' => route('community.show', ['community' => $community?->id]),
        ];
    }
}
