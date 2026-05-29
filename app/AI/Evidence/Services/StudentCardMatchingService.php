<?php

namespace App\AI\Evidence\Services;

use App\AI\Evidence\DTO\ExtractedStudentCardFieldsData;
use App\Enums\EvidenceAnalysisRecommendation;
use App\Enums\EvidenceRiskFlag;
use App\Models\VerificationRequest;

class StudentCardMatchingService
{
    public function __construct(
        private readonly VietnameseNameNormalizer $nameNormalizer,
    ) {}

    /**
     * @return array{score: float, recommendation: EvidenceAnalysisRecommendation, flags: list<EvidenceRiskFlag>, details: array<string, mixed>}
     */
    public function match(
        ExtractedStudentCardFieldsData $extracted,
        VerificationRequest $request,
    ): array {
        $flags = [];
        $details = [];
        $score = 0.0;

        // Student code match (weight 0.45)
        $codeScore = $this->matchStudentCode($extracted->studentCode, $request->submitted_student_code, $flags, $details);
        $score += $codeScore * 0.45;

        // Name match (weight 0.25)
        $nameScore = $this->matchName($extracted->fullName, $request->submitted_name, $flags, $details);
        $score += $nameScore * 0.25;

        // School match (weight 0.15)
        $schoolScore = $extracted->schoolName !== null ? 1.0 : 0.0;
        $score += $schoolScore * 0.15;
        $details['school_match'] = $schoolScore > 0;
        if ($schoolScore === 0.0 && $extracted->schoolName === null) {
            $flags[] = EvidenceRiskFlag::SchoolMismatch;
        }

        // Faculty + cohort match (weight 0.10)
        $facultyCohortScore = $this->matchFacultyCohort($extracted, $request, $flags, $details);
        $score += $facultyCohortScore * 0.10;

        // Document type match (weight 0.05 — always 1.0 for student_card flow)
        $score += 1.0 * 0.05;
        $details['document_type_score'] = 1.0;

        $details['total_score'] = round($score, 4);

        $recommendation = $this->scoreToRecommendation($score);

        return [
            'score' => round($score, 4),
            'recommendation' => $recommendation,
            'flags' => $flags,
            'details' => $details,
        ];
    }

    /**
     * @param  list<EvidenceRiskFlag>  $flags
     * @param  array<string, mixed>  $details
     */
    private function matchStudentCode(
        ?string $extracted,
        ?string $submitted,
        array &$flags,
        array &$details,
    ): float {
        if ($extracted === null) {
            $flags[] = EvidenceRiskFlag::StudentCodeMissing;
            $details['student_code_match'] = false;

            return 0.0;
        }

        $normalizedExtracted = preg_replace('/[.\s-]/', '', $extracted) ?? $extracted;
        $normalizedSubmitted = preg_replace('/[.\s-]/', '', $submitted ?? '') ?? '';

        $match = $normalizedExtracted === $normalizedSubmitted;
        $details['student_code_match'] = $match;
        $details['extracted_code'] = $extracted;
        $details['submitted_code'] = $submitted;

        if (! $match) {
            $flags[] = EvidenceRiskFlag::StudentCodeMismatch;
        }

        return $match ? 1.0 : 0.0;
    }

    /**
     * @param  list<EvidenceRiskFlag>  $flags
     * @param  array<string, mixed>  $details
     */
    private function matchName(
        ?string $extracted,
        ?string $submitted,
        array &$flags,
        array &$details,
    ): float {
        if ($extracted === null) {
            $flags[] = EvidenceRiskFlag::MissingName;
            $details['name_match'] = false;

            return 0.0;
        }

        $similarity = $this->nameNormalizer->similarity($extracted, $submitted ?? '');
        $details['name_similarity'] = $similarity;
        $details['extracted_name'] = $extracted;
        $details['submitted_name'] = $submitted;

        if ($similarity < 0.5) {
            $flags[] = EvidenceRiskFlag::NameMismatch;
        }

        return $similarity;
    }

    /**
     * @param  list<EvidenceRiskFlag>  $flags
     * @param  array<string, mixed>  $details
     */
    private function matchFacultyCohort(
        ExtractedStudentCardFieldsData $extracted,
        VerificationRequest $request,
        array &$flags,
        array &$details,
    ): float {
        $score = 0.0;

        if ($extracted->faculty !== null) {
            $score += 0.5;
        }

        if ($extracted->cohort !== null) {
            $submittedCohort = $request->submitted_cohort;
            if ($submittedCohort !== null && str_contains($extracted->cohort, (string) $submittedCohort)) {
                $score += 0.5;
            } else {
                $score += 0.3;
            }
        }

        $details['faculty_cohort_score'] = $score;

        return min(1.0, $score);
    }

    private function scoreToRecommendation(float $score): EvidenceAnalysisRecommendation
    {
        $likelyMatchThreshold = (float) config('ai-verification.thresholds.likely_match', 0.85);
        $manualReviewThreshold = (float) config('ai-verification.thresholds.manual_review', 0.65);
        $suspiciousThreshold = (float) config('ai-verification.thresholds.suspicious', 0.45);

        return match (true) {
            $score >= $likelyMatchThreshold => EvidenceAnalysisRecommendation::LikelyMatch,
            $score >= $manualReviewThreshold => EvidenceAnalysisRecommendation::ManualReview,
            $score >= $suspiciousThreshold => EvidenceAnalysisRecommendation::Suspicious,
            default => EvidenceAnalysisRecommendation::RejectRecommended,
        };
    }
}
