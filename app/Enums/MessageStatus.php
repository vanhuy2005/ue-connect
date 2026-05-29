<?php

namespace App\Enums;

enum MessageStatus: string
{
    case SENDING = 'sending';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case READ = 'read';
    case EDITED = 'edited';
    case DELETED = 'deleted';
    case FAILED = 'failed';
    case HIDDEN_BY_MODERATION = 'hidden_by_moderation';
    case REMOVED_BY_MODERATION = 'removed_by_moderation';
}
