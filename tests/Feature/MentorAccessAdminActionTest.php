<?php

namespace Tests\Feature;

use App\Actions\Mentor\RequestMentorAccessAction;
use App\Enums\MentorAccessStatus;
use App\Enums\MentorAvailabilityStatus;
use App\Models\MentorAccessRequest;
use App\Models\MentorProfile;
use App\Models\User;
use App\Notifications\Mentor\MentorAccessNeedMoreInfoNotification;
use App\Notifications\Mentor\MentorAccessRevokedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Concerns\BuildsMentorFixtures;
use Tests\TestCase;

class MentorAccessAdminActionTest extends TestCase
{
    use BuildsMentorFixtures;
    use RefreshDatabase;

    public function test_admin_can_approve_and_redirects_to_queue(): void
    {
        $admin = $this->adminUser();
        $applicant = $this->activeUser('alumni');
        $this->attachAvatar($applicant);

        $request = MentorAccessRequest::create([
            'user_id' => $applicant->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::Submitted,
            'motivation' => 'I want to help students prepare for internships.',
            'headline' => 'Career mentor with industry experience',
            'bio' => 'I have over 5 years helping students transition to the workforce.',
            'expertise_topics' => ['career planning', 'cv review', 'interview prep'],
            'help_topics' => ['resume writing', 'job search'],
            'preferred_request_types' => ['cv_review', 'career_advice'],
            'response_expectation_text' => 'Within 3 days',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.mentors.action', $request), [
            'action' => 'approve',
            'reason' => 'Qualified mentor.',
            'instruction' => '',
        ]);

        $response->assertRedirect(route('admin.mentors.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('mentor_access_requests', [
            'id' => $request->id,
            'status' => MentorAccessStatus::Approved->value,
            'reviewed_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('mentor_profiles', [
            'user_id' => $applicant->id,
            'is_active' => true,
            'headline' => 'Career mentor with industry experience',
            'bio' => 'I have over 5 years helping students transition to the workforce.',
        ]);

        $this->assertTrue($applicant->fresh()->hasDirectPermission('mentor_access'));
    }

    public function test_approve_creates_mentor_profile_with_form_data(): void
    {
        $admin = $this->adminUser();
        $applicant = $this->activeUser('alumni');
        $this->attachAvatar($applicant);

        $request = MentorAccessRequest::create([
            'user_id' => $applicant->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::Submitted,
            'motivation' => 'I want to mentor students.',
            'headline' => 'Full-stack developer mentor',
            'bio' => 'Experienced developer helping juniors.',
            'expertise_topics' => ['Laravel', 'React', 'PHP'],
            'help_topics' => ['code review', 'career advice'],
            'career_paths' => ['Web Development', 'Software Engineering'],
            'preferred_request_types' => ['cv_review', 'career_advice'],
            'response_expectation_text' => 'Within 2 days',
            'office_hours_text' => 'Weekends',
            'skills' => ['Laravel', 'React', 'PHP'],
        ]);

        $this->actingAs($admin)->post(route('admin.mentors.action', $request), [
            'action' => 'approve',
            'reason' => 'Good fit mentor.',
        ]);

        $profile = MentorProfile::where('user_id', $applicant->id)->first();

        $this->assertNotNull($profile);
        $this->assertEquals(['Laravel', 'React', 'PHP'], $profile->expertise_topics);
        $this->assertEquals(['code review', 'career advice'], $profile->help_topics);
        $this->assertEquals(['Web Development', 'Software Engineering'], $profile->career_paths);
        $this->assertEquals('Full-stack developer mentor', $profile->headline);
        $this->assertEquals('Experienced developer helping juniors.', $profile->bio);
        $this->assertSame(MentorAvailabilityStatus::Available, $profile->availability_status);
        $this->assertTrue($profile->mentor_visibility);
        $this->assertEquals(5, $profile->max_pending_requests);
    }

    public function test_admin_can_reject_and_redirects_to_queue(): void
    {
        $admin = $this->adminUser();
        $applicant = $this->activeUser('alumni');

        $request = MentorAccessRequest::create([
            'user_id' => $applicant->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::Submitted,
            'motivation' => 'I want to mentor students.',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.mentors.action', $request), [
            'action' => 'reject',
            'reason' => 'Insufficient experience.',
        ]);

        $response->assertRedirect(route('admin.mentors.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('mentor_access_requests', [
            'id' => $request->id,
            'status' => MentorAccessStatus::Rejected->value,
            'review_reason' => 'Insufficient experience.',
        ]);
    }

