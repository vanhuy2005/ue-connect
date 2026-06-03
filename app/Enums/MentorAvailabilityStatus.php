<?php

namespace App\Enums;

enum MentorAvailabilityStatus: string
{
    case Available = 'available';
    case Paused = 'paused';
    case Full = 'full';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Đang nhận yêu cầu',
            self::Paused => 'Tạm dừng',
            self::Full => 'Đã đầy',
            self::Hidden => 'Ẩn',
        };
    }

    public function isDiscoverable(): bool
    {
        return $this === self::Available;
    }
}
