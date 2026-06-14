<?php

namespace App\Console\Commands;

use App\Services\CareerPathway\CareerPathwayAuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CareerPathwayAuditImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'career-pathway:audit-import 
                            {sourcePath : The directory containing source roadmap.md files} 
                            {--json : Output results as JSON} 
                            {--csv : Output results as CSV (if not using --output)} 
                            {--fail-on-mismatch : Exit with failure code if any mismatches are found} 
                            {--sample= : Audit only a sample of files} 
                            {--program= : Audit a specific program (e.g. "Khoa CNTT - Ngành CNTT")} 
                            {--status= : Filter by a specific program status} 
                            {--output= : Directory to output JSON and CSV files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit whether all generated HCMUE markdown roadmap data has been imported into the DB correctly';

    /**
     * Execute the console command.
     */
    public function handle(CareerPathwayAuditService $auditService)
    {
        $sourcePath = $this->argument('sourcePath');
        $options = $this->options();

        if (! is_dir($sourcePath)) {
            $this->error("Source path does not exist or is not a directory: $sourcePath");

            return self::FAILURE;
        }

        $this->info("Starting Career Pathway Import Audit for: $sourcePath");
        Log::channel('single')->info("Starting Career Pathway Import Audit for: $sourcePath");

        try {
            $results = $auditService->runAudit($sourcePath, $options);
        } catch (\Exception $e) {
            $this->error('Audit failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $summary = $results['summary'];
        $mismatches = $results['mismatches'];

        if ($this->option('json')) {
            $this->line(json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            // Print Summary Table
            $this->info("\n=== AUDIT SUMMARY ===");
            $summaryRows = [];
            foreach ($summary as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subKey => $subValue) {
                        $summaryRows[] = ["{$key} -> {$subKey}", $subValue];
                    }
                } else {
                    $summaryRows[] = [$key, $value];
                }
            }
            $this->table(['Metric', 'Value'], $summaryRows);

            // Print Mismatches Table
            if (count($mismatches) > 0) {
                $this->error("\n=== DETAILED MISMATCHES (".count($mismatches).' found) ===');

                // Group by severity for better reading
                $groupedMismatches = collect($mismatches)->groupBy('severity');

                foreach (['critical', 'high', 'medium', 'low'] as $severity) {
                    if ($groupedMismatches->has($severity)) {
                        $this->warn("\n--- SEVERITY: ".strtoupper($severity).' ---');
                        $severityMismatches = $groupedMismatches->get($severity);
                        $tableRows = [];
                        foreach ($severityMismatches as $mismatch) {
                            $tableRows[] = [
                                $mismatch['issue_type'],
                                "{$mismatch['cohort']} / {$mismatch['faculty']} / {$mismatch['major']}",
                                $mismatch['expected_value'],
                                $mismatch['actual_value'],
                                $mismatch['message'],
                            ];
                        }
                        $this->table(['Issue Type', 'Program', 'Expected', 'Actual', 'Message'], $tableRows);
                    }
                }
            } else {
                $this->info("\n=== DETAILED MISMATCHES ===");
                $this->info('No mismatches found! Data is fully consistent.');
            }
        }

        if ($this->option('output')) {
            $this->info("\nDetailed exports written to: ".$this->option('output'));
        }

        if ($this->option('fail-on-mismatch') && count($mismatches) > 0) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
