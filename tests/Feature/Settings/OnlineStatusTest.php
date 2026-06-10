<?php

namespace Tests\Feature\Settings;

use App\Actions\Settings\EnsureUserSettingsExistAction;
use App\Enums\AccountStatus;
use App\Enums\ConnectionStatus;
use App\Models\Connection;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class OnlineStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        $this->user = $this->activeUser('Test User');
        app(EnsureUserSettingsExistAction::class)->execute($this->user);
    }

    /**
     * Test user active timestamp updates via middleware.
     */
    public function test_middleware_updates_last_seen_at(): void
    {
        $this->actingAs($this->user);

        $this->assertNull($this->user->last_seen_at);

        // First request should update last_seen_at
        $this->get(route('dashboard'));
        $this->user->refresh();
        $this->assertNotNull($this->user->last_seen_at);

        $firstSeen = $this->user->last_seen_at;

        // Sub-second requests should not update (throttling)
        $this->get(route('dashboard'));
        $this->user->refresh();
        $this->assertTrue($this->user->last_seen_at->eq($firstSeen));
    }

    /**
     * Test User::isOnline() helper method.
     */
    public function test_is_online_method(): void
    {
        $user = $this->user;

        $this->assertFalse($user->isOnline());

        // Less than 5 minutes ago -> online
        $user->last_seen_at = now()->subMinutes(2);
        $user->save();
        $this->assertTrue($user->isOnline());

        // More than 5 minutes ago -> offline
        $user->last_seen_at = now()->subMinutes(6);
        $user->save();
        $this->assertFalse($user->isOnline());
    }

    /**
     * Test canSeeOnlineStatus logic for "nobody" visibility.
     */
    public function test_can_see_online_status_nobody(): void
    {
        $viewer = $this->activeUser('Viewer User');

        $this->user->profilePrivacySetting->update(['online_status_visibility' => 'nobody']);

        $this->assertFalse($this->user->canSeeOnlineStatus($viewer));
    }

    /**
     * Test canSeeOnlineStatus logic for "connections" visibility.
     */
    public function test_can_see_online_status_connections(): void
    {
        $viewer = $this->activeUser('Viewer User');

        $this->user->profilePrivacySetting->update(['online_status_visibility' => 'connections']);

        // Not connected -> cannot see
        $this->assertFalse($this->user->canSeeOnlineStatus($viewer));

        // Create active connection
        Connection::create([
            'user_one_id' => min($this->user->id, $viewer->id),
            'user_two_id' => max($this->user->id, $viewer->id),
            'status' => ConnectionStatus::ACTIVE,
            'connected_at' => now(),
        ]);

        $this->assertTrue($this->user->canSeeOnlineStatus($viewer));
    }

    /**
     * Test canSeeOnlineStatus logic for "mutual_connections" visibility.
     */
    public function test_can_see_online_status_mutual_connections(): void
    {
        $viewer = $this->activeUser('Viewer User');
        $mutualFriend = $this->activeUser('Mutual Friend');

        $this->user->profilePrivacySetting->update(['online_status_visibility' => 'mutual_connections']);

        // Not connected, no mutual friends -> cannot see
        $this->assertFalse($this->user->canSeeOnlineStatus($viewer));

        // Connection with mutual friend
        Connection::create([
            'user_one_id' => min($this->user->id, $mutualFriend->id),
            'user_two_id' => max($this->user->id, $mutualFriend->id),
            'status' => ConnectionStatus::ACTIVE,
            'connected_at' => now(),
        ]);

        Connection::create([
            'user_one_id' => min($viewer->id, $mutualFriend->id),
            'user_two_id' => max($viewer->id, $mutualFriend->id),
            'status' => ConnectionStatus::ACTIVE,
            'connected_at' => now(),
        ]);

        // Shares mutual connection -> can see
        $this->assertTrue($this->user->canSeeOnlineStatus($viewer));
    }

    /**
     * Test profile page renders the green online status indicator dot based on privacy.
     */
    public function test_profile_page_renders_online_indicator_dot_correctly(): void
    {
        $otherUser = $this->activeUser('Other User');
        app(EnsureUserSettingsExistAction::class)->execute($otherUser);

        $otherUser->last_seen_at = now()->subMinutes(1);
        $otherUser->save();

        // 1. Nobody -> should not see dot
        $otherUser->profilePrivacySetting->update(['online_status_visibility' => 'nobody']);

        $this->actingAs($this->user);
        Volt::test('pages.app.profile', ['user' => $otherUser])
            ->assertDontSee('title="Trực tuyến"', false);

        // 2. Connections but not connected -> should not see dot
        $otherUser->profilePrivacySetting->update(['online_status_visibility' => 'connections']);

        Volt::test('pages.app.profile', ['user' => $otherUser])
            ->assertDontSee('title="Trực tuyến"', false);

        // 3. Connections and connected -> should see dot
        Connection::create([
            'user_one_id' => min($this->user->id, $otherUser->id),
            'user_two_id' => max($this->user->id, $otherUser->id),
            'status' => ConnectionStatus::ACTIVE,
            'connected_at' => now(),
        ]);

        Volt::test('pages.app.profile', ['user' => $otherUser])
            ->assertSee('title="Trực tuyến"', false);
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
