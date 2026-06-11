<?php

namespace App\Enums;

enum ModerationStatus: string
{
    case NONE = 'none';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
}
