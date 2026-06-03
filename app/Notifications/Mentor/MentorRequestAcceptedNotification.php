<?php

namespace App\Notifications\Mentor;

use App\Models\MentorRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MentorRequestAcceptedNotification extends Notification
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
            'type' => 'mentor_request_accepted',
            'mentor_request_id' => $this->mentorRequest->id,
            'mentor_id' => $this->mentorRequest->mentor_id,
            'mentor_name' => $this->mentorRequest->mentor->name,
            'conversation_id' => $this->mentorRequest->conversation_id,
            'title' => 'Yêu cầu cố vấn đã được chấp nhận!',
            'body' => $this->mentorRequest->mentor->name.' đã chấp nhận yêu cầu cố vấn của bạn về: '.$this->mentorRequest->topic,
            'action_url' => $this->mentorRequest->conversation_id
                ? route('messages.index', ['conversation' => $this->mentorRequest->conversation_id])
                : route('mentor.requests.show', $this->mentorRequest->id),
        ];
    }
}
