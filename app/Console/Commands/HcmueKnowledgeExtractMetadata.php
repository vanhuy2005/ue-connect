<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Ingestion\AcademicMetadataExtractor;
use Illuminate\Console\Command;

class HcmueKnowledgeExtractMetadata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:knowledge:extract-metadata 
                            {path : The path to the PDF/document file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test metadata extraction on a specific document file path';

    /**
     * Execute the console command.
     */
    public function handle(AcademicMetadataExtractor $extractor): int
    {
        $path = $this->argument('path');
        $this->info("Extracting metadata for path: '{$path}'");

        try {
            $metadata = $extractor->extract(base_path($path));

            $rows = [];
            foreach ($metadata as $key => $value) {
                $rows[] = ['Field' => $key, 'Value' => is_array($value) ? json_encode($value) : $value];
            }

            $this->table(['Field', 'Value'], $rows);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to extract metadata: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
