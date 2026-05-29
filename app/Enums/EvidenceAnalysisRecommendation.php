<?php

namespace App\Enums;

enum EvidenceAnalysisRecommendation: string
{
    case LikelyMatch = 'likely_match';
    case ManualReview = 'manual_review';
    case Suspicious = 'suspicious';
    case RejectRecommended = 'reject_recommended';
}
