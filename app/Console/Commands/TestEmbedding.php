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
    protected $description = 'Test embedding generation: shows provider, endpoint, dimension, first 5 vector values, and elapsed time';

    /**
     * Execute the console command.
     */
    public function handle(EmbeddingService $embeddingService): int
    {
        $text = $this->argument('text');
        $provider = config('ai.embedding.provider', 'gemini');
        $expectedDim = (int) config('ai.qdrant.vector_size', 1024);

        $this->info('=================== EMBEDDING TEST ===================');
        $this->line("Provider:          {$provider}");

        if ($provider === 'bge_m3') {
            $endpoint = config('ai.bge_m3.url', 'https://ntkhoi2005-hcmue-bge-m3-embedding.hf.space');
            $timeout = config('ai.bge_m3.timeout', 120);
            $this->line("Endpoint:          {$endpoint}/embed");
            $this->line("Timeout:           {$timeout}s");
        } else {
            $model = config('ai.embedding.model', 'gemini-embedding-001');
            $this->line("Model:             {$model}");
        }

        $this->line("Expected dim:      {$expectedDim}");
        $this->line("Text:              \"{$text}\"");
        $this->newLine();

        try {
            $start = microtime(true);
            $vector = $embeddingService->embed($text);
            $elapsed = round((microtime(true) - $start) * 1000);

            $actualDim = count($vector);
            $first5 = array_slice($vector, 0, 5);
            $formattedFirst5 = implode(', ', array_map(fn ($v) => number_format($v, 6), $first5));

            $this->line("Actual dimension:  {$actualDim}");
            $this->line("First 5 values:    [{$formattedFirst5}]");
            $this->line("Elapsed time:      {$elapsed} ms");
            $this->newLine();

            if ($actualDim === $expectedDim) {
                $this->info('✓ Dimension OK');

                // If using BGE-M3, do a quick sanity check on vector magnitude
                if ($provider === 'bge_m3') {
                    $magnitude = sqrt(array_sum(array_map(fn ($v) => $v * $v, $vector)));
                    $this->line('Vector magnitude:  '.number_format($magnitude, 6).' (BGE-M3 typically ~1.0 for normalized)');
                }

                return self::SUCCESS;
            }

            $this->error("✗ Dimension mismatch! Expected {$expectedDim}, got {$actualDim}.");

            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error('Failed to generate embedding: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
