<?php

namespace App\Enums;

enum ReportReason: string
{
    case SPAM = 'spam';
    case HARASSMENT = 'harassment';
    case INAPPROPRIATE_CONTENT = 'inappropriate_content';
    case MISINFORMATION = 'misinformation';
    case PRIVACY_VIOLATION = 'privacy_violation';
    case OTHER = 'other';
}
