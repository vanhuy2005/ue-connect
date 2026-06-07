<?php

namespace Tests\Feature\Social;

use App\Actions\Comments\CreateComment;
use App\Actions\Comments\DeleteComment;
use App\Actions\Comments\UpdateComment;
use App\Actions\Settings\EnsureUserSettingsExistAction;
use App\Enums\AccountStatus;
use App\Enums\CommentStatus;
use App\Enums\ConnectionStatus;
use App\Enums\PostStatus;
use App\Models\Comment;
use App\Models\Connection;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Volt;
use Tests\TestCase;

class CommentInteractionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected Post $post;

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
            'display_name' => 'Active Student',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        // Other User
        $this->otherUser = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->otherUser->assignRole('student');
        $this->otherUser->profile()->create([
            'display_name' => 'Other Student',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        // Sample Post
        $this->post = Post::factory()->create([
            'user_id' => $this->user->id,
            'body' => 'Post to comment on.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);
    }

    public function test_active_user_can_create_comment_via_action(): void
    {
        $action = resolve(CreateComment::class);

        $comment = $action->execute($this->user, $this->post, [
            'body' => 'This is a test comment!',
        ]);

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals('This is a test comment!', $comment->body);
        $this->assertEquals(CommentStatus::PUBLISHED, $comment->status);
        $this->assertEquals($this->user->id, $comment->user_id);
        $this->assertEquals($this->post->id, $comment->post_id);
        $this->assertNull($comment->parent_id);
    }

    public function test_active_user_can_create_reply_via_action(): void
    {
        $parentComment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->otherUser->id,
            'body' => 'Parent comment.',
        ]);

        $action = resolve(CreateComment::class);

        $reply = $action->execute($this->user, $this->post, [
            'body' => 'This is a reply comment!',
            'parent_id' => $parentComment->id,
        ]);

        $this->assertInstanceOf(Comment::class, $reply);
        $this->assertEquals('This is a reply comment!', $reply->body);
        $this->assertEquals($parentComment->id, $reply->parent_id);
    }

    public function test_deep_nesting_is_blocked_by_action(): void
    {
        $parentComment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->otherUser->id,
            'body' => 'Parent comment.',
        ]);

        $reply = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'parent_id' => $parentComment->id,
            'body' => 'First level reply.',
        ]);

        $action = resolve(CreateComment::class);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Chỉ hỗ trợ phản hồi bình luận cấp 1. Không cho phép lồng nhau nhiều cấp.');

        $action->execute($this->user, $this->post, [
            'body' => 'Deep nested reply (level 2).',
            'parent_id' => $reply->id,
        ]);
    }

    public function test_user_can_create_comment_via_component(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.post-detail', ['post' => $this->post])
            ->set('commentBody', 'My first comment.')
            ->call('submitComment')
            ->assertHasNoErrors()
            ->assertSet('commentBody', '')
            ->assertSet('feedbackMessage', 'Đăng bình luận thành công.');

        $this->assertDatabaseHas('comments', [
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'body' => 'My first comment.',
            'parent_id' => null,
        ]);
    }

    public function test_user_can_create_reply_via_component(): void
    {
        $parentComment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->otherUser->id,
            'body' => 'Topic starter.',
            'status' => CommentStatus::PUBLISHED,
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.post-detail', ['post' => $this->post])
            ->call('setReplyingTo', $parentComment->id)
            ->set('commentBody', 'My reply here.')
            ->call('submitComment')
            ->assertHasNoErrors()
            ->assertSet('commentBody', '')
            ->assertSet('replyingToCommentId', null);

        $this->assertDatabaseHas('comments', [
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'body' => 'My reply here.',
            'parent_id' => $parentComment->id,
        ]);
    }

    public function test_owner_can_edit_own_comment_via_action(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'body' => 'Original comment.',
            'status' => CommentStatus::PUBLISHED,
        ]);

        $action = resolve(UpdateComment::class);
        $updatedComment = $action->execute($this->user, $comment, [
            'body' => 'Updated comment body.',
        ]);

        $this->assertEquals('Updated comment body.', $updatedComment->body);
        $this->assertEquals(CommentStatus::EDITED, $updatedComment->status);
        $this->assertNotNull($updatedComment->edited_at);
    }

    public function test_owner_can_edit_own_comment_via_component(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'body' => 'Original comment text.',
            'status' => CommentStatus::PUBLISHED,
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.post-detail', ['post' => $this->post])
            ->call('startCommentEdit', $comment->id)
            ->assertSet('editingCommentId', $comment->id)
            ->assertSet('editingCommentBody', 'Original comment text.')
            ->set('editingCommentBody', 'Updated comment text.')
            ->call('saveCommentEdit')
            ->assertHasNoErrors()
            ->assertSet('editingCommentId', null)
            ->assertSet('feedbackMessage', 'Đã cập nhật bình luận thành công.');

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => 'Updated comment text.',
            'status' => CommentStatus::EDITED->value,
        ]);
    }

    public function test_non_owner_cannot_edit_comment(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->otherUser->id,
            'body' => 'Other comment.',
            'status' => CommentStatus::PUBLISHED,
        ]);

        $this->actingAs($this->user);

        $this->expectException(AuthorizationException::class);

        $action = resolve(UpdateComment::class);
        $action->execute($this->user, $comment, [
            'body' => 'Hack try.',
        ]);
    }

    public function test_owner_can_delete_own_comment_via_component(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'body' => 'Delete me.',
            'status' => CommentStatus::PUBLISHED,
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.post-detail', ['post' => $this->post])
            ->call('openCommentDeleteModal', $comment->id)
            ->assertSet('deletingCommentId', $comment->id)
            ->assertSet('showDeleteModal', true)
            ->call('executeCommentDelete')
            ->assertSet('feedbackMessage', 'Đã xóa bình luận thành công.');

        $this->assertSoftDeleted('comments', [
            'id' => $comment->id,
        ]);

        $comment->refresh();
        $this->assertEquals(CommentStatus::DELETED_BY_OWNER, $comment->status);
    }

    public function test_non_owner_cannot_delete_comment(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->otherUser->id,
            'body' => 'Other comment.',
            'status' => CommentStatus::PUBLISHED,
        ]);

        $this->actingAs($this->user);

        $this->expectException(AuthorizationException::class);

        $action = resolve(DeleteComment::class);
        $action->execute($this->user, $comment);
    }

    public function test_comment_policies_authorize_actions_correctly(): void
    {
        $ownComment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        $otherComment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->otherUser->id,
        ]);

        // Main User Permissions on comments
        $this->assertTrue($this->user->can('update', $ownComment));
        $this->assertTrue($this->user->can('delete', $ownComment));
        $this->assertFalse($this->user->can('report', $ownComment));

        $this->assertFalse($this->user->can('update', $otherComment));
        $this->assertFalse($this->user->can('delete', $otherComment));
        $this->assertTrue($this->user->can('report', $otherComment));
    }

    public function test_hidden_comments_do_not_appear_in_post_detail(): void
    {
        $hiddenComment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->otherUser->id,
            'body' => 'This comment is hidden.',
            'status' => CommentStatus::HIDDEN_BY_MODERATION,
        ]);

        $visibleComment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->otherUser->id,
            'body' => 'This comment is visible.',
            'status' => CommentStatus::PUBLISHED,
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.post-detail', ['post' => $this->post])
            ->assertSee('This comment is visible.')
            ->assertDontSee('This comment is hidden.');
    }

    public function test_cannot_comment_on_hidden_post(): void
    {
        $hiddenPost = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'Hidden post body.',
            'status' => PostStatus::HIDDEN_BY_MODERATION,
        ]);

        $this->actingAs($this->user);

        $this->expectException(AuthorizationException::class);

        $action = resolve(CreateComment::class);
        $action->execute($this->user, $hiddenPost, [
            'body' => 'Trying to comment on a hidden post.',
        ]);
    }

    public function test_search_mention_users_returns_matching_results(): void
    {
        $this->actingAs($this->user);

        // Make sure user settings exist
        app(EnsureUserSettingsExistAction::class)->execute($this->otherUser);

        $component = Volt::test('pages.app.post-detail', ['post' => $this->post]);

        // 1. Target user has default ('everyone') preference -> should show up
        $resultsOther = $component->instance()->searchMentionUsers('Other');
        $this->assertCount(1, $resultsOther);
        $this->assertEquals($this->otherUser->name, $resultsOther[0]['name']);

        // 2. Target user has 'nobody' preference -> should NOT show up
        $this->otherUser->profilePrivacySetting()->update(['mentions_preference' => 'nobody']);
        $resultsOtherNobody = $component->instance()->searchMentionUsers('Other');
        $this->assertEmpty($resultsOtherNobody);

        // 3. Target user has 'connections' preference and NOT connected -> should NOT show up
        $this->otherUser->profilePrivacySetting()->update(['mentions_preference' => 'connections']);
        $resultsOtherNotConnected = $component->instance()->searchMentionUsers('Other');
        $this->assertEmpty($resultsOtherNotConnected);

        // 4. Target user has 'connections' preference and ARE connected -> should show up
        Connection::create([
            'user_one_id' => $this->user->id,
            'user_two_id' => $this->otherUser->id,
            'status' => ConnectionStatus::ACTIVE,
        ]);
        $resultsOtherConnected = $component->instance()->searchMentionUsers('Other');
        $this->assertCount(1, $resultsOtherConnected);
        $this->assertEquals($this->otherUser->name, $resultsOtherConnected[0]['name']);
    }
}
