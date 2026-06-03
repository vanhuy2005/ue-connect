<?php

namespace Tests\Feature;

use App\Enums\MentorAvailabilityStatus;
use App\Models\MentorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\Feature\Concerns\BuildsMentorFixtures;
use Tests\TestCase;

class MentorDiscoveryTest extends TestCase
{
    use BuildsMentorFixtures;
    use RefreshDatabase;

    public function test_only_available_visible_active_mentors_are_discoverable(): void
    {
        $visible = $this->mentorProfile();
        $this->mentorProfile(null, ['availability_status' => MentorAvailabilityStatus::Paused]);
        $this->mentorProfile(null, ['mentor_visibility' => false]);
        $this->mentorProfile(null, ['is_active' => false]);

        $ids = MentorProfile::discoverable()->pluck('id')->all();

        $this->assertContains($visible->id, $ids);
        $this->assertCount(1, $ids);
    }

    public function test_mentor_discovery_links_to_the_mentor_user_profile(): void
    {
        $student = $this->activeUser();
        $mentor = $this->mentorProfile();

        $this->actingAs($student);

        Volt::test('pages.app.mentors')
            ->assertSee($mentor->user->name)
            ->assertSeeHtml(route('profile.show', $mentor->user));
    }

    public function test_approved_mentor_can_reach_profile_setup_from_discovery(): void
    {
        $mentorProfile = $this->mentorProfile();

        $this->actingAs($mentorProfile->user);

        Volt::test('pages.app.mentors')
            ->assertSee('Cập nhật hồ sơ mentor')
            ->assertSeeHtml(route('mentor.setup'));
    }
}
