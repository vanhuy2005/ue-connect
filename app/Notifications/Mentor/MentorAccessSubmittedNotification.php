<?php

namespace App\Notifications\Mentor;

use App\Models\MentorAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MentorAccessSubmittedNotification extends Notification
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
            'type' => 'mentor_access_submitted',
            'mentor_access_request_id' => $this->request->id,
            'applicant_id' => $this->request->user_id,
            'applicant_name' => $this->request->user->name,
            'role_context' => $this->request->requested_role_context,
            'title' => 'Yêu cầu trở thành Mentor mới',
            'body' => $this->request->user->name.' đã gửi yêu cầu trở thành mentor. Vui lòng xem xét.',
            'action_url' => route('admin.mentors.detail', $this->request->id),
        ];
    }
}
