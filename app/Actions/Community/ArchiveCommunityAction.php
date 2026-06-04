<?php

namespace App\Actions\Community;

use App\Enums\CommunityStatus;
use App\Models\Community;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ArchiveCommunityAction
{
    public function __construct(private readonly AuditService $audit) {}

    public function execute(User $actor, Community $community, string $reason): void
    {
        if (empty(trim($reason))) {
            throw ValidationException::withMessages([
                'reason' => 'Vui lòng cung cấp lý do lưu trữ cộng đồng.',
            ]);
        }

        if ($community->isArchived()) {
            throw ValidationException::withMessages([
                'community' => 'Cộng đồng này đã được lưu trữ.',
            ]);
        }

        DB::transaction(function () use ($community, $reason) {
            $before = $community->toArray();

            $community->update([
                'status' => CommunityStatus::Archived->value,
                'archived_at' => now(),
            ]);

            $this->audit->log([
                'action' => 'archive_community',
                'target_type' => 'community',
                'target_id' => $community->id,
                'before_values' => $before,
                'after_values' => $community->fresh()?->toArray(),
                'reason' => $reason,
            ]);
        });
    }
}
