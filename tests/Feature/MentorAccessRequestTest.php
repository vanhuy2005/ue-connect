<?php

namespace Tests\Feature;

use App\Actions\Mentor\RequestMentorAccessAction;
use App\Enums\MentorAccessStatus;
use App\Models\MentorAccessRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsMentorFixtures;
use Tests\TestCase;

class MentorAccessRequestTest extends TestCase
{
    use BuildsMentorFixtures;
    use RefreshDatabase;

    public function test_alumni_can_apply_for_mentor_access(): void
    {
        $user = $this->activeUser('alumni');

        $request = app(RequestMentorAccessAction::class)->execute($user, [
            'requested_role_context' => 'alumni',
            'motivation' => 'I want to help students prepare for internships.',
            'experience_summary' => 'Five years in student career support.',
            'expertise_topics' => ['career'],
        ]);

        $this->assertSame(MentorAccessStatus::Submitted, $request->status);
        $this->assertDatabaseHas('mentor_access_requests', [
            'user_id' => $user->id,
            'status' => MentorAccessStatus::Submitted->value,
        ]);
    }

    public function test_student_cannot_self_declare_mentor_by_default(): void
    {
        config(['mentor.enable_student_exceptional_mentors' => false]);

        $student = $this->activeUser('student');

        $this->expectException(AuthorizationException::class);

        app(RequestMentorAccessAction::class)->execute($student, [
            'requested_role_context' => 'exceptional_student',
            'motivation' => 'I want to mentor classmates.',
        ]);
    }

    public function test_student_can_submit_exceptional_mentor_application_when_enabled(): void
    {
        config(['mentor.enable_student_exceptional_mentors' => true]);

        $student = $this->activeUser('student');

        $response = $this->actingAs($student)->post(route('mentor.apply.store'), [
            'requested_role_context' => 'exceptional_student',
            'motivation' => 'I want to mentor classmates with study planning and internship preparation.',
            'experience_summary' => 'I have supported first-year students in study groups.',
            'expertise_topics' => ['study planning'],
            'career_paths' => ['education technology'],
        ]);

        $response->assertRedirect(route('mentor.dashboard'));
        $response->assertSessionHas('status', 'Yêu cầu trở thành mentor đã được gửi.');

        $this->assertDatabaseHas('mentor_access_requests', [
            'user_id' => $student->id,
            'requested_role_context' => 'exceptional_student',
            'status' => MentorAccessStatus::Submitted->value,
        ]);
    }

    public function test_application_role_context_must_match_user_profile(): void
    {
        $alumni = $this->activeUser('alumni');

        $response = $this->actingAs($alumni)->from(route('mentor.apply'))->post(route('mentor.apply.store'), [
            'requested_role_context' => 'advisor',
            'motivation' => 'I want to help students prepare for internships and early career choices.',
        ]);

        $response->assertRedirect(route('mentor.apply'));
        $response->assertSessionHasErrors('requested_role_context');

        $this->assertDatabaseMissing('mentor_access_requests', [
            'user_id' => $alumni->id,
        ]);
    }

    public function test_duplicate_application_returns_form_error_instead_of_server_error(): void
    {
        $user = $this->activeUser('alumni');

        MentorAccessRequest::create([
            'user_id' => $user->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::Submitted,
            'motivation' => 'Existing request.',
        ]);

        $response = $this->actingAs($user)->from(route('mentor.apply'))->post(route('mentor.apply.store'), [
            'requested_role_context' => 'alumni',
            'motivation' => 'I want to help students prepare for internships and early career choices.',
        ]);

        $response->assertRedirect(route('mentor.apply'));
        $response->assertSessionHasErrors('requested_role_context');
    }

    public function test_approved_applicant_sees_profile_setup_cta_on_apply_page(): void
    {
        $user = $this->activeUser('alumni');
        $this->mentorProfile($user);

        MentorAccessRequest::create([
            'user_id' => $user->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::Approved,
            'motivation' => 'Approved request.',
        ]);

        $this->actingAs($user)
            ->get(route('mentor.apply'))
            ->assertOk()
            ->assertSee('Thiết lập hồ sơ mentor')
            ->assertSee(route('mentor.setup'));
    }

    public function test_duplicate_pending_access_request_is_blocked(): void
    {
        $user = $this->activeUser('alumni');

        MentorAccessRequest::create([
            'user_id' => $user->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::Submitted,
            'motivation' => 'Existing request.',
        ]);

        $this->expectException(\Exception::class);

        app(RequestMentorAccessAction::class)->execute($user, [
            'requested_role_context' => 'alumni',
            'motivation' => 'Second request should not be accepted.',
        ]);
    }
}
