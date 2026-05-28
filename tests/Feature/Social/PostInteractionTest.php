<?php

namespace Tests\Feature\Social;

use App\Actions\Posts\CreatePost;
use App\Actions\Posts\DeletePost;
use App\Actions\Posts\UpdatePost;
use App\Enums\AccountStatus;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PostInteractionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

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
    }

    public function test_active_user_can_create_post_via_action(): void
    {
        $action = resolve(CreatePost::class);

        $post = $action->execute($this->user, [
            'body' => 'Hello HCMUE community!',
            'visibility' => 'verified_users',
        ]);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('Hello HCMUE community!', $post->body);
        $this->assertEquals(PostStatus::PUBLISHED, $post->status);
        $this->assertEquals(PostVisibility::VERIFIED_USERS, $post->visibility);
        $this->assertEquals($this->user->id, $post->user_id);
    }

    public function test_post_creation_requires_valid_data(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->set('body', '')
            ->call('submitPost')
            ->assertHasErrors(['body' => 'required']);

        Volt::test('pages.app.home-feed')
            ->set('body', str_repeat('a', 3001))
            ->call('submitPost')
            ->assertHasErrors(['body' => 'max']);
    }

    public function test_user_can_create_post_via_component(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->set('body', 'This is a test post body.')
            ->set('visibility', 'verified_users')
            ->call('submitPost')
            ->assertHasNoErrors()
            ->assertSet('body', '')
            ->assertSet('feedbackMessage', 'Đăng bài viết thành công.');

        $this->assertDatabaseHas('posts', [
            'user_id' => $this->user->id,
            'body' => 'This is a test post body.',
            'status' => PostStatus::PUBLISHED->value,
        ]);
    }

    public function test_owner_can_edit_own_post_via_action(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'body' => 'Original body.',
            'status' => PostStatus::PUBLISHED,
        ]);

        $action = resolve(UpdatePost::class);
        $updatedPost = $action->execute($this->user, $post, [
            'body' => 'Updated body.',
        ]);

        $this->assertEquals('Updated body.', $updatedPost->body);
        $this->assertEquals(PostStatus::EDITED, $updatedPost->status);
        $this->assertNotNull($updatedPost->edited_at);
    }

    public function test_owner_can_edit_own_post_via_component(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'body' => 'Original body.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->call('startEdit', $post->id)
            ->assertSet('editingPostId', $post->id)
            ->assertSet('editingBody', 'Original body.')
            ->set('editingBody', 'Updated body content.')
            ->call('saveEdit')
            ->assertHasNoErrors()
            ->assertSet('editingPostId', null)
            ->assertSet('feedbackMessage', 'Đã cập nhật bài viết thành công.');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'body' => 'Updated body content.',
            'status' => PostStatus::EDITED->value,
        ]);
    }

    public function test_non_owner_cannot_edit_post(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'Other user post.',
            'status' => PostStatus::PUBLISHED,
        ]);

        $this->actingAs($this->user);

        $this->expectException(AuthorizationException::class);

        $action = resolve(UpdatePost::class);
        $action->execute($this->user, $post, [
            'body' => 'Hack try.',
        ]);
    }

    public function test_owner_can_delete_own_post_via_component(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'body' => 'Post to be deleted.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->call('openDeleteModal', $post->id)
            ->assertSet('deletingPostId', $post->id)
            ->assertSet('showDeleteModal', true)
            ->call('executeDelete')
            ->assertSet('feedbackMessage', 'Đã xóa bài viết thành công.');

        $this->assertSoftDeleted('posts', [
            'id' => $post->id,
        ]);

        $post->refresh();
        $this->assertEquals(PostStatus::DELETED_BY_OWNER, $post->status);
    }

    public function test_non_owner_cannot_delete_post(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'Other user post.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $this->actingAs($this->user);

        $this->expectException(AuthorizationException::class);

        $action = resolve(DeletePost::class);
        $action->execute($this->user, $post);
    }

    public function test_policies_authorize_actions_correctly(): void
    {
        $ownPost = Post::factory()->create([
            'user_id' => $this->user->id,
            'status' => PostStatus::PUBLISHED,
        ]);

        $otherPost = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'status' => PostStatus::PUBLISHED,
        ]);

        // Post Owner Permissions
        $this->assertTrue($this->user->can('view', $ownPost));
        $this->assertTrue($this->user->can('update', $ownPost));
        $this->assertTrue($this->user->can('delete', $ownPost));
        $this->assertFalse($this->user->can('report', $ownPost));

        // Other User Permissions
        $this->assertTrue($this->user->can('view', $otherPost));
        $this->assertFalse($this->user->can('update', $otherPost));
        $this->assertFalse($this->user->can('delete', $otherPost));
        $this->assertTrue($this->user->can('report', $otherPost));
    }

    public function test_hidden_posts_do_not_appear_in_feed(): void
    {
        $hiddenPost = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'This post is hidden.',
            'status' => PostStatus::HIDDEN_BY_MODERATION,
            'published_at' => now(),
        ]);

        $visiblePost = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'This post is visible.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->assertSee('This post is visible.')
            ->assertDontSee('This post is hidden.');
    }
}
