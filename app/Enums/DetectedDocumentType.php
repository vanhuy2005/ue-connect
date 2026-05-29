<?php

namespace App\Enums;

enum DetectedDocumentType: string
{
    case StudentCard = 'student_card';
    case Unknown = 'unknown';
}
