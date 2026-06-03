<?php

namespace App\Enums;

enum MentorUrgency: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Không gấp',
            self::Normal => 'Bình thường',
            self::High => 'Gấp',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Low => 'slate',
            self::Normal => 'blue',
            self::High => 'red',
        };
    }
}
