<?php

namespace App\Enums;

enum MentorUrgency: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case TimeSensitive = 'time_sensitive';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Không gấp',
            self::Normal => 'Bình thường',
            self::High => 'Gấp',
            self::TimeSensitive => 'Có hạn định/Gấp',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Low => 'slate',
            self::Normal => 'blue',
            self::High => 'red',
            self::TimeSensitive => 'rose',
        };
    }
}
