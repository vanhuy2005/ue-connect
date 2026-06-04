<?php

namespace Tests\Feature;

use App\Enums\CommunityEventRsvpStatus;
use App\Enums\CommunityEventStatus;
use App\Models\Community;
use App\Models\CommunityEvent;
use App\Models\CommunityEventRsvp;
use App\Models\CommunityMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\Feature\Concerns\BuildsCommunityFixtures;
use Tests\TestCase;

class CommunityEventTest extends TestCase
{
    use BuildsCommunityFixtures;
    use RefreshDatabase;

    public function test_active_member_can_view_upcoming_events(): void
    {
        $user = $this->createActiveUser();
        $community = Community::factory()->active()->create();
        CommunityMember::factory()->active()->for($community)->for($user)->create();

        $event = CommunityEvent::factory()
            ->published()
            ->upcoming()
            ->create([
                'community_id' => $community->id,
                'title' => 'Web Dev Meetup',
            ]);

        $component = Volt::actingAs($user)
            ->test('pages.app.community-show', ['community' => $community]);

        $component->set('activeTab', 'events')
            ->assertSee('Web Dev Meetup')
            ->assertSee('0')
            ->assertSee('tham gia');
    }

    public function test_owner_can_create_published_event_from_events_tab(): void
    {
        $owner = $this->createActiveUser();
        $community = Community::factory()->active()->forOwner($owner)->create();

        Volt::actingAs($owner)
            ->test('pages.app.community-show', ['community' => $community])
            ->set('activeTab', 'events')
            ->call('openEventModal')
            ->set('eventTitle', 'Workshop Laravel')
            ->set('eventType', 'online')
            ->set('eventStatus', 'published')
            ->set('eventStartsAt', now()->addDays(2)->format('Y-m-d\TH:i'))
            ->set('eventOnlineLink', 'https://meet.example.com/laravel')
            ->call('submitEvent')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('community_events', [
            'community_id' => $community->id,
            'created_by' => $owner->id,
            'title' => 'Workshop Laravel',
            'event_type' => 'online',
            'status' => CommunityEventStatus::Published->value,
            'online_link' => 'https://meet.example.com/laravel',
        ]);
    }

    public function test_active_member_can_rsvp_going(): void
    {
        $user = $this->createActiveUser();
        $community = Community::factory()->active()->create();
        CommunityMember::factory()->active()->for($community)->for($user)->create();

        $event = CommunityEvent::factory()
            ->published()
            ->upcoming()
            ->create(['community_id' => $community->id]);

        $component = Volt::actingAs($user)
            ->test('pages.app.community-show', ['community' => $community]);

        $component->call('rsvpEvent', $event->id, 'going');

        $this->assertDatabaseHas('community_event_rsvps', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => CommunityEventRsvpStatus::Going->value,
        ]);

        $this->assertEquals(1, $event->fresh()->going_count);
    }

    public function test_active_member_can_change_rsvp_status(): void
    {
        $user = $this->createActiveUser();
        $community = Community::factory()->active()->create();
        CommunityMember::factory()->active()->for($community)->for($user)->create();

        $event = CommunityEvent::factory()
            ->published()
            ->upcoming()
            ->create(['community_id' => $community->id, 'going_count' => 1]);

        // Pre-create going RSVP
        CommunityEventRsvp::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'going',
        ]);

        $component = Volt::actingAs($user)
            ->test('pages.app.community-show', ['community' => $community]);

        // Change to interested
        $component->call('rsvpEvent', $event->id, 'interested');

        $this->assertDatabaseHas('community_event_rsvps', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'interested',
        ]);

        $event = $event->fresh();
        $this->assertEquals(0, $event->going_count);
        $this->assertEquals(1, $event->interested_count);
    }

    public function test_non_member_cannot_rsvp(): void
    {
        $user = $this->createActiveUser();
        $community = Community::factory()->active()->create();
        // User is not a member

        $event = CommunityEvent::factory()
            ->published()
            ->upcoming()
            ->create(['community_id' => $community->id]);

        $component = Volt::actingAs($user)
            ->test('pages.app.community-show', ['community' => $community]);

        $component->call('rsvpEvent', $event->id, 'going')
            ->assertDispatched('notify', type: 'error');

        $this->assertDatabaseMissing('community_event_rsvps', [
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);
    }
}
