<?php

namespace App\Enums;

enum MentorFeedbackLevel: string
{
    case Helpful = 'helpful';
    case SomewhatHelpful = 'somewhat_helpful';
    case NotHelpful = 'not_helpful';

    public function label(): string
    {
        return match ($this) {
            self::Helpful => 'Hữu ích',
            self::SomewhatHelpful => 'Khá hữu ích',
            self::NotHelpful => 'Chưa hữu ích',
        };
    }
}
