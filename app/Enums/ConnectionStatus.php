<?php

namespace App\Enums;

enum ConnectionStatus: string
{
    case ACTIVE = 'active';
    case BLOCKED = 'blocked';
    case REMOVED = 'removed';
    case RESTRICTED = 'restricted';
}
