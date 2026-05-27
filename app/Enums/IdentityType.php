<?php

namespace App\Enums;

enum IdentityType: string
{
    case CURRENT_STUDENT = 'current_student';
    case TEACHER_ADVISOR = 'teacher_advisor';
    case ALUMNI = 'alumni';
    case EXTERNAL_MENTOR = 'external_mentor';
}
