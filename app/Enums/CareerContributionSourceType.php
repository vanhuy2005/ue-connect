<?php

namespace App\Enums;

enum CareerContributionSourceType: string
{
    case COMMUNITY_CONTRIBUTED = 'community_contributed';
    case SENIOR_EXPERIENCE = 'senior_experience';
    case ALUMNI_EXPERIENCE = 'alumni_experience';
    case ADMIN_CURATED = 'admin_curated';
    case AI_SUGGESTED = 'ai_suggested';
}
