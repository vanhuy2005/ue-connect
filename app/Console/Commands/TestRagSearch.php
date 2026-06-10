<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Retrieval\RagRetrievalService;
use Illuminate\Console\Command;

class TestRagSearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:rag:test-search 
                            {query : Search query} 
                            {--cohort= : Filter by cohort}
                            {--type= : Filter by document type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test RAG vector search retrieval against Qdrant';

    /**
     * Execute the console command.
     */
    public function handle(RagRetrievalService $retrievalService): int
    {
        $query = $this->argument('query');

        $filters = [];
        if ($cohort = $this->option('cohort')) {
            $filters['cohort'] = $cohort;
        }
        if ($type = $this->option('type')) {
            $filters['document_type'] = $type;
        }

        $this->info("Searching for: '{$query}'");
        if (! empty($filters)) {
            $this->info('Filters applied: '.json_encode($filters));
        }
        $this->info('--------------------------------------------------');

        $results = $retrievalService->retrieve($query, $filters);

        if (empty($results)) {
            $this->warn('No matching chunks found above threshold score.');

            return self::SUCCESS;
        }

        foreach ($results as $index => $result) {
            $this->comment(sprintf('[%d] Score: %.4f | Doc: %s (%s)', $index + 1, $result['score'], $result['document_name'], $result['document_type']));

            if ($result['part'] || $result['chapter'] || $result['section'] || $result['article']) {
                $this->info(sprintf('Loc: Part: %s | Chap: %s | Sec: %s | Art: %s',
                    $result['part'] ?: 'N/A',
                    $result['chapter'] ?: 'N/A',
                    $result['section'] ?: 'N/A',
                    $result['article'] ?: 'N/A'
                ));
            }

            $this->line('Page Start: '.($result['page_start'] ?: 'N/A').' | Page End: '.($result['page_end'] ?: 'N/A'));
            $this->line(trim($result['chunk_text']));
            $this->line('--------------------------------------------------');
        }

        return self::SUCCESS;
    }
}
