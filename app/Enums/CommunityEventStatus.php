<?php

namespace App\Enums;

enum CommunityEventStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Nháp',
            self::Published => 'Đã đăng',
            self::Cancelled => 'Đã hủy',
            self::Completed => 'Đã kết thúc',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Published => 'green',
            self::Cancelled => 'red',
            self::Completed => 'blue',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Published;
    }
}
