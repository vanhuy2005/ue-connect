<?php

namespace App\Actions\Media;

use App\Models\Media;
use App\Services\Media\Providers\CloudinaryMediaDeliveryProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DeleteMediaAction
{
    /**
     * Delete a media asset, including its physical files and variants.
     */
    public function execute(Media $media): void
    {
        $cloudinary = app(CloudinaryMediaDeliveryProvider::class);

        // 1. Delete physical variant files
        foreach ($media->variants as $variant) {
            try {
                if (Storage::disk($variant->disk)->exists($variant->path)) {
                    Storage::disk($variant->disk)->delete($variant->path);
                }
            } catch (Throwable $e) {
                // Fail silently on disk connection errors to prevent transaction failure
            }

            if (filled($variant->cloudinary_public_id)) {
                try {
                    $cloudinary->delete($variant->cloudinary_public_id);
                } catch (Throwable $e) {
                    Log::warning('Cloudinary media variant delete failed', [
                        'media_id' => $media->id,
                        'variant_id' => $variant->id,
                        'message' => str($e->getMessage())->limit(240)->toString(),
                    ]);
                }
            }
        }

        // 2. Delete database variant records
        $media->variants()->delete();

        // 3. Delete physical primary file
        try {
            if (Storage::disk($media->primary_disk)->exists($media->primary_path)) {
                Storage::disk($media->primary_disk)->delete($media->primary_path);
            }
        } catch (Throwable $e) {
            // Fail silently on disk connection errors
        }

        // 4. Soft delete the media database record
        $media->delete();
    }
}
