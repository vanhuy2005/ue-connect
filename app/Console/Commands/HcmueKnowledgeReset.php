<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Retrieval\QdrantVectorStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HcmueKnowledgeReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:knowledge:reset
                            {--force : Force execution without confirmation}
                            {--drop-qdrant : Drop the configured Qdrant collection}
                            {--keep-logs : Do not delete ai_questions, ai_answers, and chat logs}
                            {--keep-feedback : Do not delete ai_feedback}
                            {--dry-run : Print what would be deleted without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely clear all academic chatbot knowledge databases, Qdrant vectors, and training program definitions without affecting user/social data.';

    /**
     * Execute the console command.
     */
    public function handle(QdrantVectorStore $vectorStore): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $dropQdrant = $this->option('drop-qdrant');
        $keepLogs = $this->option('keep-logs');
        $keepFeedback = $this->option('keep-feedback');

        if ($dryRun) {
            $this->info('--- DRY RUN MODE ---');
        }

        if (! $force && ! $dryRun) {
            if (! $this->confirm('WARNING: This will clear chatbot training data and programs. Are you sure you want to proceed?')) {
                $this->warn('Reset cancelled.');

                return self::SUCCESS;
            }
        }

        $knowledgeTables = [
            'training_program_extraction_candidates',
            'ai_retrieved_chunks',
            'ai_structured_queries',
            'document_chunks',
            'source_documents',
            'program_learning_outcomes',
            'curriculum_courses',
            'curriculum_course_groups',
            'training_programs',
            'knowledge_batches',
        ];

        $logTables = [
            'ai_answers',
            'ai_questions',
            'chat_messages',
            'chat_sessions',
        ];

        $feedbackTables = [
            'ai_feedback',
        ];

        $tablesToProcess = $knowledgeTables;
        if (! $keepLogs) {
            $tablesToProcess = array_merge($tablesToProcess, $logTables);
        }
        if (! $keepFeedback) {
            $tablesToProcess = array_merge($tablesToProcess, $feedbackTables);
        }

        $this->info('Calculating records to be deleted...');
        $summary = [];

        foreach ($tablesToProcess as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $summary[$table] = $count;
            } else {
                $summary[$table] = 'Table not found';
            }
        }

        $this->info('--- RESET SUMMARY ---');
        foreach ($summary as $table => $count) {
            if (is_numeric($count)) {
                $this->line("- Table '{$table}': {$count} records would be DELETED.");
            } else {
                $this->line("- Table '{$table}': {$count}");
            }
        }

        if ($dropQdrant) {
            $collection = config('ai.qdrant.collection');
            $this->line("- Qdrant Collection '{$collection}': Would be DROPPED & RECREATED.");
        }

        if ($dryRun) {
            $this->info('Dry run completed. No data was modified.');
            $this->printReminders();

            return self::SUCCESS;
        }

        // Execute deletions in order of foreign key dependency hierarchy
        $this->warn('Executing database reset...');

        DB::transaction(function () use ($tablesToProcess) {
            // Disable foreign key constraints during purge
            $this->disableForeignKeys();

            foreach ($tablesToProcess as $table) {
                if (Schema::hasTable($table)) {
                    $deleted = DB::table($table)->delete();
                    $this->line("Purged table '{$table}' ({$deleted} records).");
                }
            }

            // Restore faculties fields added by chatbot migration if needed
            if (Schema::hasTable('faculties')) {
                // We keep the faculties table but clear added programs
                // Nothing extra to do, cascade took care of it
            }

            $this->enableForeignKeys();
        });

        $this->info('Database tables successfully cleared.');

        if ($dropQdrant) {
            $collectionName = config('ai.qdrant.collection');
            $this->warn("Dropping Qdrant collection '{$collectionName}'...");
            if ($vectorStore->collectionExists()) {
                $deletedCol = $vectorStore->deleteCollection();
                if ($deletedCol) {
                    $this->info("Collection '{$collectionName}' dropped successfully from Qdrant Cloud.");
                } else {
                    $this->error("Failed to drop collection '{$collectionName}'.");
                }
            } else {
                $this->line("Collection '{$collectionName}' does not exist in Qdrant.");
            }
        }

        $this->info('Knowledge base reset completed successfully.');
        $this->printReminders();

        return self::SUCCESS;
    }

    protected function printReminders(): void
    {
        $this->newLine();
        $this->info('Next steps to rebuild the chatbot index:');
        $this->line('1. php artisan hcmue:qdrant:create-collection');
        $this->line('2. php artisan hcmue:knowledge:inventory --path=database/AI/Chuongtrinhdaotao');
        $this->line('3. php artisan hcmue:knowledge:import-directory --path=database/AI/Chuongtrinhdaotao --sync');
    }

    protected function disableForeignKeys(): void
    {
        $driverName = DB::connection()->getDriverName();
        if ($driverName === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } elseif ($driverName === 'sqlsrv') {
            // Disable all constraints
            DB::statement('EXEC sp_MSforeachtable "ALTER TABLE ? NOCHECK CONSTRAINT all"');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }
    }

    protected function enableForeignKeys(): void
    {
        $driverName = DB::connection()->getDriverName();
        if ($driverName === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } elseif ($driverName === 'sqlsrv') {
            // Enable all constraints
            DB::statement('EXEC sp_MSforeachtable "ALTER TABLE ? WITH CHECK CHECK CONSTRAINT all"');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
