<?php

namespace App\Notifications\Community;

use App\Models\CommunitySuggestion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommunitySuggestionReviewedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly CommunitySuggestion $suggestion) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusLabel = $this->suggestion->status?->label() ?? 'Cập nhật trạng thái';
        $body = match ($this->suggestion->status?->value) {
            'need_more_information' => 'Đề xuất cộng đồng "'.$this->suggestion->suggested_name.'" cần bổ sung thông tin: '.($this->suggestion->admin_instruction ?? 'Vui lòng kiểm tra lại.'),
            'converted_to_community' => 'Đề xuất cộng đồng "'.$this->suggestion->suggested_name.'" đã được phê duyệt và tạo thành công.',
            'rejected' => 'Đề xuất cộng đồng "'.$this->suggestion->suggested_name.'" không được phê duyệt: '.($this->suggestion->admin_reason ?? 'Không rõ lý do.'),
            default => 'Đề xuất cộng đồng "'.$this->suggestion->suggested_name.'" đã được cập nhật trạng thái: '.$statusLabel,
        };

        return [
            'type' => 'community_suggestion_reviewed',
            'suggestion_id' => $this->suggestion->id,
            'suggested_name' => $this->suggestion->suggested_name,
            'status' => $this->suggestion->status?->value,
            'title' => 'Đề xuất cộng đồng: '.$statusLabel,
            'body' => $body,
            'action_url' => route('community.index').'?subTab=mine',
        ];
    }
}
