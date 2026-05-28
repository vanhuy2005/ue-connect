<?php

namespace Tests\Feature\Social;

use App\Actions\Comments\CreateComment;
use App\Actions\Comments\DeleteComment;
use App\Enums\AccountStatus;
use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
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
        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);

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
            ->call('deleteComment', $comment->id)
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

    public function test_user_can_toggle_like_on_comment(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->otherUser->id,
            'body' => 'Random comment.',
        ]);

        $this->actingAs($this->user);

        // Toggle like on comment
        Volt::test('pages.app.post-detail', ['post' => $this->post])
            ->call('toggleCommentLike', $comment->id);

        $this->assertDatabaseHas('comment_likes', [
            'comment_id' => $comment->id,
            'user_id' => $this->user->id,
        ]);

        // Toggle again to unlike
        Volt::test('pages.app.post-detail', ['post' => $this->post])
            ->call('toggleCommentLike', $comment->id);

        $this->assertDatabaseMissing('comment_likes', [
            'comment_id' => $comment->id,
            'user_id' => $this->user->id,
        ]);
    }
}
