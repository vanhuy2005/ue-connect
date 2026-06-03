<?php

namespace App\Notifications\Mentor;

use App\Models\MentorRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MentorRequestDeclinedNotification extends Notification
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
            'type' => 'mentor_request_declined',
            'mentor_request_id' => $this->mentorRequest->id,
            'mentor_id' => $this->mentorRequest->mentor_id,
            'mentor_name' => $this->mentorRequest->mentor->name,
            'title' => 'Yêu cầu cố vấn đã bị từ chối',
            'body' => $this->mentorRequest->mentor->name.' đã từ chối yêu cầu cố vấn về: '.$this->mentorRequest->topic,
            'action_url' => route('mentor.requests.show', $this->mentorRequest->id),
        ];
    }
}
