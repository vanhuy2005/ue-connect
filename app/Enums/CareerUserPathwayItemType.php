<?php

namespace App\Enums;

enum CareerUserPathwayItemType: string
{
    case SEMESTER_NOTE = 'semester_note';
    case COURSE = 'course';
    case PROJECT = 'project';
    case SKILL = 'skill';
    case RESOURCE = 'resource';
    case INTERNSHIP = 'internship';
    case MISTAKE = 'mistake';
    case ADVICE = 'advice';
    case MILESTONE = 'milestone';
    case CUSTOM = 'custom';
}
