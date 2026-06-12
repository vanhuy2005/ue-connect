<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class HcmueKnowledgeRebuild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:knowledge:rebuild
                            {--path=database/AI/Chuongtrinhdaotao : Directory path relative to project root}
                            {--force : Force reset and rebuild without confirmation}
                            {--drop-qdrant : Recreate the Qdrant collection during reset}
                            {--sync : Run indexing synchronously}
                            {--limit= : Limit imported files count}
                            {--only-cohort= : Filter by cohort}
                            {--only-major= : Filter by major}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Orchestrate complete reset, recreate, inventory, import directory, and smoke test chatbot ingestion pipeline';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = $this->option('path');
        $force = $this->option('force');
        $dropQdrant = $this->option('drop-qdrant');
        $sync = $this->option('sync');
        $limit = $this->option('limit');
        $onlyCohort = $this->option('only-cohort');
        $onlyMajor = $this->option('only-major');

        if (! $force) {
            $confirm = $this->confirm("WARNING: This will wipe your chatbot DB and Qdrant collection '{$path}' and rebuild it. Continue?");
            if (! $confirm) {
                $this->warn('Rebuild aborted.');

                return self::SUCCESS;
            }
        }

        $this->info('=================================================================');
        $this->info('         STARTING CHATBOT KNOWLEDGE BASE REBUILD PIPELINE        ');
        $this->info('=================================================================');

        // Step 1: Reset DB & Qdrant
        $this->info("\n>>> STEP 1: Resetting database and Qdrant collection...");
        $this->call('hcmue:knowledge:reset', [
            '--force' => true,
            '--drop-qdrant' => $dropQdrant,
        ]);

        // Step 2: Create Qdrant Collection (re-register indexes)
        $this->info("\n>>> STEP 2: Creating Qdrant collection and indexes...");
        $this->call('hcmue:qdrant:create-collection');

        // Step 3: Run directory inventory
        $this->info("\n>>> STEP 3: Running inventory analysis...");
        $this->call('hcmue:knowledge:inventory', [
            '--path' => $path,
        ]);

        // Step 4: Import directory contents
        $this->info("\n>>> STEP 4: Importing and indexing files...");
        $this->call('hcmue:knowledge:import-directory', [
            '--path' => $path,
            '--sync' => $sync,
            '--limit' => $limit,
            '--force' => true,
            '--only-cohort' => $onlyCohort,
            '--only-major' => $onlyMajor,
        ]);

        // Step 5: Verify status via diagnose
        $this->info("\n>>> STEP 5: Diagnosing vector store health...");
        $this->call('hcmue:qdrant:diagnose');

        // Step 6: Smoke tests
        $this->info("\n>>> STEP 6: Running chatbot pipeline smoke tests...");
        $this->runSmokeTests();

        return self::SUCCESS;
    }

    /**
     * Run smoke tests to ensure queries are resolved successfully.
     */
    protected function runSmokeTests(): void
    {
        $testQueries = [
            'K49 CNTT cần bao nhiêu tín chỉ để tốt nghiệp?',
            'K49 Công nghệ thông tin cần bao nhiêu tín chỉ?',
            'Chuẩn đầu ra ngành Công nghệ thông tin K49 là gì?',
            'Điều kiện tốt nghiệp là gì?',
        ];

        $allPassed = true;

        foreach ($testQueries as $query) {
            $this->newLine();
            $this->comment("Testing query: \"{$query}\"");

            try {
                // Call chat debug programmatically to capture and print results
                $exitCode = $this->callSilent('hcmue:chat:debug', [
                    'question' => $query,
                ]);

                if ($exitCode !== 0) {
                    $allPassed = false;
                    $this->error("  -> Smoke test FAILED for: \"{$query}\"");
                } else {
                    $this->info("  -> Smoke test PASSED for: \"{$query}\"");
                }
            } catch (\Exception $e) {
                $allPassed = false;
                $this->error('  -> Smoke test CRASHED with error: '.$e->getMessage());
            }
        }

        $this->newLine();
        $this->info('=================================================================');
        if ($allPassed) {
            $this->info('       ALL SMOKE TESTS PASSED! CHATBOT PIPELINE READY.           ');
        } else {
            $this->error('   SOME SMOKE TESTS FAILED. CHECK ABOVE DETAILS FOR TROUBLESHOOTING.  ');
        }
        $this->info('=================================================================');
    }
}
