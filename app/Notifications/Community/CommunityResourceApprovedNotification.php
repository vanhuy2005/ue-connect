<?php

namespace App\Notifications\Community;

use App\Models\CommunityResource;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommunityResourceApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public CommunityResource $resource) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'community_resource_approved',
            'community_id' => $this->resource->community_id,
            'resource_id' => $this->resource->id,
            'resource_title' => $this->resource->title,
            'title' => 'Tài nguyên được phê duyệt',
            'body' => 'Tài nguyên "'.$this->resource->title.'" của bạn đã được phê duyệt và hiển thị trong cộng đồng.',
            'action_url' => route('community.show', ['community' => $this->resource->community_id]),
        ];
    }
}
