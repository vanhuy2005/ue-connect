<?php

namespace App\Enums;

enum CommentStatus: string
{
    case PUBLISHED = 'published';
    case EDITED = 'edited';
    case HIDDEN_BY_MODERATION = 'hidden_by_moderation';
    case DELETED_BY_OWNER = 'deleted_by_owner';
    case DELETED_BY_MODERATION = 'deleted_by_moderation';
}
