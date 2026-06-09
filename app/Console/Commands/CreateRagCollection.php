<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Retrieval\QdrantVectorStore;
use Illuminate\Console\Command;

class CreateRagCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:rag:create-collection 
                            {--force : Force recreate collection if it already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the Qdrant vector store collection for HCMUE Chatbot RAG';

    /**
     * Execute the console command.
     */
    public function handle(QdrantVectorStore $vectorStore): int
    {
        $collectionName = config('ai.qdrant.collection', 'hcmue_academic_chunks');
        $driver = env('AI_LLM_DRIVER', 'gemini');

        // Determine vector size
        // Gemini text-embedding-004 is 768 dimensions.
        // OpenAI text-embedding-3-small / ada-002 are 1536 dimensions.
        $vectorSize = ($driver === 'openai') ? 1536 : 768;

        $this->info("Target Qdrant Driver: {$driver}");
        $this->info("Target Vector Dimension Size: {$vectorSize}");
        $this->info("Checking collection: {$collectionName}...");

        if ($vectorStore->collectionExists()) {
            if ($this->option('force')) {
                $this->warn("Collection '{$collectionName}' already exists. Deleting it first due to --force...");
                $vectorStore->deleteCollection();
            } else {
                $this->info("Collection '{$collectionName}' already exists. No action taken.");

                return self::SUCCESS;
            }
        }

        $this->info("Creating collection '{$collectionName}'...");
        $created = $vectorStore->createCollection($vectorSize);

        if ($created) {
            $this->info("Successfully created collection '{$collectionName}'!");

            return self::SUCCESS;
        }

        $this->error("Failed to create collection '{$collectionName}'. Please check Qdrant connection logs.");

        return self::FAILURE;
    }
}
