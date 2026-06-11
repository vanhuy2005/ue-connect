<?php

namespace App\Actions\Mentor;

use App\Enums\MentorAccessStatus;
use App\Models\MentorAccessRequest;
use App\Models\MentorProfile;
use App\Models\User;
use App\Notifications\Mentor\MentorAccessRevokedNotification;
use App\Services\AuditService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RevokeMentorAccessAction
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * Admin revokes a mentor's access and deactivates their profile.
     *
     * @param  array{reason: string, admin_notes?: ?string}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $admin, MentorProfile $mentorProfile, array $data): void
    {
        Gate::forUser($admin)->authorize('revoke', $mentorProfile);

        $before = $mentorProfile->toArray();

        DB::transaction(function () use ($admin, $mentorProfile, $data, $before) {
            // 1. Deactivate mentor profile (removes from discovery)
            $mentorProfile->update([
                'is_active' => false,
                'mentor_visibility' => false,
            ]);

            // 2. Update the most recent approved access request to revoked
            MentorAccessRequest::where('user_id', $mentorProfile->user_id)
                ->where('status', MentorAccessStatus::Approved)
                ->latest()
                ->first()
                ?->update([
                    'status' => MentorAccessStatus::Revoked,
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => now(),
                    'review_reason' => $data['reason'],
                    'admin_notes' => $data['admin_notes'] ?? null,
                ]);

            if ($mentorProfile->user?->hasDirectPermission('mentor_access')) {
                $mentorProfile->user->revokePermissionTo('mentor_access');
            }

            // Notify user
            $mentorProfile->user->notify(new MentorAccessRevokedNotification($data['reason'] ?? null));

            // 3. Audit log
            $this->audit->log([
                'action' => 'mentor_access_revoked',
                'target_type' => 'mentor_profile',
                'target_id' => $mentorProfile->id,
                'before_values' => $before,
                'after_values' => $mentorProfile->toArray(),
                'reason' => $data['reason'],
            ]);
        });
    }
}
