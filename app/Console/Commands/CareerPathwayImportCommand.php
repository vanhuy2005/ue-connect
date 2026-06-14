<?php

namespace App\Console\Commands;

use App\Services\CareerPathway\CareerPathwayImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CareerPathwayImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'career-pathway:import {directory? : The directory containing roadmap.md files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Career Pathway markdown files into the database';

    /**
     * Execute the console command.
     */
    public function handle(CareerPathwayImportService $importService)
    {
        $directory = $this->argument('directory') ?? base_path('database/HCMUE-db_md');

        $this->info("Starting Career Pathway Import from: $directory");
        Log::channel('single')->info("Starting Career Pathway Import from: $directory"); // Or custom channel if configured

        $run = $importService->importFromDirectory($directory, $this->output);

        $this->info("Import finished with status: {$run->status->value}");

        return self::SUCCESS;
    }
}
