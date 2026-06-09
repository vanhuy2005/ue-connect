<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Retrieval\QdrantVectorStore;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QdrantVectorStoreTest extends TestCase
{
    protected string $qdrantUrl = 'http://localhost:6333';

    protected string $collection = 'hcmue_academic_chunks';

    /**
     * Test checking if a collection exists.
     */
    public function test_can_check_if_collection_exists(): void
    {
        Http::fake([
            "{$this->qdrantUrl}/collections/{$this->collection}" => Http::response(['status' => 'ok'], 200),
        ]);

        $store = new QdrantVectorStore;
        $exists = $store->collectionExists();

        $this->assertTrue($exists);
    }

    /**
     * Test creating a collection.
     */
    public function test_can_create_collection(): void
    {
        Http::fake([
            "{$this->qdrantUrl}/collections/{$this->collection}" => Http::response(['status' => 'ok'], 200),
        ]);

        $store = new QdrantVectorStore;
        $created = $store->createCollection(768);

        $this->assertTrue($created);

        Http::assertSent(function ($request) {
            return $request->url() === "{$this->qdrantUrl}/collections/{$this->collection}" &&
                $request->method() === 'PUT' &&
                $request['vectors']['size'] === 768;
        });
    }

    /**
     * Test upserting points.
     */
    public function test_can_upsert_points(): void
    {
        Http::fake([
            "{$this->qdrantUrl}/collections/{$this->collection}/points?wait=true" => Http::response(['status' => 'ok'], 200),
        ]);

        $store = new QdrantVectorStore;

        $points = [
            [
                'id' => 123,
                'vector' => array_fill(0, 768, 0.1),
                'payload' => ['title' => 'Test point'],
            ],
        ];

        $success = $store->upsert($points);

        $this->assertTrue($success);

        Http::assertSent(function ($request) {
            return $request->url() === "{$this->qdrantUrl}/collections/{$this->collection}/points?wait=true" &&
                $request->method() === 'PUT' &&
                count($request['points']) === 1 &&
                $request['points'][0]['id'] === 123;
        });
    }

    /**
     * Test searching vector store.
     */
    public function test_can_search_collection(): void
    {
        $mockResults = [
            [
                'id' => 123,
                'version' => 1,
                'score' => 0.89,
                'payload' => ['document_name' => 'Sổ tay sinh viên', 'chunk_text' => 'Nội dung mẫu'],
            ],
        ];

        Http::fake([
            "{$this->qdrantUrl}/collections/{$this->collection}/points/search" => Http::response(['result' => $mockResults], 200),
        ]);

        $store = new QdrantVectorStore;
        $vector = array_fill(0, 768, 0.2);

        $results = $store->search($vector, 5, 0.7, ['cohort' => 'K48']);

        $this->assertCount(1, $results);
        $this->assertEquals(123, $results[0]['id']);
        $this->assertEquals(0.89, $results[0]['score']);

        Http::assertSent(function ($request) use ($vector) {
            return $request->url() === "{$this->qdrantUrl}/collections/{$this->collection}/points/search" &&
                $request->method() === 'POST' &&
                $request['vector'] === $vector &&
                $request['limit'] === 5 &&
                $request['score_threshold'] === 0.7 &&
                isset($request['filter']['must']);
        });
    }
}
