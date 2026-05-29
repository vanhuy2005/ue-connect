<?php

namespace Tests\Unit\AI;

use App\AI\Evidence\Services\StudentCardFieldExtractor;
use Tests\TestCase;

class StudentCardFieldExtractorTest extends TestCase
{
    protected StudentCardFieldExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extractor = new StudentCardFieldExtractor;
    }

    public function test_mssv_extraction_from_dot_separated_format(): void
    {
        $text = "TRƯỜNG ĐẠI HỌC SƯ PHẠM TP.HCM\nMSSV: 49.01.104.055\nHỌ TÊN: Nguyễn Văn A";
        $fields = $this->extractor->extract($text);

        $this->assertEquals('4901104055', $fields->studentCode);
    }

    public function test_mssv_extraction_from_compact_format(): void
    {
        $text = "Mã số sinh viên: 4901104055\nKhoa Công nghệ thông tin";
        $fields = $this->extractor->extract($text);

        $this->assertEquals('4901104055', $fields->studentCode);
    }

    public function test_hcmue_alias_matching(): void
    {
        $text1 = 'TRƯỜNG ĐẠI HỌC SƯ PHẠM TP.HCM';
        $fields1 = $this->extractor->extract($text1);
        $this->assertEquals('Trường Đại học Sư phạm TP.HCM', $fields1->schoolName);

        $text2 = 'HCMUE Student Card';
        $fields2 = $this->extractor->extract($text2);
        $this->assertEquals('Trường Đại học Sư phạm TP.HCM', $fields2->schoolName);
    }

    public function test_cohort_extraction(): void
    {
        $text = "Khóa: K49\nNgành: Sư phạm Tin học";
        $fields = $this->extractor->extract($text);
        $this->assertEquals('K49', $fields->cohort);

        $textYear = 'Khóa: 2023-2027';
        $fieldsYear = $this->extractor->extract($textYear);
        $this->assertEquals('2023-2027', $fieldsYear->cohort);
    }

    public function test_faculty_alias_matching(): void
    {
        $text = "Khoa: CNTT\nNiên khóa: 2022-2026";
        $fields = $this->extractor->extract($text);
        $this->assertEquals('Công nghệ thông tin', $fields->faculty);
    }

    public function test_returns_null_fields_when_not_found(): void
    {
        $text = 'Random text without anything matching';
        $fields = $this->extractor->extract($text);

        $this->assertNull($fields->studentCode);
        $this->assertNull($fields->schoolName);
        $this->assertNull($fields->faculty);
        $this->assertNull($fields->cohort);
    }
}
