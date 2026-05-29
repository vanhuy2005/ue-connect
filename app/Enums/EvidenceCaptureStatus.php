<?php

namespace App\Enums;

enum EvidenceCaptureStatus: string
{
    case Started = 'started';
    case Completed = 'completed';
    case Expired = 'expired';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}
