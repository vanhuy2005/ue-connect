<?php

namespace Tests\Feature;

use App\Channels\Messages\WebPushMessage;
use App\Channels\WebPushChannel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Tests\TestCase;

class WebPushChannelTest extends TestCase
{
    use RefreshDatabase;

    public function test_channel_skips_when_browser_push_is_disabled(): void
    {
        $user = User::factory()->create();
        $user->notificationPreference()->create([
            'browser_push_enabled' => false,
            'push_messages_enabled' => true,
        ]);
        $user->pushSubscriptions()->create($this->subscriptionPayload());

        $notification = new class extends Notification
        {
            public bool $webPushPayloadBuilt = false;

            public function toWebPush(object $notifiable): WebPushMessage
            {
                $this->webPushPayloadBuilt = true;

                return (new WebPushMessage)
                    ->title('Skipped')
                    ->body('This should not be sent.')
                    ->category('push_messages_enabled');
            }
        };

        (new WebPushChannel)->send($user->fresh('notificationPreference'), $notification);

        $this->assertFalse($notification->webPushPayloadBuilt);
        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'failed_attempts' => 0,
            'revoked_at' => null,
        ]);
    }

    public function test_channel_skips_when_category_push_is_disabled(): void
    {
        $user = User::factory()->create();
        $user->notificationPreference()->create([
            'browser_push_enabled' => true,
            'push_messages_enabled' => false,
        ]);
        $user->pushSubscriptions()->create($this->subscriptionPayload());

        $notification = new class extends Notification
        {
            public bool $webPushPayloadBuilt = false;

            public function toWebPush(object $notifiable): WebPushMessage
            {
                $this->webPushPayloadBuilt = true;

                return (new WebPushMessage)
                    ->title('Skipped')
                    ->body('This should not be sent.')
                    ->category('push_messages_enabled');
            }
        };

        (new WebPushChannel)->send($user->fresh('notificationPreference'), $notification);

        $this->assertTrue($notification->webPushPayloadBuilt);
        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'failed_attempts' => 0,
            'revoked_at' => null,
        ]);
    }

    public function test_channel_handles_missing_preferences_without_crashing(): void
    {
        $user = User::factory()->create();

        (new WebPushChannel)->send($user, new class extends Notification
        {
            public function toWebPush(object $notifiable): WebPushMessage
            {
                return (new WebPushMessage)
                    ->title('No subscriptions')
                    ->body('No preferences or subscriptions.');
            }
        });

        $this->assertTrue(true);
    }

    /**
     * @return array<string, mixed>
     */
    private function subscriptionPayload(): array
    {
        return [
            'endpoint' => 'https://example.test/push/'.fake()->uuid(),
            'public_key' => 'test-public-key',
            'auth_token' => 'test-auth-token',
            'content_encoding' => 'aes128gcm',
            'user_agent' => 'PHPUnit',
            'failed_attempts' => 0,
        ];
    }
}
