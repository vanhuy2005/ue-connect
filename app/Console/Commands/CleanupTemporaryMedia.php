<?php

namespace App\Console\Commands;

use App\Actions\Media\DeleteMediaAction;
use App\Models\Media;
use Illuminate\Console\Command;

class CleanupTemporaryMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:cleanup-temporary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired temporary uploads that were never promoted';

    /**
     * Execute the console command.
     */
    public function handle(DeleteMediaAction $deleteAction): int
    {
        $ttlMinutes = config('media.processing.temp_ttl_minutes', 60);
        $threshold = now()->subMinutes($ttlMinutes);

        $expiredMedia = Media::where('status', 'temporary')
            ->where('created_at', '<', $threshold)
            ->get();

        $count = $expiredMedia->count();
        $this->info("Found {$count} expired temporary media assets.");

        foreach ($expiredMedia as $media) {
            $deleteAction->execute($media);
            $this->line("Deleted temporary media ID: {$media->id} (Created at: {$media->created_at})");
        }

        $this->info('Completed temporary media cleanup.');

        return Command::SUCCESS;
    }
}
