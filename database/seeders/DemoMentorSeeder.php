<?php

namespace Database\Seeders;

use App\Enums\ConversationStatus;
use App\Enums\ConversationType;
use App\Enums\MentorAccessStatus;
use App\Enums\MentorAvailabilityStatus;
use App\Enums\MentorFeedbackLevel;
use App\Enums\MentorRequestStatus;
use App\Enums\MentorUrgency;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\AuditLog;
use App\Models\BlockedUser;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\MentorAccessRequest;
use App\Models\MentorFeedback;
use App\Models\MentorProfile;
use App\Models\MentorRequest;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoMentorSeeder extends Seeder
{
    /** @var array<string, User> */
    private array $users = [];

    /** @var array<string, MentorProfile> */
    private array $mentorProfiles = [];

    public function run(): void
    {
        DB::transaction(function () {
            $this->resolveUsers();
            $this->seedMentorAccessRequests();
            $this->seedMentorProfiles();
            $this->seedMentorRequests();
            $this->seedSafetyArtifacts();
            $this->seedNotifications();
            $this->seedAuditLogs();
        });

        $this->printGuide();
    }

    private function resolveUsers(): void
    {
        foreach ([
            'mentor_manager' => 'mentor.manager@hcmue.edu.vn',
            'student' => 'student@hcmue.edu.vn',
            'student2' => 'student2@hcmue.edu.vn',
            'student_cntt' => 'student.cntt@hcmue.edu.vn',
            'student_math' => 'student.math@hcmue.edu.vn',
            'student_english' => 'student.english@hcmue.edu.vn',
            'limit_student' => 'limit.student@hcmue.edu.vn',
            'blocked_student' => 'blocked.student@hcmue.edu.vn',
            'student_peermentor' => 'student.peermentor@hcmue.edu.vn',
            'alumni_mentor' => 'alumni.mentor@hcmue.edu.vn',
            'alumni_paused' => 'alumni.paused@hcmue.edu.vn',
            'alumni_hidden' => 'alumni.hidden@hcmue.edu.vn',
            'alumni_full' => 'alumni.full@hcmue.edu.vn',
            'alumni_pending' => 'alumni.pending@hcmue.edu.vn',
            'alumni_underreview' => 'alumni.underreview@hcmue.edu.vn',
            'alumni_moreinfo' => 'alumni.moreinfo@hcmue.edu.vn',
            'alumni_rejected' => 'alumni.rejected@hcmue.edu.vn',
            'alumni_revoked' => 'alumni.revoked@hcmue.edu.vn',
            'advisor_mentor' => 'advisor.mentor@hcmue.edu.vn',
            'advisor_pending' => 'advisor.pending@hcmue.edu.vn',
            'advisor_moreinfo' => 'advisor.moreinfo@hcmue.edu.vn',
            'advisor_rejected' => 'advisor.rejected@hcmue.edu.vn',
            'advisor_paused' => 'advisor.paused@hcmue.edu.vn',
            'advisor_hidden' => 'advisor.hidden@hcmue.edu.vn',
        ] as $key => $email) {
            $this->users[$key] = User::where('email', $email)->firstOrFail();
        }
    }

    private function seedMentorAccessRequests(): void
    {
        foreach ([
            ['alumni_mentor', 'alumni', MentorAccessStatus::Approved, ['Frontend', 'Laravel', 'CV Review']],
            ['alumni_paused', 'alumni', MentorAccessStatus::Approved, ['Product', 'Career switch']],
            ['alumni_hidden', 'alumni', MentorAccessStatus::Approved, ['Backend', 'API']],
            ['alumni_full', 'alumni', MentorAccessStatus::Approved, ['Data', 'Internship']],
            ['alumni_pending', 'alumni', MentorAccessStatus::Submitted, ['Teaching']],
            ['alumni_underreview', 'alumni', MentorAccessStatus::UnderReview, ['Mentoring']],
            ['alumni_moreinfo', 'alumni', MentorAccessStatus::NeedMoreInfo, ['Portfolio']],
            ['alumni_rejected', 'alumni', MentorAccessStatus::Rejected, ['Career']],
            ['alumni_revoked', 'alumni', MentorAccessStatus::Revoked, ['Management']],
            ['advisor_mentor', 'advisor', MentorAccessStatus::Approved, ['Nghiên cứu khoa học', 'AI']],
            ['advisor_pending', 'advisor', MentorAccessStatus::Submitted, ['Academic writing']],
            ['advisor_moreinfo', 'advisor', MentorAccessStatus::NeedMoreInfo, ['Research']],
            ['advisor_rejected', 'advisor', MentorAccessStatus::Rejected, ['Counseling']],
            ['advisor_paused', 'advisor', MentorAccessStatus::Approved, ['Data', 'AI']],
            ['advisor_hidden', 'advisor', MentorAccessStatus::Approved, ['Psychology']],
            ['student_peermentor', 'exceptional_student', config('mentor.enable_student_exceptional_mentors') ? MentorAccessStatus::Approved : MentorAccessStatus::Rejected, ['Kinh nghiệm học tập']],
        ] as [$userKey, $roleContext, $status, $expertise]) {
            $this->mentorAccess($this->users[$userKey], $roleContext, $status, $expertise);
        }
    }

    private function seedMentorProfiles(): void
    {
        foreach ([
            ['alumni_mentor', MentorAvailabilityStatus::Available, true, true, ['Frontend', 'Laravel', 'CV Review'], ['Định hướng thực tập', 'Review portfolio']],
            ['alumni_paused', MentorAvailabilityStatus::Paused, true, true, ['Product', 'Career switch'], ['Định hướng nghề nghiệp']],
            ['alumni_hidden', MentorAvailabilityStatus::Available, false, true, ['Backend', 'API'], ['Thiết kế backend']],
            ['alumni_full', MentorAvailabilityStatus::Full, true, true, ['Data', 'Internship'], ['Chuẩn bị thực tập']],
            ['alumni_revoked', MentorAvailabilityStatus::Hidden, false, false, ['Management'], ['Mentoring']],
            ['advisor_mentor', MentorAvailabilityStatus::Available, true, true, ['Nghiên cứu khoa học', 'AI', 'Backend'], ['Định hướng học thuật', 'Chọn đề tài']],
            ['advisor_paused', MentorAvailabilityStatus::Paused, true, true, ['Data', 'AI'], ['Định hướng nghiên cứu']],
            ['advisor_hidden', MentorAvailabilityStatus::Available, false, true, ['Psychology'], ['Tư vấn học tập']],
        ] as [$userKey, $availability, $visible, $active, $expertise, $helpTopics]) {
            $this->mentorProfiles[$userKey] = $this->mentorProfile($this->users[$userKey], $availability, $visible, $active, $expertise, $helpTopics);
        }

        if (config('mentor.enable_student_exceptional_mentors')) {
            $this->mentorProfiles['student_peermentor'] = $this->mentorProfile(
                $this->users['student_peermentor'],
                MentorAvailabilityStatus::Available,
                true,
                true,
                ['Giải tích', 'Cấu trúc dữ liệu', 'Kinh nghiệm học tập'],
                ['Ôn tập môn khó', 'Kinh nghiệm học tập']
            );
        }
    }

    private function seedMentorRequests(): void
    {
        $submitted = $this->mentorRequest($this->users['student'], $this->mentorProfiles['alumni_mentor'], MentorRequestStatus::Submitted, 'Review CV thực tập Frontend', MentorUrgency::Normal);
        $accepted = $this->mentorRequest($this->users['student2'], $this->mentorProfiles['advisor_mentor'], MentorRequestStatus::Accepted, 'Chọn đề tài nghiên cứu AI', MentorUrgency::High, [
            'mentor_response' => 'Thầy có thể hỗ trợ em định hình phạm vi đề tài.',
            'accepted_at' => now()->subDays(2),
        ]);
        $needMoreInfo = $this->mentorRequest($this->users['student'], $this->mentorProfiles['advisor_mentor'], MentorRequestStatus::NeedMoreInfo, 'Xây dựng lộ trình nghiên cứu Backend', MentorUrgency::Normal, [
            'more_info_question' => 'Em có thể mô tả rõ hơn mục tiêu nghiên cứu không?',
        ]);
        $declined = $this->mentorRequest($this->users['student2'], $this->mentorProfiles['alumni_mentor'], MentorRequestStatus::Declined, 'Tư vấn chuyển hướng sang Product', MentorUrgency::Low, [
            'decline_reason' => 'Hiện tại mentor không phù hợp với chủ đề này.',
            'declined_at' => now()->subDay(),
        ]);
        $cancelled = $this->mentorRequest($this->users['student_cntt'], $this->mentorProfiles['alumni_paused'], MentorRequestStatus::Cancelled, 'Xin góp ý portfolio cá nhân', MentorUrgency::Normal);
        $completed = $this->mentorRequest($this->users['student2'], $this->mentorProfiles['alumni_paused'], MentorRequestStatus::Completed, 'Review kế hoạch thực tập hè', MentorUrgency::Normal, [
            'accepted_at' => now()->subDays(10),
            'completed_at' => now()->subDay(),
        ]);
        $reported = $this->mentorRequest($this->users['student_math'], $this->mentorProfiles['advisor_paused'], MentorRequestStatus::Reported, 'Báo cáo tương tác cố vấn', MentorUrgency::Normal);
        $closed = $this->mentorRequest($this->users['student_english'], $this->mentorProfiles['advisor_mentor'], MentorRequestStatus::Closed, 'Tổng kết định hướng học thuật', MentorUrgency::Low);

        foreach ([$submitted, $needMoreInfo, $declined, $cancelled, $reported, $closed] as $request) {
            $request->update(['conversation_id' => null]);
        }

        $this->conversationForAcceptedRequest($accepted, [
            [$this->users['student2'], 'Em muốn làm đề tài AI nhưng chưa biết thu hẹp phạm vi.'],
            [$this->users['advisor_mentor'], 'Mình bắt đầu từ dữ liệu, mục tiêu học thuật và thời gian em có nhé.'],
        ]);

        $this->conversationForAcceptedRequest($completed, [
            [$this->users['student2'], 'Em gửi mentor kế hoạch thực tập hè của em.'],
            [$this->users['alumni_paused'], 'Kế hoạch ổn, em nên bổ sung portfolio và lịch apply cụ thể.'],
        ]);

        MentorFeedback::updateOrCreate(
            ['mentor_request_id' => $completed->id],
            [
                'student_id' => $completed->student_id,
                'mentor_id' => $completed->mentor_id,
                'helpfulness_level' => MentorFeedbackLevel::Helpful,
                'feedback_text' => 'Mentor góp ý rõ ràng, giúp em biết cần chuẩn bị gì trước khi apply.',
                'is_private' => true,
            ]
        );

        foreach (['alumni_hidden', 'alumni_full', 'advisor_paused', 'advisor_hidden'] as $mentorKey) {
            $this->mentorRequest($this->users['limit_student'], $this->mentorProfiles[$mentorKey], MentorRequestStatus::Submitted, 'Pending limit demo '.$mentorKey, MentorUrgency::Normal);
        }
    }

    private function seedSafetyArtifacts(): void
    {
        BlockedUser::updateOrCreate(
            [
                'blocker_id' => $this->users['alumni_mentor']->id,
                'blocked_id' => $this->users['blocked_student']->id,
            ],
            [
                'reason' => 'Demo block for mentor UAT.',
                'source_type' => 'mentor_profile',
                'source_id' => $this->mentorProfiles['alumni_mentor']->id,
            ]
        );

        $reportedRequest = MentorRequest::where('status', MentorRequestStatus::Reported->value)->first();
        if (! $reportedRequest) {
            return;
        }

        Report::updateOrCreate(
            [
                'reporter_id' => $reportedRequest->student_id,
                'target_type' => 'mentor_request',
                'target_id' => $reportedRequest->id,
            ],
            [
                'reason' => ReportReason::OTHER,
                'description' => 'Demo report cho mentor request trong UAT.',
                'status' => ReportStatus::PENDING,
            ]
        );
    }

    private function seedNotifications(): void
    {
        $pendingAccess = MentorAccessRequest::where('status', MentorAccessStatus::Submitted->value)->first();
        if ($pendingAccess) {
            $this->notification($this->users['mentor_manager'], 'mentor_access_submitted', [
                'type' => 'mentor_access_submitted',
                'mentor_access_request_id' => $pendingAccess->id,
                'title' => 'Yêu cầu trở thành Mentor mới',
                'body' => $pendingAccess->user->name.' đã gửi yêu cầu trở thành mentor.',
                'action_url' => route('admin.mentors.detail', $pendingAccess->id),
                'demo_seed' => true,
            ]);
        }

        foreach (MentorRequest::with(['student', 'mentor'])->get() as $request) {
            match ($request->status) {
                MentorRequestStatus::Submitted => $this->notification($request->mentor, 'mentor_request_submitted_'.$request->id, [
                    'type' => 'mentor_request_submitted',
                    'mentor_request_id' => $request->id,
                    'title' => 'Yêu cầu cố vấn mới',
                    'body' => $request->student->name.' đã gửi yêu cầu cố vấn về: '.$request->topic,
                    'action_url' => route('mentor.requests.show', $request),
                    'demo_seed' => true,
                ]),
                MentorRequestStatus::Accepted => $this->notification($request->student, 'mentor_request_accepted_'.$request->id, [
                    'type' => 'mentor_request_accepted',
                    'mentor_request_id' => $request->id,
                    'title' => 'Yêu cầu cố vấn đã được chấp nhận',
                    'body' => $request->mentor->name.' đã chấp nhận yêu cầu cố vấn của bạn.',
                    'action_url' => route('mentor.requests.show', $request),
                    'demo_seed' => true,
                ]),
                MentorRequestStatus::Declined => $this->notification($request->student, 'mentor_request_declined_'.$request->id, [
                    'type' => 'mentor_request_declined',
                    'mentor_request_id' => $request->id,
                    'title' => 'Yêu cầu cố vấn đã bị từ chối',
                    'body' => $request->mentor->name.' đã từ chối yêu cầu cố vấn.',
                    'action_url' => route('mentor.requests.show', $request),
                    'demo_seed' => true,
                ]),
                MentorRequestStatus::NeedMoreInfo => $this->notification($request->student, 'mentor_request_more_info_'.$request->id, [
                    'type' => 'mentor_request_more_info',
                    'mentor_request_id' => $request->id,
                    'title' => 'Mentor cần thêm thông tin',
                    'body' => $request->mentor->name.' cần thêm thông tin để xem xét yêu cầu.',
                    'action_url' => route('mentor.requests.show', $request),
                    'demo_seed' => true,
                ]),
                default => null,
            };
        }
    }

    private function seedAuditLogs(): void
    {
        foreach ([
            ['mentor_access.approved', 'mentor_access_request', MentorAccessRequest::where('status', MentorAccessStatus::Approved->value)->value('id')],
            ['mentor_access.rejected', 'mentor_access_request', MentorAccessRequest::where('status', MentorAccessStatus::Rejected->value)->value('id')],
            ['mentor_access.need_more_info', 'mentor_access_request', MentorAccessRequest::where('status', MentorAccessStatus::NeedMoreInfo->value)->value('id')],
            ['mentor_access.revoked', 'mentor_access_request', MentorAccessRequest::where('status', MentorAccessStatus::Revoked->value)->value('id')],
            ['mentor_request.accepted', 'mentor_request', MentorRequest::where('status', MentorRequestStatus::Accepted->value)->value('id')],
            ['mentor_request.declined', 'mentor_request', MentorRequest::where('status', MentorRequestStatus::Declined->value)->value('id')],
        ] as [$action, $targetType, $targetId]) {
            if (! $targetId) {
                continue;
            }

            AuditLog::updateOrCreate(
                ['action_key' => $action, 'target_type' => $targetType, 'target_id' => $targetId],
                [
                    'actor_id' => $this->users['mentor_manager']->id,
                    'actor_type' => 'user',
                    'before_values' => ['demo' => true],
                    'after_values' => ['demo' => true],
                    'reason' => 'Demo mentor lifecycle audit log.',
                    'metadata' => ['demo_seed' => true],
                    'created_at' => now(),
                ]
            );
        }
    }

    /**
     * @param  array<int, string>  $expertise
     */
    private function mentorAccess(User $user, string $roleContext, MentorAccessStatus $status, array $expertise): MentorAccessRequest
    {
        $reviewed = ! in_array($status, [MentorAccessStatus::Draft, MentorAccessStatus::Submitted], true);

        return MentorAccessRequest::updateOrCreate(
            ['user_id' => $user->id, 'requested_role_context' => $roleContext],
            [
                'status' => $status,
                'motivation' => 'Tôi muốn chia sẻ kinh nghiệm học tập và nghề nghiệp với sinh viên HCMUE.',
                'experience_summary' => 'Có kinh nghiệm mentor theo chủ đề: '.implode(', ', $expertise).'.',
                'expertise_topics' => $expertise,
                'career_paths' => ['Giáo dục', 'Công nghệ', 'Nghiên cứu'],
                'reviewed_by' => $reviewed ? $this->users['mentor_manager']->id : null,
                'reviewed_at' => $reviewed ? now()->subDays(3) : null,
                'review_reason' => match ($status) {
                    MentorAccessStatus::Rejected => 'Chưa đủ thông tin kinh nghiệm mentor.',
                    MentorAccessStatus::NeedMoreInfo => 'Cần bổ sung minh chứng hoặc mô tả lĩnh vực hỗ trợ.',
                    MentorAccessStatus::Revoked => 'Demo revoked mentor access for safety UAT.',
                    default => $reviewed ? 'Demo reviewed mentor access.' : null,
                },
                'admin_notes' => $reviewed ? 'Demo mentor access lifecycle state.' : null,
            ]
        );
    }

    /**
     * @param  array<int, string>  $expertise
     * @param  array<int, string>  $helpTopics
     */
    private function mentorProfile(User $user, MentorAvailabilityStatus $availability, bool $visible, bool $active, array $expertise, array $helpTopics): MentorProfile
    {
        return MentorProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'headline' => 'Mentor hỗ trợ '.implode(', ', array_slice($expertise, 0, 2)),
                'bio' => 'Mentor demo có kinh nghiệm hỗ trợ sinh viên HCMUE theo yêu cầu có cấu trúc.',
                'expertise_topics' => $expertise,
                'career_paths' => ['Education', 'Technology', 'Research'],
                'skills' => ['Mentoring', 'Feedback', 'Career planning'],
                'help_topics' => $helpTopics,
                'preferred_request_types' => ['CV review', 'Career direction', 'Academic guidance'],
                'availability_status' => $availability,
                'mentor_visibility' => $visible,
                'max_pending_requests' => $availability === MentorAvailabilityStatus::Full ? 1 : 5,
                'max_monthly_accepts' => 8,
                'response_expectation_text' => 'Phản hồi trong 2-3 ngày làm việc.',
                'office_hours_text' => 'Tối thứ 3 và thứ 5, 20:00-21:30.',
                'is_active' => $active,
                'approved_at' => now()->subDays(5),
                'approved_by' => $this->users['mentor_manager']->id,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function mentorRequest(User $student, MentorProfile $mentorProfile, MentorRequestStatus $status, string $topic, MentorUrgency $urgency, array $extra = []): MentorRequest
    {
        return MentorRequest::updateOrCreate(
            ['student_id' => $student->id, 'mentor_id' => $mentorProfile->user_id, 'topic' => $topic],
            array_merge([
                'mentor_profile_id' => $mentorProfile->id,
                'goal' => 'Muốn nhận góp ý rõ ràng để có bước tiếp theo trong học tập hoặc nghề nghiệp.',
                'question' => 'Em nên chuẩn bị gì trước tiên để đạt mục tiêu này?',
                'urgency' => $urgency,
                'context' => 'Demo request cho manual UAT mentor workflow.',
                'expected_outcome' => 'Có danh sách việc cần làm sau buổi trao đổi.',
                'status' => $status,
            ], $extra)
        );
    }

    /**
     * @param  array<int, array{0: User, 1: string}>  $messages
     */
    private function conversationForAcceptedRequest(MentorRequest $request, array $messages): Conversation
    {
        $student = $request->student;
        $mentor = $request->mentor;
        $lowId = min($student->id, $mentor->id);
        $highId = max($student->id, $mentor->id);

        $conversation = Conversation::updateOrCreate(
            [
                'conversation_type' => ConversationType::DIRECT->value,
                'direct_user_low_id' => $lowId,
                'direct_user_high_id' => $highId,
            ],
            [
                'status' => ConversationStatus::ACTIVE->value,
                'created_by' => $mentor->id,
                'mentor_request_id' => $request->id,
                'last_message_at' => now(),
            ]
        );

        foreach ([$student, $mentor] as $participant) {
            ConversationParticipant::updateOrCreate(
                ['conversation_id' => $conversation->id, 'user_id' => $participant->id],
                ['participant_role' => 'member', 'status' => 'active', 'joined_at' => now()]
            );
        }

        $lastMessage = Message::updateOrCreate(
            [
                'conversation_id' => $conversation->id,
                'message_type' => MessageType::SYSTEM->value,
                'body' => 'Yêu cầu cố vấn đã được chấp nhận. Hai bên có thể bắt đầu trao đổi về: '.$request->topic.'.',
            ],
            ['sender_id' => $mentor->id, 'status' => MessageStatus::SENT->value]
        );

        foreach ($messages as [$sender, $body]) {
            $lastMessage = Message::updateOrCreate(
                ['conversation_id' => $conversation->id, 'sender_id' => $sender->id, 'body' => $body],
                ['message_type' => MessageType::TEXT->value, 'status' => MessageStatus::SENT->value]
            );
        }

        $conversation->update(['last_message_id' => $lastMessage->id, 'last_message_at' => $lastMessage->created_at ?? now()]);
        $request->update(['conversation_id' => $conversation->id]);

        return $conversation;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function notification(User $user, string $key, array $data): void
    {
        DB::table('notifications')->updateOrInsert(
            ['id' => $this->stableUuid('ueconnect.demo.mentor.'.$user->email.'.'.$key)],
            [
                'type' => 'App\\Notifications\\DemoMentorNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function stableUuid(string $seed): string
    {
        $hash = md5($seed);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 12, 4),
            substr($hash, 16, 4),
            substr($hash, 20, 12)
        );
    }

    private function printGuide(): void
    {
        $this->command->newLine();
        $this->command->info('UEConnect mentor UAT data seeded. Use password for every account.');
    }
}
