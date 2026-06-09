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

    protected string $collection = 'hcmue_academic_chunks';

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
            $mock->shouldReceive('embed')
                ->once()
                ->with('quy định đăng ký học phần')
                ->andReturn(array_fill(0, 768, 0.15));
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
}
