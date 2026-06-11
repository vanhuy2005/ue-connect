<?php

namespace App\Notifications\Mentor;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MentorAccessRevokedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly ?string $reason = null) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'mentor_access_revoked',
            'title' => 'Quyền truy cập Mentor bị thu hồi',
            'body' => 'Quyền truy cập Mentor của bạn đã bị thu hồi bởi quản trị viên. '.($this->reason ? 'Lý do: '.$this->reason : ''),
            'action_url' => null,
        ];
    }
}
