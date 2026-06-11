<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Ingestion\AcademicDocumentTextExtractor;
use App\AI\HcmueChatbot\Ingestion\AcademicMetadataExtractor;
use App\AI\HcmueChatbot\Ingestion\PdfTextQualityAnalyzer;
use Tests\TestCase;

class AcademicImporterServicesTest extends TestCase
{
    /**
     * Test AcademicMetadataExtractor path parsing.
     */
    public function test_academic_metadata_extractor_parses_paths_correctly(): void
    {
        $extractor = new AcademicMetadataExtractor;

        // 1. Test student handbook path
        $path1 = 'database/AI/Sotaysinhvien/2025-2026.pdf';
        $meta1 = $extractor->extract($path1);

        $this->assertEquals('student_handbook', $meta1['document_type']);
        $this->assertEquals(2025, $meta1['academic_year']);
        $this->assertEquals('K51', $meta1['cohort']);
        $this->assertNull($meta1['faculty']);
        $this->assertNull($meta1['major']);

        // 2. Test curriculum path without metadata.json (path-based extraction)
        $path2 = 'database/AI/Chuongtrinhdaotao/2023 - Khóa 49/Khoa/Công nghệ thông tin/Ngành/Sư phạm Tin học/Chuongtrinhkhung/CTK_SPTin.pdf';
        $meta2 = $extractor->extract($path2);

        $this->assertEquals('training_program', $meta2['document_type']);
        $this->assertEquals(2023, $meta2['academic_year']);
        $this->assertEquals('K49', $meta2['cohort']);
        $this->assertEquals('Khoa Công nghệ thông tin', $meta2['faculty']);
        $this->assertEquals('Sư phạm Tin học', $meta2['major']);

        // 3. Test learning outcome path
        $path3 = 'database/AI/Chuongtrinhdaotao/2022 - Khóa 48/Khoa/Toán - Tin/Ngành/Toán/Chuandaura/CDR_Toan.pdf';
        $meta3 = $extractor->extract($path3);

        $this->assertEquals('learning_outcome', $meta3['document_type']);
        $this->assertEquals('K48', $meta3['cohort']);
        $this->assertEquals('Khoa Toán - Tin', $meta3['faculty']);
        $this->assertEquals('Toán', $meta3['major']);
    }

    /**
     * Test PdfTextQualityAnalyzer logic.
     */
    public function test_pdf_text_quality_analyzer_detects_scanned_vs_readable_documents(): void
    {
        // Mock text extractor
        $mockExtractor = $this->createMock(AcademicDocumentTextExtractor::class);

        $analyzer = new PdfTextQualityAnalyzer($mockExtractor);

        // Scenario A: Short/empty text (scanned PDF)
        $mockExtractor->method('extract')
            ->willReturn('Trang 1 \f Trang 2');

        $resultA = $analyzer->analyze('dummy_path.pdf');
        $this->assertTrue($resultA['is_scanned']);
        $this->assertTrue($resultA['needs_ocr']);
        $this->assertLessThan(500, $resultA['text_length']);

        // Scenario B: Long text (readable PDF)
        $longText = str_repeat('Trường Đại học Sư phạm Thành phố Hồ Chí Minh là một trường đại học đầu ngành về đào tạo sư phạm. ', 10);

        $mockExtractor = $this->createMock(AcademicDocumentTextExtractor::class);
        $mockExtractor->method('extract')
            ->willReturn($longText);

        $analyzerB = new PdfTextQualityAnalyzer($mockExtractor);
        $resultB = $analyzerB->analyze('dummy_path.pdf');

        $this->assertFalse($resultB['is_scanned']);
        $this->assertFalse($resultB['needs_ocr']);
        $this->assertGreaterThan(500, $resultB['text_length']);
    }
}
