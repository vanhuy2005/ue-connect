<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Ingestion\AcademicDocumentChunker;
use Tests\TestCase;

class AcademicDocumentChunkerTest extends TestCase
{
    /**
     * Test semantic chunking of a structured regulation.
     */
    public function test_can_chunk_structured_regulation_by_article(): void
    {
        $chunker = new AcademicDocumentChunker;

        $text = "Phần thứ nhất\n".
                "QUY ĐỊNH CHUNG\n".
                "Chương I\n".
                "ĐIỀU KHOẢN THI HÀNH\n".
                "Mục 1\n".
                "Phạm vi áp dụng\n".
                "Điều 1. Phạm vi điều chỉnh\n".
                "Quy chế này quy định về đào tạo đại học tại HCMUE.\n".
                "Điều 2. Đối tượng áp dụng\n".
                "Áp dụng cho toàn thể sinh viên và giảng viên trường.\n".
                "\f". // Page break
                "Chương II\n".
                "ĐÀO TẠO THEO TÍN CHỈ\n".
                "Điều 3. Đăng ký học phần\n".
                'Sinh viên đăng ký tối đa 25 tín chỉ mỗi học kỳ.';

        $chunks = $chunker->chunk($text);

        $this->assertCount(4, $chunks);

        // Verify preamble chunk
        $this->assertEquals(0, $chunks[0]['chunk_index']);
        $this->assertStringContainsString('QUY ĐỊNH CHUNG', $chunks[0]['chunk_text']);
        $this->assertEquals('Phần thứ nhất', $chunks[0]['part']);
        $this->assertEquals('Chương I', $chunks[0]['chapter']);
        $this->assertEquals('Mục 1', $chunks[0]['section']);
        $this->assertEquals('Preamble', $chunks[0]['article']);

        // Verify first article chunk
        $this->assertEquals(1, $chunks[1]['chunk_index']);
        $this->assertStringContainsString('Điều 1. Phạm vi điều chỉnh', $chunks[1]['chunk_text']);
        $this->assertEquals('Điều 1. Phạm vi điều chỉnh', $chunks[1]['article']);
        $this->assertEquals(1, $chunks[1]['page_start']);
        $this->assertEquals(1, $chunks[1]['page_end']);

        // Verify second article chunk
        $this->assertEquals(2, $chunks[2]['chunk_index']);
        $this->assertStringContainsString('Điều 2. Đối tượng áp dụng', $chunks[2]['chunk_text']);
        $this->assertEquals('Điều 2. Đối tượng áp dụng', $chunks[2]['article']);
        $this->assertEquals(1, $chunks[2]['page_start']);
        $this->assertEquals(2, $chunks[2]['page_end']); // Ends when next article in page 2 starts

        // Verify third article chunk
        $this->assertEquals(3, $chunks[3]['chunk_index']);
        $this->assertStringContainsString('Điều 3. Đăng ký học phần', $chunks[3]['chunk_text']);
        $this->assertEquals('Chương II', $chunks[3]['chapter']);
        $this->assertEquals('Điều 3. Đăng ký học phần', $chunks[3]['article']);
        $this->assertEquals(2, $chunks[3]['page_start']);
        $this->assertEquals(2, $chunks[3]['page_end']);
    }

    /**
     * Test fallback paragraph-based chunking for unstructured text.
     */
    public function test_fallback_chunking_for_unstructured_text(): void
    {
        $chunker = new AcademicDocumentChunker;

        // 3 long paragraphs to test length aggregation fallback
        $para1 = str_repeat('Dòng text ngẫu nhiên 1. ', 40); // ~960 chars
        $para2 = str_repeat('Dòng text ngẫu nhiên 2. ', 40); // ~960 chars
        $text = $para1."\n\n".$para2;

        $chunks = $chunker->chunk($text);

        // Should split into 2 chunks because their combined length is > 1000 characters
        $this->assertCount(2, $chunks);
        $this->assertEquals(0, $chunks[0]['chunk_index']);
        $this->assertEquals(trim($para1), $chunks[0]['chunk_text']);
        $this->assertNull($chunks[0]['article']);
    }
}
