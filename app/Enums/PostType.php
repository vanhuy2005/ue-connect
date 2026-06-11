<?php

namespace App\Enums;

enum PostType: string
{
    case STANDARD = 'standard';
    case EXPERIENCE = 'experience';
    case CAREER_INSIGHT = 'career_insight';
    case OPPORTUNITY = 'opportunity';
}
