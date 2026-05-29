<?php

namespace App\AI\Evidence\Providers;

use App\AI\Evidence\Contracts\EvidenceAnalyzer;
use App\AI\Evidence\DTO\EvidenceAnalysisResultData;
use App\AI\Evidence\DTO\ExtractedStudentCardFieldsData;
use App\AI\Evidence\Services\OllamaStudentCardNormalizer;
use App\AI\Evidence\Services\StudentCardFieldExtractor;
use App\AI\Evidence\Services\StudentCardMatchingService;
use App\AI\Evidence\Services\StudentCardOcrService;
use App\Enums\DetectedDocumentType;
use App\Enums\EvidenceAnalysisRecommendation;
use App\Enums\EvidenceRiskFlag;
use App\Models\VerificationEvidence;

class LocalHybridStudentCardAnalyzer implements EvidenceAnalyzer
{
    public function __construct(
        private readonly StudentCardOcrService $ocrService,
        private readonly StudentCardFieldExtractor $ruleExtractor,
        private readonly OllamaStudentCardNormalizer $ollamaNormalizer,
        private readonly StudentCardMatchingService $matcher,
    ) {}

    public function analyze(VerificationEvidence $evidence): EvidenceAnalysisResultData
    {
        $mediaFile = $evidence->mediaFile;

        if ($mediaFile === null) {
            return EvidenceAnalysisResultData::manualReview(EvidenceRiskFlag::ManualReviewRequired);
        }

        // Step 1: OCR
        $ocrResult = $this->ocrService->extractText($mediaFile->path);
        $flags = $ocrResult['flags'];
        $ocrText = $ocrResult['text'];

        if (empty($ocrText)) {
            return new EvidenceAnalysisResultData(
                recommendation: EvidenceAnalysisRecommendation::ManualReview,
                confidenceScore: 0.0,
                ocrText: null,
                riskFlags: $flags,
                reviewSummary: 'OCR không đọc được. Cần kiểm tra thủ công.',
                provider: 'local_hybrid',
                modelName: $ocrResult['engine'],
            );
        }

        // Step 2: Try Ollama normalization first, fall back to rule-based
        $ollamaResult = $this->ollamaNormalizer->normalize($ocrText);
        $flags = array_merge($flags, $ollamaResult['flags']);

        $extracted = $ollamaResult['data'] ?? $this->ruleExtractor->extract($ocrText);

        // Step 3: Matching
        $matchResult = $this->matcher->match($extracted, $evidence->verificationRequest);
        $allFlags = array_merge($flags, $matchResult['flags']);

        $docType = $extracted->schoolName !== null
            ? DetectedDocumentType::StudentCard
            : DetectedDocumentType::Unknown;

        $modelName = $ocrResult['engine'];
        if ($ollamaResult['data'] !== null) {
            $modelName .= '+'.config('ai-verification.local_hybrid.ollama_model', 'ollama');
        }

        return new EvidenceAnalysisResultData(
            recommendation: $matchResult['recommendation'],
            confidenceScore: $matchResult['score'],
            documentTypeDetected: $docType,
            documentTypeConfidence: $extracted->schoolName !== null ? 0.85 : 0.3,
            ocrText: config('ai-verification.privacy.store_raw_ocr_text') ? $ocrText : null,
            extractedFields: $extracted,
            matchResult: $matchResult['details'],
            riskFlags: $allFlags,
            reviewSummary: $this->buildSummary($matchResult['score'], $extracted, $ollamaResult['data'] !== null),
            provider: 'local_hybrid',
            modelName: $modelName,
        );
    }

    private function buildSummary(float $score, ExtractedStudentCardFieldsData $extracted, bool $ollamaUsed): string
    {
        $engine = $ollamaUsed ? 'OCR + Ollama' : 'OCR + rule-based';
        $parts = ["Phân tích bằng $engine."];

        if ($extracted->studentCode !== null) {
            $parts[] = 'Đọc được MSSV.';
        }

        if ($extracted->fullName !== null) {
            $parts[] = 'Đọc được họ tên.';
        }

        $parts[] = sprintf('Điểm khớp: %.0f%%.', $score * 100);

        return implode(' ', $parts);
    }
}
