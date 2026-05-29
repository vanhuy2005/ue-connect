<?php

namespace Tests\Feature\Social;

use App\Actions\Connections\AcceptGreeting;
use App\Actions\Connections\BlockUser;
use App\Actions\Connections\SendGreeting;
use App\Actions\Messaging\FindOrCreateDirectConversation;
use App\Actions\Messaging\SendMessage;
use App\Actions\Messaging\SendSharedPostMessage;
use App\Enums\AccountStatus;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Connection;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected User $stranger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);

        // Main User
        $this->user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->user->assignRole('student');
        $this->user->profile()->create([
            'display_name' => 'Active User',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        // Other User (Connected)
        $this->otherUser = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->otherUser->assignRole('student');
        $this->otherUser->profile()->create([
            'display_name' => 'Connected User',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        // Stranger (Not Connected)
        $this->stranger = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->stranger->assignRole('student');
        $this->stranger->profile()->create([
            'display_name' => 'Stranger User',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        // Establish connection between User and Other User
        $sendAction = resolve(SendGreeting::class);
        $greeting = $sendAction->execute($this->user, $this->otherUser);
        $acceptAction = resolve(AcceptGreeting::class);
        $acceptAction->execute($this->otherUser, $greeting);
    }

    public function test_connected_users_can_find_or_create_direct_conversation(): void
    {
        $action = resolve(FindOrCreateDirectConversation::class);
        $conversation = $action->execute($this->user, $this->otherUser);

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertEquals(2, $conversation->participants()->count());
    }

    public function test_user_can_send_text_message(): void
    {
        $convoAction = resolve(FindOrCreateDirectConversation::class);
        $conversation = $convoAction->execute($this->user, $this->otherUser);

        $msgAction = resolve(SendMessage::class);
        $message = $msgAction->execute($this->user, $conversation, [
            'body' => 'Hello friend, how are you?',
        ]);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals('Hello friend, how are you?', $message->body);
        $this->assertEquals($this->user->id, $message->sender_id);
        $this->assertEquals(MessageType::TEXT, $message->message_type);
        $this->assertEquals(MessageStatus::SENT, $message->status);

        // Verify conversation last message updated
        $conversation->refresh();
        $this->assertEquals($message->id, $conversation->last_message_id);
        $this->assertNotNull($conversation->last_message_at);
    }

    public function test_non_connected_users_cannot_send_messages(): void
    {
        $convoAction = resolve(FindOrCreateDirectConversation::class);
        // Create conversation manually for testing policy bypass attempt
        $conversation = $convoAction->execute($this->user, $this->stranger);

        // Remove active connection if any (none exists)
        $this->expectException(AuthorizationException::class);

        $msgAction = resolve(SendMessage::class);
        $msgAction->execute($this->user, $conversation, [
            'body' => 'Spamming stranger.',
        ]);
    }

    public function test_blocked_user_cannot_send_messages(): void
    {
        $convoAction = resolve(FindOrCreateDirectConversation::class);
        $conversation = $convoAction->execute($this->user, $this->otherUser);

        // Block other user
        $blockAction = resolve(BlockUser::class);
        $blockAction->execute($this->user, $this->otherUser);

        $this->expectException(AuthorizationException::class);

        $msgAction = resolve(SendMessage::class);
        $msgAction->execute($this->otherUser, $conversation, [
            'body' => 'Can you hear me?',
        ]);
    }

    public function test_user_can_share_post_via_message_to_connected_user(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'body' => 'Awesome post content.',
            'status' => PostStatus::PUBLISHED,
        ]);

        $convoAction = resolve(FindOrCreateDirectConversation::class);
        $conversation = $convoAction->execute($this->user, $this->otherUser);

        $shareAction = resolve(SendSharedPostMessage::class);
        $message = $shareAction->execute($this->user, $conversation, $post, [
            'body' => 'Check this out!',
        ]);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals(MessageType::SHARED_POST, $message->message_type);
        $this->assertEquals($post->id, $message->shared_post_id);
        $this->assertEquals('Check this out!', $message->body);
    }

    public function test_cannot_share_post_recipient_cannot_view(): void
    {
        // Private post of user
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'body' => 'Super private diary.',
            'status' => PostStatus::PUBLISHED,
            'visibility' => PostVisibility::PRIVATE,
        ]);

        $convoAction = resolve(FindOrCreateDirectConversation::class);
        $conversation = $convoAction->execute($this->user, $this->otherUser);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Người nhận không có quyền xem bài viết này.');

        $shareAction = resolve(SendSharedPostMessage::class);
        $shareAction->execute($this->user, $conversation, $post);
    }

    public function test_volt_messages_thread_interaction(): void
    {
        $convoAction = resolve(FindOrCreateDirectConversation::class);
        $conversation = $convoAction->execute($this->user, $this->otherUser);

        $this->actingAs($this->user);

        Volt::test('pages.app.messages', ['activeConversation' => $conversation])
            ->assertSet('selectedConversationId', $conversation->id)
            ->set('newMessageBody', 'Hi Volt!')
            ->call('submitMessage')
            ->assertHasNoErrors()
            ->assertSet('newMessageBody', '');

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $this->user->id,
            'body' => 'Hi Volt!',
            'message_type' => MessageType::TEXT->value,
        ]);
    }
}
