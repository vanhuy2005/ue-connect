<?php

namespace App\AI\Evidence\DTO;

use App\Enums\DetectedDocumentType;
use App\Enums\EvidenceAnalysisRecommendation;
use App\Enums\EvidenceRiskFlag;

readonly class EvidenceAnalysisResultData
{
    /**
     * @param  list<EvidenceRiskFlag>  $riskFlags
     * @param  array<string, mixed>  $matchResult
     */
    public function __construct(
        public EvidenceAnalysisRecommendation $recommendation,
        public float $confidenceScore,
        public ?DetectedDocumentType $documentTypeDetected = null,
        public ?float $documentTypeConfidence = null,
        public ?string $ocrText = null,
        public ?ExtractedStudentCardFieldsData $extractedFields = null,
        public array $matchResult = [],
        public array $riskFlags = [],
        public ?string $reviewSummary = null,
        public ?string $provider = null,
        public ?string $modelName = null,
    ) {}

    /**
     * @return list<string>
     */
    public function riskFlagValues(): array
    {
        return array_map(fn (EvidenceRiskFlag $flag) => $flag->value, $this->riskFlags);
    }

    public static function manualReview(EvidenceRiskFlag ...$flags): self
    {
        return new self(
            recommendation: EvidenceAnalysisRecommendation::ManualReview,
            confidenceScore: 0.0,
            riskFlags: array_values($flags),
        );
    }
}
