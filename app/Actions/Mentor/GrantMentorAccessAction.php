<?php

namespace App\Actions\Mentor;

use App\Enums\MentorAccessStatus;
use App\Enums\MentorAvailabilityStatus;
use App\Models\MentorAccessRequest;
use App\Models\MentorProfile;
use App\Models\User;
use App\Notifications\Mentor\MentorAccessApprovedNotification;
use App\Services\AuditService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class GrantMentorAccessAction
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * Admin approves a mentor access request and creates/activates the mentor profile.
     *
     * @param  array{reason?: ?string, admin_notes?: ?string}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $admin, MentorAccessRequest $request, array $data = []): MentorProfile
    {
        Gate::forUser($admin)->authorize('review', $request);

        $before = $request->toArray();

        return DB::transaction(function () use ($admin, $request, $data, $before) {
            // 1. Update access request status
            $request->status = MentorAccessStatus::Approved;
            $request->reviewed_by = $admin->id;
            $request->reviewed_at = now();
            $request->review_reason = $data['reason'] ?? null;

            if (isset($data['admin_notes'])) {
                $request->admin_notes = $data['admin_notes'];
            }

            $request->save();
            Permission::findOrCreate('mentor_access', 'web');
            $request->user->givePermissionTo('mentor_access');

            // 2. Create or reactivate mentor profile
            $mentorProfile = MentorProfile::updateOrCreate(
                ['user_id' => $request->user_id],
                [
                    'is_active' => true,
                    'availability_status' => MentorAvailabilityStatus::Available,
                    'mentor_visibility' => true,
                    'max_pending_requests' => config('mentor.default_max_pending_requests', 5),
                    'approved_at' => now(),
                    'approved_by' => $admin->id,
                    'expertise_topics' => $request->expertise_topics ?? [],
                    'career_paths' => $request->career_paths,
                    'headline' => $request->headline,
                    'bio' => $request->bio,
                    'help_topics' => $request->help_topics ?? [],
                    'preferred_request_types' => $request->preferred_request_types ?? [],
                    'skills' => $request->skills ?? [],
                    'response_expectation_text' => $request->response_expectation_text,
                    'office_hours_text' => $request->office_hours_text,
                ]
            );

            $mentorProfile->is_public_ready = $mentorProfile->checkIfPublicReady();
            $mentorProfile->save();

            // 3. Notify applicant
            $request->user->notify(new MentorAccessApprovedNotification($request, $mentorProfile));

            // 4. Audit log
            $this->audit->log([
                'action' => 'mentor_access_approved',
                'target_type' => 'mentor_access_request',
                'target_id' => $request->id,
                'before_values' => $before,
                'after_values' => $request->toArray(),
                'reason' => $data['reason'] ?? null,
            ]);

            return $mentorProfile;
        });
    }
}
