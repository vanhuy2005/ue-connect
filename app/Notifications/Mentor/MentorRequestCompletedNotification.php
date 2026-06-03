<?php

namespace App\Notifications\Mentor;

use App\Models\MentorRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MentorRequestCompletedNotification extends Notification
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
            'type' => 'mentor_request_completed',
            'mentor_request_id' => $this->mentorRequest->id,
            'title' => 'Yêu cầu cố vấn đã hoàn thành',
            'body' => 'Yêu cầu cố vấn về '.$this->mentorRequest->topic.' đã được đánh dấu hoàn thành.',
            'action_url' => route('mentor.requests.show', $this->mentorRequest->id),
        ];
    }
}
