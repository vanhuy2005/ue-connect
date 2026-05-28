<?php

namespace App\Enums;

enum PostVisibility: string
{
    case VERIFIED_USERS = 'verified_users';
    case CONNECTIONS_ONLY = 'connections_only';
    case COMMUNITY = 'community';
    case PRIVATE = 'private';
}
