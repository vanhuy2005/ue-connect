<?php

namespace App\Enums;

enum CareerPositionItemType: string
{
    case COURSE = 'course';
    case SKILL = 'skill';
    case PROJECT = 'project';
    case RESOURCE = 'resource';
    case CERTIFICATE = 'certificate';
    case EXPERIENCE = 'experience';
    case ADVICE = 'advice';
    case CUSTOM = 'custom';
}
