<?php

namespace App\Notifications\Mentor;

use App\Models\MentorAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MentorAccessRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly MentorAccessRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'mentor_access_rejected',
            'mentor_access_request_id' => $this->request->id,
            'title' => 'Yêu cầu Mentor không được duyệt',
            'body' => 'Yêu cầu trở thành mentor của bạn chưa được phê duyệt lần này. '.($this->request->review_reason ? 'Lý do: '.$this->request->review_reason : ''),
            'action_url' => route('mentor.apply'),
        ];
    }
}
