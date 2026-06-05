<?php

namespace App\Actions\Mentor;

use App\Enums\MentorAccessStatus;
use App\Models\MentorAccessRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class RequestMentorAccessAction
{
    /**
     * @return array<string, string>
     */
    public static function eligibleRoleContextsFor(User $user): array
    {
        $roleType = $user->profile?->role_type;

        if ($roleType === 'alumni' && in_array('alumni', config('mentor.eligible_role_contexts', []), true)) {
            return ['alumni' => 'Cựu sinh viên'];
        }

        if ($roleType === 'teacher' && in_array('teacher', config('mentor.eligible_role_contexts', []), true)) {
            return ['teacher' => 'Giảng viên'];
        }

        if ($roleType === 'student' && config('mentor.enable_student_exceptional_mentors')) {
            return ['exceptional_student' => 'Sinh viên nổi bật'];
        }

        return [];
    }

    /**
     * Submit a mentor access request.
     *
     * @param  array{requested_role_context: string, motivation: string, experience_summary?: ?string, expertise_topics?: array<string>, career_paths?: ?array<string>, portfolio_link?: ?string, availability_note?: ?string, policy_agreed?: ?bool, headline?: ?string, bio?: ?string, help_topics?: ?array<string>, preferred_request_types?: ?array<string>, skills?: ?array<string>, response_expectation_text?: ?string, office_hours_text?: ?string, evidence_media_id?: ?int}  $data
     *
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function execute(User $user, array $data): MentorAccessRequest
    {
        Gate::forUser($user)->authorize('create', MentorAccessRequest::class);

        if (! array_key_exists($data['requested_role_context'], self::eligibleRoleContextsFor($user))) {
            throw new \Exception('Vai trò mentor bạn chọn không phù hợp với hồ sơ hiện tại.');
        }

        // Block if there is already a pending/approved access request
        $existing = MentorAccessRequest::where('user_id', $user->id)
            ->whereIn('status', [
                MentorAccessStatus::Submitted->value,
                MentorAccessStatus::UnderReview->value,
                MentorAccessStatus::Approved->value,
                MentorAccessStatus::NeedMoreInfo->value,
            ])
            ->first();

        if ($existing) {
            throw new \Exception('Bạn đã có yêu cầu mentor đang chờ xử lý hoặc đã được duyệt.');
        }

        return MentorAccessRequest::create([
            'user_id' => $user->id,
            'requested_role_context' => $data['requested_role_context'],
            'status' => MentorAccessStatus::Submitted,
            'motivation' => $data['motivation'],
            'experience_summary' => $data['experience_summary'] ?? null,
            'expertise_topics' => $data['expertise_topics'] ?? [],
            'career_paths' => $data['career_paths'] ?? null,
            'portfolio_link' => $data['portfolio_link'] ?? null,
            'availability_note' => $data['availability_note'] ?? null,
            'policy_agreed' => $data['policy_agreed'] ?? false,
            'headline' => $data['headline'] ?? null,
            'bio' => $data['bio'] ?? null,
            'help_topics' => $data['help_topics'] ?? [],
            'preferred_request_types' => $data['preferred_request_types'] ?? [],
            'skills' => $data['skills'] ?? [],
            'response_expectation_text' => $data['response_expectation_text'] ?? null,
            'office_hours_text' => $data['office_hours_text'] ?? null,
            'evidence_media_id' => $data['evidence_media_id'] ?? null,
        ]);
    }
}
