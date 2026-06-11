<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\LLM\BgeM3EmbeddingProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class BgeM3EmbeddingProviderTest extends TestCase
{
    private string $baseUrl = 'https://ntkhoi2005-hcmue-bge-m3-embedding.hf.space';

    private int $dim = 1024;

    protected function setUp(): void
    {
        parent::setUp();
        config(['ai.bge_m3.url' => $this->baseUrl]);
        config(['ai.qdrant.vector_size' => $this->dim]);
    }

    /**
     * Successful embed returns a 1024-float array.
     */
    public function test_embed_returns_correct_dimension_vector(): void
    {
        $fakeVector = array_fill(0, $this->dim, 0.001);

        Http::fake([
            "{$this->baseUrl}/embed" => Http::response([
                'dimension' => $this->dim,
                'vector' => $fakeVector,
            ], 200),
        ]);

        $provider = new BgeM3EmbeddingProvider($this->baseUrl, 30, $this->dim);
        $vector = $provider->embed('công nghệ thông tin tín chỉ tốt nghiệp');

        $this->assertCount($this->dim, $vector);
        $this->assertIsFloat($vector[0]);
    }

    /**
     * A response missing the "vector" key throws a RuntimeException.
     */
    public function test_embed_throws_on_missing_vector_field(): void
    {
        Http::fake([
            "{$this->baseUrl}/embed" => Http::response(['dimension' => $this->dim], 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/missing the "vector" field/');

        $provider = new BgeM3EmbeddingProvider($this->baseUrl, 30, $this->dim);
        $provider->embed('test');
    }

    /**
     * A vector with wrong dimension throws a RuntimeException.
     */
    public function test_embed_throws_on_dimension_mismatch(): void
    {
        Http::fake([
            "{$this->baseUrl}/embed" => Http::response([
                'dimension' => 768,
                'vector' => array_fill(0, 768, 0.001),
            ], 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/dimension mismatch/i');

        $provider = new BgeM3EmbeddingProvider($this->baseUrl, 30, $this->dim);
        $provider->embed('test');
    }

    /**
     * A 500 server error triggers retries and ultimately throws a RuntimeException.
     */
    public function test_embed_throws_on_server_error(): void
    {
        Http::fake([
            "{$this->baseUrl}/embed" => Http::response('Internal Server Error', 500),
        ]);

        $this->expectException(RuntimeException::class);

        $provider = new BgeM3EmbeddingProvider($this->baseUrl, 5, $this->dim);
        $provider->embed('test');
    }

    /**
     * batchEmbed returns one vector per input text.
     */
    public function test_batch_embed_returns_one_vector_per_text(): void
    {
        $fakeVector = array_fill(0, $this->dim, 0.001);

        Http::fake([
            "{$this->baseUrl}/embed" => Http::response([
                'dimension' => $this->dim,
                'vector' => $fakeVector,
            ], 200),
        ]);

        $provider = new BgeM3EmbeddingProvider($this->baseUrl, 30, $this->dim);
        $results = $provider->batchEmbed(['text one', 'text two', 'text three']);

        $this->assertCount(3, $results);
        foreach ($results as $vector) {
            $this->assertCount($this->dim, $vector);
        }
    }

    /**
     * generate() always throws because BGE-M3 is embedding-only.
     */
    public function test_generate_throws_unsupported_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/does not support text generation/');

        $provider = new BgeM3EmbeddingProvider($this->baseUrl, 30, $this->dim);
        $provider->generate('hello');
    }

    /**
     * getBaseUrl() and getExpectedDimension() return the configured values.
     */
    public function test_accessors_return_configured_values(): void
    {
        $provider = new BgeM3EmbeddingProvider($this->baseUrl, 30, $this->dim);

        $this->assertSame($this->baseUrl, $provider->getBaseUrl());
        $this->assertSame($this->dim, $provider->getExpectedDimension());
    }
}
