<?php

namespace App\Enums;

enum CareerContributionStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case PENDING_REVIEW = 'pending_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case HIDDEN_BY_MODERATION = 'hidden_by_moderation';
    case VERIFIED = 'verified';
    case DELETED = 'deleted';
}
