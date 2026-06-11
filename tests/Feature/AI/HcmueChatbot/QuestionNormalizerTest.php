<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Chat\MajorCatalogService;
use App\AI\HcmueChatbot\Chat\QuestionNormalizerService;
use Tests\TestCase;

class QuestionNormalizerTest extends TestCase
{
    private QuestionNormalizerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $mockCatalog = $this->createMock(MajorCatalogService::class);
        $mockCatalog->method('aliases')->willReturn([
            'công nghệ thông tin' => 'Công nghệ thông tin',
            'cong nghe thong tin' => 'Công nghệ thông tin',
            'cntt' => 'Công nghệ thông tin',
            'sư phạm toán học' => 'Sư phạm Toán học',
            'su pham toan hoc' => 'Sư phạm Toán học',
            'sp toan' => 'Sư phạm Toán học',
            'sptoan' => 'Sư phạm Toán học',
            'ngôn ngữ hàn quốc' => 'Ngôn ngữ Hàn Quốc',
            'ngon ngu han quoc' => 'Ngôn ngữ Hàn Quốc',
            'ngôn ngữ hàn' => 'Ngôn ngữ Hàn Quốc',
            'ngon ngu han' => 'Ngôn ngữ Hàn Quốc',
            'tieng han' => 'Ngôn ngữ Hàn Quốc',
            'nn han' => 'Ngôn ngữ Hàn Quốc',
            'sư phạm tin học' => 'Sư phạm Tin học',
            'su pham tin hoc' => 'Sư phạm Tin học',
            'sp tin' => 'Sư phạm Tin học',
        ]);

        $mockCatalog->method('removeAccents')->willReturnCallback(function (string $str) {
            $map = [
                'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
                'd' => 'đ',
                'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
                'i' => 'í|ì|ỉ|ĩ|ị',
                'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
                'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
                'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            ];
            foreach ($map as $ascii => $pattern) {
                $str = preg_replace("/({$pattern})/iu", $ascii, $str);
            }

            return $str;
        });

        $this->service = new QuestionNormalizerService($mockCatalog);
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
