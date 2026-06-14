<?php

namespace App\Enums;

enum SourceExtractionStatus: string
{
    case PENDING = 'pending';
    case EXTRACTED = 'extracted';
    case FAILED = 'failed';
    case IGNORED = 'ignored';
}
