<?php

namespace App\AI\Evidence\Contracts;

use App\AI\Evidence\DTO\EvidenceAnalysisResultData;
use App\Models\VerificationEvidence;

interface EvidenceAnalyzer
{
    public function analyze(VerificationEvidence $evidence): EvidenceAnalysisResultData;
}
