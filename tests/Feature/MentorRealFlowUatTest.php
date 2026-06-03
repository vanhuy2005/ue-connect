<?php

namespace Tests\Feature;

use App\Actions\Messaging\SendMessage;
use App\Enums\AccountStatus;
use App\Enums\MentorAccessStatus;
use App\Enums\MentorAvailabilityStatus;
use App\Enums\MentorFeedbackLevel;
use App\Enums\MentorRequestStatus;
use App\Enums\MessageType;
use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\MentorAccessRequest;
use App\Models\MentorFeedback;
use App\Models\MentorProfile;
use App\Models\MentorRequest;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsMentorFixtures;
use Tests\TestCase;

class MentorRealFlowUatTest extends TestCase
{
    use BuildsMentorFixtures;
    use RefreshDatabase;

    public function test_real_mentor_uat_happy_path_from_access_application_to_chat_feedback_and_revoke(): void
    {
        $admin = $this->adminUser();
        $mentor = $this->activeUser('alumni');
        $student = $this->activeUser('student');

        $this->actingAs($mentor)
            ->get(route('mentor.apply'))
            ->assertOk()
            ->assertSee('Đăng ký trở thành mentor')
            ->assertSee('Cựu sinh viên');

        $this->attachAvatar($mentor);

        $this->actingAs($mentor)
            ->from(route('mentor.apply'))
            ->post(route('mentor.apply.store'), [
                'requested_role_context' => 'alumni',
                'motivation' => 'Tôi muốn hỗ trợ sinh viên chuẩn bị thực tập và xây dựng portfolio nghề nghiệp.',
                'experience_summary' => 'Tôi có nhiều năm làm frontend và từng hướng dẫn sinh viên thực tập.',
                'expertise_topics' => ['Frontend', 'CV Review'],
                'career_paths' => ['Software Engineering'],
                'headline' => 'Frontend mentor cho sinh viên chuẩn bị thực tập',
                'bio' => 'Tôi giúp sinh viên định hướng frontend, CV, portfolio và chuẩn bị phỏng vấn intern.',
                'help_topics' => ['Review CV', 'Phỏng vấn intern'],
                'preferred_request_types' => ['cv_review', 'interview_prep'],
                'response_expectation_text' => 'Thường phản hồi trong vài ngày.',
                'policy_agreed' => true,
            ])
            ->assertRedirect(route('mentor.dashboard'))
            ->assertSessionHas('status', 'Yêu cầu trở thành mentor đã được gửi.');

        $accessRequest = MentorAccessRequest::query()->where('user_id', $mentor->id)->firstOrFail();
        $this->assertSame(MentorAccessStatus::Submitted, $accessRequest->status);

        $this->actingAs($admin)
            ->from(route('admin.mentors.index'))
            ->post(route('admin.mentors.approve', $accessRequest), [
                'reason' => 'Đủ điều kiện UAT mentor.',
                'admin_notes' => 'Approved in real flow UAT.',
            ])
            ->assertRedirect(route('admin.mentors.index'))
            ->assertSessionHas('status', 'Mentor access approved.');

        $mentorProfile = MentorProfile::query()->where('user_id', $mentor->id)->firstOrFail();
        $this->assertTrue($mentorProfile->is_active);
        $this->assertTrue($mentorProfile->mentor_visibility);
        $this->assertSame(MentorAvailabilityStatus::Available, $mentorProfile->availability_status);
        $this->assertTrue(AuditLog::query()
            ->where('action_key', 'mentor_access_approved')
            ->where('target_type', 'mentor_access_request')
            ->where('target_id', $accessRequest->id)
            ->exists());
        $this->assertNotificationTypeExists($mentor, 'mentor_access_approved');

        $this->actingAs($mentor)
            ->from(route('mentor.setup'))
            ->patch(route('mentor.setup.update'), [
                'headline' => 'Frontend mentor cho sinh viên chuẩn bị thực tập',
                'bio' => 'Tôi giúp sinh viên định hướng frontend, CV, portfolio và chuẩn bị phỏng vấn intern.',
                'expertise_topics_text' => 'Frontend, Laravel, CV Review',
                'help_topics_text' => 'Review CV, Portfolio, Phỏng vấn intern',
                'career_paths_text' => 'Software Engineering',
                'skills_text' => 'Laravel, React',
                'preferred_request_types' => ['career_advice', 'portfolio_review'],
                'availability_status' => 'available',
                'mentor_visibility' => true,
                'max_pending_requests' => 3,
                'response_expectation_text' => 'Thường phản hồi trong vài ngày.',
                'office_hours_text' => 'Buổi tối trong tuần.',
            ])
            ->assertRedirect(route('mentor.setup'))
            ->assertSessionHas('status', 'Hồ sơ mentor đã được cập nhật.');

        $mentorProfile = $mentorProfile->fresh();
        $this->assertSame('Frontend mentor cho sinh viên chuẩn bị thực tập', $mentorProfile->headline);
        $this->assertContains($mentorProfile->id, MentorProfile::discoverable()->pluck('id')->all());

        $this->actingAs($student)
            ->get(route('mentor.discovery'))
            ->assertOk()
            ->assertSee('Frontend mentor cho sinh viên chuẩn bị thực tập')
            ->assertSee($mentor->name);

        $this->actingAs($student)
            ->get(route('mentor.show', $mentorProfile))
            ->assertOk()
            ->assertSee('Gửi yêu cầu cố vấn');

        $this->actingAs($student)
            ->from(route('mentor.show', $mentorProfile))
            ->post(route('mentor.requests.store'), [
                'mentor_profile_id' => $mentorProfile->id,
                'topic' => 'Review CV thực tập Frontend',
                'goal' => 'Chuẩn bị apply Frontend Intern',
                'question' => 'CV của em đang thiếu gì để apply Frontend Intern?',
                'urgency' => 'normal',
                'context' => 'Em đang học Laravel và React.',
                'expected_outcome' => 'Có checklist cải thiện CV.',
            ])
            ->assertRedirect();

        $mentorRequest = MentorRequest::query()->where('student_id', $student->id)->where('mentor_id', $mentor->id)->firstOrFail();
        $this->assertSame(MentorRequestStatus::Submitted, $mentorRequest->status);
        $this->assertNull($mentorRequest->conversation_id);
        $this->assertSame(0, Conversation::query()->where('mentor_request_id', $mentorRequest->id)->count());
        $this->assertNotificationTypeExists($mentor, 'mentor_request_submitted');

        $this->actingAs($student)
            ->from(route('mentor.show', $mentorProfile))
            ->post(route('mentor.requests.store'), [
                'mentor_profile_id' => $mentorProfile->id,
                'topic' => 'Duplicate request',
                'goal' => 'Test duplicate guard',
                'question' => 'Em gửi lại cùng mentor được không?',
                'urgency' => 'normal',
            ])
            ->assertRedirect(route('mentor.show', $mentorProfile))
            ->assertSessionHasErrors('mentor_profile_id');

        $this->actingAs($mentor)
            ->from(route('mentor.requests.show', $mentorRequest))
            ->post(route('mentor.requests.accept', $mentorRequest), [
                'mentor_response' => 'Mentor sẽ góp ý CV trong cuộc trò chuyện.',
            ])
            ->assertRedirect(route('mentor.requests.show', $mentorRequest))
            ->assertSessionHas('status', 'Yêu cầu cố vấn đã được chấp nhận.');

        $mentorRequest = $mentorRequest->fresh();
        $this->assertSame(MentorRequestStatus::Accepted, $mentorRequest->status);
        $this->assertNotNull($mentorRequest->conversation_id);
        $this->assertNotificationTypeExists($student, 'mentor_request_accepted');

        $conversation = Conversation::query()->with('participants', 'messages')->findOrFail($mentorRequest->conversation_id);
        $this->assertSame($mentorRequest->id, $conversation->mentor_request_id);
        $this->assertTrue($conversation->participants->pluck('user_id')->contains($student->id));
        $this->assertTrue($conversation->participants->pluck('user_id')->contains($mentor->id));
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'message_type' => MessageType::SYSTEM->value,
        ]);

        $message = app(SendMessage::class)->execute($student, $conversation, [
            'body' => 'Em gửi CV nhờ mentor góp ý ạ.',
        ]);

        $this->assertSame($conversation->id, $message->conversation_id);
        $this->assertNotificationClassExists($mentor, 'App\Notifications\MessageReceived');

        $this->actingAs($student)
            ->from(route('mentor.requests.show', $mentorRequest))
            ->post(route('mentor.requests.complete', $mentorRequest))
            ->assertRedirect(route('mentor.requests.show', $mentorRequest))
            ->assertSessionHas('status', 'Yêu cầu cố vấn đã hoàn thành.');

        $mentorRequest = $mentorRequest->fresh();
        $this->assertSame(MentorRequestStatus::Completed, $mentorRequest->status);
        $this->assertNotificationTypeExists($student, 'mentor_request_completed');
        $this->assertNotificationTypeExists($mentor, 'mentor_request_completed');

        $this->actingAs($student)
            ->from(route('mentor.requests.show', $mentorRequest))
            ->post(route('mentor.requests.feedback', $mentorRequest), [
                'helpfulness_level' => 'helpful',
                'feedback_text' => 'Mentor góp ý rõ ràng và thực tế.',
            ])
            ->assertRedirect(route('mentor.requests.show', $mentorRequest))
            ->assertSessionHas('status', 'Cảm ơn bạn đã gửi phản hồi.');

        $feedback = MentorFeedback::query()->where('mentor_request_id', $mentorRequest->id)->firstOrFail();
        $this->assertSame(MentorFeedbackLevel::Helpful, $feedback->helpfulness_level);
        $this->assertTrue($feedback->is_private);
        $this->assertNotificationTypeExists($mentor, 'mentor_feedback_submitted');

        $this->actingAs($admin)
            ->from(route('admin.mentors.detail', $accessRequest))
            ->post(route('admin.mentors.revoke', $mentorProfile), [
                'reason' => 'Kết thúc UAT revoke.',
                'admin_notes' => 'Revoked after full UAT.',
            ])
            ->assertRedirect(route('admin.mentors.detail', $accessRequest))
            ->assertSessionHas('status', 'Mentor access revoked.');

        $mentorProfile = $mentorProfile->fresh();
        $this->assertFalse($mentorProfile->is_active);
        $this->assertFalse($mentorProfile->mentor_visibility);
        $this->assertNotContains($mentorProfile->id, MentorProfile::discoverable()->pluck('id')->all());
        $this->assertTrue(AuditLog::query()
            ->where('action_key', 'mentor_access_revoked')
            ->where('target_type', 'mentor_profile')
            ->where('target_id', $mentorProfile->id)
            ->exists());
    }

    public function test_real_mentor_uat_safety_limits_and_alternate_request_states_without_seeddata(): void
    {
        $mentorProfile = $this->mentorProfile(null, [
            'headline' => 'Advisor mentor UAT',
            'max_pending_requests' => 1,
        ]);
        $mentor = $mentorProfile->user;
        $student = $this->activeUser('student');

        $this->actingAs($mentor)
            ->from(route('mentor.setup'))
            ->post(route('mentor.availability'), [
                'availability_status' => 'paused',
            ])
            ->assertRedirect(route('mentor.setup'))
            ->assertSessionHas('status', 'Trạng thái mentor đã được cập nhật.');

        $this->actingAs($student)
            ->from(route('mentor.show', $mentorProfile))
            ->post(route('mentor.requests.store'), [
                'mentor_profile_id' => $mentorProfile->id,
                'topic' => 'Paused mentor request',
                'goal' => 'Confirm paused guard',
                'question' => 'Mentor paused có nhận request không?',
                'urgency' => 'normal',
            ])
            ->assertRedirect(route('mentor.show', $mentorProfile))
            ->assertSessionHasErrors('mentor_profile_id');

        $this->actingAs($mentor)
            ->from(route('mentor.setup'))
            ->post(route('mentor.availability'), [
                'availability_status' => 'available',
            ])
            ->assertRedirect(route('mentor.setup'));

        $moreInfoRequest = $this->mentorRequest($student, $mentorProfile);

        $this->actingAs($mentor)
            ->from(route('mentor.requests.show', $moreInfoRequest))
            ->post(route('mentor.requests.ask-more-info', $moreInfoRequest), [
                'more_info_question' => 'Em bổ sung timeline và mục tiêu cụ thể hơn nhé.',
            ])
            ->assertRedirect(route('mentor.requests.show', $moreInfoRequest))
            ->assertSessionHas('status', 'Đã yêu cầu thêm thông tin.');

        $this->assertSame(MentorRequestStatus::NeedMoreInfo, $moreInfoRequest->fresh()->status);
        $this->assertNotificationTypeExists($student, 'mentor_request_more_info');

        $this->actingAs($student)
            ->from(route('mentor.requests.show', $moreInfoRequest))
            ->post(route('mentor.requests.cancel', $moreInfoRequest))
            ->assertRedirect(route('mentor.requests.show', $moreInfoRequest))
            ->assertSessionHas('status', 'Yêu cầu cố vấn đã được hủy.');

        $this->assertSame(MentorRequestStatus::Cancelled, $moreInfoRequest->fresh()->status);
        $this->assertNotificationTypeExists($mentor, 'mentor_request_cancelled');

        $declinedRequest = $this->mentorRequest($student, $mentorProfile);

        $this->actingAs($mentor)
            ->from(route('mentor.requests.show', $declinedRequest))
            ->post(route('mentor.requests.decline', $declinedRequest), [
                'decline_reason' => 'Tuần này mentor chưa có thời gian phù hợp.',
            ])
            ->assertRedirect(route('mentor.requests.show', $declinedRequest))
            ->assertSessionHas('status', 'Yêu cầu cố vấn đã bị từ chối.');

        $this->assertSame(MentorRequestStatus::Declined, $declinedRequest->fresh()->status);
        $this->assertNull($declinedRequest->fresh()->conversation_id);
        $this->assertNotificationTypeExists($student, 'mentor_request_declined');

        $reportedRequest = $this->mentorRequest($student, $mentorProfile);

        $this->actingAs($student)
            ->from(route('mentor.requests.show', $reportedRequest))
            ->post(route('mentor.requests.report', $reportedRequest), [
                'reason' => 'harassment',
                'description' => 'Mentor request UAT report.',
            ])
            ->assertRedirect(route('mentor.requests.show', $reportedRequest))
            ->assertSessionHas('status', 'Báo cáo mentor request đã được gửi.');

        $this->assertSame(MentorRequestStatus::Reported, $reportedRequest->fresh()->status);
        $report = Report::query()->where('target_type', 'mentor_request')->where('target_id', $reportedRequest->id)->firstOrFail();
        $this->assertSame($student->id, $report->reporter_id);
        $this->assertDatabaseHas('blocked_users', [
            'blocker_id' => $student->id,
            'blocked_id' => $mentor->id,
            'source_type' => 'report',
            'source_id' => $report->id,
        ]);

        $this->actingAs($student)
            ->from(route('mentor.show', $mentorProfile))
            ->post(route('mentor.requests.store'), [
                'mentor_profile_id' => $mentorProfile->id,
                'topic' => 'Blocked request',
                'goal' => 'Confirm block guard',
                'question' => 'Block rồi có gửi được nữa không?',
                'urgency' => 'normal',
            ])
            ->assertRedirect(route('mentor.show', $mentorProfile))
            ->assertSessionHasErrors('mentor_profile_id');

        $suspendedStudent = $this->activeUser('student');
        $suspendedStudent->update(['account_status' => AccountStatus::SUSPENDED]);

        $this->actingAs($suspendedStudent)
            ->from(route('mentor.show', $mentorProfile))
            ->post(route('mentor.requests.store'), [
                'mentor_profile_id' => $mentorProfile->id,
                'topic' => 'Suspended request',
                'goal' => 'Confirm account restriction',
                'question' => 'Suspended user có gửi được không?',
                'urgency' => 'normal',
            ])
            ->assertRedirect(route('system.account-restricted'));
    }

    private function assertNotificationTypeExists(User $user, string $type): void
    {
        $this->assertTrue(
            $user->notifications()->where('data->type', $type)->exists(),
            "Expected database notification type [{$type}] for user [{$user->id}]."
        );
    }

    private function assertNotificationClassExists(User $user, string $notificationClass): void
    {
        $this->assertTrue(
            $user->notifications()->where('type', $notificationClass)->exists(),
            "Expected database notification class [{$notificationClass}] for user [{$user->id}]."
        );
    }
}
