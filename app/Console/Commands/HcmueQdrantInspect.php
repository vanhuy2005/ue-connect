<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Retrieval\QdrantVectorStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class HcmueQdrantInspect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:qdrant:inspect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inspect Qdrant collection points and dump unique payload metadata values';

    /**
     * Execute the console command.
     */
    public function handle(QdrantVectorStore $vectorStore): int
    {
        $this->info('=================== QDRANT INSPECT ===================');

        $url = rtrim(config('ai.qdrant.url', 'http://localhost:6333'), '/');
        $apiKey = config('ai.qdrant.api_key', '');
        $collection = config('ai.qdrant.collection', 'hcmue_knowledge');

        $this->line("Endpoint:   {$url}");
        $this->line("Collection: {$collection}");
        $this->newLine();

        $headers = [
            'Content-Type' => 'application/json',
        ];
        if (! empty($apiKey)) {
            $headers['api-key'] = $apiKey;
        }

        // 1. Get points count
        $this->comment('Fetching collection information...');
        try {
            $response = Http::withHeaders($headers)
                ->withoutVerifying()
                ->timeout(10)
                ->get("{$url}/collections/{$collection}");

            if ($response->failed()) {
                $this->error('Failed to connect to Qdrant collection: '.$response->body());

                return self::FAILURE;
            }

            $colData = $response->json('result') ?? [];
            $pointsCount = $colData['points_count'] ?? $colData['vectors_count'] ?? 0;
            $this->info("Total points: {$pointsCount}");
        } catch (\Exception $e) {
            $this->error('Connection error: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();

        // 2. Fetch unique values
        $fields = [
            'khoa_hoc' => 'Unique Cohorts (khoa_hoc)',
            'cohort' => 'Unique Cohorts (cohort)',
            'nganh' => 'Unique Majors (nganh)',
            'major' => 'Unique Majors (major)',
            'knowledge_type' => 'Unique Knowledge Types',
            'loai_tai_lieu' => 'Unique Document Types (loai_tai_lieu)',
            'document_type' => 'Unique Document Types (document_type)',
        ];

        foreach ($fields as $field => $label) {
            $this->comment("Fetching {$label}...");
            try {
                $values = $vectorStore->scrollUniquePayloadValues($field, [], 250, 100);
                if (empty($values)) {
                    $this->line('  (None)');
                } else {
                    sort($values);
                    foreach ($values as $val) {
                        $this->line("  - \"{$val}\"");
                    }
                }
            } catch (\Exception $e) {
                $this->warn('  Failed to fetch: '.$e->getMessage());
            }
            $this->newLine();
        }

        $this->info('======================================================');

        return self::SUCCESS;
    }
}
