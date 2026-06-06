<?php

namespace App\Enums;

enum PostType: string
{
    case STANDARD = 'standard';
    case EXPERIENCE_SHARE = 'experience_share';
    case MENTOR_INSIGHT = 'mentor_insight';
    case OPPORTUNITY = 'opportunity';
}
