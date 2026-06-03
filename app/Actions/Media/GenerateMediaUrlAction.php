<?php

namespace App\Actions\Media;

use App\Models\Media;
use App\Models\MediaVariant;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class GenerateMediaUrlAction
{
    /**
     * Generate a dynamic safe URL for a Media or MediaVariant asset.
     */
    public function execute(Media|MediaVariant $media, ?string $variant = null, ?User $viewer = null, ?string $context = null): ?string
    {
        // 1. If it's a MediaVariant, get the parent Media to perform visibility checks
        $parentMedia = $media instanceof MediaVariant ? $media->media : $media;

        // 2. Perform Gate check for private media
        if ($parentMedia->visibility !== 'public' && $viewer) {
            if (Gate::forUser($viewer)->denies('view', $parentMedia)) {
                return null;
            }
        }

        // 3. If we are looking for a specific variant on a primary Media object
        if ($media instanceof Media && ! empty($variant) && $variant !== 'original') {
            $variantModel = $media->variants()->where('variant_name', $variant)->first();
            if ($variantModel) {
                return $this->resolveUrlForAsset($variantModel, $parentMedia);
            }
        }

        // 4. Resolve URL for the given asset (either the variant model or the primary Media model)
        return $this->resolveUrlForAsset($media, $parentMedia);
    }

    /**
     * Resolve the actual URL for a Media or MediaVariant.
     */
    protected function resolveUrlForAsset(Media|MediaVariant $asset, Media $parentMedia): ?string
    {
        // If private, always generate a secure signed temporary route
        if ($parentMedia->visibility !== 'public') {
            return URL::temporarySignedRoute(
                'media.preview',
                now()->addMinutes(config('media.processing.temp_ttl_minutes', 60)),
                [
                    'media' => $parentMedia,
                    'variant' => $asset instanceof MediaVariant ? $asset->variant_name : null,
                ]
            );
        }

        if ($this->shouldPreferCloudinary($asset)) {
            return $asset instanceof MediaVariant
                ? $asset->cloudinary_secure_url
                : $asset->delivery_url;
        }

        // If the asset has a delivery URL already populated, use it
        if ($asset instanceof Media && ! empty($asset->delivery_url)) {
            return $asset->delivery_url;
        }

        if ($asset instanceof MediaVariant && ! empty($asset->url)) {
            return $asset->url;
        }

        // For public local disks
        if ($asset->provider === 'local') {
            return Storage::disk($asset->disk)->url($asset->path);
        }

        // For public R2/S3 disks
        if ($asset->provider === 'r2') {
            if (empty(config("filesystems.disks.{$asset->disk}.url"))) {
                return route('media.preview', [
                    'media' => $parentMedia,
                    'variant' => $asset instanceof MediaVariant ? $asset->variant_name : null,
                ]);
            }

            return Storage::disk($asset->disk)->url($asset->path);
        }

        return null;
    }

    protected function shouldPreferCloudinary(Media|MediaVariant $asset): bool
    {
        if (config('media.storage.strategy') !== 'hybrid_public_cloudinary') {
            return false;
        }

        if ($asset instanceof MediaVariant) {
            return $asset->cloudinary_sync_status === 'synced'
                && filled($asset->cloudinary_secure_url);
        }

        return $asset->delivery_provider === 'cloudinary' && filled($asset->delivery_url);
    }
}
