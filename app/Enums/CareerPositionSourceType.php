<?php

namespace App\Enums;

enum CareerPositionSourceType: string
{
    case OFFICIAL_COURSE = 'official_course';
    case COMMUNITY_CONTRIBUTION = 'community_contribution';
    case USER_CREATED = 'user_created';
    case ADMIN_CURATED = 'admin_curated';
}
