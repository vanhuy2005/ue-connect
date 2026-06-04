<?php

namespace App\Actions\Community;

use App\Enums\CommunityResourceStatus;
use App\Models\CommunityResource;
use App\Models\User;
use App\Notifications\Community\CommunityResourceApprovedNotification;
use App\Notifications\Community\CommunityResourceRejectedNotification;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewCommunityResourceAction
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * @param  array{action: string, reason?: string}  $data
     */
    public function execute(User $actor, CommunityResource $resource, array $data): void
    {
        $action = $data['action'] ?? '';

        if (! in_array($action, ['approve', 'reject'])) {
            throw ValidationException::withMessages([
                'action' => 'Hành động không hợp lệ.',
            ]);
        }

        if ($action === 'reject' && empty(trim($data['reason'] ?? ''))) {
            throw ValidationException::withMessages([
                'reason' => 'Vui lòng cung cấp lý do từ chối tài nguyên.',
            ]);
        }

        if (! in_array($resource->status?->value, [
            CommunityResourceStatus::PendingReview->value,
            CommunityResourceStatus::Draft->value,
        ])) {
            throw ValidationException::withMessages([
                'resource' => 'Tài nguyên này không ở trạng thái có thể xét duyệt.',
            ]);
        }

        DB::transaction(function () use ($actor, $resource, $action, $data) {
            $before = $resource->toArray();
            $community = $resource->community;

            if ($action === 'approve') {
                $resource->update([
                    'status' => CommunityResourceStatus::Published->value,
                    'approved_by' => $actor->id,
                    'approved_at' => now(),
                    'rejection_reason' => null,
                ]);

                if ($community) {
                    $community->increment('resource_count');
                }

                $resource->submitter?->notify(
                    new CommunityResourceApprovedNotification($resource)
                );
            } else {
                $resource->update([
                    'status' => CommunityResourceStatus::Rejected->value,
                    'rejection_reason' => $data['reason'],
                ]);

                $resource->submitter?->notify(
                    new CommunityResourceRejectedNotification($resource, $data['reason'])
                );
            }

            $this->audit->log([
                'action' => $action === 'approve' ? 'approve_community_resource' : 'reject_community_resource',
                'target_type' => 'community_resource',
                'target_id' => $resource->id,
                'context_type' => 'community',
                'context_id' => $resource->community_id,
                'before_values' => $before,
                'after_values' => $resource->fresh()?->toArray(),
                'reason' => $data['reason'] ?? null,
            ]);
        });
    }
}
