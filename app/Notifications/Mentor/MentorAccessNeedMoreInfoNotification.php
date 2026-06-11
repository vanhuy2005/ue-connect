<?php

namespace App\Notifications\Mentor;

use App\Models\MentorAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MentorAccessNeedMoreInfoNotification extends Notification
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
            'type' => 'mentor_access_need_more_info',
            'mentor_access_request_id' => $this->request->id,
            'title' => 'Yêu cầu Mentor cần thêm thông tin',
            'body' => 'Yêu cầu trở thành mentor của bạn cần bổ sung thêm thông tin. '.($this->request->review_reason ? 'Chi tiết: '.$this->request->review_reason : ''),
            'action_url' => null,
        ];
    }
}
