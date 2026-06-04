<?php

namespace Tests\Feature\Social;

use App\Actions\Posts\TogglePostSave;
use App\Enums\AccountStatus;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class SavedPostsTest extends TestCase
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
        $this->otherUser->profile()->create([
            'display_name' => 'Other User',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        // Sample post
        $this->post = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'Post to be saved.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);
    }

    public function test_user_can_save_post_and_view_on_saved_posts_page(): void
    {
        $this->actingAs($this->user);

        // Initially no saved posts
        Volt::test('pages.app.saved-posts')
            ->assertSee('Chưa có bài viết đã lưu');

        // Toggle save post
        $action = resolve(TogglePostSave::class);
        $action->execute($this->user, $this->post);

        // Verify it is saved in DB
        $this->assertDatabaseHas('post_saves', [
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        // Verify it appears on saved posts page
        Volt::test('pages.app.saved-posts')
            ->assertDontSee('Chưa có bài viết đã lưu')
            ->assertSee('Post to be saved.');

        // Unsave post
        $action->execute($this->user, $this->post);

        // Verify it disappeared from saved posts page
        Volt::test('pages.app.saved-posts')
            ->assertSee('Chưa có bài viết đã lưu')
            ->assertDontSee('Post to be saved.');
    }

    public function test_hidden_saved_post_does_not_appear_on_saved_posts_page(): void
    {
        $this->actingAs($this->user);

        // Save the post
        $action = resolve(TogglePostSave::class);
        $action->execute($this->user, $this->post);

        // Moderation hides the post
        $this->post->status = PostStatus::HIDDEN_BY_MODERATION;
        $this->post->save();

        // Verify it does not appear on saved posts page even if saved
        Volt::test('pages.app.saved-posts')
            ->assertSee('Chưa có bài viết đã lưu')
            ->assertDontSee('Post to be saved.');
    }
}
