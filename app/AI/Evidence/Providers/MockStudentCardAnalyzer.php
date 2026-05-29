<?php

namespace App\AI\Evidence\Providers;

use App\AI\Evidence\Contracts\EvidenceAnalyzer;
use App\AI\Evidence\DTO\EvidenceAnalysisResultData;
use App\AI\Evidence\DTO\ExtractedStudentCardFieldsData;
use App\Enums\DetectedDocumentType;
use App\Enums\EvidenceAnalysisRecommendation;
use App\Models\VerificationEvidence;

class MockStudentCardAnalyzer implements EvidenceAnalyzer
{
    public function analyze(VerificationEvidence $evidence): EvidenceAnalysisResultData
    {
        $submittedCode = optional($evidence->verificationRequest)->submitted_student_code;

        return new EvidenceAnalysisResultData(
            recommendation: EvidenceAnalysisRecommendation::LikelyMatch,
            confidenceScore: 0.92,
            documentTypeDetected: DetectedDocumentType::StudentCard,
            documentTypeConfidence: 0.98,
            ocrText: null,
            extractedFields: new ExtractedStudentCardFieldsData(
                fullName: optional($evidence->verificationRequest)->submitted_name,
                studentCode: $submittedCode,
                faculty: 'Công nghệ thông tin',
                academicProgram: null,
                cohort: 'K49',
                schoolName: 'Trường Đại học Sư phạm TP.HCM',
                portraitPresentHint: true,
            ),
            matchResult: [
                'student_code_match' => true,
                'name_similarity' => 1.0,
                'school_match' => true,
                'total_score' => 0.92,
            ],
            riskFlags: [],
            reviewSummary: '[Mock] Phân tích thẻ sinh viên thành công. Đây là dữ liệu giả lập cho môi trường kiểm thử.',
            provider: 'mock',
            modelName: 'mock-student-card-analyzer-v1',
        );
    }
}
