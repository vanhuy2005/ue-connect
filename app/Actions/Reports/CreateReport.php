<?php

namespace App\Actions\Reports;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateReport
{
    /**
     * Create a report for a target model (post or comment).
     *
     * @param  array{reason: string, description?: string|null}  $data
     *
     * @throws ValidationException
     */
    public function execute(User $user, Model $target, array $data): Report
    {
        if (! $user->isActive()) {
            throw ValidationException::withMessages([
                'reporter_id' => 'Chỉ tài khoản đang hoạt động mới có thể thực hiện báo cáo.',
            ]);
        }

        // Validate user cannot report their own content
        if ($target->user_id === $user->id) {
            throw ValidationException::withMessages([
                'target' => 'Bạn không thể báo cáo nội dung do chính mình tạo ra.',
            ]);
        }

        $targetType = $target->getMorphClass();
        $targetId = $target->id;

        // Check for active duplicate report by the same user on the same target
        $existingReport = Report::where('reporter_id', $user->id)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('status', ReportStatus::PENDING)
            ->first();

        if ($existingReport) {
            throw ValidationException::withMessages([
                'target' => 'Bạn đã gửi báo cáo cho nội dung này và hệ thống đang xử lý.',
            ]);
        }

        return Report::create([
            'reporter_id' => $user->id,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'reason' => $data['reason'],
            'description' => $data['description'] ?? null,
            'status' => ReportStatus::PENDING->value,
        ]);
    }
}
