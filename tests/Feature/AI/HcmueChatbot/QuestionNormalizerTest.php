<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Chat\QuestionNormalizerService;
use Tests\TestCase;

class QuestionNormalizerTest extends TestCase
{
    private QuestionNormalizerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QuestionNormalizerService;
    }

    public function test_expands_cntt_abbreviation(): void
    {
        $result = $this->service->normalize('Ngành cntt K51 có bao nhiêu tín chỉ?');
        $this->assertStringContainsString('Công nghệ thông tin', $result['normalized_question']);
    }

    public function test_normalizes_cohort_k51(): void
    {
        $result = $this->service->normalize('Ngành CNTT k51 học những môn gì?');
        $this->assertStringContainsString('K51', $result['normalized_question']);
        $this->assertEquals('K51', $result['detected_terms']['cohort']);
    }

    public function test_normalizes_cohort_khoa_50(): void
    {
        $result = $this->service->normalize('Khóa 50 ngành sư phạm tin học mấy tín chỉ?');
        $this->assertStringContainsString('K50', $result['normalized_question']);
        $this->assertEquals('K50', $result['detected_terms']['cohort']);
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
