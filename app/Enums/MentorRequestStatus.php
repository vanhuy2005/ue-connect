<?php

namespace App\Enums;

enum MentorRequestStatus: string
{
    case Submitted = 'submitted';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case NeedMoreInfo = 'need_more_info';
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
            self::Cancelled => 'slate',
            self::Completed => 'green',
            self::Reported => 'orange',
            self::Closed => 'slate',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Submitted, self::Accepted, self::NeedMoreInfo]);
    }

    public function isPending(): bool
    {
        return $this === self::Submitted || $this === self::NeedMoreInfo;
    }
}
