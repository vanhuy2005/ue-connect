<?php

namespace App\Notifications;

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
        return ['database', 'mail'];
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
}
