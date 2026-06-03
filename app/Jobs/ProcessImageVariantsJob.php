<?php

namespace App\Jobs;

use App\Models\Media;
use App\Models\MediaVariant;
use App\Services\Media\MediaStorageRouter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessImageVariantsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(public Media $media) {}

    public function handle(MediaStorageRouter $router): void
    {
        // 1. Double check the media is in temporary status
        if ($this->media->status !== 'temporary') {
            return;
        }

        try {
            $this->media->update(['status' => 'processing']);

            // 2. Fetch the raw original content
            $contents = Storage::disk($this->media->primary_disk)->get($this->media->primary_path);
            if (empty($contents)) {
                throw new \RuntimeException('Raw original content is missing.');
            }

            // 3. Load image into GD
            $srcGd = @imagecreatefromstring($contents);
            if (! $srcGd) {
                throw new \RuntimeException('Failed to load image via GD.');
            }

            $srcW = imagesx($srcGd);
            $srcH = imagesy($srcGd);

            // 4. Resolve variants based on collection
            $variantSpecs = $this->getVariantSpecsForCollection($this->media->collection);
            $primaryProvider = $router->getPrimaryProvider($this->media->collection, $this->media->visibility);
            $deliveryProvider = $router->getDeliveryProvider($this->media->collection, $this->media->visibility);

            $processedVariants = [];

            foreach ($variantSpecs as $name => $spec) {
                $targetW = $spec['w'];
                $targetH = $spec['h'];
                $crop = $spec['crop'] ?? false;

                // Create the resampled canvas
                $resizedGd = $this->resizeImage($srcGd, $srcW, $srcH, $targetW, $targetH, $crop);

                // Output to temporary memory buffer in WebP format
                ob_start();
                $quality = config('media.processing.quality', 82);
                imagewebp($resizedGd, null, $quality);
                $webpContents = ob_get_clean();
                imagedestroy($resizedGd);

                if (empty($webpContents)) {
                    continue;
                }

                // Determine unique variant storage path
                $uuid = $this->media->uuid;
                $userId = $this->media->user_id;
                $collection = $this->media->collection;
                $variantPath = "{$collection}s/{$userId}/{$uuid}/{$name}.webp";

                // Save using resolved provider
                $storedVariant = $primaryProvider->put($variantPath, $webpContents);

                // Add to variants registry
                $processedVariants[$name] = [
                    'variant_name' => $name,
                    'provider' => $storedVariant->provider,
                    'disk' => $storedVariant->disk,
                    'path' => $storedVariant->path,
                    'url' => $storedVariant->url,
                    'mime_type' => 'image/webp',
                    'size_bytes' => strlen($webpContents),
                    'width' => $targetW ?: (int) ($srcW * ($targetH / $srcH)),
                    'height' => $targetH ?: (int) ($srcH * ($targetW / $srcW)),
                ];
            }

            imagedestroy($srcGd);

            // 5. Store variants to database
            foreach ($processedVariants as $name => $variantData) {
                MediaVariant::create(array_merge($variantData, [
                    'media_id' => $this->media->id,
                ]));
            }

            // 6. Delivery URL configuration
            // If Cloudinary delivery is enabled, configure delivery provider for the main object URL too
            $deliveryUrl = null;
            if ($deliveryProvider) {
                $deliveryUrl = $deliveryProvider->publicUrl($this->media->primary_path);
            }

            // 7. Cleanup raw original if public
            $finalPath = $this->media->primary_path;
            $finalDisk = $this->media->primary_disk;
            $finalProvider = $this->media->primary_provider;

            $keepOriginal = $this->media->visibility === 'public'
                ? config('media.processing.keep_original_public', false)
                : config('media.processing.keep_original_private', true);

            if (! $keepOriginal && $this->media->visibility === 'public') {
                // Delete raw original to save costs
                Storage::disk($this->media->primary_disk)->delete($this->media->primary_path);

                // Promote the largest variant (e.g. 'display' or 'feed') as the main original path
                $largestVariantName = match ($this->media->collection) {
                    'avatar' => 'display',
                    'profile_cover' => 'desktop',
                    'post_image' => 'detail',
                    'message_attachment' => 'display',
                    default => array_key_first($processedVariants),
                };

                if (isset($processedVariants[$largestVariantName])) {
                    $largest = $processedVariants[$largestVariantName];
                    $finalPath = $largest['path'];
                    $finalDisk = $largest['disk'];
                    $finalProvider = $largest['provider'];

                    if ($deliveryProvider) {
                        $deliveryUrl = $deliveryProvider->publicUrl($finalPath);
                    }
                }
            }

            // 8. Promote media status to ready
            $this->media->update([
                'status' => 'ready',
                'primary_path' => $finalPath,
                'primary_disk' => $finalDisk,
                'primary_provider' => $finalProvider,
                'delivery_provider' => $deliveryProvider ? 'cloudinary' : null,
                'delivery_url' => $deliveryUrl,
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing media variants: '.$e->getMessage(), [
                'media_id' => $this->media->id,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->media->update(['status' => 'failed']);
        }
    }

    /**
     * Get variant configurations based on context collection.
     */
    protected function getVariantSpecsForCollection(string $collection): array
    {
        return match ($collection) {
            'avatar' => [
                'thumb' => ['w' => 96, 'h' => 96, 'crop' => true],
                'display' => ['w' => 320, 'h' => 320, 'crop' => true],
            ],
            'profile_cover' => [
                'mobile' => ['w' => 800, 'h' => 300, 'crop' => true],
                'desktop' => ['w' => 1600, 'h' => 600, 'crop' => true],
            ],
            'post_image' => [
                'thumb' => ['w' => 320, 'h' => 240, 'crop' => false],
                'feed' => ['w' => 1080, 'h' => 810, 'crop' => false],
                'detail' => ['w' => 1600, 'h' => 1200, 'crop' => false],
            ],
            'message_attachment', 'message_image' => [
                'thumb' => ['w' => 320, 'h' => 240, 'crop' => false],
                'display' => ['w' => 1080, 'h' => 810, 'crop' => false],
            ],
            'verification_evidence' => [
                'preview' => ['w' => 1600, 'h' => 1200, 'crop' => false],
            ],
            default => [
                'thumb' => ['w' => 150, 'h' => 150, 'crop' => true],
            ],
        };
    }

    /**
     * Resize image safely using pure GD true color operations.
     */
    protected function resizeImage($srcGd, int $srcW, int $srcH, int $targetW, int $targetH, bool $cropSquare)
    {
        if ($cropSquare) {
            // Perfect square cropping (ideal for avatars)
            $size = min($srcW, $srcH);
            $srcX = (int) (($srcW - $size) / 2);
            $srcY = (int) (($srcH - $size) / 2);

            $dstGd = imagecreatetruecolor($targetW, $targetH);
            imagealphablending($dstGd, false);
            imagesavealpha($dstGd, true);

            imagecopyresampled($dstGd, $srcGd, 0, 0, $srcX, $srcY, $targetW, $targetH, $size, $size);

            return $dstGd;
        }

        // Standard aspect ratio calculation
        $ratio = $srcW / $srcH;
        if ($targetW && ! $targetH) {
            $targetH = (int) ($targetW / $ratio);
        } elseif (! $targetW && $targetH) {
            $targetW = (int) ($targetH * $ratio);
        } else {
            // Keep aspect ratio within constraints
            if ($srcW / $targetW > $srcH / $targetH) {
                $targetH = (int) ($targetW / $ratio);
            } else {
                $targetW = (int) ($targetH * $ratio);
            }
        }

        $dstGd = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($dstGd, false);
        imagesavealpha($dstGd, true);

        imagecopyresampled($dstGd, $srcGd, 0, 0, 0, 0, $targetW, $targetH, $srcW, $srcH);

        return $dstGd;
    }
}
