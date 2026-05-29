<?php

namespace App\Enums;

enum EvidenceAnalysisStatus: string
{
    case Queued = 'queued';
    case Processing = 'processing';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Skipped = 'skipped';
    case ManualReviewRequired = 'manual_review_required';
}