    public function test_admin_can_request_more_info_and_redirects_to_queue(): void
    {
        Notification::fake();
        $admin = $this->adminUser();
        $applicant = $this->activeUser('alumni');

        $request = MentorAccessRequest::create([
            'user_id' => $applicant->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::Submitted,
            'motivation' => 'I want to mentor students.',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.mentors.action', $request), [
            'action' => 'request_more_info',
            'reason' => 'Please provide your portfolio.',
            'instruction' => 'Upload samples of your work.',
        ]);

        $response->assertRedirect(route('admin.mentors.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('mentor_access_requests', [
            'id' => $request->id,
            'status' => MentorAccessStatus::NeedMoreInfo->value,
            'review_reason' => 'Please provide your portfolio.',
            'admin_notes' => 'Upload samples of your work.',
        ]);

        Notification::assertSentTo($applicant, MentorAccessNeedMoreInfoNotification::class);
    }

    public function test_admin_can_revoke_and_redirects_to_queue(): void
    {
        Notification::fake();
        $admin = $this->adminUser();
        $applicant = $this->activeUser('alumni');
        $profile = $this->mentorProfile($applicant);

        $request = MentorAccessRequest::where('user_id', $applicant->id)
            ->where('status', MentorAccessStatus::Approved)
            ->first();

        $response = $this->actingAs($admin)->post(route('admin.mentors.action', $request), [
            'action' => 'revoke',
            'reason' => 'Violated community guidelines.',
            'instruction' => '',
        ]);

        $response->assertRedirect(route('admin.mentors.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('mentor_access_requests', [
            'id' => $request->id,
            'status' => MentorAccessStatus::Revoked->value,
        ]);

        $this->assertDatabaseHas('mentor_profiles', [
            'id' => $profile->id,
            'is_active' => false,
            'mentor_visibility' => false,
        ]);

        $this->assertFalse($applicant->fresh()->hasDirectPermission('mentor_access'));

        Notification::assertSentTo($applicant, MentorAccessRevokedNotification::class);
    }

    public function test_non_admin_cannot_access_action_endpoint(): void
    {
        $student = $this->activeUser('student');

        $request = MentorAccessRequest::create([
            'user_id' => $student->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::Submitted,
            'motivation' => 'Test.',
        ]);

        $response = $this->actingAs($student)->post(route('admin.mentors.action', $request), [
            'action' => 'approve',
            'reason' => 'Test.',
        ]);

        $response->assertForbidden();
    }

    public function test_approve_fails_without_avatar(): void
    {
        $admin = $this->adminUser();
        $applicant = $this->activeUser('alumni');

        $request = MentorAccessRequest::create([
            'user_id' => $applicant->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::Submitted,
            'motivation' => 'I want to mentor students.',
            'headline' => 'Career mentor',
            'bio' => 'I have experience mentoring students.',
            'expertise_topics' => ['career', 'cv'],
            'help_topics' => ['interview'],
            'preferred_request_types' => ['career_advice'],
            'response_expectation_text' => 'Within 3 days',
        ]);

        $response = $this->actingAs($admin)->from(route('admin.mentors.detail', $request->id))->post(route('admin.mentors.action', $request), [
            'action' => 'approve',
            'reason' => 'Qualified mentor.',
        ]);

        $response->assertRedirect(route('admin.mentors.detail', $request->id));
        $response->assertSessionHasErrors('mentor_profile');

        $this->assertDatabaseMissing('mentor_profiles', [
            'user_id' => $applicant->id,
        ]);
    }

    public function test_approve_already_approved_request_skips_trust_check(): void
    {
        $admin = $this->adminUser();
        $applicant = $this->activeUser('alumni');
        $this->mentorProfile($applicant);

        $request = MentorAccessRequest::where('user_id', $applicant->id)
            ->where('status', MentorAccessStatus::Approved)
            ->first();

        $this->actingAs($admin)->post(route('admin.mentors.action', $request), [
            'action' => 'approve',
            'reason' => 'Re-affirming approval.',
        ]);

        $this->assertDatabaseHas('mentor_access_requests', [
            'id' => $request->id,
            'status' => MentorAccessStatus::Approved->value,
        ]);
    }

