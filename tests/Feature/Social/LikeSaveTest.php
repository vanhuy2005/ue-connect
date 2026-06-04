<?php

namespace Tests\Feature\Social;

use App\Actions\Posts\TogglePostLike;
use App\Actions\Posts\TogglePostSave;
use App\Enums\AccountStatus;
use App\Enums\PostStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class LikeSaveTest extends TestCase
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

        // Active post
        $this->post = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'Liking body.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);
    }

    public function test_user_can_like_and_unlike_visible_post_via_action(): void
    {
        $action = resolve(TogglePostLike::class);

        // Like
        $action->execute($this->user, $this->post);
        $this->assertDatabaseHas('post_likes', [
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        // Unlike
        $action->execute($this->user, $this->post);
        $this->assertDatabaseMissing('post_likes', [
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_cannot_like_hidden_post_via_action(): void
    {
        $hiddenPost = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'status' => PostStatus::HIDDEN_BY_MODERATION,
        ]);

        $action = resolve(TogglePostLike::class);

        $this->expectException(AuthorizationException::class);
        $action->execute($this->user, $hiddenPost);
    }

    public function test_user_can_save_and_unsave_visible_post_via_action(): void
    {
        $action = resolve(TogglePostSave::class);

        // Save
        $action->execute($this->user, $this->post);
        $this->assertDatabaseHas('post_saves', [
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        // Unsave
        $action->execute($this->user, $this->post);
        $this->assertDatabaseMissing('post_saves', [
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_cannot_save_hidden_post_via_action(): void
    {
        $hiddenPost = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'status' => PostStatus::HIDDEN_BY_MODERATION,
        ]);

        $action = resolve(TogglePostSave::class);

        $this->expectException(AuthorizationException::class);
        $action->execute($this->user, $hiddenPost);
    }

    public function test_post_detail_like_button_likes_post_not_comment(): void
    {
        // Create comment with same id as post (by deleting/refreshing or explicit state)
        $comment = Comment::factory()->create([
            'id' => $this->post->id, // Force same ID
            'post_id' => $this->post->id,
            'user_id' => $this->otherUser->id,
            'body' => 'Comment with post ID.',
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.post-detail', ['post' => $this->post])
            ->call('togglePostLike', $this->post->id);

        // Verify the post is liked
        $this->assertDatabaseHas('post_likes', [
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        // Verify that no comment like is created
        $this->assertDatabaseMissing('comment_likes', [
            'comment_id' => $comment->id,
            'user_id' => $this->user->id,
        ]);
    }
}
