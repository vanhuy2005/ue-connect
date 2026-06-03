<?php

namespace Tests\Feature;

use App\Actions\Mentor\ToggleMentorAvailabilityAction;
use App\Actions\Mentor\UpdateMentorProfileAction;
use App\Enums\MentorAvailabilityStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsMentorFixtures;
use Tests\TestCase;

class MentorProfileSetupTest extends TestCase
{
    use BuildsMentorFixtures;
    use RefreshDatabase;

    public function test_user_without_approved_mentor_profile_sees_guided_setup_state(): void
    {
        $user = $this->activeUser('alumni');

        $this->actingAs($user)
            ->get(route('mentor.setup'))
            ->assertOk()
            ->assertSee('Bạn chưa thể thiết lập hồ sơ mentor')
            ->assertSee('Đăng ký làm mentor');
    }

    public function test_inactive_mentor_profile_setup_page_renders_without_forbidden_response(): void
    {
        $mentor = $this->activeUser('alumni');
        $this->mentorProfile($mentor, [
            'is_active' => false,
            'mentor_visibility' => false,
        ]);

        $this->actingAs($mentor)
            ->get(route('mentor.setup'))
            ->assertOk()
            ->assertSee('Hồ sơ chưa public')
            ->assertSee('Lưu hồ sơ mentor');
    }

    public function test_approved_mentor_can_update_profile(): void
    {
        $mentor = $this->activeUser('alumni');
        $profile = $this->mentorProfile($mentor);

        app(UpdateMentorProfileAction::class)->execute($mentor, $profile, [
            'headline' => 'Internship mentor',
            'help_topics' => ['cv', 'interview'],
        ]);

        $this->assertSame('Internship mentor', $profile->fresh()->headline);
        $this->assertSame(['cv', 'interview'], $profile->fresh()->help_topics);
    }

    public function test_mentor_update_policy_accepts_database_ids_cast_as_strings(): void
    {
        $mentor = $this->activeUser('alumni');
        $profile = $this->mentorProfile($mentor);

        $profile->setRawAttributes(array_merge($profile->getAttributes(), [
            'user_id' => (string) $mentor->id,
        ]), true);

        app(UpdateMentorProfileAction::class)->execute($mentor, $profile, [
            'headline' => 'Mentor xử lý dữ liệu và định hướng học tập',
        ]);

        $this->assertSame('Mentor xử lý dữ liệu và định hướng học tập', $profile->fresh()->headline);
    }

    public function test_availability_toggle_works(): void
    {
        $mentor = $this->activeUser('alumni');
        $profile = $this->mentorProfile($mentor);

        app(ToggleMentorAvailabilityAction::class)->execute($mentor, $profile, MentorAvailabilityStatus::Paused);

        $this->assertSame(MentorAvailabilityStatus::Paused, $profile->fresh()->availability_status);
    }

    public function test_inactive_mentor_profile_owner_can_save_setup_without_unauthorized_page(): void
    {
        $mentor = $this->activeUser('alumni');
        $profile = $this->mentorProfile($mentor, [
            'is_active' => false,
            'mentor_visibility' => false,
        ]);
        $this->attachAvatar($mentor);

        $this->actingAs($mentor)
            ->from(route('mentor.setup'))
            ->patch(route('mentor.setup.update'), [
                'headline' => 'Mentor hỗ trợ CV và định hướng thực tập',
                'bio' => 'Tôi hỗ trợ sinh viên chuẩn bị CV, portfolio và định hướng thực tập bằng kinh nghiệm thực tế.',
                'expertise_topics_text' => 'CV Review, Portfolio',
                'help_topics_text' => 'Review CV, Định hướng thực tập',
                'career_paths_text' => 'Software Engineering',
                'skills_text' => 'Laravel, React',
                'preferred_request_types' => ['cv_review', 'career_advice'],
                'availability_status' => 'paused',
                'mentor_visibility' => false,
                'max_pending_requests' => 3,
                'response_expectation_text' => 'Phản hồi trong 2-3 ngày làm việc.',
                'office_hours_text' => 'Buổi tối trong tuần.',
            ])
            ->assertRedirect(route('mentor.setup'))
            ->assertSessionHas('status', 'Hồ sơ mentor đã được cập nhật.');

        $profile->refresh();

        $this->assertSame('Mentor hỗ trợ CV và định hướng thực tập', $profile->headline);
        $this->assertFalse($profile->mentor_visibility);
        $this->assertSame(['CV Review', 'Portfolio'], $profile->expertise_topics);
    }

    public function test_mentor_setup_returns_form_error_when_authorization_fails(): void
    {
        $mentor = $this->activeUser('alumni');
        $this->mentorProfile($mentor);
        $this->attachAvatar($mentor);

        $this->mock(UpdateMentorProfileAction::class)
            ->shouldReceive('execute')
            ->once()
            ->andThrow(new AuthorizationException('Denied by policy.'));

        $this->actingAs($mentor)
            ->from(route('mentor.setup'))
            ->patch(route('mentor.setup.update'), [
                'headline' => 'Mentor hỗ trợ CV và định hướng thực tập',
                'bio' => 'Tôi hỗ trợ sinh viên chuẩn bị CV, portfolio và định hướng thực tập bằng kinh nghiệm thực tế.',
                'expertise_topics_text' => 'CV Review, Portfolio',
                'help_topics_text' => 'Review CV, Định hướng thực tập',
                'career_paths_text' => 'Software Engineering',
                'skills_text' => 'Laravel, React',
                'preferred_request_types' => ['cv_review', 'career_advice'],
                'availability_status' => 'available',
                'mentor_visibility' => true,
                'max_pending_requests' => 3,
                'response_expectation_text' => 'Phản hồi trong 2-3 ngày làm việc.',
                'office_hours_text' => 'Buổi tối trong tuần.',
            ])
            ->assertRedirect(route('mentor.setup'))
            ->assertSessionHasErrors('mentor_profile');
    }

    public function test_mentor_setup_requires_trusted_avatar_before_saving(): void
    {
        $mentor = $this->activeUser('alumni');
        $this->mentorProfile($mentor);

        $this->actingAs($mentor)
            ->from(route('mentor.setup'))
            ->patch(route('mentor.setup.update'), [
                'headline' => 'Mentor hỗ trợ CV và định hướng thực tập',
                'bio' => 'Tôi hỗ trợ sinh viên chuẩn bị CV, portfolio và định hướng thực tập bằng kinh nghiệm thực tế.',
                'expertise_topics_text' => 'CV Review, Portfolio',
                'help_topics_text' => 'Review CV, Định hướng thực tập',
                'preferred_request_types' => ['cv_review'],
                'availability_status' => 'available',
                'mentor_visibility' => true,
                'max_pending_requests' => 3,
                'response_expectation_text' => 'Phản hồi trong 2-3 ngày làm việc.',
            ])
            ->assertRedirect(route('mentor.setup'))
            ->assertSessionHasErrors('avatar');
    }
}
