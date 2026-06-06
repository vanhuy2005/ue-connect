<?php

namespace Tests\Feature\Social;

use App\Actions\Connections\AcceptGreeting;
use App\Actions\Connections\BlockUser;
use App\Actions\Connections\SendGreeting;
use App\Actions\Messaging\FindOrCreateDirectConversation;
use App\Actions\Messaging\SendMessage;
use App\Actions\Messaging\SendSharedPostMessage;
use App\Enums\AccountStatus;
use App\Enums\ConversationStatus;
use App\Enums\ConversationType;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Connection;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
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
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

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

    public function test_direct_conversation_pair_is_reused_and_unique(): void
    {
        $action = resolve(FindOrCreateDirectConversation::class);

        // A-B creates conversation
        $conversation1 = $action->execute($this->user, $this->otherUser);

        // B-A returns the exact same conversation
        $conversation2 = $action->execute($this->otherUser, $this->user);
        $this->assertEquals($conversation1->id, $conversation2->id);

        // Try to manually create duplicate and verify database unique constraint prevents duplicate direct pairs
        $this->expectException(QueryException::class);
        Conversation::create([
            'conversation_type' => ConversationType::DIRECT,
            'direct_user_low_id' => min($this->user->id, $this->otherUser->id),
            'direct_user_high_id' => max($this->user->id, $this->otherUser->id),
            'status' => ConversationStatus::ACTIVE,
        ]);
    }

    public function test_cannot_create_direct_convo_if_not_connected(): void
    {
        $action = resolve(FindOrCreateDirectConversation::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Hãy kết nối bạn bè trước khi bắt đầu trò chuyện.');

        $action->execute($this->user, $this->stranger);
    }

    public function test_cannot_create_direct_convo_if_blocked(): void
    {
        $blockAction = resolve(BlockUser::class);
        $blockAction->execute($this->user, $this->otherUser);

        $action = resolve(FindOrCreateDirectConversation::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Không thể tạo cuộc trò chuyện do trạng thái chặn giữa hai người dùng.');

        $action->execute($this->user, $this->otherUser);
    }

    public function test_cannot_create_direct_convo_with_self(): void
    {
        $action = resolve(FindOrCreateDirectConversation::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Không thể tạo cuộc trò chuyện với chính mình.');

        $action->execute($this->user, $this->user);
    }

    public function test_non_participant_cannot_access_conversation_via_route(): void
    {
        $convoAction = resolve(FindOrCreateDirectConversation::class);
        $conversation = $convoAction->execute($this->user, $this->otherUser);

        $this->actingAs($this->stranger);

        Volt::test('pages.app.messages', ['activeConversation' => $conversation])
            ->assertForbidden();
    }

    public function test_non_participant_cannot_load_conversation_via_livewire_selection(): void
    {
        $convoAction = resolve(FindOrCreateDirectConversation::class);
        $conversation = $convoAction->execute($this->user, $this->otherUser);

        $this->actingAs($this->stranger);

        Volt::test('pages.app.messages')
            ->call('selectConversation', $conversation->id)
            ->assertForbidden();
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

    public function test_active_conversation_exposes_profile_links_for_recipient_and_shared_post_author(): void
    {
        $conversation = resolve(FindOrCreateDirectConversation::class)->execute($this->user, $this->otherUser);
        $post = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'status' => PostStatus::PUBLISHED,
            'visibility' => PostVisibility::VERIFIED_USERS,
            'published_at' => now(),
        ]);

        resolve(SendSharedPostMessage::class)->execute($this->otherUser, $conversation, $post);

        $this->actingAs($this->user);

        Volt::test('pages.app.messages', ['activeConversation' => $conversation])
            ->assertSeeHtml(route('profile.show', $this->otherUser));
    }

    public function test_cannot_send_empty_or_whitespace_message(): void
    {
        $convoAction = resolve(FindOrCreateDirectConversation::class);
        $conversation = $convoAction->execute($this->user, $this->otherUser);
        $msgAction = resolve(SendMessage::class);

        // Empty message
        try {
            $msgAction->execute($this->user, $conversation, ['body' => '']);
            $this->fail('Empty message should be rejected.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('body', $e->errors());
        }

        // Whitespace-only message
        try {
            $msgAction->execute($this->user, $conversation, ['body' => "   \n  \t  "]);
            $this->fail('Whitespace-only message should be rejected.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('body', $e->errors());
        }
    }

    public function test_cannot_send_message_over_2000_chars(): void
    {
        $convoAction = resolve(FindOrCreateDirectConversation::class);
        $conversation = $convoAction->execute($this->user, $this->otherUser);
        $msgAction = resolve(SendMessage::class);

        $longBody = str_repeat('a', 2001);

        $this->expectException(ValidationException::class);
        $msgAction->execute($this->user, $conversation, ['body' => $longBody]);
    }

    public function test_valid_message_is_saved_trimmed(): void
    {
        $convoAction = resolve(FindOrCreateDirectConversation::class);
        $conversation = $convoAction->execute($this->user, $this->otherUser);
        $msgAction = resolve(SendMessage::class);

        $message = $msgAction->execute($this->user, $conversation, ['body' => "   Hello trimming test!   \n "]);
        $this->assertEquals('Hello trimming test!', $message->body);
    }

    public function test_non_connected_users_cannot_send_messages(): void
    {
        // Manually create direct conversation bypassing FindOrCreateDirectConversation
        $conversation = Conversation::create([
            'conversation_type' => ConversationType::DIRECT,
            'status' => ConversationStatus::ACTIVE,
        ]);
        $conversation->participants()->create(['user_id' => $this->user->id]);
        $conversation->participants()->create(['user_id' => $this->stranger->id]);

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

    public function test_shared_post_metadata_does_not_store_body_excerpt(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'body' => 'Secret original post body excerpt',
            'status' => PostStatus::PUBLISHED,
        ]);

        $convoAction = resolve(FindOrCreateDirectConversation::class);
        $conversation = $convoAction->execute($this->user, $this->otherUser);

        $shareAction = resolve(SendSharedPostMessage::class);
        $message = $shareAction->execute($this->user, $conversation, $post, [
            'body' => 'Look at this!',
        ]);

        $metadata = $message->metadata_json;
        $this->assertArrayNotHasKey('body_excerpt', $metadata);
        $this->assertArrayNotHasKey('author_name', $metadata);
        $this->assertEquals($this->user->id, $metadata['author_id']);
        $this->assertNotNull($metadata['shared_at']);
    }

    public function test_recipient_sees_preview_if_allowed(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'body' => 'Visble post content',
            'status' => PostStatus::PUBLISHED,
        ]);

        $convoAction = resolve(FindOrCreateDirectConversation::class);
        $conversation = $convoAction->execute($this->user, $this->otherUser);

        $shareAction = resolve(SendSharedPostMessage::class);
        $message = $shareAction->execute($this->user, $conversation, $post);

        $this->actingAs($this->otherUser);

        Volt::test('pages.app.messages', ['activeConversation' => $conversation])
            ->assertSee('Visble post content')
            ->assertDontSee('Bài viết này không còn khả dụng.');
    }

    public function test_recipient_sees_unavailable_state_after_post_hidden_or_deleted(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'body' => 'Hidden or deleted content',
            'status' => PostStatus::PUBLISHED,
        ]);

        $convoAction = resolve(FindOrCreateDirectConversation::class);
        $conversation = $convoAction->execute($this->user, $this->otherUser);

        $shareAction = resolve(SendSharedPostMessage::class);
        $message = $shareAction->execute($this->user, $conversation, $post);

        // Hide post
        $post->update(['status' => PostStatus::HIDDEN_BY_MODERATION]);

        $this->actingAs($this->otherUser);

        Volt::test('pages.app.messages', ['activeConversation' => $conversation])
            ->assertDontSee('Hidden or deleted content')
            ->assertSee('Bài viết này không còn khả dụng.');

        // Reset and soft delete post
        $post->update(['status' => PostStatus::PUBLISHED]);
        $post->delete();

        Volt::test('pages.app.messages', ['activeConversation' => $conversation])
            ->assertDontSee('Hidden or deleted content')
            ->assertSee('Bài viết này không còn khả dụng.');
    }

    public function test_post_policy_share_authorizations(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'body' => 'Shared post policy test',
            'status' => PostStatus::PUBLISHED,
        ]);

        $this->assertTrue($this->user->can('share', $post));

        // Hidden post
        $post->update(['status' => PostStatus::HIDDEN_BY_MODERATION]);
        $this->assertFalse($this->user->can('share', $post));

        // Soft deleted post
        $post->update(['status' => PostStatus::PUBLISHED]);
        $post->delete();
        $this->assertFalse($this->user->can('share', $post));
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
