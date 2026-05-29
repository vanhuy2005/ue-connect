<?php

namespace App\Enums;

enum ConversationStatus: string
{
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';
    case BLOCKED = 'blocked';
    case RESTRICTED = 'restricted';
    case DELETED = 'deleted';
}
