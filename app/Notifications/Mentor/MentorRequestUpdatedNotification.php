<?php

namespace App\Notifications\Mentor;

use App\Models\MentorRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MentorRequestUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly MentorRequest $mentorRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'mentor_request_updated',
            'mentor_request_id' => $this->mentorRequest->id,
            'student_id' => $this->mentorRequest->student_id,
            'student_name' => $this->mentorRequest->student->name,
            'topic' => $this->mentorRequest->topic,
            'urgency' => $this->mentorRequest->urgency->value,
            'title' => 'Yêu cầu cố vấn được cập nhật',
            'body' => $this->mentorRequest->student->name.' đã cập nhật thông tin yêu cầu cố vấn về: '.$this->mentorRequest->topic,
            'action_url' => route('mentor.requests.show', $this->mentorRequest->id),
        ];
    }
}
