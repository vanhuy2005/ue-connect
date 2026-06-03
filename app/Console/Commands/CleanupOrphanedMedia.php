<?php

namespace App\Console\Commands;

use App\Actions\Media\DeleteMediaAction;
use App\Models\Media;
use Illuminate\Console\Command;

class CleanupOrphanedMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:cleanup-orphaned';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned media records that have no parent model';

    /**
     * Execute the console command.
     */
    public function handle(DeleteMediaAction $deleteAction): int
    {
        // Select media records with no parent and created more than 24 hours ago
        $orphanedMedia = Media::whereNull('mediable_id')
            ->whereIn('status', ['ready', 'failed'])
            ->where('created_at', '<', now()->subDay())
            ->get();

        $count = $orphanedMedia->count();
        $this->info("Found {$count} orphaned media assets.");

        foreach ($orphanedMedia as $media) {
            $deleteAction->execute($media);
            $this->line("Deleted orphaned media ID: {$media->id} (Collection: {$media->collection})");
        }

        $this->info('Completed orphaned media cleanup.');

        return Command::SUCCESS;
    }
}
