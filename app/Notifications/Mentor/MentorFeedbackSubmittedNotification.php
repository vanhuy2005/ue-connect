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
            'mentor_request_id' => $this->feedback->mentor_request_id,
            'student_id' => $this->feedback->student_id,
            'student_name' => $this->feedback->student->name,
            'helpfulness_level' => $this->feedback->helpfulness_level->value,
            'title' => 'Bạn có phản hồi cố vấn mới',
            'body' => $this->feedback->student->name.' đã gửi phản hồi riêng tư cho phiên cố vấn.',
            'action_url' => route('mentor.requests.show', $this->feedback->mentor_request_id),
        ];
    }
}
