<?php

namespace App\Console\Commands;

use App\Services\Media\MediaStorageRouter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('media:debug-config')]
#[Description('Show active media storage and delivery configuration')]
class MediaDebugConfig extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cloudinaryCollections = implode(', ', MediaStorageRouter::PUBLIC_CLOUDINARY_COLLECTIONS);

        $this->info('--- Media Debug Config ---');
        $this->line('Strategy: <comment>'.config('media.storage.strategy').'</comment>');
        $this->line('R2 enabled: <comment>'.($this->bool(config('media.r2.enabled'))).'</comment>');
        $this->line('Cloudinary enabled: <comment>'.($this->bool(config('media.providers.cloudinary.enabled'))).'</comment>');
        $this->line('Firebase enabled: <comment>'.($this->bool(config('media.providers.firebase.enabled'))).'</comment>');
        $this->line('Public disk: <comment>'.config('media.public_disk').'</comment>');
        $this->line('Private disk: <comment>'.config('media.private_disk').'</comment>');
        $this->line('R2 public bucket: <comment>'.config('filesystems.disks.r2_public.bucket').'</comment>');
        $this->line('R2 private bucket: <comment>'.config('filesystems.disks.r2_private.bucket').'</comment>');
        $this->line('Cloudinary cloud name: <comment>'.$this->mask(config('media.providers.cloudinary.cloud_name')).'</comment>');
        $this->line('Cloudinary API key: <comment>'.$this->mask(config('media.providers.cloudinary.api_key')).'</comment>');
        $this->line('Cloudinary upload folder: <comment>'.config('media.providers.cloudinary.upload_folder').'</comment>');
        $this->line('Cloudinary fail-open: <comment>'.$this->bool(config('media.providers.cloudinary.fail_open')).'</comment>');
        $this->line('Cloudinary sync public variants: <comment>'.$this->bool(config('media.providers.cloudinary.sync_public_variants')).'</comment>');
        $this->line('Cloudinary eligible collections: <comment>'.$cloudinaryCollections.'</comment>');
        $this->line('User daily upload count limit: <comment>'.config('media.quota.user_daily_upload_count').'</comment>');
        $this->line('User daily upload MB: <comment>'.config('media.quota.user_daily_upload_mb').'</comment>');
        $this->line('User monthly upload MB: <comment>'.config('media.quota.user_monthly_upload_mb').'</comment>');
        $this->line('Global daily upload MB: <comment>'.config('media.quota.global_daily_upload_mb').'</comment>');
        $this->line('Cloudinary daily sync limit: <comment>'.config('media.quota.cloudinary_daily_sync_limit').'</comment>');
        $this->line('Disable Cloudinary at sync limit: <comment>'.$this->bool(config('media.quota.disable_cloudinary_when_limit_reached')).'</comment>');

        return Command::SUCCESS;
    }

    protected function bool(mixed $value): string
    {
        return (bool) $value ? 'true' : 'false';
    }

    protected function mask(?string $value): string
    {
        if (blank($value)) {
            return '<missing>';
        }

        return Str::mask($value, '*', 4, max(strlen($value) - 8, 0));
    }
}
