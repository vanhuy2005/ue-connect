<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class RealtimeHealthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ueconnect:realtime-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the health of the realtime system including Redis, Cache, Queue, and Reverb';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('--- Realtime System Health Check ---');

        // Check Redis
        try {
            Redis::connection()->ping();
            $this->info('Redis: OK');
        } catch (\Exception $e) {
            $this->error('Redis: Disconnected');
        }

        // Check Cache
        $cacheStore = config('cache.default');
        $this->info("Cache store: {$cacheStore}");

        // Check Queue
        $queueConnection = config('queue.default');
        $this->info("Queue connection: {$queueConnection}");

        // Check Broadcast
        $broadcastConnection = config('broadcasting.default');
        $this->info("Broadcast connection: {$broadcastConnection}");

        // Check Jobs
        try {
            $pendingJobs = DB::table('jobs')->count();
            $this->info("Database jobs pending: {$pendingJobs}");
        } catch (\Exception $e) {
            $this->info('Database jobs pending: N/A (Table missing or inaccessible)');
        }

        try {
            $failedJobs = DB::table('failed_jobs')->count();
            $this->info("Failed jobs: {$failedJobs}");
        } catch (\Exception $e) {
            $this->info('Failed jobs: N/A (Table missing or inaccessible)');
        }

        // Check Reverb Config
        $reverbHost = env('REVERB_HOST');
        if ($reverbHost) {
            $this->info('Reverb host configured: yes');
        } else {
            $this->error('Reverb host configured: no');
        }
    }
}
