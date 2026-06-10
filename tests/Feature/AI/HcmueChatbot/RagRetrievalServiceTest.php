<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\LLM\EmbeddingService;
use App\AI\HcmueChatbot\Retrieval\RagRetrievalService;
use App\Models\DocumentChunk;
use App\Models\SourceDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RagRetrievalServiceTest extends TestCase
{
    use RefreshDatabase;

    protected string $qdrantUrl = 'http://localhost:6333';

    protected string $collection = 'hcmue_knowledge';

    protected function setUp(): void
    {
        parent::setUp();
        config(['ai.qdrant.url' => 'http://localhost:6333']);
        config(['ai.qdrant.collection' => 'hcmue_knowledge']);
    }

    /**
     * Test retrieving chunks from query.
     */
    public function test_can_retrieve_semantic_results(): void
    {
        // 1. Create a dummy SourceDocument and DocumentChunk in DB
        $doc = SourceDocument::create([
            'document_type' => 'student_handbook',
            'title' => 'Sổ tay sinh viên',
            'cohort' => 'K48',
            'effective_year' => 2022,
            'file_path' => 'hcmue/sources/dummy.pdf',
            'mime_type' => 'application/pdf',
            'source_hash' => 'dummyhash',
            'status' => 'active',
        ]);

        $chunk = DocumentChunk::create([
            'source_document_id' => $doc->id,
            'chunk_index' => 0,
            'chunk_text' => 'Điều 5. Quy định về đăng ký học phần tại trường ĐH Sư phạm TPHCM.',
            'token_count' => 25,
            'page_start' => 2,
            'page_end' => 2,
            'article' => 'Điều 5',
            'embedding_status' => 'success',
            'vector_id' => '1',
        ]);

        // 2. Mock EmbeddingService
        $this->mock(EmbeddingService::class, function ($mock) {
            $mock->shouldReceive('batchEmbed')
                ->once()
                ->andReturnUsing(fn ($vars) => array_fill(0, count($vars), array_fill(0, 768, 0.15)));
        });

        // 3. Mock Qdrant REST API search response
        $mockPoint = [
            'id' => $chunk->id,
            'score' => 0.88,
            'payload' => [
                'source_document_id' => $doc->id,
                'document_name' => 'Sổ tay sinh viên',
                'document_type' => 'student_handbook',
                'cohort' => 'K48',
                'effective_year' => 2022,
                'page_start' => 2,
                'page_end' => 2,
                'article' => 'Điều 5',
                'chunk_text' => 'Điều 5. Quy định về đăng ký học phần tại trường ĐH Sư phạm TPHCM.',
            ],
        ];

        Http::fake([
            "{$this->qdrantUrl}/collections/{$this->collection}/points/search" => Http::response(['result' => [$mockPoint]], 200),
        ]);

        // 4. Execute RagRetrievalService
        $retrievalService = $this->app->make(RagRetrievalService::class);
        $results = $retrievalService->retrieve('quy định đăng ký học phần', ['cohort' => 'K48']);

        // 5. Assertions
        $this->assertCount(1, $results);
        $this->assertEquals($chunk->id, $results[0]['id']);
        $this->assertEquals(0.88, $results[0]['score']);
        $this->assertEquals($chunk->chunk_text, $results[0]['chunk_text']);
        $this->assertEquals('Sổ tay sinh viên', $results[0]['document_name']);
        $this->assertEquals('Điều 5', $results[0]['article']);
    }

    /**
     * Test retrieving chunks from query with the new payload schema.
     */
    public function test_can_retrieve_semantic_results_with_new_schema(): void
    {
        // 1. Create a dummy SourceDocument and DocumentChunk in DB
        $doc = SourceDocument::create([
            'document_type' => 'training_program',
            'title' => 'Chương trình đào tạo',
            'cohort' => '2023 - Khóa 49',
            'effective_year' => 2023,
            'file_path' => 'hcmue/sources/dummy2.pdf',
            'mime_type' => 'application/pdf',
            'source_hash' => 'dummyhash2',
            'status' => 'active',
        ]);

        $chunk = DocumentChunk::create([
            'source_document_id' => $doc->id,
            'chunk_index' => 0,
            'chunk_text' => 'Tổng số tín chỉ toàn khóa học là 122 tín chỉ.',
            'token_count' => 20,
            'page_start' => 5,
            'page_end' => 5,
            'article' => null,
            'embedding_status' => 'success',
            'vector_id' => '2',
        ]);

        // 2. Mock EmbeddingService
        $this->mock(EmbeddingService::class, function ($mock) {
            $mock->shouldReceive('batchEmbed')
                ->once()
                ->andReturnUsing(fn ($vars) => array_fill(0, count($vars), array_fill(0, 1024, 0.15)));
        });

        // 3. Mock Qdrant REST API search response (using new schema fields)
        $mockPoint = [
            'id' => $chunk->id,
            'score' => 0.95,
            'payload' => [
                'source_document_id' => $doc->id,
                'khoa_hoc' => '2023 - Khóa 49',
                'khoa' => 'Khoa Công nghệ thông tin',
                'nganh' => 'Công nghệ thông tin',
                'knowledge_type' => 'curriculum',
                'loai_tai_lieu' => 'chuong_trinh_khung',
                'source_file' => '02_Chuong_trinh_khung.pdf',
                'page' => 5,
                'chunk_index' => 0,
                'text' => 'Tổng số tín chỉ toàn khóa học là 122 tín chỉ.',
            ],
        ];

        Http::fake([
            "{$this->qdrantUrl}/collections/{$this->collection}/points/search" => Http::response(['result' => [$mockPoint]], 200),
        ]);

        // 4. Execute RagRetrievalService
        $retrievalService = $this->app->make(RagRetrievalService::class);
        // Query has k49 which normalizes to "2023 - Khóa 49"
        $results = $retrievalService->retrieve('k49 công nghệ thông tin cần bao nhiêu tín chỉ để tốt nghiệp');

        // 5. Assertions
        $this->assertCount(1, $results);
        $this->assertEquals($chunk->id, $results[0]['id']);
        $this->assertEquals(0.95, $results[0]['score']);
        $this->assertEquals($chunk->chunk_text, $results[0]['chunk_text']);
        $this->assertEquals('02_Chuong_trinh_khung.pdf', $results[0]['document_name']);
        $this->assertEquals('2023 - Khóa 49', $results[0]['cohort']);
        $this->assertEquals('chuong_trinh_khung', $results[0]['document_type']);
        $this->assertEquals(5, $results[0]['page_start']);
    }
}
