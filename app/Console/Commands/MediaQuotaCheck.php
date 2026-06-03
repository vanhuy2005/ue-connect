<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Media\MediaQuotaService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('media:quota-check {--user= : Optional user ID to inspect}')]
#[Description('Show media upload/storage quota usage')]
class MediaQuotaCheck extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(MediaQuotaService $quota): int
    {
        $user = null;
        if ($this->option('user')) {
            $user = User::find((int) $this->option('user'));
            if (! $user) {
                $this->error('User not found.');

                return Command::FAILURE;
            }
        }

        $report = $quota->report($user);
        $global = $report['global'];

        $this->info('--- Media Quota Check ---');
        $this->line('Global daily upload: '.$this->formatBytes($global['daily_upload_bytes']).' / '.$this->formatBytes($global['limits']['global_daily_upload_bytes']));
        $this->line('Global storage footprint: '.$this->formatBytes($global['storage_bytes']));
        $this->line('Cloudinary synced today: '.$global['cloudinary_synced_today'].' / '.$global['limits']['cloudinary_daily_sync_limit']);

        if ($report['user']) {
            $userUsage = $report['user'];
            $this->newLine();
            $this->line('User #'.$userUsage['user_id'].' daily uploads: '.$userUsage['daily_upload_count'].' / '.$userUsage['limits']['daily_upload_count']);
            $this->line('User #'.$userUsage['user_id'].' daily upload bytes: '.$this->formatBytes($userUsage['daily_upload_bytes']).' / '.$this->formatBytes($userUsage['limits']['daily_upload_bytes']));
            $this->line('User #'.$userUsage['user_id'].' monthly upload bytes: '.$this->formatBytes($userUsage['monthly_upload_bytes']).' / '.$this->formatBytes($userUsage['limits']['monthly_upload_bytes']));
            $this->line('User #'.$userUsage['user_id'].' storage footprint: '.$this->formatBytes($userUsage['storage_bytes']));
        }

        $rows = collect($report['top_users_today'])
            ->map(fn ($row): array => [
                'user_id' => $row->user_id,
                'upload_count' => $row->upload_count,
                'upload_mb' => round(((int) $row->upload_bytes) / 1024 / 1024, 2),
            ])
            ->all();

        if ($rows !== []) {
            $this->newLine();
            $this->table(['user_id', 'upload_count', 'upload_mb'], $rows);
        }

        return Command::SUCCESS;
    }

    protected function formatBytes(int $bytes): string
    {
        return round($bytes / 1024 / 1024, 2).' MB';
    }
}
