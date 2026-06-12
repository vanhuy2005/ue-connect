<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Retrieval\QdrantDiagnosticsService;
use Illuminate\Console\Command;

class HcmueQdrantDiagnose extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:qdrant:diagnose';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform detailed diagnostics on Qdrant Vector Store connectivity, structure, and retrieval';

    /**
     * Execute the console command.
     */
    public function handle(QdrantDiagnosticsService $diagnosticsService): int
    {
        $this->info('Initializing Qdrant Vector Store diagnostics...');
        $this->info('-----------------------------------------------------------------');

        $report = $diagnosticsService->diagnose();

        // 1. Connection Check
        $this->line('CONNECTION HEALTH:');
        $this->line('Endpoint:           '.config('ai.qdrant.url'));
        $this->line('Status:             '.($report['reachable'] ? 'REACHABLE (OK)' : 'UNREACHABLE (FAIL)'));
        if ($report['version']) {
            $this->line('Version:            '.$report['version']);
        }
        $this->newLine();

        if (! $report['reachable']) {
            $this->error('CRITICAL ERROR: Unable to establish contact with Qdrant server.');
            if ($report['error']) {
                $this->error($report['error']);
            }

            return self::FAILURE;
        }

        // 2. Collection Check
        $this->line('COLLECTION CONFIGURATION:');
        $this->line('Collection Name:    '.config('ai.qdrant.collection'));
        $this->line('Exists:             '.($report['collection_exists'] ? 'YES' : 'NO'));
        if ($report['collection_exists']) {
            $this->line('Status:             '.strtoupper($report['status']));
            $this->line('Points Count:       '.$report['points_count']);
            $this->line('Vector Size (Dims): '.($report['vector_size'] ?: 'N/A'));
            $this->line('Distance Metric:    '.($report['distance'] ?: 'N/A'));
            $this->line('Payload Indexes:');
            if (empty($report['payload_indexes'])) {
                $this->line('     - None');
            } else {
                foreach ($report['payload_indexes'] as $field => $schema) {
                    $this->line("     - {$field} (".($schema['data_type'] ?? 'unknown').')');
                }
            }
        }
        $this->newLine();

        if (! $report['collection_exists']) {
            $this->error("CRITICAL ERROR: Collection does not exist. You can run 'php artisan hcmue:rag:create-collection' to initialize it.");
            if ($report['error']) {
                $this->error($report['error']);
            }

            return self::FAILURE;
        }

        // 3. Sample Points scroll check
        $this->line('SAMPLE POINTS IN COLLECTION (Up to 3):');
        if (empty($report['samples'])) {
            $this->warn('No points indexed in this collection yet.');
        } else {
            foreach ($report['samples'] as $point) {
                $payload = $point['payload'] ?? [];
                $this->comment("Point ID: {$point['id']}");
                $this->line(' - Document: '.($payload['document_name'] ?? 'N/A').' (ID: '.($payload['source_document_id'] ?? 'N/A').')');
                $this->line(' - Type:     '.($payload['document_type'] ?? 'N/A').' | Cohort: '.($payload['cohort'] ?? 'N/A').' | Year: '.($payload['academic_year'] ?? 'N/A'));
                $this->line(' - Faculty:  '.($payload['faculty'] ?? 'N/A').' | Major: '.($payload['major'] ?? 'N/A'));
                $this->line(' - Text:     '.mb_substr($payload['chunk_text'] ?? '', 0, 80).'...');
                $this->newLine();
            }
        }

        // 4. Search Test Performance
        $this->line('SEARCH RETRIEVAL PERFORMANCE TEST:');
        $test = $report['search_test'];
        if ($test['success']) {
            $this->line('Status:             SUCCESS');
            $this->line('Search Latency:     '.$test['latency_ms'].' ms');
            $this->line('Results Fetched:    '.$test['results_count']);
        } else {
            $this->error('Status:             FAILED');
            if ($report['error']) {
                $this->error($report['error']);
            }
        }

        $this->info('-----------------------------------------------------------------');
        $this->info('Diagnostics complete.');

        return self::SUCCESS;
    }
}
