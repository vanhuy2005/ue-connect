<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Chat\MajorCatalogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class HcmueMajorsSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:majors:sync
        {--export : Also export the list to storage/app/hcmue_majors.json}
        {--show-aliases : Print all generated aliases for each major}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the HCMUE major catalog from Qdrant, refresh the cache, and optionally export to JSON';

    /**
     * Execute the console command.
     */
    public function handle(MajorCatalogService $catalog): int
    {
        $this->info('=================== HCMUE MAJORS SYNC ===================');
        $this->line('Fetching unique "nganh" values from Qdrant...');
        $this->newLine();

        $majors = $catalog->refresh();

        if (empty($majors)) {
            $this->error('No majors found. Check Qdrant connection and collection name.');
            $this->line('Collection: '.config('ai.qdrant.collection'));
            $this->line('URL:        '.config('ai.qdrant.url'));

            return self::FAILURE;
        }

        $this->info('Found '.count($majors).' unique majors:');
        $this->newLine();

        foreach ($majors as $i => $major) {
            $this->line(sprintf('  %2d. %s', $i + 1, $major));
        }

        $this->newLine();

        if ($this->option('show-aliases')) {
            $this->info('=== Generated Aliases ===');
            foreach ($majors as $major) {
                $this->line("<fg=cyan>{$major}</>");
                $aliases = $catalog->generateAliases($major);
                foreach ($aliases as $alias) {
                    $this->line("   → \"{$alias}\"");
                }
                $this->newLine();
            }

            $this->info('=== Manual Aliases (selected) ===');
            $allAliases = $catalog->aliases();
            $shown = 0;
            foreach ($allAliases as $alias => $canonical) {
                $this->line("  \"{$alias}\" → {$canonical}");
                if (++$shown >= 20) {
                    $this->line('  ... (use --show-aliases to see all)');
                    break;
                }
            }
            $this->newLine();
        }

        if ($this->option('export')) {
            $json = json_encode($majors, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            Storage::put('hcmue_majors.json', $json);
            $this->info('Exported to: storage/app/hcmue_majors.json');
        }

        $this->info('Cache refreshed (TTL: 24 hours, key: '.MajorCatalogService::CACHE_KEY.').');
        $this->info('=========================================================');

        return self::SUCCESS;
    }
}
