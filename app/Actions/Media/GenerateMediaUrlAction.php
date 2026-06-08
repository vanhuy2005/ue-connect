<?php

namespace App\Actions\Media;

use App\Models\Media;
use App\Models\MediaFile;
use App\Models\MediaVariant;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class GenerateMediaUrlAction
{
    /**
     * @var array<string, ?string>
     */
    private static array $requestUrlCache = [];

    /**
     * Generate a dynamic safe URL for a Media, MediaVariant, or MediaFile asset.
     */
    public function execute(Media|MediaVariant|MediaFile $media, ?string $variant = null, ?User $viewer = null, ?string $context = null): ?string
    {
        $cacheKey = $this->requestCacheKey($media, $variant, $viewer);
        if (array_key_exists($cacheKey, self::$requestUrlCache)) {
            return self::$requestUrlCache[$cacheKey];
        }

        // Handle MediaFile directly
        if ($media instanceof MediaFile) {
            return self::$requestUrlCache[$cacheKey] = $this->resolveUrlForMediaFile($media);
        }

        // 1. If it's a MediaVariant, get the parent Media to perform visibility checks
        $parentMedia = $media instanceof MediaVariant ? $media->media : $media;

        // 2. Perform Gate check for private media
        if ($parentMedia->visibility !== 'public' && $viewer) {
            if (Gate::forUser($viewer)->denies('view', $parentMedia)) {
                return self::$requestUrlCache[$cacheKey] = null;
            }
        }

        // 3. If we are looking for a specific variant on a primary Media object
        if ($media instanceof Media && ! empty($variant) && $variant !== 'original') {
            $variantModel = $media->variant($variant);

            if ($variantModel) {
                return self::$requestUrlCache[$cacheKey] = $this->resolveUrlForAsset($variantModel, $parentMedia);
            }
        }

        // 4. Resolve URL for the given asset (either the variant model or the primary Media model)
        return self::$requestUrlCache[$cacheKey] = $this->resolveUrlForAsset($media, $parentMedia);
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

        $url = null;

        if ($this->shouldPreferCloudinary($asset)) {
            $url = $asset instanceof MediaVariant
                ? $asset->cloudinary_secure_url
                : $asset->delivery_url;
        } elseif ($asset instanceof Media && ! empty($asset->delivery_url)) {
            // If the asset has a delivery URL already populated, use it
            $url = $asset->delivery_url;
        } elseif ($asset instanceof MediaVariant && ! empty($asset->url)) {
            $url = $asset->url;
        } elseif ($asset->provider === 'local') {
            // For public local disks
            $url = Storage::disk($asset->disk)->url($asset->path);
        } elseif ($asset->provider === 'r2') {
            // For public R2/S3 disks
            if (empty(config("filesystems.disks.{$asset->disk}.url"))) {
                $url = route('media.preview', [
                    'media' => $parentMedia,
                    'variant' => $asset instanceof MediaVariant ? $asset->variant_name : null,
                ]);
            } else {
                $url = Storage::disk($asset->disk)->url($asset->path);
            }
        }

        // Dynamically match current request host and port for local assets to prevent connection errors
        if ($url && ! app()->runningInConsole() && request()) {
            $appUrl = config('app.url');
            $requestHost = request()->getSchemeAndHttpHost();
            if ($appUrl && str_starts_with($url, $appUrl)) {
                $url = $requestHost.substr($url, strlen($appUrl));
            }
        }

        return $url;
    }

    /**
     * Resolve URL for MediaFile legacy objects.
     */
    protected function resolveUrlForMediaFile(MediaFile $mediaFile): ?string
    {
        if ($mediaFile->visibility !== 'public') {
            try {
                return Storage::disk($mediaFile->disk)->temporaryUrl(
                    $mediaFile->path,
                    now()->addMinutes(config('media.processing.temp_ttl_minutes', 60))
                );
            } catch (\Throwable $e) {
                return Storage::disk($mediaFile->disk)->url($mediaFile->path);
            }
        }

        return Storage::disk($mediaFile->disk)->url($mediaFile->path);
    }

    protected function shouldPreferCloudinary(Media|MediaVariant $asset): bool
    {
        $strategy = config('media.storage.strategy');
        if (! in_array($strategy, ['hybrid_public_cloudinary', 'r2_cloudinary'])) {
            return false;
        }

        if ($asset instanceof MediaVariant) {
            return $asset->cloudinary_sync_status === 'synced'
                && filled($asset->cloudinary_secure_url);
        }

        return $asset->delivery_provider === 'cloudinary' && filled($asset->delivery_url);
    }

    private function requestCacheKey(Media|MediaVariant|MediaFile $media, ?string $variant, ?User $viewer): string
    {
        $type = 'media';
        if ($media instanceof MediaVariant) {
            $type = 'variant';
        } elseif ($media instanceof MediaFile) {
            $type = 'file';
        }

        return implode(':', [
            $type,
            $media->getKey(),
            $variant ?: 'default',
            $viewer?->getKey() ?: 'guest',
        ]);
    }
}
