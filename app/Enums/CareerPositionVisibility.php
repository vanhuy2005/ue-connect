<?php

namespace App\Enums;

enum CareerPositionVisibility: string
{
    case PUBLIC = 'public';
    case UNLISTED = 'unlisted';
    case PRIVATE = 'private';
    case HIDDEN = 'hidden';
}
