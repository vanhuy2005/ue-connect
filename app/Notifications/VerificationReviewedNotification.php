<?php

namespace App\Notifications;

use App\Channels\Messages\WebPushMessage;
use App\Channels\WebPushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationReviewedNotification extends Notification
{
    use Queueable;

    protected $requestModel;

    public function __construct($requestModel)
    {
        $this->requestModel = $requestModel;
    }

    public function via($notifiable)
    {
        return ['database', 'mail', WebPushChannel::class];
    }

    public function toMail($notifiable)
    {
        $status = $this->requestModel->status ?? 'updated';

        return (new MailMessage)
            ->subject('Verification status updated')
            ->line('Your verification request status has been updated: '.$status)
            ->action('View profile', url('/app/profile'))
            ->line('If you believe this is an error, contact support.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'verification_reviewed',
            'verification_id' => $this->requestModel->id ?? null,
            'status' => $this->requestModel->status ?? null,
        ];
    }

    public function toWebPush($notifiable): WebPushMessage
    {
        $status = $this->requestModel->status ?? 'updated';
        $statusText = $status === 'approved' ? 'đã được chấp nhận' : ($status === 'rejected' ? 'đã bị từ chối' : 'đã cập nhật');

        return (new WebPushMessage)
            ->title('Xác thực danh tính')
            ->body('Yêu cầu xác thực danh tính của bạn '.$statusText.'.')
            ->url(url('/app/profile'))
            ->icon('/images/icons/icon-192.png')
            ->tag('verification_reviewed_'.($this->requestModel->id ?? uniqid()))
            ->category('push_verification_enabled');
    }
}
