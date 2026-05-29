<?php

namespace App\Enums;

enum GreetingStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
    case BLOCKED = 'blocked';
    case REPORTED = 'reported';
}
