<?php

namespace App\Enums;

enum CareerUserPathwayVisibility: string
{
    case PUBLIC = 'public';
    case UNLISTED = 'unlisted';
    case PRIVATE = 'private';
    case HIDDEN = 'hidden';
}
