<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\CommunityMemberRole;
use App\Enums\CommunityMemberStatus;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class CommunityShowTabStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_switching_tabs_closes_open_modals(): void
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        Profile::create([
            'user_id' => $user->id,
            'display_name' => $user->name,
            'role_type' => 'student',
            'profile_status' => 'complete',
            'profile_completed_at' => now(),
        ]);

        $community = Community::factory()->active()->openJoin()->create();
        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'role' => CommunityMemberRole::Member,
            'status' => CommunityMemberStatus::Active,
            'joined_at' => now(),
        ]);

        $this->actingAs($user);

        Volt::test('pages.app.community-show', ['community' => $community])
            ->set('showResourceModal', true)
            ->call('setActiveTab', 'events')
            ->assertSet('activeTab', 'events')
            ->assertSet('showResourceModal', false)
            ->assertSet('showJoinModal', false)
            ->assertSet('showLeaveModal', false);
    }
}
