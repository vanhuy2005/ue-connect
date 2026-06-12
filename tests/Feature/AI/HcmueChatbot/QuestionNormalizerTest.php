<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Chat\CohortCatalogService;
use App\AI\HcmueChatbot\Chat\CohortMajorCatalogService;
use App\AI\HcmueChatbot\Chat\MajorCatalogService;
use App\AI\HcmueChatbot\Chat\QuestionNormalizerService;
use Tests\TestCase;

class QuestionNormalizerTest extends TestCase
{
    private QuestionNormalizerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $mockCohortCatalog = $this->createMock(CohortCatalogService::class);
        $mockMajorCatalog = $this->createMock(MajorCatalogService::class);
        $mockCohortMajorCatalog = $this->createMock(CohortMajorCatalogService::class);

        $mockCohortMajorCatalog->method('detectCohort')->willReturnCallback(function (string $query) {
            if (preg_match('/k51/i', $query)) {
                return [
                    'canonical_cohort' => '2025 - Khóa 51',
                    'cohort_alias' => 'k51',
                ];
            }
            if (preg_match('/khóa 50/i', $query)) {
                return [
                    'canonical_cohort' => '2024 - Khóa 50',
                    'cohort_alias' => 'khóa 50',
                ];
            }

            return null;
        });

        $mockCohortMajorCatalog->method('detectMajor')->willReturnCallback(function (string $query) {
            $queryLower = mb_strtolower($query, 'UTF-8');
            if (str_contains($queryLower, 'cntt')) {
                return [
                    'canonical_major' => 'Công nghệ thông tin',
                    'matched_alias' => 'cntt',
                ];
            }
            if (str_contains($queryLower, 'sư phạm tin học') || str_contains($queryLower, 'sp tin')) {
                return [
                    'canonical_major' => 'Sư phạm Tin học',
                    'matched_alias' => 'sư phạm tin học',
                ];
            }
            if (str_contains($queryLower, 'công nghệ thông tin')) {
                return [
                    'canonical_major' => 'Công nghệ thông tin',
                    'matched_alias' => 'công nghệ thông tin',
                ];
            }

            return null;
        });

        $this->service = new QuestionNormalizerService($mockMajorCatalog, $mockCohortCatalog, $mockCohortMajorCatalog);
    }

    public function test_expands_cntt_abbreviation(): void
    {
        $result = $this->service->normalize('Ngành cntt K51 có bao nhiêu tín chỉ?');
        $this->assertStringContainsString('Công nghệ thông tin', $result['normalized_question']);
    }

    public function test_normalizes_cohort_k51(): void
    {
        $result = $this->service->normalize('Ngành CNTT k51 học những môn gì?');
        // k51 is expanded to "2025 - Khóa 51" by the normalizer
        $this->assertStringContainsString('2025 - Khóa 51', $result['normalized_question']);
        $this->assertEquals('2025 - Khóa 51', $result['detected_terms']['cohort']);
    }

    public function test_normalizes_cohort_khoa_50(): void
    {
        $result = $this->service->normalize('Khóa 50 ngành sư phạm tin học mấy tín chỉ?');
        // "Khóa 50" is expanded to "2024 - Khóa 50" by the normalizer
        $this->assertStringContainsString('2024 - Khóa 50', $result['normalized_question']);
        $this->assertEquals('2024 - Khóa 50', $result['detected_terms']['cohort']);
    }

    public function test_detects_policy_topic_hoc_lai(): void
    {
        $result = $this->service->normalize('Nếu em rớt học phần bắt buộc thì phải học lại không?');
        $this->assertEquals('học lại', $result['detected_terms']['policy_topic']);
    }

    public function test_detects_policy_topic_tot_nghiep(): void
    {
        $result = $this->service->normalize('Điều kiện tốt nghiệp của sinh viên là gì?');
        $this->assertEquals('điều kiện tốt nghiệp', $result['detected_terms']['policy_topic']);
    }

    public function test_detects_major_keyword(): void
    {
        $result = $this->service->normalize('Ngành Công nghệ thông tin K51 học mấy học kỳ?');
        $this->assertEquals('Công nghệ thông tin', $result['detected_terms']['major']);
    }

    public function test_preserves_original_question(): void
    {
        $original = 'Ngành cntt k51 có bao nhiêu tc?';
        $result = $this->service->normalize($original);
        $this->assertEquals($original, $result['original_question']);
    }
}
