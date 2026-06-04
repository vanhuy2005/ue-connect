<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\Feature\Concerns\BuildsCommunityFixtures;
use Tests\TestCase;

class CommunityListTest extends TestCase
{
    use BuildsCommunityFixtures;
    use RefreshDatabase;

    public function test_verified_user_can_view_communities_list_page(): void
    {
        $user = $this->createActiveUser();

        $response = $this->actingAs($user)->get(route('community.index'));

        $response->assertStatus(200);
        $response->assertSeeLivewire('pages.app.communities');
    }

    public function test_unverified_user_cannot_view_communities_list_page(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('community.index'));

        $response->assertRedirect();
    }

    public function test_communities_list_shows_only_discoverable_and_active_communities(): void
    {
        $user = $this->createActiveUser();

        Community::factory()->active()->create([
            'name' => 'Active Public Club',
            'visibility' => 'public',
        ]);

        Community::factory()->suspended()->create([
            'name' => 'Suspended Club',
        ]);

        Community::factory()->active()->create([
            'name' => 'Hidden Club',
            'visibility' => 'hidden',
        ]);

        $component = Volt::actingAs($user)->test('pages.app.communities');

        $component->assertSee('Active Public Club')
            ->assertDontSee('Suspended Club')
            ->assertDontSee('Hidden Club');
    }

    public function test_owner_can_access_hidden_community_from_managed_section(): void
    {
        $owner = $this->createActiveUser();
        $community = Community::factory()->active()->forOwner($owner)->create([
            'name' => 'Hidden Owner Club',
            'visibility' => 'hidden',
        ]);

        Volt::actingAs($owner)
            ->test('pages.app.communities')
            ->assertSee('Cộng đồng bạn quản lý')
            ->assertSee('Hidden Owner Club')
            ->assertSee('Ẩn')
            ->assertSee('Chủ sở hữu');

        $response = $this->actingAs($owner)->get(route('community.show', $community));

        $response->assertOk();
    }

    public function test_user_can_filter_communities_by_type_and_search_query(): void
    {
        $user = $this->createActiveUser();

        Community::factory()->active()->club()->create(['name' => 'Chess Club']);
        Community::factory()->active()->academic()->create(['name' => 'Math Group']);

        $component = Volt::actingAs($user)->test('pages.app.communities');

        // Initial view should see both
        $component->assertSee('Chess Club')
            ->assertSee('Math Group');

        // Filter by type 'club'
        $component->set('type', 'club')
            ->assertSee('Chess Club')
            ->assertDontSee('Math Group');

        // Clear type and search 'Math'
        $component->set('type', '')
            ->set('search', 'Math')
            ->assertSee('Math Group')
            ->assertDontSee('Chess Club');
    }
}
