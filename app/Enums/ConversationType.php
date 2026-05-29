<?php

namespace App\Enums;

enum ConversationType: string
{
    case DIRECT = 'direct';
    case MENTOR_REQUEST = 'mentor_request';
    case COMMUNITY_CHAT = 'community_chat';
    case SYSTEM = 'system';
}
