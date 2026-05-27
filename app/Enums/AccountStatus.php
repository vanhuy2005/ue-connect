<?php

namespace App\Enums;

enum AccountStatus: string
{
    case REGISTERED = 'registered';
    case PENDING_VERIFICATION = 'pending_verification';
    case ACTIVE = 'active';
    case PROFILE_INCOMPLETE = 'profile_incomplete';
    case RESTRICTED = 'restricted';
    case SUSPENDED = 'suspended';
    case BANNED = 'banned';
    case DELETED = 'deleted';
}
