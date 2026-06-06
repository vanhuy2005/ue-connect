<?php

namespace Tests\Feature;

use App\Actions\Follows\FollowUser;
use App\Actions\Follows\UnfollowUser;
use App\Enums\AccountStatus;
use App\Models\Connection;
use App\Models\Greeting;
use App\Models\MentorAccessRequest;
use App\Models\MentorRequest;
use App\Models\User;
use App\Models\UserFollow;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Volt;
use Tests\TestCase;

class UserFollowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        $this->user = $this->activeUser('Follower User');
        $this->otherUser = $this->activeUser('Followed User');
    }

    public function test_user_can_follow_and_unfollow_another_user(): void
    {
        $follow = app(FollowUser::class)->execute($this->user, $this->otherUser);

        $this->assertInstanceOf(UserFollow::class, $follow);
        $this->assertDatabaseHas('user_follows', [
            'follower_id' => $this->user->id,
            'following_id' => $this->otherUser->id,
        ]);

        $deleted = app(UnfollowUser::class)->execute($this->user, $this->otherUser);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('user_follows', [
            'follower_id' => $this->user->id,
            'following_id' => $this->otherUser->id,
        ]);
    }

    public function test_self_follow_is_blocked(): void
    {
        $this->expectException(ValidationException::class);

        app(FollowUser::class)->execute($this->user, $this->user);
    }

    public function test_duplicate_follow_is_blocked_by_validation_and_backed_by_unique_index(): void
    {
        app(FollowUser::class)->execute($this->user, $this->otherUser);

        try {
            app(FollowUser::class)->execute($this->user, $this->otherUser);
            $this->fail('Duplicate follow action was not blocked.');
        } catch (ValidationException) {
            $this->assertSame(1, UserFollow::count());
        }

        $migrationPath = glob(database_path('migrations/*_create_user_follows_table.php'))[0];
        $migration = file_get_contents($migrationPath);

        $this->assertStringContainsString("unique(['follower_id', 'following_id']", $migration);
        $this->assertStringContainsString('user_follows_follower_following_unique', $migration);
    }

    public function test_follow_does_not_mutate_friend_or_mentor_relationships(): void
    {
        app(FollowUser::class)->execute($this->user, $this->otherUser);

        $this->assertSame(0, Connection::count());
        $this->assertSame(0, Greeting::count());
        $this->assertSame(0, MentorRequest::count());
        $this->assertSame(0, MentorAccessRequest::count());
    }

    public function test_follow_routes_return_status_counts(): void
    {
        $csrfToken = 'follow-test-token';

        $this->actingAs($this->user)
            ->withSession(['_token' => $csrfToken])
            ->withHeader('X-CSRF-TOKEN', $csrfToken)
            ->postJson(route('users.follow', $this->otherUser))
            ->assertOk()
            ->assertJson([
                'isFollowing' => true,
                'followersCount' => 1,
                'followingCount' => 0,
            ]);

        $this->actingAs($this->user)
            ->withSession(['_token' => $csrfToken])
            ->withHeader('X-CSRF-TOKEN', $csrfToken)
            ->deleteJson(route('users.unfollow', $this->otherUser))
            ->assertOk()
            ->assertJson([
                'isFollowing' => false,
                'followersCount' => 0,
                'followingCount' => 0,
            ]);
    }

    public function test_follow_route_blocks_self_follow(): void
    {
        $csrfToken = 'follow-test-token';

        $this->actingAs($this->user)
            ->withSession(['_token' => $csrfToken])
            ->withHeader('X-CSRF-TOKEN', $csrfToken)
            ->postJson(route('users.follow', $this->user))
            ->assertUnprocessable();
    }

    public function test_profile_hides_follow_button_on_own_profile_and_shows_it_for_other_profile(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.profile', ['user' => $this->user])
            ->assertDontSee('wire:click="followUser"', false)
            ->assertDontSee('wire:click="unfollowUser"', false);

        Volt::test('pages.app.profile', ['user' => $this->otherUser])
            ->assertSee('wire:click="followUser"', false)
            ->assertSet('isFollowing', false)
            ->assertSet('followersCount', 0)
            ->call('followUser')
            ->assertSet('isFollowing', true)
            ->assertSet('followersCount', 1)
            ->assertSee('wire:click="unfollowUser"', false)
            ->call('unfollowUser')
            ->assertSet('isFollowing', false)
            ->assertSet('followersCount', 0);
    }

    public function test_user_follow_relations_return_aggregate_counts_without_loading_rows(): void
    {
        UserFollow::factory()->create([
            'follower_id' => $this->user->id,
            'following_id' => $this->otherUser->id,
        ]);

        $this->assertTrue($this->user->following()->whereKey($this->otherUser->id)->exists());
        $this->assertTrue($this->otherUser->followers()->whereKey($this->user->id)->exists());
        $this->assertSame(1, UserFollow::where('following_id', $this->otherUser->id)->count());
        $this->assertSame(1, UserFollow::where('follower_id', $this->user->id)->count());
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
            'discoverable' => true,
        ]);

        return $user;
    }
}
