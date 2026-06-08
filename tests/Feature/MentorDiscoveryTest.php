<?php

namespace Tests\Feature;

use App\Enums\MentorAvailabilityStatus;
use App\Models\MentorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Volt\Volt;
use Tests\Feature\Concerns\BuildsMentorFixtures;
use Tests\TestCase;

class MentorDiscoveryTest extends TestCase
{
    use BuildsMentorFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        MentorProfile::flushTopicsCache();
    }

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

    public function test_filter_by_expertise_topic_returns_only_matching_mentors(): void
    {
        $student = $this->activeUser();
        $mentorLaravel = $this->mentorProfile(null, [
            'expertise_topics' => ['Laravel', 'PHP'],
            'help_topics' => ['Backend', 'API'],
        ]);
        $this->mentorProfile(null, [
            'expertise_topics' => ['UI/UX', 'Figma'],
            'help_topics' => ['Design', 'Prototype'],
        ]);

        $this->actingAs($student);

        Volt::test('pages.app.mentors')
            ->set('selectedTopics', ['Laravel'])
            ->assertSee($mentorLaravel->user->name)
            ->assertSee('Laravel');
    }

    public function test_filter_by_expertise_topic_excludes_non_matching(): void
    {
        $student = $this->activeUser();
        $this->mentorProfile(null, [
            'expertise_topics' => ['Laravel', 'PHP'],
        ]);
        $mentorUiUx = $this->mentorProfile(null, [
            'expertise_topics' => ['UI/UX', 'Figma'],
        ]);

        $this->actingAs($student);

        $view = Volt::test('pages.app.mentors')
            ->set('selectedTopics', ['UI/UX']);

        $view->assertSee($mentorUiUx->user->name);
    }

    public function test_search_by_expertise_topic_returns_matching_mentors(): void
    {
        $student = $this->activeUser();
        $mentor = $this->mentorProfile(null, [
            'expertise_topics' => ['Machine Learning', 'Data Science'],
        ]);

        $this->actingAs($student);

        Volt::test('pages.app.mentors')
            ->set('search', 'Machine Learning')
            ->assertSee($mentor->user->name);
    }

    public function test_availability_filter_shows_only_available_mentors(): void
    {
        $student = $this->activeUser();
        $available = $this->mentorProfile();
        $paused = $this->mentorProfile(null, [
            'availability_status' => MentorAvailabilityStatus::Paused,
        ]);

        $this->actingAs($student);

        Volt::test('pages.app.mentors')
            ->set('availabilityFilter', 'available')
            ->assertSee($available->user->name)
            ->assertDontSee($paused->user->name);
    }

    public function test_empty_search_shows_improved_empty_state(): void
    {
        $student = $this->activeUser();
        $this->mentorProfile();

        $this->actingAs($student);

        Volt::test('pages.app.mentors')
            ->set('search', 'xyznonexistent')
            ->assertSee('Không tìm thấy mentor phù hợp')
            ->assertSee('Thử thay đổi bộ lọc');
    }

    public function test_clear_all_filters_resets_search_and_filters(): void
    {
        $student = $this->activeUser();
        $mentor = $this->mentorProfile();

        $this->actingAs($student);

        $component = Volt::test('pages.app.mentors')
            ->set('search', 'something')
            ->set('selectedTopics', ['career'])
            ->set('availabilityFilter', 'available');

        $component->
            call('clearFilters');

        $component->assertSee($mentor->user->name);
    }

    public function test_select_topic_sets_and_clears_filter(): void
    {
        $student = $this->activeUser();
        $this->mentorProfile();

        $this->actingAs($student);

        $component = Volt::test('pages.app.mentors');

        $component->call('selectTopic', 'career');
        $this->assertEquals(['career'], $component->selectedTopics);

        $component->call('selectTopic', 'career');
        $this->assertSame([], $component->selectedTopics);
    }

    public function test_get_all_topics_returns_unique_sorted_list(): void
    {
        Cache::forget('mentor:all-topics');
        $this->mentorProfile(null, [
            'expertise_topics' => ['PHP', 'Laravel'],
            'help_topics' => ['Backend'],
        ]);
        $this->mentorProfile(null, [
            'expertise_topics' => ['PHP', 'React'],
            'help_topics' => ['Laravel'],
        ]);

        $topics = MentorProfile::getAllTopics();

        $this->assertContains('PHP', $topics);
        $this->assertContains('Laravel', $topics);
        $this->assertContains('Backend', $topics);
        $this->assertContains('React', $topics);
        $this->assertCount(4, $topics);
    }

    public function test_discoverable_with_filter_by_expertise_scope(): void
    {
        $mentor = $this->mentorProfile(null, [
            'expertise_topics' => ['Laravel', 'PHP'],
        ]);
        $this->mentorProfile(null, [
            'expertise_topics' => ['UI/UX', 'Figma'],
        ]);

        $ids = MentorProfile::discoverable()
            ->filterByExpertise('Laravel')
            ->pluck('id')
            ->all();

        $this->assertCount(1, $ids);
        $this->assertContains($mentor->id, $ids);
    }

    public function test_search_fulltext_finds_by_help_topic(): void
    {
        $student = $this->activeUser();
        $mentor = $this->mentorProfile(null, [
            'help_topics' => ['internship', 'resume'],
        ]);

        $this->actingAs($student);

        Volt::test('pages.app.mentors')
            ->set('search', 'internship')
            ->assertSee($mentor->user->name);
    }
}
