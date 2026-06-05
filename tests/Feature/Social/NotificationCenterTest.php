<?php

namespace Tests\Feature\Social;

use App\Actions\Connections\AcceptGreeting;
use App\Actions\Connections\SendGreeting;
use App\Actions\Messaging\FindOrCreateDirectConversation;
use App\Actions\Messaging\SendMessage;
use App\Enums\AccountStatus;
use App\Models\User;
use App\Notifications\GreetingReceived;
use App\Notifications\MessageReceived;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $sender;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        // Main User
        $this->user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $this->user->assignRole('student');
        $this->user->profile()->create([
            'display_name' => 'Active User',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);

        // Sender
        $this->sender = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $this->sender->assignRole('student');
        $this->sender->profile()->create([
            'display_name' => 'John Sender',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);
    }

    public function test_receiving_greeting_sends_notification(): void
    {
        $sendAction = resolve(SendGreeting::class);
        $greeting = $sendAction->execute($this->sender, $this->user, ['message' => 'Hello friend!']);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->user->id,
            'type' => GreetingReceived::class,
        ]);

        $notification = $this->user->notifications()->first();
        $this->assertEquals('Lời chào kết nối mới', $notification->data['title']);
        $this->assertEquals($this->sender->name.' đã gửi cho bạn một lời chào kết nối.', $notification->data['body']);
    }

    public function test_receiving_message_sends_safe_notification(): void
    {
        // Connect them first
        $sendAction = resolve(SendGreeting::class);
        $greeting = $sendAction->execute($this->sender, $this->user);
        $acceptAction = resolve(AcceptGreeting::class);
        $acceptAction->execute($this->user, $greeting);

        $convoAction = resolve(FindOrCreateDirectConversation::class);
        $conversation = $convoAction->execute($this->sender, $this->user);

        $msgAction = resolve(SendMessage::class);
        $msgAction->execute($this->sender, $conversation, ['body' => 'Highly confidential message body']);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->user->id,
            'type' => MessageReceived::class,
        ]);

        $notification = $this->user->notifications()->where('type', MessageReceived::class)->first();
        $this->assertEquals('Tin nhắn mới', $notification->data['title']);
        $this->assertEquals('Bạn có tin nhắn mới.', $notification->data['body']);
    }

    public function test_notification_center_actions(): void
    {
        // Create a notification for the user
        $sendAction = resolve(SendGreeting::class);
        $greeting = $sendAction->execute($this->sender, $this->user, ['message' => 'Hello friend!']);

        $notification = $this->user->notifications()->first();
        $this->assertNull($notification->read_at);

        $this->actingAs($this->user);

        // Mark as read Volt call
        Volt::test('pages.app.notifications')
            ->assertSee('Lời chào kết nối mới')
            ->call('markAsRead', $notification->id)
            ->assertHasNoErrors();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_notification_center_mark_all_as_read(): void
    {
        // Create two notifications from two different senders
        $sendAction = resolve(SendGreeting::class);
        $sendAction->execute($this->sender, $this->user, ['message' => 'Hello 1!']);

        $sender2 = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $sender2->assignRole('student');
        $sender2->profile()->create([
            'display_name' => 'John Sender Two',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);
        $sendAction->execute($sender2, $this->user, ['message' => 'Hello 2!']);

        $this->assertEquals(2, $this->user->unreadNotifications()->count());

        $this->actingAs($this->user);

        Volt::test('pages.app.notifications')
            ->call('markAllAsRead')
            ->assertHasNoErrors();

        $this->assertEquals(0, $this->user->unreadNotifications()->count());
    }
}
