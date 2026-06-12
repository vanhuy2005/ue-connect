<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HcmueCatalogSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:catalog:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the cohort & major catalog mapping and validation data from Qdrant payloads safely';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=================== HCMUE CATALOG SYNC ===================');
        $this->comment('Fetching unique khoa_hoc and nganh from Qdrant payloads...');

        $url = rtrim(config('ai.qdrant.url', 'http://localhost:6333'), '/');
        $apiKey = config('ai.qdrant.api_key', '');
        $collection = config('ai.qdrant.collection', 'hcmue_academic_chunks');

        $headers = ['Content-Type' => 'application/json'];
        if (! empty($apiKey)) {
            $headers['api-key'] = $apiKey;
        }

        $offset = null;
        $byCohort = [];
        $byMajor = [];
        $cohorts = [];
        $majors = [];
        $pointsProcessed = 0;

        try {
            do {
                $body = [
                    'limit' => 500,
                    'with_payload' => ['include' => ['khoa_hoc', 'nganh']],
                    'with_vector' => false,
                ];
                if ($offset) {
                    $body['offset'] = $offset;
                }

                $response = Http::withHeaders($headers)
                    ->withoutVerifying()
                    ->timeout(30)
                    ->post("{$url}/collections/{$collection}/points/scroll", $body);

                if ($response->failed()) {
                    throw new \Exception('Qdrant scroll API request failed: Status '.$response->status().' - '.$response->body());
                }

                $result = $response->json('result') ?? [];
                $points = $result['points'] ?? [];
                $pointsProcessed += count($points);

                foreach ($points as $point) {
                    $payload = $point['payload'] ?? [];
                    $cohort = $payload['khoa_hoc'] ?? null;
                    $major = $payload['nganh'] ?? null;

                    if ($cohort && $major) {
                        $cohort = trim($cohort);
                        $major = trim($major);

                        if ($cohort !== '' && $major !== '') {
                            if (! in_array($cohort, $cohorts, true)) {
                                $cohorts[] = $cohort;
                            }
                            if (! in_array($major, $majors, true)) {
                                $majors[] = $major;
                            }

                            if (! isset($byCohort[$cohort])) {
                                $byCohort[$cohort] = [];
                            }
                            if (! in_array($major, $byCohort[$cohort], true)) {
                                $byCohort[$cohort][] = $major;
                            }

                            if (! isset($byMajor[$major])) {
                                $byMajor[$major] = [];
                            }
                            if (! in_array($cohort, $byMajor[$major], true)) {
                                $byMajor[$major][] = $cohort;
                            }
                        }
                    }
                }

                $offset = $result['next_page_offset'] ?? null;
            } while ($offset);

            if (empty($cohorts) || empty($majors)) {
                throw new \Exception('Qdrant returned empty cohorts or majors. Sync aborted to prevent overwriting existing data.');
            }

            // Sort lists
            sort($cohorts);
            sort($majors);
            foreach ($byCohort as $c => &$ms) {
                sort($ms);
            }
            foreach ($byMajor as $m => &$cs) {
                sort($cs);
            }

            // Total relationships count
            $relCount = 0;
            foreach ($byCohort as $c => $ms) {
                $relCount += count($ms);
            }

            $catalogData = [
                'cohorts' => $cohorts,
                'majors' => $majors,
                'by_cohort' => $byCohort,
                'by_major' => $byMajor,
            ];

            // Safe write to a temporary file first
            $tempPath = 'hcmue_catalog_temp_'.time().'.json';
            Storage::put($tempPath, json_encode($catalogData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            if (! Storage::exists($tempPath) || Storage::size($tempPath) === 0) {
                throw new \Exception('Failed to write data to temporary file.');
            }

            // Atomic replacement
            Storage::put('hcmue_catalog.json', Storage::get($tempPath));
            Storage::delete($tempPath);

            // Cache in memory / Laravel cache
            Cache::put('hcmue_catalog_data', $catalogData, 86400 * 30); // 30 days

            $this->info('Catalog Sync Succeeded!');
            $this->line('- Processed points:      '.$pointsProcessed);
            $this->line('- Total Cohorts:         '.count($cohorts));
            $this->line('- Total Majors:          '.count($majors));
            $this->line('- Total Relationships:   '.$relCount);

            Log::info('HCMUE Catalog Sync successfully completed.', [
                'cohorts_count' => count($cohorts),
                'majors_count' => count($majors),
                'relationships_count' => $relCount,
            ]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Sync Failed: '.$e->getMessage());
            Log::error('HCMUE Catalog Sync failed: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return self::FAILURE;
        }
    }
}
