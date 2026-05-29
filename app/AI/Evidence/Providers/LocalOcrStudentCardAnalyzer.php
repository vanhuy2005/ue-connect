<?php

namespace App\AI\Evidence\Providers;

use App\AI\Evidence\Contracts\EvidenceAnalyzer;
use App\AI\Evidence\DTO\EvidenceAnalysisResultData;
use App\AI\Evidence\DTO\ExtractedStudentCardFieldsData;
use App\AI\Evidence\Services\StudentCardFieldExtractor;
use App\AI\Evidence\Services\StudentCardMatchingService;
use App\AI\Evidence\Services\StudentCardOcrService;
use App\Enums\DetectedDocumentType;
use App\Enums\EvidenceAnalysisRecommendation;
use App\Enums\EvidenceRiskFlag;
use App\Models\VerificationEvidence;

class LocalOcrStudentCardAnalyzer implements EvidenceAnalyzer
{
    public function __construct(
        private readonly StudentCardOcrService $ocrService,
        private readonly StudentCardFieldExtractor $extractor,
        private readonly StudentCardMatchingService $matcher,
    ) {}

    public function analyze(VerificationEvidence $evidence): EvidenceAnalysisResultData
    {
        $mediaFile = $evidence->mediaFile;

        if ($mediaFile === null) {
            return EvidenceAnalysisResultData::manualReview(EvidenceRiskFlag::ManualReviewRequired);
        }

        $ocrResult = $this->ocrService->extractText($mediaFile->path);
        $flags = $ocrResult['flags'];

        if (empty($ocrResult['text'])) {
            return new EvidenceAnalysisResultData(
                recommendation: EvidenceAnalysisRecommendation::ManualReview,
                confidenceScore: 0.0,
                ocrText: null,
                riskFlags: $flags,
                reviewSummary: 'OCR không thể đọc nội dung ảnh. Vui lòng kiểm tra thủ công.',
                provider: 'local_ocr',
                modelName: $ocrResult['engine'],
            );
        }

        $extracted = $this->extractor->extract($ocrResult['text']);
        $matchResult = $this->matcher->match($extracted, $evidence->verificationRequest);

        $allFlags = array_merge($flags, $matchResult['flags']);
        $docType = $extracted->schoolName !== null
            ? DetectedDocumentType::StudentCard
            : DetectedDocumentType::Unknown;

        return new EvidenceAnalysisResultData(
            recommendation: $matchResult['recommendation'],
            confidenceScore: $matchResult['score'],
            documentTypeDetected: $docType,
            documentTypeConfidence: $extracted->schoolName !== null ? 0.8 : 0.2,
            ocrText: config('ai-verification.privacy.store_raw_ocr_text') ? $ocrResult['text'] : null,
            extractedFields: $extracted,
            matchResult: $matchResult['details'],
            riskFlags: $allFlags,
            reviewSummary: $this->buildReviewSummary($matchResult['score'], $extracted),
            provider: 'local_ocr',
            modelName: $ocrResult['engine'],
        );
    }

    private function buildReviewSummary(float $score, ExtractedStudentCardFieldsData $extracted): string
    {
        $parts = [];

        if ($extracted->studentCode !== null) {
            $parts[] = 'Đã đọc được MSSV.';
        }

        if ($extracted->fullName !== null) {
            $parts[] = 'Đã đọc được họ tên.';
        }

        if ($extracted->schoolName !== null) {
            $parts[] = 'Phát hiện tên trường HCMUE.';
        }

        $parts[] = sprintf('Điểm khớp tổng: %.0f%%.', $score * 100);

        return implode(' ', $parts);
    }
}
