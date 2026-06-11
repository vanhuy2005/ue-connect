<?php

namespace Tests\Feature\Social;

use App\Actions\Posts\CreatePost;
use App\Actions\Posts\DeletePost;
use App\Actions\Posts\UpdatePost;
use App\Enums\AccountStatus;
use App\Enums\ConnectionStatus;
use App\Enums\MessageType;
use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Enums\PostVisibility;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Connection;
use App\Models\Post;
use App\Models\PostRepost;
use App\Models\User;
use App\Models\UserFollow;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
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
            ->set('perPage', 20)
            ->set('body', 'This is a test post body.')
            ->set('visibility', 'verified_users')
            ->call('submitPost')
            ->assertHasNoErrors()
            ->assertSet('body', '')
            ->assertSet('perPage', 5)
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

    public function test_home_feed_load_more_expands_visible_posts_without_page_links(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            Post::factory()->create([
                'user_id' => $this->otherUser->id,
                'body' => "Feed load more post {$i}.",
                'status' => PostStatus::PUBLISHED,
                'published_at' => now()->subMinutes($i),
            ]);
        }

        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->assertSet('perPage', 5)
            ->assertSee('Feed load more post 1.')
            ->assertSee('Feed load more post 5.')
            ->assertDontSee('Feed load more post 6.')
            ->call('loadMore')
            ->assertSet('perPage', 10)
            ->assertSee('Feed load more post 6.')
            ->assertSee('Feed load more post 10.')
            ->assertDontSee('Feed load more post 11.');
    }

    public function test_for_you_feed_prioritizes_friends_following_communities_then_recent_posts(): void
    {
        $friend = $this->activeUser('Friend Author');
        $followed = $this->activeUser('Followed Author');
        $communityAuthor = $this->activeUser('Community Author');
        $recentAuthor = $this->activeUser('Recent Author');
        $community = Community::factory()->active()->create();

        Connection::create([
            'user_one_id' => min($this->user->id, $friend->id),
            'user_two_id' => max($this->user->id, $friend->id),
            'status' => ConnectionStatus::ACTIVE,
            'connected_at' => now(),
        ]);

        UserFollow::factory()->create([
            'follower_id' => $this->user->id,
            'following_id' => $followed->id,
        ]);

        CommunityMember::factory()->active()->create([
            'community_id' => $community->id,
            'user_id' => $this->user->id,
        ]);

        Post::factory()->create([
            'user_id' => $recentAuthor->id,
            'body' => 'Recent fallback post.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        Post::factory()->create([
            'user_id' => $communityAuthor->id,
            'scope_type' => 'community',
            'scope_id' => $community->id,
            'body' => 'Joined community post.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now()->subMinute(),
        ]);

        Post::factory()->create([
            'user_id' => $followed->id,
            'body' => 'Following author post.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now()->subMinutes(2),
        ]);

        Post::factory()->create([
            'user_id' => $friend->id,
            'body' => 'Friend author post.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now()->subMinutes(3),
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->assertSet('activeFeedTab', 'for_you')
            ->assertSeeInOrder([
                'Friend author post.',
                'Following author post.',
                'Joined community post.',
                'Recent fallback post.',
            ]);
    }

    public function test_following_feed_only_shows_posts_from_followed_authors(): void
    {
        $followed = $this->activeUser('Followed Only Author');
        $randomAuthor = $this->activeUser('Random Author');
        $communityAuthor = $this->activeUser('Community Only Author');
        $community = Community::factory()->active()->create();

        UserFollow::factory()->create([
            'follower_id' => $this->user->id,
            'following_id' => $followed->id,
        ]);

        CommunityMember::factory()->active()->create([
            'community_id' => $community->id,
            'user_id' => $this->user->id,
        ]);

        Post::factory()->create([
            'user_id' => $followed->id,
            'body' => 'Following tab visible post.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        Post::factory()->create([
            'user_id' => $randomAuthor->id,
            'body' => 'Following tab random post.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now()->subMinute(),
        ]);

        Post::factory()->create([
            'user_id' => $communityAuthor->id,
            'scope_type' => 'community',
            'scope_id' => $community->id,
            'body' => 'Following tab community-only post.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now()->subMinutes(2),
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->set('perPage', 20)
            ->call('setFeedTab', 'following')
            ->assertSet('activeFeedTab', 'following')
            ->assertSet('perPage', 5)
            ->assertSee('Following tab visible post.')
            ->assertDontSee('Following tab random post.')
            ->assertDontSee('Following tab community-only post.');
    }

    public function test_friend_only_posts_are_hidden_from_non_friends_across_feed_profile_detail_and_share(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'Friend-only private campus update.',
            'visibility' => PostVisibility::CONNECTIONS_ONLY,
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $this->assertFalse($this->user->can('view', $post));
        $this->assertFalse($this->user->can('share', $post));

        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->assertDontSee('Friend-only private campus update.');

        Volt::test('pages.app.profile', ['user' => $this->otherUser])
            ->assertDontSee('Friend-only private campus update.');

        $this->get(route('posts.show', $post))->assertForbidden();

        Connection::create([
            'user_one_id' => min($this->user->id, $this->otherUser->id),
            'user_two_id' => max($this->user->id, $this->otherUser->id),
            'status' => ConnectionStatus::ACTIVE,
            'connected_at' => now(),
        ]);

        $this->assertTrue($this->user->can('view', $post));
        $this->assertTrue($this->user->can('share', $post));

        Volt::test('pages.app.home-feed')
            ->assertSee('Friend-only private campus update.');
    }

    public function test_community_posts_require_membership_and_are_hidden_from_non_members(): void
    {
        $community = Community::factory()->active()->create();
        $member = $this->activeUser('Community Member Viewer');

        CommunityMember::factory()->active()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
        ]);

        $post = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'scope_type' => 'community',
            'scope_id' => $community->id,
            'body' => 'Community-only research note.',
            'visibility' => PostVisibility::COMMUNITY,
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $this->assertFalse($this->user->can('view', $post));
        $this->assertFalse($this->user->can('share', $post));
        $this->assertTrue($member->can('view', $post));
        $this->assertTrue($member->can('share', $post));

        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->assertDontSee('Community-only research note.');

        Volt::test('pages.app.profile', ['user' => $this->otherUser])
            ->assertDontSee('Community-only research note.');

        $this->get(route('posts.show', $post))->assertForbidden();

        $this->actingAs($member);

        Volt::test('pages.app.home-feed')
            ->assertSee('Community-only research note.');

        $this->get(route('posts.show', $post))->assertOk();
    }

    public function test_community_audience_requires_selected_joined_community(): void
    {
        $community = Community::factory()->active()->create();

        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->set('body', 'Community audience without target.')
            ->set('visibility', 'community')
            ->call('submitPost')
            ->assertHasErrors(['selectedCommunityId' => 'required_if']);

        CommunityMember::factory()->active()->create([
            'community_id' => $community->id,
            'user_id' => $this->user->id,
        ]);

        Volt::test('pages.app.home-feed')
            ->set('body', 'Community audience with target.')
            ->set('visibility', 'community')
            ->set('selectedCommunityId', $community->id)
            ->call('submitPost')
            ->assertHasNoErrors()
            ->assertSet('visibility', 'verified_users')
            ->assertSet('selectedCommunityId', null);

        $this->assertDatabaseHas('posts', [
            'user_id' => $this->user->id,
            'body' => 'Community audience with target.',
            'visibility' => PostVisibility::COMMUNITY->value,
            'scope_type' => 'community',
            'scope_id' => $community->id,
        ]);
    }

    public function test_home_feed_can_share_post_to_multiple_recipients(): void
    {
        $recipientOne = $this->activeUser('Recipient One');
        $recipientTwo = $this->activeUser('Recipient Two');

        $this->connectUsers($this->user, $recipientOne);
        $this->connectUsers($this->user, $recipientTwo);

        $post = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'Share this campus post with multiple friends.',
            'visibility' => PostVisibility::VERIFIED_USERS,
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->call('startShare', $post->id)
            ->call('toggleShareRecipient', $recipientOne->id)
            ->call('toggleShareRecipient', $recipientTwo->id)
            ->set('shareOptionalMessage', 'Read this when you can.')
            ->call('executeShare')
            ->assertSet('showShareModal', false)
            ->assertSet('selectedShareUserIds', [])
            ->assertSet('feedbackMessage', 'Đã chia sẻ bài viết qua tin nhắn cho 2 người nhận.');

        $this->assertDatabaseCount('messages', 2);
        $this->assertDatabaseHas('messages', [
            'sender_id' => $this->user->id,
            'body' => 'Read this when you can.',
            'message_type' => MessageType::SHARED_POST->value,
            'shared_post_id' => $post->id,
        ]);
    }

    public function test_home_feed_share_reports_partial_failure_when_some_recipients_cannot_view_post(): void
    {
        $recipientAllowed = $this->activeUser('Allowed Recipient');
        $recipientBlockedByPrivacy = $this->activeUser('Privacy Blocked Recipient');

        $this->connectUsers($this->user, $this->otherUser);
        $this->connectUsers($this->user, $recipientAllowed);
        $this->connectUsers($this->user, $recipientBlockedByPrivacy);
        $this->connectUsers($this->otherUser, $recipientAllowed);

        $post = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'Friend-only share target.',
            'visibility' => PostVisibility::CONNECTIONS_ONLY,
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->call('startShare', $post->id)
            ->call('toggleShareRecipient', $recipientAllowed->id)
            ->call('toggleShareRecipient', $recipientBlockedByPrivacy->id)
            ->call('executeShare')
            ->assertSet('showShareModal', false)
            ->assertSet('feedbackMessage', 'Đã gửi cho 1 người nhận. Không gửi được cho: Privacy Blocked Recipient.');

        $this->assertDatabaseCount('messages', 1);
        $this->assertDatabaseHas('messages', [
            'sender_id' => $this->user->id,
            'message_type' => MessageType::SHARED_POST->value,
            'shared_post_id' => $post->id,
        ]);
    }

    public function test_home_feed_share_recipient_toggle_prevents_duplicate_selection(): void
    {
        $recipient = $this->activeUser('Toggle Recipient');

        $this->connectUsers($this->user, $recipient);

        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->call('toggleShareRecipient', $recipient->id)
            ->assertSet('selectedShareUserIds', [$recipient->id])
            ->call('toggleShareRecipient', $recipient->id)
            ->assertSet('selectedShareUserIds', []);
    }

    public function test_home_feed_can_toggle_repost_and_update_state(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'Repostable academic update.',
            'visibility' => PostVisibility::VERIFIED_USERS,
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->call('toggleRepost', $post->id)
            ->assertSet('feedbackMessage', 'Đã đăng lại bài viết.');

        $this->assertDatabaseHas('post_reposts', [
            'post_id' => $post->id,
            'user_id' => $this->user->id,
        ]);

        Volt::test('pages.app.home-feed')
            ->assertSee('Đăng lại: 1')
            ->call('toggleRepost', $post->id)
            ->assertSet('feedbackMessage', 'Đã hủy đăng lại bài viết.');

        $this->assertDatabaseMissing('post_reposts', [
            'post_id' => $post->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_home_feed_shows_repost_event_label(): void
    {
        $reposter = $this->activeUser('Campus Reposter');
        $post = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'Original post shown through repost.',
            'visibility' => PostVisibility::VERIFIED_USERS,
            'status' => PostStatus::PUBLISHED,
            'published_at' => now()->subHour(),
        ]);

        PostRepost::create([
            'post_id' => $post->id,
            'user_id' => $reposter->id,
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->assertSee('Campus Reposter đã đăng lại')
            ->assertSee('Original post shown through repost.');
    }

    public function test_user_cannot_repost_post_they_cannot_view(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'Friend-only repost blocked.',
            'visibility' => PostVisibility::CONNECTIONS_ONLY,
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->call('toggleRepost', $post->id)
            ->assertSet('feedbackMessage', 'Bạn không có quyền xem bài viết này.');

        $this->assertDatabaseMissing('post_reposts', [
            'post_id' => $post->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_profile_reposts_tab_only_shows_visible_reposts(): void
    {
        $visiblePost = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'Visible repost on profile.',
            'visibility' => PostVisibility::VERIFIED_USERS,
            'status' => PostStatus::PUBLISHED,
            'published_at' => now()->subMinutes(5),
        ]);
        $hiddenPost = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'Hidden repost on profile.',
            'visibility' => PostVisibility::CONNECTIONS_ONLY,
            'status' => PostStatus::PUBLISHED,
            'published_at' => now()->subMinutes(4),
        ]);

        PostRepost::create([
            'post_id' => $visiblePost->id,
            'user_id' => $this->otherUser->id,
        ]);
        PostRepost::create([
            'post_id' => $hiddenPost->id,
            'user_id' => $this->otherUser->id,
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.profile', ['user' => $this->otherUser])
            ->set('activeTab', 'reposts')
            ->assertSee('Đăng lại')
            ->assertSee('Other Student đã đăng lại')
            ->assertSee('Visible repost on profile.')
            ->assertDontSee('Hidden repost on profile.');
    }

    public function test_student_cannot_create_non_standard_posts_via_action(): void
    {
        $action = resolve(CreatePost::class);

        $this->expectException(ValidationException::class);

        $action->execute($this->user, [
            'body' => 'Checking student restriction',
            'visibility' => 'verified_users',
            'post_type' => PostType::EXPERIENCE->value,
        ]);
    }

    public function test_alumni_and_teacher_can_create_non_standard_posts_via_action(): void
    {
        // 1. Alumni
        $alumni = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $alumni->assignRole('alumni');
        $alumni->profile()->create([
            'display_name' => 'Alumni User',
            'role_type' => 'alumni',
            'profile_status' => 'complete',
        ]);

        $action = resolve(CreatePost::class);
        $post = $action->execute($alumni, [
            'body' => 'Alumni sharing experience',
            'visibility' => 'verified_users',
            'post_type' => PostType::EXPERIENCE->value,
        ]);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals(PostType::EXPERIENCE, $post->post_type);

        // 2. Teacher
        $teacher = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $teacher->assignRole('teacher');
        $teacher->profile()->create([
            'display_name' => 'Teacher User',
            'role_type' => 'teacher',
            'profile_status' => 'complete',
        ]);

        $post2 = $action->execute($teacher, [
            'body' => 'Teacher sharing career insight',
            'visibility' => 'verified_users',
            'post_type' => PostType::CAREER_INSIGHT->value,
        ]);

        $this->assertInstanceOf(Post::class, $post2);
        $this->assertEquals(PostType::CAREER_INSIGHT, $post2->post_type);
    }

    public function test_student_cannot_create_non_standard_posts_via_component(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->set('body', 'Student attempting to post experience')
            ->set('postType', PostType::EXPERIENCE->value)
            ->call('submitPost')
            ->assertHasErrors(['post_type']);
    }

    public function test_alumni_can_create_non_standard_posts_via_component(): void
    {
        $alumni = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $alumni->assignRole('alumni');
        $alumni->profile()->create([
            'display_name' => 'Alumni User',
            'role_type' => 'alumni',
            'profile_status' => 'complete',
        ]);

        $this->actingAs($alumni);

        Volt::test('pages.app.home-feed')
            ->set('body', 'Alumni sharing experience via component')
            ->set('postType', PostType::EXPERIENCE->value)
            ->call('submitPost')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', [
            'user_id' => $alumni->id,
            'body' => 'Alumni sharing experience via component',
            'post_type' => PostType::EXPERIENCE->value,
        ]);
    }

    public function test_home_feed_can_filter_posts_by_type(): void
    {
        $alumni = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $alumni->assignRole('alumni');
        $alumni->profile()->create([
            'display_name' => 'Alumni User',
            'role_type' => 'alumni',
            'profile_status' => 'complete',
        ]);

        Post::factory()->create([
            'user_id' => $alumni->id,
            'body' => 'Experience post to filter.',
            'post_type' => PostType::EXPERIENCE->value,
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        Post::factory()->create([
            'user_id' => $alumni->id,
            'body' => 'Career Insight post to filter.',
            'post_type' => PostType::CAREER_INSIGHT->value,
            'status' => PostStatus::PUBLISHED,
            'published_at' => now()->subMinute(),
        ]);

        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->assertSee('Experience post to filter.')
            ->assertSee('Career Insight post to filter.')
            ->call('setTypeFilter', 'experience')
            ->assertSet('activeTypeFilter', 'experience')
            ->assertSee('Experience post to filter.')
            ->assertDontSee('Career Insight post to filter.')
            ->call('setTypeFilter', 'career_insight')
            ->assertSet('activeTypeFilter', 'career_insight')
            ->assertSee('Career Insight post to filter.')
            ->assertDontSee('Experience post to filter.')
            ->call('setTypeFilter', 'career_insight')
            ->assertSet('activeTypeFilter', 'all')
            ->assertSee('Experience post to filter.')
            ->assertSee('Career Insight post to filter.');
    }

    public function test_home_feed_tabs_and_filters_are_mutually_exclusive(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->assertSet('activeFeedTab', 'for_you')
            ->assertSet('activeTypeFilter', 'all')
            ->call('setTypeFilter', 'experience')
            ->assertSet('activeTypeFilter', 'experience')
            ->call('setFeedTab', 'following')
            ->assertSet('activeFeedTab', 'following')
            ->assertSet('activeTypeFilter', 'all')
            ->call('setTypeFilter', 'career_insight')
            ->assertSet('activeTypeFilter', 'career_insight')
            ->call('setFeedTab', 'for_you')
            ->assertSet('activeFeedTab', 'for_you')
            ->assertSet('activeTypeFilter', 'all');
    }

    private function activeUser(string $name): User
    {
        $user = User::factory()->create([
            'name' => $name,
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $user->assignRole('student');
        $user->profile()->create([
            'display_name' => $name,
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        return $user;
    }

    private function connectUsers(User $userA, User $userB): void
    {
        Connection::create([
            'user_one_id' => min($userA->id, $userB->id),
            'user_two_id' => max($userA->id, $userB->id),
            'status' => ConnectionStatus::ACTIVE,
            'connected_at' => now(),
        ]);
    }
}
