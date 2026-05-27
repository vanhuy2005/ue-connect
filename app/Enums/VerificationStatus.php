<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case NOT_SUBMITTED = 'not_submitted';
    case DRAFT = 'draft';
    case PENDING_REVIEW = 'pending_review';
    case UNDER_REVIEW = 'under_review';
    case NEEDS_MORE_INFORMATION = 'needs_more_information';
    case RESUBMITTED = 'resubmitted';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CONFLICT = 'conflict';
    case SUSPICIOUS = 'suspicious';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
}
