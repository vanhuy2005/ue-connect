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
     */
    public function send($notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWebPush')) {
            return;
        }

        // Check if user has global browser push enabled
        $preferences = $notifiable->notificationPreference ?? null;
        if ($preferences && ! $preferences->browser_push_enabled) {
            Log::debug('WebPush skipped because browser push is disabled.', [
                'user_id' => $notifiable->getKey(),
                'notification' => $notification::class,
            ]);

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
        if ($category && $preferences) {
            $preferences = $notifiable->notificationPreference;
            $hasCategoryPreference = array_key_exists($category, $preferences->getAttributes())
                || array_key_exists($category, $preferences->getCasts());

            if (! $hasCategoryPreference) {
                Log::warning('WebPush category preference is missing.', [
                    'user_id' => $notifiable->getKey(),
                    'category' => $category,
                    'notification' => $notification::class,
                ]);
            } elseif (! (bool) $preferences->getAttribute($category)) {
                Log::debug('WebPush skipped because category push is disabled.', [
                    'user_id' => $notifiable->getKey(),
                    'category' => $category,
                    'notification' => $notification::class,
                ]);

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
                Log::warning('WebPush failed to send.', [
                    'endpoint' => $this->maskEndpoint($endpoint),
                    'reason' => $report->getReason(),
                ]);

                if ($report->isSubscriptionExpired()) {
                    $sub->revoke();
                } else {
                    $sub->markFailed();
                }
            }
        }
    }

    private function maskEndpoint(string $endpoint): string
    {
        if (strlen($endpoint) <= 24) {
            return '***';
        }

        return substr($endpoint, 0, 16).'...'.substr($endpoint, -8);
    }
}
