<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Retrieval\QdrantVectorStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class HcmueQdrantCreateCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:qdrant:create-collection 
                            {--recreate : Force delete and recreate the collection if it exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or verify the Qdrant vector collection with payload indexes for academic chatbot';

    /**
     * Execute the console command.
     */
    public function handle(QdrantVectorStore $vectorStore): int
    {
        $collectionName = config('ai.qdrant.collection', 'hcmue_academic_chunks');
        $vectorSize = config('ai.qdrant.vector_size', 768);
        $distance = config('ai.qdrant.distance', 'Cosine');

        $this->info("Target Qdrant Collection: {$collectionName}");
        $this->info("Vector Dimensions:       {$vectorSize}");
        $this->info("Distance Metric:         {$distance}");

        $exists = $vectorStore->collectionExists($collectionName);

        if ($exists) {
            if ($this->option('recreate')) {
                $this->warn("Collection '{$collectionName}' already exists. Recreating as requested (--recreate)...");
                $vectorStore->deleteCollection($collectionName);
            } else {
                $this->info("Collection '{$collectionName}' already exists. Verifying parameters...");
                
                // Verify parameters by querying Qdrant directly
                $url = rtrim(config('ai.qdrant.url'), '/');
                $apiKey = config('ai.qdrant.api_key');
                $headers = ['Content-Type' => 'application/json'];
                if ($apiKey) {
                    $headers['api-key'] = $apiKey;
                }

                $response = Http::withHeaders($headers)
                    ->withoutVerifying()
                    ->get("{$url}/collections/{$collectionName}");

                if ($response->successful()) {
                    $config = $response->json('result.config.params.vectors');
                    $currentSize = $config['size'] ?? null;
                    $currentDistance = $config['distance'] ?? null;

                    if ($currentSize == $vectorSize && strtolower($currentDistance) == strtolower($distance)) {
                        $this->info("Collection verified! Parameters match (Dimensions: {$currentSize}, Distance: {$currentDistance}).");
                        $this->createAllPayloadIndexes($vectorStore, $collectionName);
                        return self::SUCCESS;
                    } else {
                        $this->error("Parameter mismatch! Collection has size {$currentSize} ({$currentDistance}), expected {$vectorSize} ({$distance}).");
                        $this->line("Please run with --recreate to fix the configuration.");
                        return self::FAILURE;
                    }
                } else {
                    $this->error("Failed to query collection configuration: " . $response->body());
                    return self::FAILURE;
                }
            }
        }

        $this->info("Creating collection '{$collectionName}'...");
        $created = $vectorStore->createCollection($vectorSize, $distance, $collectionName);

        if (! $created) {
            $this->error("Failed to create collection '{$collectionName}'.");
            return self::FAILURE;
        }

        $this->info("Collection '{$collectionName}' created successfully.");
        $this->createAllPayloadIndexes($vectorStore, $collectionName);

        return self::SUCCESS;
    }

    /**
     * Create all required payload indexes.
     */
    protected function createAllPayloadIndexes(QdrantVectorStore $vectorStore, string $collectionName): void
    {
        $this->info('Creating payload indexes...');

        $indexes = [
            'document_type' => 'keyword',
            'cohort' => 'keyword',
            'academic_year' => 'keyword',
            'faculty' => 'keyword',
            'major' => 'keyword',
            'normalized_major' => 'keyword',
            'source_document_id' => 'integer',
            'visibility' => 'keyword',
            'knowledge_batch_id' => 'keyword',
        ];

        foreach ($indexes as $field => $type) {
            $this->line("- Indexing '{$field}' as '{$type}'...");
            $indexed = $vectorStore->createPayloadIndex($field, $type, $collectionName);
            if (! $indexed) {
                $this->warn("  Failed to create index for field: {$field}");
            }
        }

        $this->info('All payload indexes created.');
    }
}
