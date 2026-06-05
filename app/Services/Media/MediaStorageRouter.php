<?php

namespace App\Services\Media;

use App\Services\Media\Contracts\MediaStorageProvider;
use App\Services\Media\Providers\CloudinaryMediaDeliveryProvider;
use App\Services\Media\Providers\LocalMediaStorageProvider;
use App\Services\Media\Providers\S3CompatibleMediaStorageProvider;

class MediaStorageRouter
{
    public const PUBLIC_CLOUDINARY_COLLECTIONS = ['avatar', 'profile_cover', 'post_image', 'community_avatar', 'community_cover'];

    /**
     * Resolve the primary storage provider for a collection and visibility.
     */
    public function getPrimaryProvider(string $collection, string $visibility = 'private'): MediaStorageProvider
    {
        $strategy = $this->activeStrategy();
        $r2Enabled = (bool) config('media.r2.enabled', config('media.providers.r2.enabled', false));

        if ($strategy === 'local_only' || ! $r2Enabled) {
            $disk = ($visibility === 'public') ? 'public' : 'private';

            return new LocalMediaStorageProvider($disk);
        }

        if (in_array($strategy, [
            'r2_primary',
            'hybrid_public_cloudinary',
            'r2_with_cloudinary_delivery',
            'r2_cloudinary',
        ], true)) {
            $disk = ($visibility === 'public')
                ? config('media.providers.r2.public_disk', 'r2_public')
                : config('media.providers.r2.private_disk', 'r2_private');

            return new S3CompatibleMediaStorageProvider($disk);
        }

        if ($strategy === 'cloudinary_public_local_private') {
            if ($visibility === 'public') {
                return new LocalMediaStorageProvider(config('media.public_disk', 'public'));
            } else {
                return new LocalMediaStorageProvider(config('media.private_disk', 'local'));
            }
        }

        // Fallback safely
        return new LocalMediaStorageProvider(config('media.private_disk', 'local'));
    }

    /**
     * Get the public delivery provider (e.g. Cloudinary) if enabled.
     */
    public function getDeliveryProvider(string $collection, string $visibility = 'public'): ?MediaStorageProvider
    {
        if ($visibility !== 'public') {
            return null;
        }

        $strategy = $this->activeStrategy();
        $cloudinaryEnabled = (bool) config('media.providers.cloudinary.enabled', false);

        if (! in_array($collection, self::PUBLIC_CLOUDINARY_COLLECTIONS, true)) {
            return null;
        }

        if ($cloudinaryEnabled && in_array($strategy, [
            'hybrid_public_cloudinary',
            'r2_with_cloudinary_delivery',
            'r2_cloudinary',
            'cloudinary_public_local_private',
        ], true)) {
            return new CloudinaryMediaDeliveryProvider;
        }

        return null;
    }

    protected function activeStrategy(): string
    {
        return (string) config('media.storage.strategy', config('media.default_strategy', 'local_only'));
    }
}
