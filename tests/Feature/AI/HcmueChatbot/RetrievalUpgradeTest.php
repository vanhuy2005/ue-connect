<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Chat\AnswerComposerService;
use App\AI\HcmueChatbot\LLM\EmbeddingService;
use App\AI\HcmueChatbot\Retrieval\AcademicQueryAnalyzer;
use App\AI\HcmueChatbot\Retrieval\QdrantVectorStore;
use App\AI\HcmueChatbot\Retrieval\RagRetrievalService;
use App\Models\DocumentChunk;
use App\Models\SourceDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class RetrievalUpgradeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test AcademicQueryAnalyzer parsing cohort, major, doc_type, topics.
     */
    public function test_query_analyzer_parses_academic_queries_correctly(): void
    {
        $analyzer = new AcademicQueryAnalyzer;

        // Query 1
        $res1 = $analyzer->analyze('Chuẩn đầu ra ngành Công nghệ thông tin K51 là gì?');
        $this->assertEquals('K51', $res1['cohort']);
        $this->assertEquals('Công nghệ thông tin', $res1['major']);
        $this->assertEquals('learning_outcome', $res1['document_type']);
        $this->assertContains('chuẩn đầu ra', $res1['topics']);

        // Query 2
        $res2 = $analyzer->analyze('Quy chế học vụ K50 về cảnh báo học tập và tín chỉ');
        $this->assertEquals('K50', $res2['cohort']);
        $this->assertEquals('academic_regulation', $res2['document_type']);
        $this->assertContains('cảnh báo học tập', $res2['topics']);
        $this->assertContains('tín chỉ', $res2['topics']);
    }

    /**
     * Test metadata filtering and rule-based reranking.
     */
    public function test_retrieval_service_filters_and_reranks_results(): void
    {
        // 1. Create SourceDocument and chunks
        $doc1 = SourceDocument::create([
            'title' => 'Sổ tay sinh viên K51',
            'document_type' => 'student_handbook',
            'cohort' => 'K51',
            'effective_year' => 2025,
            'status' => 'active',
        ]);

        $chunk1 = DocumentChunk::create([
            'source_document_id' => $doc1->id,
            'chunk_index' => 1,
            'chunk_text' => 'Điều 1: Quy định về đăng ký tín chỉ học tập cho sinh viên khóa K51.',
            'token_count' => 10,
            'metadata_json' => [
                'document_name' => 'Sổ tay sinh viên K51',
                'document_type' => 'student_handbook',
                'cohort' => 'K51',
                'academic_year' => '2025',
                'faculty' => 'Khoa Công nghệ thông tin',
                'major' => 'Công nghệ thông tin',
            ],
            'embedding_status' => 'success',
        ]);

        $doc2 = SourceDocument::create([
            'title' => 'Quy chế học tập K49',
            'document_type' => 'regulation',
            'cohort' => 'K49',
            'effective_year' => 2023,
            'status' => 'active',
        ]);

        $chunk2 = DocumentChunk::create([
            'source_document_id' => $doc2->id,
            'chunk_index' => 1,
            'chunk_text' => 'Điều 5: Quy chế thi lại cho sinh viên khóa K49.',
            'token_count' => 10,
            'metadata_json' => [
                'document_name' => 'Quy chế học tập K49',
                'document_type' => 'regulation',
                'cohort' => 'K49',
                'academic_year' => '2023',
                'faculty' => 'Khoa Công nghệ thông tin',
                'major' => 'Công nghệ thông tin',
            ],
            'embedding_status' => 'success',
        ]);

        // 2. Mock vectorStore and embeddingService
        $mockEmbedding = $this->createMock(EmbeddingService::class);
        $mockEmbedding->method('batchEmbed')
            ->willReturn(array_fill(0, 5, array_fill(0, 768, 0.1)));

        $mockQdrant = $this->createMock(QdrantVectorStore::class);
        $mockQdrant->method('search')
            ->willReturn([
                [
                    'id' => $chunk1->id,
                    'score' => 0.70,
                    'payload' => $chunk1->metadata_json,
                ],
                [
                    'id' => $chunk2->id,
                    'score' => 0.80, // Higher vector score but cohort mismatch
                    'payload' => $chunk2->metadata_json,
                ],
            ]);

        // 3. Retrieve
        $retrieval = new RagRetrievalService($mockEmbedding, $mockQdrant, new AcademicQueryAnalyzer);

        // Target cohort is K51, document_type is student_handbook
        $results = $retrieval->retrieve('Đăng ký tín chỉ K51 công nghệ thông tin');

        $this->assertNotEmpty($results);

        // Assert chunk1 has higher rerank score due to matches, even though chunk2 had higher initial score
        $resultMap = collect($results)->keyBy('id');

        $this->assertTrue($resultMap->has($chunk1->id));
        $this->assertTrue($resultMap->has($chunk2->id));

        $res1 = $resultMap->get($chunk1->id);
        $res2 = $resultMap->get($chunk2->id);

        $this->assertGreaterThan($res2['rerank_score'], $res1['rerank_score']);
    }

    /**
     * Test Ollama prompt compaction limit.
     */
    public function test_ollama_context_packing(): void
    {
        Config::set('ai.llm_provider', 'ollama');
        Config::set('ai.ollama.model', 'gemma2:2b');
        Config::set('ai.ollama.rag_top_k', 1);
        Config::set('ai.ollama.chunk_max_chars', 10);

        $composer = app(AnswerComposerService::class);

        $ragChunks = [
            [
                'id' => 1,
                'score' => 0.8,
                'chunk_text' => 'This is a very long text chunk that should be truncated.',
                'metadata' => [
                    'document_name' => 'Handbook',
                    'ignored_large_metadata' => str_repeat('foo', 100),
                ],
            ],
            [
                'id' => 2,
                'score' => 0.7,
                'chunk_text' => 'Should be ignored entirely due to rag_top_k limit.',
                'metadata' => [],
            ],
        ];

        // We can check protected/private methods using reflection or verify outputs in mock driver
        $reflector = new \ReflectionClass(AnswerComposerService::class);
        $method = $reflector->getMethod('compactRagChunks');
        $method->setAccessible(true);

        $compacted = $method->invoke($composer, $ragChunks);

        $this->assertCount(1, $compacted);
        $this->assertStringContainsString('...[Nội dung', $compacted[0]['chunk_text']);
        $this->assertLessThan(90, mb_strlen($compacted[0]['chunk_text']));
        $this->assertArrayNotHasKey('ignored_large_metadata', $compacted[0]['metadata']);
    }

    /**
     * Test grounded response fallback when context is missing.
     */
    public function test_grounded_fallback_when_context_is_empty(): void
    {
        $composer = app(AnswerComposerService::class);

        // Both DB and RAG result empty
        $res = $composer->compose(
            'Quy chế học vụ K51 thế nào?',
            'quy chế học vụ k51 thế nào',
            ['source' => 'rag', 'intent' => 'general_query'],
            null, // empty structured DB
            [] // empty RAG chunks
        );

        $this->assertEquals('Xin lỗi, dữ liệu hiện tại không đề cập đến nội dung bạn đang tìm kiếm.', $res['answer_text']);
    }
}
