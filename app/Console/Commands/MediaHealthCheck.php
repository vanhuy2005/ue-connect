<?php

namespace App\Console\Commands;

use App\Services\Media\Providers\CloudinaryMediaDeliveryProvider;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class MediaHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:health-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform diagnostic health checks on media storage configurations and disks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('--- Starting Media Storage Health Check ---');

        $strategy = (string) config('media.storage.strategy', config('media.default_strategy', 'local_only'));
        $r2Enabled = (bool) config('media.r2.enabled', config('media.providers.r2.enabled', false));
        $cloudinaryEnabled = (bool) config('media.providers.cloudinary.enabled', false);
        $firebaseEnabled = (bool) config('media.providers.firebase.enabled', false);
        $publicDisk = (string) config('media.public_disk', 'public');
        $privateDisk = (string) config('media.private_disk', 'local');
        $r2PublicDisk = (string) config('media.providers.r2.public_disk', 'r2_public');
        $r2PrivateDisk = (string) config('media.providers.r2.private_disk', 'r2_private');

        $this->line("Active storage strategy: <comment>{$strategy}</comment>");
        $this->line('media.r2.enabled: <comment>'.($r2Enabled ? 'true' : 'false').'</comment>');
        $this->line('media.providers.cloudinary.enabled: <comment>'.($cloudinaryEnabled ? 'true' : 'false').'</comment>');
        $this->line('media.providers.firebase.enabled: <comment>'.($firebaseEnabled ? 'true' : 'false').'</comment>');
        $this->line("Configured public disk: <comment>{$publicDisk}</comment>");
        $this->line("Configured private disk: <comment>{$privateDisk}</comment>");

        $this->newLine();
        $this->info('Cloudflare R2 configuration');
        $this->printR2DiskConfig($r2PublicDisk);
        $this->printR2DiskConfig($r2PrivateDisk);

        $results = [];

        if ($strategy === 'local_only' || ! $r2Enabled) {
            $this->info("\nChecking active local disks...");
            $results[$publicDisk] = $this->checkDisk($publicDisk);
            $results[$privateDisk] = $this->checkDisk($privateDisk);
        } else {
            $this->info("\nChecking active Cloudflare R2 disks...");
            $results[$r2PublicDisk] = $this->checkDisk($r2PublicDisk);
            $results[$r2PrivateDisk] = $this->checkDisk($r2PrivateDisk);
        }

        if ($cloudinaryEnabled) {
            $this->info("\nChecking Cloudinary configurations...");
            $cloudName = config('media.providers.cloudinary.cloud_name');
            $apiKey = config('media.providers.cloudinary.api_key');
            $apiSecret = config('media.providers.cloudinary.api_secret');

            if (filled($cloudName) && filled($apiKey) && filled($apiSecret)) {
                $this->info('Cloudinary credentials: PASS cloud='.$cloudName.' key='.$this->mask($apiKey));
                $cloudinaryResult = app(CloudinaryMediaDeliveryProvider::class)->healthCheck();
                if ($cloudinaryResult['ok']) {
                    $this->info('Cloudinary tiny upload/delete: PASS url='.($cloudinaryResult['url'] ?? '<none>'));
                } else {
                    $this->error('Cloudinary tiny upload/delete: FAIL '.($cloudinaryResult['message'] ?? 'Unknown error'));
                    $results['cloudinary_upload'] = false;
                }
            } else {
                $this->error('Cloudinary credentials: FAIL missing CLOUD_NAME, API_KEY, or API_SECRET.');
                $results['cloudinary_config'] = false;
            }
        } else {
            $this->line("\nCloudinary public delivery is <comment>DISABLED</comment>.");
        }

        $passed = ! in_array(false, $results, true);

        $this->newLine();
        $this->line('Health check result: '.($passed ? '<info>PASS</info>' : '<error>FAIL</error>'));
        $this->info('--- Media Storage Health Check Completed ---');

        return $passed ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Test a disk for read, write, and delete operations.
     */
    protected function checkDisk(string $disk): bool
    {
        $this->line("Testing disk: <info>{$disk}</info>...");
        $testFile = 'health-check/'.Str::uuid().'.txt';
        $testContent = 'Health Check '.now()->toDateTimeString();

        try {
            /** @var Filesystem $filesystem */
            $filesystem = Storage::disk($disk);

            $filesystem->put($testFile, $testContent);
            $this->line('  [PASS] Write successful.');

            $read = $filesystem->get($testFile);
            if ($read === $testContent) {
                $this->line('  [PASS] Read successful and matched content.');
            } else {
                $this->error("  [FAIL] Read mismatch. Read: '{$read}', Expected: '{$testContent}'");

                return false;
            }

            $filesystem->delete($testFile);
            if (! $filesystem->exists($testFile)) {
                $this->line('  [PASS] Delete successful.');

                return true;
            } else {
                $this->error('  [FAIL] Delete failed. File still exists.');

                return false;
            }
        } catch (Throwable $e) {
            $this->error('  [FAIL] Disk operation failed. Error: '.$e->getMessage());

            return false;
        }
    }

    protected function printR2DiskConfig(string $disk): void
    {
        $config = config("filesystems.disks.{$disk}", []);

        $this->line("Disk <info>{$disk}</info>:");
        $this->line('  bucket: '.($config['bucket'] ?? '<missing>'));
        $this->line('  endpoint: '.($config['endpoint'] ?? '<missing>'));
        $this->line('  url: '.(($config['url'] ?? null) ?: '<controller fallback>'));
        $this->line('  key: '.$this->mask($config['key'] ?? null));
        $this->line('  secret: '.$this->mask($config['secret'] ?? null));
        $this->line('  throw: '.(($config['throw'] ?? false) ? 'true' : 'false'));
    }

    protected function mask(?string $value): string
    {
        if (blank($value)) {
            return '<missing>';
        }

        return Str::mask($value, '*', 4, max(strlen($value) - 8, 0));
    }
}
