<?php

namespace App\Console\Commands;

use App\Models\MediaVariant;
use App\Services\Media\MediaQuotaService;
use App\Services\Media\MediaStorageRouter;
use App\Services\Media\Providers\CloudinaryMediaDeliveryProvider;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

#[Signature('media:sync-cloudinary {--failed-only : Only sync failed or pending variants}')]
#[Description('Sync eligible public media variants to Cloudinary')]
class SyncCloudinaryMedia extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(CloudinaryMediaDeliveryProvider $cloudinary, MediaQuotaService $quota): int
    {
        $query = MediaVariant::query()
            ->with('media')
            ->whereHas('media', function ($query) {
                $query->where('visibility', 'public')
                    ->whereIn('collection', MediaStorageRouter::PUBLIC_CLOUDINARY_COLLECTIONS);
            });

        if ($this->option('failed-only')) {
            $query->whereIn('cloudinary_sync_status', ['failed', 'pending']);
        }

        $summary = [
            'scanned' => 0,
            'synced' => 0,
            'skipped_private' => 0,
            'failed' => 0,
        ];

        $query->orderBy('id')->chunkById(50, function ($variants) use ($cloudinary, &$summary) {
            foreach ($variants as $variant) {
                $summary['scanned']++;
                $media = $variant->media;

                if (! $media || $media->visibility !== 'public' || ! in_array($media->collection, MediaStorageRouter::PUBLIC_CLOUDINARY_COLLECTIONS, true)) {
                    $summary['skipped_private']++;

                    continue;
                }

                if ($quota->disableCloudinaryWhenLimitReached() && ! $quota->canSyncCloudinary()) {
                    $variant->update([
                        'cloudinary_sync_status' => 'skipped',
                        'cloudinary_error_code' => $quota->cloudinaryLimitReason(),
                        'cloudinary_error_message' => 'Cloudinary daily sync limit reached; using R2 fallback.',
                    ]);

                    continue;
                }

                try {
                    $contents = Storage::disk($variant->disk)->get($variant->path);
                    $publicId = $variant->cloudinary_public_id
                        ?: $cloudinary->publicId($media->collection, $media->uuid, $variant->variant_name);
                    $result = $cloudinary->uploadVariant($publicId, $contents, $variant->mime_type);

                    $variant->update([
                        'cloudinary_public_id' => $result['public_id'],
                        'cloudinary_version' => $result['version'],
                        'cloudinary_secure_url' => $result['secure_url'],
                        'cloudinary_format' => $result['format'],
                        'cloudinary_bytes' => $result['bytes'],
                        'cloudinary_resource_type' => $result['resource_type'],
                        'cloudinary_synced_at' => now(),
                        'cloudinary_sync_status' => 'synced',
                        'cloudinary_error_code' => null,
                        'cloudinary_error_message' => null,
                    ]);

                    $summary['synced']++;
                } catch (Throwable $e) {
                    $variant->update([
                        'cloudinary_sync_status' => 'failed',
                        'cloudinary_error_code' => 'upload_failed',
                        'cloudinary_error_message' => str($e->getMessage())->limit(240)->toString(),
                    ]);

                    $summary['failed']++;
                }
            }
        });

        $this->table(['scanned', 'synced', 'skipped_private', 'failed'], [$summary]);

        return $summary['failed'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
