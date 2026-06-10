<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Chat\CohortCatalogService;
use Illuminate\Console\Command;

class HcmueCohortsList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:cohorts:list {--refresh : Refresh the cached catalog from Qdrant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all unique cohorts stored in Qdrant';

    /**
     * Execute the console command.
     */
    public function handle(CohortCatalogService $catalog): int
    {
        $this->info('=================== HCMUE COHORTS LIST ===================');

        if ($this->option('refresh')) {
            $this->comment('Refreshing cohort catalog cache from Qdrant...');
            $cohorts = $catalog->refresh();
        } else {
            $cohorts = $catalog->allCohorts();
        }

        if (empty($cohorts)) {
            $this->error('No cohorts found. Check Qdrant connectivity.');

            return self::FAILURE;
        }

        $this->info('Found '.count($cohorts).' unique cohorts:');
        $this->newLine();

        foreach ($cohorts as $i => $cohort) {
            $this->line(sprintf('  %2d. %s', $i + 1, $cohort));
        }

        $this->newLine();
        $this->info('==========================================================');

        return self::SUCCESS;
    }
}
