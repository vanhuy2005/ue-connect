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

            // --- Single Cloudinary Upload Logic ---
            $cloudinaryResult = null;
            $cloudinaryError = null;

            if ($syncCloudinary) {
                if ($quota->disableCloudinaryWhenLimitReached() && ! $quota->canSyncCloudinary()) {
                    $cloudinaryError = [
                        'cloudinary_sync_status' => 'skipped',
                        'cloudinary_error_code' => $quota->cloudinaryLimitReason(),
                        'cloudinary_error_message' => 'Cloudinary daily sync limit reached; using R2 fallback.',
                    ];
                } else {
                    $isProfileImage = in_array($this->media->collection, ['avatar', 'community_avatar']);
                    if ($isProfileImage) {
                        $publicId = 'avatars/user_'.$this->media->user_id;
                        $options = [
                            'folder' => 'avatars',
                            'public_id' => 'user_'.$this->media->user_id,
                            'overwrite' => true,
                            'invalidate' => true,
                        ];
                    } else {
                        $publicId = $deliveryProvider->publicId($this->media->collection, $this->media->uuid, 'display');
                        $options = [];
                    }

                    $hash = md5($contents);

                    Log::info('CLOUDINARY_UPLOAD_CALL', [
                        'user_id' => auth()->id() ?? $this->media->user_id,
                        'hash' => $hash,
                    ]);

                    try {
                        // Upload only the display/original source once to Cloudinary
                        $result = $deliveryProvider->uploadVariant($publicId, $contents, $this->media->mime_type, $options);

                        Log::info('CLOUDINARY_UPLOAD_SUCCESS', [
                            'user_id' => auth()->id() ?? $this->media->user_id,
                            'public_id' => $result['public_id'] ?? $publicId,
                            'url' => $result['secure_url'] ?? null,
                        ]);

                        $cloudinaryResult = $result;
                    } catch (\Throwable $e) {
                        Log::warning('Cloudinary sync failed', [
                            'media_id' => $this->media->id,
                            'collection' => $this->media->collection,
                            'message' => str($e->getMessage())->limit(240)->toString(),
                        ]);

                        $cloudinaryError = [
                            'cloudinary_sync_status' => 'failed',
                            'cloudinary_error_code' => 'upload_failed',
                            'cloudinary_error_message' => str($e->getMessage())->limit(240)->toString(),
                        ];
                    }
                }
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

                if ($syncCloudinary) {
                    if ($cloudinaryError) {
                        $cloudinaryData = $cloudinaryError + [
                            'cloudinary_public_id' => $deliveryProvider->publicId($this->media->collection, $this->media->uuid, $name),
                        ];
                    } elseif ($cloudinaryResult) {
                        // Derive URL using transformation from single public_id
                        $transformations = $this->getCloudinaryTransformations($spec);
                        $derivedUrl = $deliveryProvider->buildTransformationUrl(
                            $cloudinaryResult['public_id'],
                            $transformations,
                            $cloudinaryResult['version']
                        );

                        Log::info('VARIANT_URL_GENERATED', [
                            'variant' => $name,
                            'url' => $derivedUrl,
                        ]);

                        $cloudinaryData = [
                            'cloudinary_public_id' => $cloudinaryResult['public_id'],
                            'cloudinary_version' => $cloudinaryResult['version'],
                            'cloudinary_secure_url' => $derivedUrl,
                            'cloudinary_format' => $cloudinaryResult['format'],
                            'cloudinary_bytes' => $cloudinaryResult['bytes'],
                            'cloudinary_resource_type' => $cloudinaryResult['resource_type'],
                            'cloudinary_synced_at' => now(),
                            'cloudinary_sync_status' => 'synced',
                            'cloudinary_error_code' => null,
                            'cloudinary_error_message' => null,
                        ];
                    }
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

            $deliveryUrl = $cloudinaryResult['secure_url'] ?? null;

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

            Log::info('IMAGE_DB_UPDATED', [
                'user_id' => $this->media->user_id,
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
                'display' => ['w' => 320, 'h' => 320, 'crop' => true],
                'thumb' => ['w' => 96, 'h' => 96, 'crop' => true],
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
            'verification_evidence', 'report_evidence', 'mentor_evidence' => [
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
     * Generate transformation instructions for Cloudinary variants.
     *
     * @param  array{w: int, h: int, crop?: bool}  $spec
     */
    protected function getCloudinaryTransformations(array $spec): array
    {
        $w = $spec['w'];
        $h = $spec['h'];
        $crop = $spec['crop'] ?? false;

        $transformations = [];
        if ($crop) {
            $transformations['c'] = 'fill';
        } else {
            $transformations['c'] = 'limit';
        }
        $transformations['w'] = $w;
        $transformations['h'] = $h;

        return $transformations;
    }
}
