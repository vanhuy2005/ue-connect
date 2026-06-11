<?php

namespace App\Notifications\Mentor;

use App\Models\MentorFeedback;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MentorFeedbackSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly MentorFeedback $feedback) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'mentor_feedback_submitted',
            'mentor_feedback_id' => $this->feedback->id,
            'helpfulness_level' => $this->feedback->helpfulness_level->value,
            'title' => 'Bạn có phản hồi cố vấn mới',
            'body' => 'Một sinh viên đã gửi phản hồi ẩn danh cho phiên cố vấn của bạn.',
            'action_url' => route('mentor.dashboard'),
        ];
    }
}
