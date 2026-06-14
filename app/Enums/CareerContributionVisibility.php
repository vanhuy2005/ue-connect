<?php

namespace App\Enums;

enum CareerContributionVisibility: string
{
    case PUBLIC = 'public';
    case COMMUNITY = 'community';
    case PRIVATE = 'private';
    case HIDDEN = 'hidden';
}
