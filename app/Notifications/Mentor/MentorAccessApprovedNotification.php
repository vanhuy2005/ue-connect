<?php

namespace App\Notifications\Mentor;

use App\Models\MentorAccessRequest;
use App\Models\MentorProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MentorAccessApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly MentorAccessRequest $request,
        public readonly MentorProfile $mentorProfile,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'mentor_access_approved',
            'mentor_profile_id' => $this->mentorProfile->id,
            'title' => 'Yêu cầu Mentor đã được duyệt!',
            'body' => 'Chúc mừng! Tài khoản Mentor của bạn đã được phê duyệt. Hãy thiết lập hồ sơ Mentor để bắt đầu.',
            'action_url' => route('mentor.setup'),
        ];
    }
}
