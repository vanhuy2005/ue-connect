<?php

namespace App\Enums;

enum CommunityEventRsvpStatus: string
{
    case Going = 'going';
    case Interested = 'interested';
    case Declined = 'declined';
    case Cancelled = 'cancelled';
    case Waitlisted = 'waitlisted';

    public function label(): string
    {
        return match ($this) {
            self::Going => 'Tham gia',
            self::Interested => 'Quan tâm',
            self::Declined => 'Không tham gia',
            self::Cancelled => 'Đã hủy',
            self::Waitlisted => 'Trong danh sách chờ',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Going => 'green',
            self::Interested => 'blue',
            self::Declined => 'gray',
            self::Cancelled => 'slate',
            self::Waitlisted => 'yellow',
        };
    }

    public function countsAsAttendee(): bool
    {
        return $this === self::Going;
    }
}
