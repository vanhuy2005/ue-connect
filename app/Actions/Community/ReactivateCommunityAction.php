<?php

namespace App\Actions\Community;

use App\Enums\CommunityStatus;
use App\Models\Community;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReactivateCommunityAction
{
    public function __construct(private readonly AuditService $audit) {}

    public function execute(User $actor, Community $community, ?string $reason = null): void
    {
        if (! $community->isSuspended()) {
            throw ValidationException::withMessages([
                'community' => 'Cộng đồng này hiện không bị tạm khóa.',
            ]);
        }

        DB::transaction(function () use ($community, $reason) {
            $before = $community->toArray();

            $community->update([
                'status' => CommunityStatus::Active->value,
                'suspended_reason' => null,
                'suspended_safe_reason' => null,
                'suspended_at' => null,
            ]);

            $this->audit->log([
                'action' => 'reactivate_community',
                'target_type' => 'community',
                'target_id' => $community->id,
                'before_values' => $before,
                'after_values' => $community->fresh()?->toArray(),
                'reason' => $reason,
            ]);
        });
    }
}
