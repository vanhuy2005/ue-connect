<?php

namespace App\Enums;

enum CommunityJoinRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Đang chờ',
            self::Approved => 'Đã chấp nhận',
            self::Rejected => 'Đã từ chối',
            self::Cancelled => 'Đã hủy',
            self::Expired => 'Đã hết hạn',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Approved => 'green',
            self::Rejected => 'red',
            self::Cancelled => 'gray',
            self::Expired => 'slate',
        };
    }
}
