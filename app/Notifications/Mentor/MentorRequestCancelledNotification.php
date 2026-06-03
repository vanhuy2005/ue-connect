<?php

namespace App\Notifications\Mentor;

use App\Models\MentorRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MentorRequestCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly MentorRequest $mentorRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'mentor_request_cancelled',
            'mentor_request_id' => $this->mentorRequest->id,
            'student_id' => $this->mentorRequest->student_id,
            'student_name' => $this->mentorRequest->student->name,
            'title' => 'Yêu cầu cố vấn đã được hủy',
            'body' => $this->mentorRequest->student->name.' đã hủy yêu cầu cố vấn về: '.$this->mentorRequest->topic,
            'action_url' => route('mentor.requests.show', $this->mentorRequest->id),
        ];
    }
}
