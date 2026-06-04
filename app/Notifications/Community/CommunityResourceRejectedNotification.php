<?php

namespace App\Notifications\Community;

use App\Models\CommunityResource;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommunityResourceRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public CommunityResource $resource,
        public string $safeReason
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'community_resource_rejected',
            'community_id' => $this->resource->community_id,
            'resource_id' => $this->resource->id,
            'resource_title' => $this->resource->title,
            'title' => 'Tài nguyên không được phê duyệt',
            'body' => 'Tài nguyên "'.$this->resource->title.'" không được phê duyệt. '.$this->safeReason,
            'action_url' => route('community.show', ['community' => $this->resource->community_id]),
        ];
    }
}
