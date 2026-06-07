<?php

namespace App\Jobs\Media;

use App\Models\Media;
use App\Models\MediaVariant;
use App\Services\Media\MediaQuotaService;
use App\Services\Media\MediaStorageRouter;
use App\Services\Media\Providers\CloudinaryMediaDeliveryProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProcessImageVariantsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(public Media $media) {}

    public function handle(MediaStorageRouter $router, MediaQuotaService $quota): void
    {
        $originalStatus = $this->media->status;

        if (! in_array($originalStatus, ['temporary', 'ready'])) {
            return;
        }

        if ($originalStatus === 'ready' && $this->media->variants()->exists()) {
            return;
        }

        try {
            $this->media->update(['status' => 'processing']);

            $contents = Storage::disk($this->media->primary_disk)->get($this->media->primary_path);
            if (empty($contents)) {
                throw new RuntimeException('Raw original content is missing.');
            }

            $sourceImage = @imagecreatefromstring($contents);
            if (! $sourceImage) {
                throw new RuntimeException('Failed to load image via GD.');
            }

            $sourceWidth = imagesx($sourceImage);
            $sourceHeight = imagesy($sourceImage);
            $variantSpecs = $this->getVariantSpecsForCollection($this->media->collection);
            $primaryProvider = $router->getPrimaryProvider($this->media->collection, $this->media->visibility);
            $deliveryProvider = $router->getDeliveryProvider($this->media->collection, $this->media->visibility);
            $syncCloudinary = $deliveryProvider instanceof CloudinaryMediaDeliveryProvider
                && (bool) config('media.providers.cloudinary.sync_public_variants', true);
            $outputFormat = config('media.processing.output_format', 'webp');
            if ($outputFormat === 'webp' && ! function_exists('imagewebp')) {
                $outputFormat = 'jpeg';
            }

            $processedVariants = [];

            foreach ($variantSpecs as $name => $spec) {
                $resizedImage = $this->resizeImage($sourceImage, $sourceWidth, $sourceHeight, $spec['w'], $spec['h'], $spec['crop'] ?? false);

                ob_start();
                if ($outputFormat === 'webp') {
                    imagewebp($resizedImage, null, config('media.processing.quality', 82));
                } elseif ($outputFormat === 'png') {
                    imagepng($resizedImage, null, 9);
                } else {
                    imagejpeg($resizedImage, null, config('media.processing.quality', 82));
                }
                $variantContents = ob_get_clean();
                imagedestroy($resizedImage);

                if (empty($variantContents)) {
                    continue;
                }

                $ext = $outputFormat === 'jpeg' ? 'jpg' : $outputFormat;
                $mimeType = 'image/'.($outputFormat === 'jpeg' ? 'jpeg' : $outputFormat);

                $variantPath = "{$this->media->collection}s/{$this->media->user_id}/{$this->media->uuid}/{$name}.{$ext}";
                $storedVariant = $primaryProvider->put($variantPath, $variantContents);
                $cloudinaryData = [
                    'cloudinary_sync_status' => $syncCloudinary ? 'pending' : 'skipped',
                ];

                if ($syncCloudinary && $quota->disableCloudinaryWhenLimitReached() && ! $quota->canSyncCloudinary()) {
                    $cloudinaryData = [
                        'cloudinary_sync_status' => 'skipped',
                        'cloudinary_public_id' => $deliveryProvider->publicId($this->media->collection, $this->media->uuid, $name),
                        'cloudinary_error_code' => $quota->cloudinaryLimitReason(),
                        'cloudinary_error_message' => 'Cloudinary daily sync limit reached; using R2 fallback.',
                    ];
                } elseif ($syncCloudinary) {
                    $cloudinaryData = $this->syncVariantToCloudinary($deliveryProvider, $name, $variantContents, $mimeType);
                }

                $processedVariants[$name] = [
                    'variant_name' => $name,
                    'provider' => $storedVariant->provider,
                    'disk' => $storedVariant->disk,
                    'path' => $storedVariant->path,
                    'url' => $storedVariant->url,
                    'mime_type' => $mimeType,
                    'size_bytes' => strlen($variantContents),
                    'width' => $spec['w'],
                    'height' => $spec['h'],
                ] + $cloudinaryData;
            }

            imagedestroy($sourceImage);

            foreach ($processedVariants as $variantData) {
                MediaVariant::create($variantData + ['media_id' => $this->media->id]);
            }

            $deliveryUrl = null;

            $finalPath = $this->media->primary_path;
            $finalDisk = $this->media->primary_disk;
            $finalProvider = $this->media->primary_provider;
            $keepOriginal = $this->media->visibility === 'public'
                ? config('media.processing.keep_original_public', false)
                : config('media.processing.keep_original_private', true);

            $newExt = $this->media->extension;
            $newMime = $this->media->mime_type;

            if (! $keepOriginal && $this->media->visibility === 'public') {
                Storage::disk($this->media->primary_disk)->delete($this->media->primary_path);

                $largestVariantName = match ($this->media->collection) {
                    'avatar', 'community_avatar' => 'display',
                    'profile_cover', 'community_cover' => 'desktop',
                    'post_image' => 'detail',
                    'message_attachment' => 'display',
                    default => array_key_first($processedVariants),
                };

                if (isset($processedVariants[$largestVariantName])) {
                    $largest = $processedVariants[$largestVariantName];
                    $finalPath = $largest['path'];
                    $finalDisk = $largest['disk'];
                    $finalProvider = $largest['provider'];
                    $deliveryUrl = $largest['cloudinary_secure_url'] ?? null;
                    $newExt = isset($ext) ? $ext : $this->media->extension;
                    $newMime = isset($mimeType) ? $mimeType : $this->media->mime_type;
                }
            }

            $this->media->refresh();

            $finalStatus = ($originalStatus === 'ready' || $this->media->status === 'ready' || $this->media->mediable_id !== null)
                ? 'ready'
                : 'temporary';

            $this->media->update([
                'status' => $finalStatus,
                'primary_path' => $finalPath,
                'primary_disk' => $finalDisk,
                'primary_provider' => $finalProvider,
                'delivery_provider' => $deliveryProvider ? 'cloudinary' : null,
                'delivery_url' => $deliveryUrl,
                'extension' => $newExt,
                'mime_type' => $newMime,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error processing media variants: '.$e->getMessage(), [
                'media_id' => $this->media->id,
            ]);

            $this->media->update(['status' => 'failed']);

            if (request()->has('debug_media_job')) {
                throw $e;
            }
        }
    }

    /**
     * @return array<string, array{w: int, h: int, crop?: bool}>
     */
    protected function getVariantSpecsForCollection(string $collection): array
    {
        return match ($collection) {
            'avatar', 'community_avatar' => [
                'thumb' => ['w' => 96, 'h' => 96, 'crop' => true],
                'display' => ['w' => 320, 'h' => 320, 'crop' => true],
            ],
            'profile_cover', 'community_cover' => [
                'mobile' => ['w' => 800, 'h' => 300, 'crop' => true],
                'desktop' => ['w' => 1600, 'h' => 600, 'crop' => true],
            ],
            'post_image' => [
                'thumb' => ['w' => 320, 'h' => 240],
                'feed' => ['w' => 1080, 'h' => 810],
                'detail' => ['w' => 1600, 'h' => 1200],
            ],
            'message_attachment', 'message_image' => [
                'thumb' => ['w' => 320, 'h' => 240],
                'display' => ['w' => 1080, 'h' => 810],
            ],
            'verification_evidence', 'report_evidence' => [
                'preview' => ['w' => 1600, 'h' => 1200],
            ],
            default => [
                'thumb' => ['w' => 150, 'h' => 150, 'crop' => true],
            ],
        };
    }

    protected function resizeImage($sourceImage, int $sourceWidth, int $sourceHeight, int $targetWidth, int $targetHeight, bool $cropSquare)
    {
        if ($cropSquare) {
            $size = min($sourceWidth, $sourceHeight);
            $sourceX = (int) (($sourceWidth - $size) / 2);
            $sourceY = (int) (($sourceHeight - $size) / 2);
            $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
            imagecopyresampled($targetImage, $sourceImage, 0, 0, $sourceX, $sourceY, $targetWidth, $targetHeight, $size, $size);

            return $targetImage;
        }

        $ratio = $sourceWidth / $sourceHeight;
        if ($sourceWidth / $targetWidth > $sourceHeight / $targetHeight) {
            $targetHeight = (int) ($targetWidth / $ratio);
        } else {
            $targetWidth = (int) ($targetHeight * $ratio);
        }

        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

        return $targetImage;
    }

    /**
     * @return array{
     *     cloudinary_public_id?: string,
     *     cloudinary_version?: int|null,
     *     cloudinary_secure_url?: string|null,
     *     cloudinary_format?: string|null,
     *     cloudinary_bytes?: int|null,
     *     cloudinary_resource_type?: string|null,
     *     cloudinary_synced_at?: Carbon,
     *     cloudinary_sync_status: string,
     *     cloudinary_error_code?: string|null,
     *     cloudinary_error_message?: string|null
     * }
     */
    protected function syncVariantToCloudinary(CloudinaryMediaDeliveryProvider $provider, string $variantName, string $contents, string $mimeType): array
    {
        $publicId = $provider->publicId($this->media->collection, $this->media->uuid, $variantName);

        try {
            $result = $provider->uploadVariant($publicId, $contents, $mimeType);

            return [
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
            ];
        } catch (\Throwable $e) {
            Log::warning('Cloudinary variant sync failed', [
                'media_id' => $this->media->id,
                'collection' => $this->media->collection,
                'variant' => $variantName,
                'message' => str($e->getMessage())->limit(240)->toString(),
            ]);

            return [
                'cloudinary_public_id' => $publicId,
                'cloudinary_synced_at' => null,
                'cloudinary_sync_status' => 'failed',
                'cloudinary_error_code' => 'upload_failed',
                'cloudinary_error_message' => str($e->getMessage())->limit(240)->toString(),
            ];
        }
    }
}
