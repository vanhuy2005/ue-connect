<?php

namespace Tests\Unit\AI;

use App\AI\Evidence\DTO\ExtractedStudentCardFieldsData;
use App\AI\Evidence\Services\StudentCardMatchingService;
use App\AI\Evidence\Services\VietnameseNameNormalizer;
use App\Enums\EvidenceAnalysisRecommendation;
use App\Enums\EvidenceRiskFlag;
use App\Models\VerificationRequest;
use Tests\TestCase;

class StudentCardMatchingServiceTest extends TestCase
{
    protected StudentCardMatchingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StudentCardMatchingService(new VietnameseNameNormalizer);
    }

    public function test_exact_all_fields_match_gives_likely_match(): void
    {
        $extracted = new ExtractedStudentCardFieldsData(
            fullName: 'Nguyen Van A',
            studentCode: '4901104055',
            faculty: 'Công nghệ thông tin',
            cohort: 'K49',
            schoolName: 'Trường Đại học Sư phạm TP.HCM'
        );

        $request = new VerificationRequest([
            'submitted_name' => 'Nguyen Van A',
            'submitted_student_code' => '49.01.104.055',
            'submitted_cohort' => 'K49',
        ]);

        $matchResult = $this->service->match($extracted, $request);

        $this->assertEquals(EvidenceAnalysisRecommendation::LikelyMatch, $matchResult['recommendation']);
        $this->assertGreaterThanOrEqual(0.85, $matchResult['score']);
        $this->assertEmpty($matchResult['flags']);
    }

    public function test_student_code_match_but_name_mismatch_gives_manual_review(): void
    {
        $extracted = new ExtractedStudentCardFieldsData(
            fullName: 'Tran Thi B',
            studentCode: '4901104055',
            faculty: 'Công nghệ thông tin',
            cohort: 'K49',
            schoolName: 'Trường Đại học Sư phạm TP.HCM'
        );

        $request = new VerificationRequest([
            'submitted_name' => 'Nguyen Van A',
            'submitted_student_code' => '49.01.104.055',
            'submitted_cohort' => 'K49',
        ]);

        $matchResult = $this->service->match($extracted, $request);

        $this->assertEquals(EvidenceAnalysisRecommendation::ManualReview, $matchResult['recommendation']);
        $this->assertContains(EvidenceRiskFlag::NameMismatch, $matchResult['flags']);
    }

    public function test_all_fields_mismatched_gives_reject_recommended(): void
    {
        $extracted = new ExtractedStudentCardFieldsData(
            fullName: 'Tran Thi B',
            studentCode: '5001104999',
            faculty: 'Lịch sử',
            cohort: 'K50',
            schoolName: null
        );

        $request = new VerificationRequest([
            'submitted_name' => 'Nguyen Van A',
            'submitted_student_code' => '49.01.104.055',
            'submitted_cohort' => 'K49',
        ]);

        $matchResult = $this->service->match($extracted, $request);

        $this->assertEquals(EvidenceAnalysisRecommendation::RejectRecommended, $matchResult['recommendation']);
        $this->assertContains(EvidenceRiskFlag::StudentCodeMismatch, $matchResult['flags']);
        $this->assertContains(EvidenceRiskFlag::NameMismatch, $matchResult['flags']);
        $this->assertContains(EvidenceRiskFlag::SchoolMismatch, $matchResult['flags']);
    }
}
