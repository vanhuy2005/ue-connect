<?php

namespace App\Enums;

enum CareerPositionImportanceLevel: string
{
    case OPTIONAL = 'optional';
    case RECOMMENDED = 'recommended';
    case IMPORTANT = 'important';
    case CORE = 'core';
}
