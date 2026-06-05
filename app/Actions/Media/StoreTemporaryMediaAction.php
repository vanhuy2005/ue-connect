<?php

namespace App\Actions\Media;

use App\Jobs\Media\ProcessImageVariantsJob;
use App\Models\Media;
use App\Models\User;
use App\Services\Media\MediaQuotaService;
use App\Services\Media\MediaStorageRouter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class StoreTemporaryMediaAction
{
    public function __construct(
        protected ValidateMediaUploadAction $validateUpload,
        protected MediaStorageRouter $router,
        protected MediaQuotaService $quota
    ) {}

    /**
     * Store an uploaded file as a temporary media record and dispatch variant generation.
     */
    /**
     * @param  array{visibility?: string}  $options
     */
    public function execute(User $user, TemporaryUploadedFile|UploadedFile $file, string $collection, array $options = []): Media
    {
        // 1. Validate the file
        $this->validateUpload->execute($file, $collection);
        $this->quota->assertCanUpload($user, (int) $file->getSize());

        // 2. Resolve default visibility if none provided
        $visibility = $options['visibility'] ?? match ($collection) {
            'avatar', 'profile_cover', 'post_image', 'comment_image' => 'public',
            default => 'private',
        };

        // 3. Generate UUID and unique storage paths
        $uuid = (string) Str::uuid();
        $extension = strtolower($file->getClientOriginalExtension());
        $tempPath = "temp/{$user->id}/{$uuid}/original.{$extension}";

        // 4. Raw temporary uploads always start private; variants are routed later by collection visibility.
        $provider = $this->router->getPrimaryProvider($collection, 'private');
        $fileContent = $file->get();
        $storedObject = $provider->put($tempPath, $fileContent);

        // 5. Calculate SHA256 checksum
        $checksum = hash('sha256', $fileContent);

        // 6. Get image dimensions
        $dimensions = @getimagesizefromstring($fileContent);
        $width = $dimensions ? $dimensions[0] : null;
        $height = $dimensions ? $dimensions[1] : null;

        // 7. Create database record
        $strategy = config('media.storage.strategy', config('media.default_strategy', 'local_only'));

        $media = Media::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'collection' => $collection,
            'primary_provider' => $storedObject->provider,
            'primary_disk' => $storedObject->disk,
            'primary_path' => $storedObject->path,
            'delivery_provider' => null,
            'delivery_url' => null,
            'storage_strategy' => $strategy,
            'visibility' => $visibility,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'extension' => $extension,
            'size_bytes' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'checksum_sha256' => $checksum,
            'status' => 'temporary',
        ]);

        // 8. Dispatch processing variants job (either sync or async based on settings)
        if (app()->runningUnitTests() || app()->environment('local') || config('media.processing.sync', false)) {
            ProcessImageVariantsJob::dispatchSync($media);
        } else {
            ProcessImageVariantsJob::dispatch($media);
        }

        return $media;
    }
}
