<?php

namespace App\Actions\Community;

use App\Enums\CommunityStatus;
use App\Models\Community;
use App\Models\User;
use App\Notifications\Community\CommunitySuspendedNotification;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SuspendCommunityAction
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * @param  array{reason: string, safe_reason: string, notify_members?: bool}  $data
     */
    public function execute(User $actor, Community $community, array $data): void
    {
        $reason = $data['reason'] ?? '';
        $safeReason = $data['safe_reason'] ?? '';

        if (strlen(trim($reason)) < 10) {
            throw ValidationException::withMessages([
                'reason' => 'Lý do nội bộ phải có ít nhất 10 ký tự.',
            ]);
        }

        if (empty(trim($safeReason))) {
            throw ValidationException::withMessages([
                'safe_reason' => 'Vui lòng cung cấp thông báo công khai (user-safe) về việc tạm khóa.',
            ]);
        }

        if ($community->isSuspended()) {
            throw ValidationException::withMessages([
                'community' => 'Cộng đồng này đã bị tạm khóa.',
            ]);
        }

        DB::transaction(function () use ($community, $reason, $safeReason, $data) {
            $before = $community->toArray();

            $community->update([
                'status' => CommunityStatus::Suspended->value,
                'suspended_reason' => $reason,
                'suspended_safe_reason' => $safeReason,
                'suspended_at' => now(),
            ]);

            // Optionally notify members (only show safe reason)
            if ($data['notify_members'] ?? false) {
                $community->activeMembers()
                    ->with('user')
                    ->get()
                    ->each(fn ($member) => $member->user?->notify(
                        new CommunitySuspendedNotification($community, $safeReason)
                    ));
            }

            $this->audit->log([
                'action' => 'suspend_community',
                'target_type' => 'community',
                'target_id' => $community->id,
                'before_values' => $before,
                'after_values' => $community->fresh()?->toArray(),
                'reason' => $reason,
            ]);
        });
    }
}