    public function test_approve_fails_without_minimal_trust_fields(): void
    {
        $admin = $this->adminUser();
        $applicant = $this->activeUser('alumni');
        $this->attachAvatar($applicant);

        $request = MentorAccessRequest::create([
            'user_id' => $applicant->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::Submitted,
            'motivation' => 'I want to mentor students.',
            'expertise_topics' => ['career'],
            'help_topics' => [],
            'preferred_request_types' => [],
            'response_expectation_text' => '',
        ]);

        $response = $this->actingAs($admin)->from(route('admin.mentors.detail', $request->id))->post(route('admin.mentors.action', $request), [
            'action' => 'approve',
            'reason' => 'Qualified.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('mentor_profile');

        $this->assertDatabaseMissing('mentor_profiles', [
            'user_id' => $applicant->id,
        ]);
    }

    public function test_reject_stays_on_same_page_when_validation_fails(): void
    {
        $admin = $this->adminUser();
        $applicant = $this->activeUser('alumni');

        $request = MentorAccessRequest::create([
            'user_id' => $applicant->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::Submitted,
            'motivation' => 'I want to mentor students.',
        ]);

        $response = $this->actingAs($admin)->from(route('admin.mentors.detail', $request->id))->post(route('admin.mentors.action', $request), [
            'action' => 'invalid_action',
            'reason' => '',
        ]);

        $response->assertRedirect(route('admin.mentors.detail', $request->id));
        $response->assertSessionHasErrors('action');

        $this->assertDatabaseHas('mentor_access_requests', [
            'id' => $request->id,
            'status' => MentorAccessStatus::Submitted->value,
        ]);
    }

    public function test_alumni_can_resubmit_after_admin_requests_more_info(): void
    {
        $admin = $this->adminUser();
        $applicant = $this->activeUser('alumni');
        $this->attachAvatar($applicant);

        $request = MentorAccessRequest::create([
            'user_id' => $applicant->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::NeedMoreInfo,
            'motivation' => 'Original motivation text',
            'headline' => 'Original headline',
            'bio' => 'Original bio that is long enough for validation purposes here.',
            'expertise_topics' => ['PHP', 'Laravel'],
            'help_topics' => ['code review', 'career advice'],
            'preferred_request_types' => ['cv_review'],
            'response_expectation_text' => 'Within 3 days',
            'policy_agreed' => true,
            'review_reason' => 'Please provide more details about your experience.',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($applicant)->post(route('mentor.apply.store'), [
            'requested_role_context' => 'alumni',
            'motivation' => 'Updated motivation with more details for mentoring students.',
            'experience_summary' => 'Updated experience with more relevant background.',
            'headline' => 'Updated headline for mentor profile',
            'bio' => 'Updated bio that is much more detailed and longer than the minimum forty characters requirement.',
            'expertise_topics' => ['PHP', 'Laravel', 'React', 'Vue.js'],
            'help_topics' => ['code review', 'career advice', 'interview prep'],
            'career_paths' => ['Web Development'],
            'skills' => ['PHP', 'Laravel', 'React'],
            'preferred_request_types' => ['cv_review', 'career_advice', 'interview_prep'],
            'response_expectation_text' => 'Within 2 days',
            'office_hours_text' => 'Weekends',
            'portfolio_link' => '',
            'availability_note' => '',
            'policy_agreed' => true,
        ]);

        $response->assertRedirect(route('mentor.dashboard'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('mentor_access_requests', [
            'id' => $request->id,
            'status' => MentorAccessStatus::Submitted->value,
            'motivation' => 'Updated motivation with more details for mentoring students.',
            'headline' => 'Updated headline for mentor profile',
        ]);

        $this->assertDatabaseMissing('mentor_access_requests', [
            'id' => $request->id,
            'review_reason' => 'Please provide more details about your experience.',
        ]);
    }

    public function test_alumni_sees_prefilled_form_when_need_more_info(): void
    {
        $applicant = $this->activeUser('alumni');
        $this->attachAvatar($applicant);

        MentorAccessRequest::create([
            'user_id' => $applicant->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::NeedMoreInfo,
            'motivation' => 'I want to help students with their career planning and growth.',
            'headline' => 'Career mentor with experience',
            'bio' => 'I have five years of experience helping students transition to the workforce successfully.',
            'expertise_topics' => ['career planning', 'cv review'],
            'help_topics' => ['resume writing', 'job search'],
            'preferred_request_types' => ['career_advice'],
            'response_expectation_text' => 'Within 3 business days',
            'policy_agreed' => true,
            'review_reason' => 'Please add more expertise topics.',
        ]);

        $response = $this->actingAs($applicant)->get(route('mentor.apply'));

        $response->assertOk();
        $response->assertSee('Yêu cầu cần bổ sung thông tin');
        $response->assertSee('Please add more expertise topics.');
        $response->assertSee('Cập nhật và gửi lại');
        $response->assertSee('Career mentor with experience');
        $response->assertSee('I want to help students with their career planning and growth.');
    }

    public function test_request_more_info_deactivates_existing_mentor_profile(): void
    {
        $admin = $this->adminUser();
        $applicant = $this->activeUser('alumni');
        $profile = $this->mentorProfile($applicant);

        $request = MentorAccessRequest::where('user_id', $applicant->id)
            ->where('status', MentorAccessStatus::Approved)
            ->first();

        $this->actingAs($admin)->post(route('admin.mentors.action', $request), [
            'action' => 'request_more_info',
            'reason' => 'Please update your profile details.',
            'instruction' => 'Update headline and bio.',
        ]);

        $this->assertDatabaseHas('mentor_access_requests', [
            'id' => $request->id,
            'status' => MentorAccessStatus::NeedMoreInfo->value,
        ]);

        $this->assertDatabaseHas('mentor_profiles', [
            'id' => $profile->id,
            'is_active' => false,
            'mentor_visibility' => false,
        ]);
    }

    public function test_revoked_user_can_reapply(): void
    {
        $admin = $this->adminUser();
        $applicant = $this->activeUser('alumni');
        $profile = $this->mentorProfile($applicant);

        $request = MentorAccessRequest::where('user_id', $applicant->id)
            ->where('status', MentorAccessStatus::Approved)
            ->first();

        $this->actingAs($admin)->post(route('admin.mentors.action', $request), [
            'action' => 'revoke',
            'reason' => 'Violated guidelines.',
        ]);

        $this->attachAvatar($applicant);

        $response = $this->actingAs($applicant)->post(route('mentor.apply.store'), [
            'requested_role_context' => 'alumni',
            'motivation' => 'I want to rejoin the mentor program with improved profile.',
            'experience_summary' => 'Updated experience.',
            'headline' => 'Re-applying mentor',
            'bio' => 'I am re-applying with a more complete and detailed profile for mentoring students.',
            'expertise_topics' => ['career planning', 'cv review', 'interview prep'],
            'help_topics' => ['resume writing', 'job search', 'soft skills'],
            'preferred_request_types' => ['cv_review', 'career_advice'],
            'response_expectation_text' => 'Within 2 days.',
            'policy_agreed' => true,
        ]);

        $response->assertRedirect(route('mentor.dashboard'));
        $response->assertSessionHas('status', 'Yêu cầu trở thành mentor đã được gửi.');

        $this->assertDatabaseHas('mentor_access_requests', [
            'user_id' => $applicant->id,
            'status' => MentorAccessStatus::Submitted->value,
        ]);
    }

    public function test_revoked_user_sees_reapply_ui_on_dashboard(): void
    {
        $applicant = $this->activeUser('alumni');
        $profile = $this->mentorProfile($applicant);
        $this->revokeMentorAccess($applicant, $profile);

        $response = $this->actingAs($applicant)->get(route('mentor.dashboard'));

        $response->assertOk();
        $response->assertSee('Bạn chưa có hồ sơ mentor');
        $response->assertSee('Gửi đăng ký ngay');
        $response->assertSee(route('mentor.apply'));
        $response->assertDontSee('Quyền mentor đã bị thu hồi');
        $response->assertDontSee('Lý do thu hồi');
    }

    public function test_revoked_user_sees_reapply_ui_on_setup_page(): void
    {
        $applicant = $this->activeUser('alumni');
        $profile = $this->mentorProfile($applicant);
        $this->revokeMentorAccess($applicant, $profile);

        $response = $this->actingAs($applicant)->get(route('mentor.setup'));

        $response->assertOk();
        $response->assertSee('Bạn chưa có hồ sơ mentor được duyệt');
        $response->assertSee('Đăng ký làm mentor');
        $response->assertDontSee('Quyền mentor đã bị thu hồi');
    }

    public function test_need_more_info_ui_appears_on_dashboard_without_profile(): void
    {
        $applicant = $this->activeUser('alumni');

        MentorAccessRequest::create([
            'user_id' => $applicant->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::NeedMoreInfo,
            'motivation' => 'I want to mentor students.',
            'review_reason' => 'Please add more expertise topics.',
            'reviewed_by' => $this->adminUser()->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($applicant)->get(route('mentor.dashboard'));

        $response->assertOk();
        $response->assertSee('Cần thêm thông tin');
        $response->assertSee('Please add more expertise topics.');
        $response->assertSee('Chỉnh sửa đơn đăng ký');
        $response->assertDontSee('Độ hoàn thiện');
    }

    public function test_need_more_info_ui_on_dashboard_when_was_approved(): void
    {
        $admin = $this->adminUser();
        $applicant = $this->activeUser('alumni');
        $profile = $this->mentorProfile($applicant);

        $request = MentorAccessRequest::where('user_id', $applicant->id)
            ->where('status', MentorAccessStatus::Approved)
            ->first();

        $this->actingAs($admin)->post(route('admin.mentors.action', $request), [
            'action' => 'request_more_info',
            'reason' => 'Please update your profile details.',
            'instruction' => 'Update headline and bio.',
        ]);

        $response = $this->actingAs($applicant)->get(route('mentor.dashboard'));

        $response->assertOk();
        $response->assertSee('Cần thêm thông tin');
        $response->assertSee('Please update your profile details.');
        $response->assertSee('Chỉnh sửa đơn đăng ký');
        $response->assertDontSee('Độ hoàn thiện');
        $response->assertDontSee('Cập nhật hồ sơ');
    }

    public function test_admin_can_request_more_info_then_alumni_resubmits_full_flow(): void
    {
        $admin = $this->adminUser();
        $applicant = $this->activeUser('alumni');
        $this->attachAvatar($applicant);

        // 1. Applicant submits
        $submission = app(RequestMentorAccessAction::class)->execute($applicant, [
            'requested_role_context' => 'alumni',
            'motivation' => 'I want to mentor students in career planning and development.',
            'headline' => 'Career mentor',
            'bio' => 'I have over five years helping students with career planning and interview prep.',
            'expertise_topics' => ['career planning'],
            'help_topics' => ['interview prep'],
            'preferred_request_types' => ['career_advice'],
            'response_expectation_text' => 'Within 3 days',
            'policy_agreed' => true,
        ]);

        $this->assertEquals(MentorAccessStatus::Submitted, $submission->status);

        // 2. Admin requests more info
        $this->actingAs($admin)->post(route('admin.mentors.action', $submission), [
            'action' => 'request_more_info',
            'reason' => 'Please add at least 2 expertise topics.',
            'instruction' => 'Update your topics.',
        ]);

        $this->assertDatabaseHas('mentor_access_requests', [
            'id' => $submission->id,
            'status' => MentorAccessStatus::NeedMoreInfo->value,
        ]);

        // 3. Applicant resubmits with more info
        $response = $this->actingAs($applicant)->post(route('mentor.apply.store'), [
            'requested_role_context' => 'alumni',
            'motivation' => 'I want to mentor students in career planning and development.',
            'headline' => 'Career mentor',
            'bio' => 'I have over five years helping students with career planning and interview prep.',
            'expertise_topics' => ['career planning', 'cv review', 'interview prep'],
            'help_topics' => ['interview prep', 'resume writing'],
            'preferred_request_types' => ['career_advice', 'cv_review'],
            'response_expectation_text' => 'Within 3 days',
            'policy_agreed' => true,
        ]);

        $response->assertRedirect(route('mentor.dashboard'));
        $response->assertSessionHas('status', 'Yêu cầu mentor đã được cập nhật và gửi lại.');

        $this->assertDatabaseHas('mentor_access_requests', [
            'id' => $submission->id,
            'status' => MentorAccessStatus::Submitted->value,
            'review_reason' => null,
        ]);
    }

    private function revokeMentorAccess(User $applicant, MentorProfile $profile): void
    {
        $admin = $this->adminUser();

        $request = MentorAccessRequest::where('user_id', $applicant->id)
            ->where('status', MentorAccessStatus::Approved)
            ->first();

        $response = $this->actingAs($admin)->post(route('admin.mentors.action', $request), [
            'action' => 'revoke',
            'reason' => 'Test revocation reason that is long enough.',
        ]);
        if (session('errors')) {
            dd(session('errors')->all());
        }
    }
}
