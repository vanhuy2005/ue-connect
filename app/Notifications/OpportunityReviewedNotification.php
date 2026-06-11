<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OpportunityReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Post $post,
        public string $status,
        public ?string $reason = null
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toArray(mixed $notifiable): array
    {
        $titleMap = [
            'approved' => 'Bài đăng Cơ hội đã được duyệt',
            'rejected' => 'Bài đăng Cơ hội bị từ chối',
        ];

        $bodyMap = [
            'approved' => 'Bài đăng cơ hội "'.$this->post->body.'" của bạn đã được kiểm duyệt và xuất bản.',
            'rejected' => 'Bài đăng cơ hội của bạn bị từ chối'.($this->reason ? '. Lý do: '.$this->reason : '.'),
        ];

        return [
            'type' => 'opportunity_reviewed',
            'post_id' => $this->post->id,
            'status' => $this->status,
            'reason' => $this->reason,
            'title' => $titleMap[$this->status] ?? 'Cập nhật bài đăng Cơ hội',
            'body' => $bodyMap[$this->status] ?? 'Trạng thái bài đăng cơ hội của bạn đã được cập nhật.',
            'action_url' => route('posts.show', $this->post),
        ];
    }
}
