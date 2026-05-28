<?php

namespace Tests\Feature\Social;

use App\Actions\Posts\HidePostFromFeed;
use App\Actions\Posts\TogglePostSave;
use App\Enums\AccountStatus;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PostHideTest extends TestCase
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
        $this->otherUser->profile()->create([
            'display_name' => 'Other User',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        // Sample post
        $this->post = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'Post to be hidden.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);
    }

    public function test_user_can_hide_post_from_feed(): void
    {
        $this->actingAs($this->user);

        // Initially post is visible
        Volt::test('pages.app.home-feed')
            ->assertSee('Post to be hidden.');

        // Hide post via component action
        Volt::test('pages.app.home-feed')
            ->call('hidePost', $this->post->id)
            ->assertSet('feedbackMessage', 'Đã ẩn bài viết khỏi bảng tin của bạn.');

        // Verify hide record exists in DB
        $this->assertDatabaseHas('post_hides', [
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        // Feed should no longer show the hidden post for this user
        Volt::test('pages.app.home-feed')
            ->assertDontSee('Post to be hidden.');
    }

    public function test_hidden_post_still_visible_to_other_users(): void
    {
        // First hide it for $this->user
        $action = resolve(HidePostFromFeed::class);
        $action->execute($this->user, $this->post);

        // acting as other user
        $this->actingAs($this->otherUser);

        // Verify it is still visible to the other user
        Volt::test('pages.app.home-feed')
            ->assertSee('Post to be hidden.');
    }

    public function test_hidden_saved_post_by_user_does_not_appear_on_saved_posts_page(): void
    {
        $this->actingAs($this->user);

        // Save the post
        $saveAction = resolve(TogglePostSave::class);
        $saveAction->execute($this->user, $this->post);

        // Hide the post
        $hideAction = resolve(HidePostFromFeed::class);
        $hideAction->execute($this->user, $this->post);

        // Verify it does not appear on saved posts page
        Volt::test('pages.app.saved-posts')
            ->assertSee('Chưa có bài viết đã lưu')
            ->assertDontSee('Post to be hidden.');
    }
}
