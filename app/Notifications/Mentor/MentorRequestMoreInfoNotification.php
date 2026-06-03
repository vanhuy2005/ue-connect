<?php

namespace App\Notifications\Mentor;

use App\Models\MentorRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MentorRequestMoreInfoNotification extends Notification
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
            'type' => 'mentor_request_more_info',
            'mentor_request_id' => $this->mentorRequest->id,
            'mentor_id' => $this->mentorRequest->mentor_id,
            'mentor_name' => $this->mentorRequest->mentor->name,
            'title' => 'Mentor cần thêm thông tin',
            'body' => $this->mentorRequest->mentor->name.' cần thêm thông tin cho yêu cầu: '.$this->mentorRequest->topic,
            'action_url' => route('mentor.requests.show', $this->mentorRequest->id),
        ];
    }
}
