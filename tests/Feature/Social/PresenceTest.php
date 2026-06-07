<?php

namespace Tests\Feature\Social;

use App\Enums\AccountStatus;
use App\Enums\ConnectionStatus;
use App\Models\Connection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_send_heartbeat_which_updates_last_seen(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
            'show_activity_status' => true,
            'last_seen_at' => null,
        ]);

        $response = $this->actingAs($user)->postJson(route('presence.heartbeat'));

        $response->assertOk()
            ->assertJson(['status' => 'ok']);

        $user->refresh();
        $this->assertNotNull($user->last_seen_at);
        $this->assertTrue($user->isOnline());
    }

    public function test_heartbeat_is_throttled_in_cache(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
            'show_activity_status' => true,
            'last_seen_at' => now()->subMinutes(10),
        ]);

        // First heartbeat updates DB
        $response1 = $this->actingAs($user)->postJson(route('presence.heartbeat'));
        $response1->assertOk();
        $user->refresh();

        // Set DB back manually to see if it updates again (which it shouldn't if throttled)
        $user->update(['last_seen_at' => now()->subMinutes(10)]);

        // Second heartbeat does NOT update DB due to 2-minute cache throttle
        $response2 = $this->actingAs($user)->postJson(route('presence.heartbeat'));
        $response2->assertOk();
        $user->refresh();
        $this->assertEquals($user->last_seen_at->toDateTimeString(), now()->subMinutes(10)->toDateTimeString());
    }

    public function test_visibility_policy_allows_friends_to_see_presence(): void
    {
        $userA = User::factory()->create(['show_activity_status' => true, 'last_seen_at' => now()]);
        $userB = User::factory()->create(['show_activity_status' => true]);

        // Not friends initially
        $this->assertFalse($userB->canSeePresenceOf($userA));

        // Create active connection
        Connection::create([
            'user_one_id' => min($userA->id, $userB->id),
            'user_two_id' => max($userA->id, $userB->id),
            'status' => ConnectionStatus::ACTIVE,
        ]);

        $this->assertTrue($userB->canSeePresenceOf($userA));
    }

    public function test_visibility_policy_respects_privacy_toggle(): void
    {
        $userA = User::factory()->create(['show_activity_status' => false, 'last_seen_at' => now()]);
        $userB = User::factory()->create(['show_activity_status' => true]);

        Connection::create([
            'user_one_id' => min($userA->id, $userB->id),
            'user_two_id' => max($userA->id, $userB->id),
            'status' => ConnectionStatus::ACTIVE,
        ]);

        // Since userA has disabled activity status, userB cannot see it
        $this->assertFalse($userB->canSeePresenceOf($userA));

        // And userA cannot see userB's status either (reciprocity!)
        $this->assertFalse($userA->canSeePresenceOf($userB));
    }
}
