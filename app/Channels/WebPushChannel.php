<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (! method_exists($notification, 'toWebPush')) {
            return;
        }

        // Check if user has global browser push enabled
        $preferences = $notifiable->notificationPreference ?? null;
        if ($preferences && ! $preferences->browser_push_enabled) {
            return;
        }

        $subscriptions = $notifiable->pushSubscriptions()->active()->get();
        if ($subscriptions->isEmpty()) {
            return;
        }

        $message = $notification->toWebPush($notifiable);
        if (! $message) {
            return;
        }

        $category = $message->getCategory();
        if ($category && isset($notifiable->notificationPreference)) {
            $preferences = $notifiable->notificationPreference;
            if (! $preferences->{$category}) {
                return;
            }
        }

        $auth = [
            'VAPID' => [
                'subject' => config('webpush.vapid.subject'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ];

        $webPush = new WebPush($auth);

        $payload = json_encode($message->toArray());

        foreach ($subscriptions as $subscription) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->public_key,
                    'authToken' => $subscription->auth_token,
                    'contentEncoding' => $subscription->content_encoding,
                ]),
                $payload
            );
        }

        /**
         * Check sent results
         *
         * @var MessageSentReport $report
         */
        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            $sub = $subscriptions->firstWhere('endpoint', $endpoint);

            if (! $sub) {
                continue;
            }

            if ($report->isSuccess()) {
                $sub->markUsed();
            } else {
                Log::warning("WebPush failed to send for endpoint {$endpoint}: {$report->getReason()}");

                if ($report->isSubscriptionExpired()) {
                    $sub->revoke();
                } else {
                    $sub->markFailed();
                }
            }
        }
    }
}
