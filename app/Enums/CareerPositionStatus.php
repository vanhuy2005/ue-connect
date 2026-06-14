<?php

namespace App\Enums;

enum CareerPositionStatus: string
{
    case DRAFT = 'draft';
    case REVIEWING = 'reviewing';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case HIDDEN_BY_MODERATION = 'hidden_by_moderation';
    case REJECTED = 'rejected';
}
