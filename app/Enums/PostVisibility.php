<?php

namespace App\Enums;

enum PostVisibility: string
{
    case VERIFIED_USERS = 'verified_users';
    case CONNECTIONS_ONLY = 'connections_only';
    case COMMUNITY = 'community';
    case PRIVATE = 'private';
    case HIDDEN_BY_SYSTEM = 'hidden_by_system';
}
