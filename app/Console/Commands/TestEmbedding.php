<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\LLM\EmbeddingService;
use Illuminate\Console\Command;

class TestEmbedding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:embedding:test {text : The text to embed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test embedding generation and verify dimensions';

    /**
     * Execute the console command.
     */
    public function handle(EmbeddingService $embeddingService): int
    {
        $text = $this->argument('text');
        $this->info("Sending text to embed: \"{$text}\"");

        try {
            $vector = $embeddingService->embed($text);
            $length = count($vector);

            $this->info('Provider: '.env('AI_EMBEDDING_DRIVER', 'gemini'));
            $this->info('Model: '.env('AI_EMBEDDING_MODEL', 'text-embedding-004'));
            $this->info('Configured dimension: '.config('ai.qdrant.vector_size', 768));
            $this->info("Actual vector length: {$length}");

            if ($length === (int) config('ai.qdrant.vector_size', 768)) {
                $this->info('OK');

                return 0;
            } else {
                $this->error('Dimension mismatch!');

                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Failed to generate embedding: '.$e->getMessage());

            return 1;
        }
    }
}
