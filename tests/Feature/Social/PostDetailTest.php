<?php

namespace Tests\Feature\Social;

use App\Enums\AccountStatus;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PostDetailTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected Post $post;

    protected Comment $comment;

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
        $this->otherUser->profile()->create([
            'display_name' => 'Other User',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        // Sample post
        $this->post = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'Post detail body content.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        // Comment
        $this->comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->otherUser->id,
            'body' => 'Visible comment.',
            'status' => CommentStatus::PUBLISHED,
        ]);
    }

    public function test_post_detail_renders_normally_and_supports_options(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.post-detail', ['post' => $this->post])
            ->assertSee('Post detail body content.')
            ->assertSee('Visible comment.')
            ->assertSeeHtml(route('profile.show', $this->otherUser));
    }

    public function test_post_detail_like_affects_post_like_not_comment_like(): void
    {
        $this->actingAs($this->user);

        // Force-delete default comment to prevent ID conflicts with soft-deletes
        $this->comment->forceDelete();

        // Force comment to have same ID as post for collision check
        $collisionComment = Comment::factory()->create([
            'id' => $this->post->id,
            'post_id' => $this->post->id,
            'user_id' => $this->otherUser->id,
            'body' => 'Collision comment.',
            'status' => CommentStatus::PUBLISHED,
        ]);

        Volt::test('pages.app.post-detail', ['post' => $this->post])
            ->call('togglePostLike', $this->post->id);

        // Verify post liked
        $this->assertDatabaseHas('post_likes', [
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        // Verify comment with same ID was NOT liked
        $this->assertDatabaseMissing('comment_likes', [
            'comment_id' => $collisionComment->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_hidden_comments_do_not_render_normally(): void
    {
        $this->actingAs($this->user);

        $hiddenComment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->otherUser->id,
            'body' => 'Secret comment.',
            'status' => CommentStatus::HIDDEN_BY_MODERATION,
        ]);

        Volt::test('pages.app.post-detail', ['post' => $this->post])
            ->assertSee('Visible comment.')
            ->assertDontSee('Secret comment.');
    }

    public function test_deleted_comment_with_replies_renders_placeholder(): void
    {
        $this->actingAs($this->user);

        // Main comment is deleted but has active reply
        $deletedComment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->otherUser->id,
            'body' => 'Deleted starter.',
            'status' => CommentStatus::DELETED_BY_OWNER,
        ]);

        $activeReply = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'parent_id' => $deletedComment->id,
            'body' => 'Active reply text.',
            'status' => CommentStatus::PUBLISHED,
        ]);

        Volt::test('pages.app.post-detail', ['post' => $this->post])
            ->assertSee('Bình luận này không còn khả dụng.')
            ->assertSee('Active reply text.')
            ->assertDontSee('Deleted starter.');
    }

    public function test_comment_dropdown_menu_permissions(): void
    {
        $this->actingAs($this->user);

        // For own comment, see Edit and Delete.
        $ownComment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'body' => 'My own comment.',
            'status' => CommentStatus::PUBLISHED,
        ]);

        // For other comment, see Report.
        $otherComment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->otherUser->id,
            'body' => 'Someone else comment.',
            'status' => CommentStatus::PUBLISHED,
        ]);

        Volt::test('pages.app.post-detail', ['post' => $this->post])
            ->assertSee('My own comment.')
            ->assertSee('Someone else comment.');
    }
}
