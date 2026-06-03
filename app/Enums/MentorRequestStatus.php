<?php

namespace App\Enums;

enum MentorRequestStatus: string
{
    case Submitted = 'submitted';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case NeedMoreInfo = 'need_more_info';
    case UpdatedByStudent = 'updated_by_student';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case Reported = 'reported';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Đã gửi',
            self::Accepted => 'Đã chấp nhận',
            self::Declined => 'Đã từ chối',
            self::NeedMoreInfo => 'Cần thêm thông tin',
            self::UpdatedByStudent => 'Đã cập nhật (Sinh viên)',
            self::Cancelled => 'Đã hủy',
            self::Completed => 'Hoàn thành',
            self::Reported => 'Đã báo cáo',
            self::Closed => 'Đã đóng',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Submitted => 'blue',
            self::Accepted => 'emerald',
            self::Declined => 'red',
            self::NeedMoreInfo => 'amber',
            self::UpdatedByStudent => 'blue',
            self::Cancelled => 'slate',
            self::Completed => 'green',
            self::Reported => 'orange',
            self::Closed => 'slate',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Submitted, self::Accepted, self::NeedMoreInfo, self::UpdatedByStudent]);
    }

    public function isPending(): bool
    {
        return in_array($this, [self::Submitted, self::NeedMoreInfo, self::UpdatedByStudent]);
    }
}
