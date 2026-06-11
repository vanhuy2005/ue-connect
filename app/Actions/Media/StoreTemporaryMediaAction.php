<?php

namespace App\Actions\Media;

use App\Jobs\Media\ProcessImageVariantsJob;
use App\Models\Media;
use App\Models\User;
use App\Services\Media\MediaQuotaService;
use App\Services\Media\MediaStorageRouter;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
        // 0. Log the upload request
        Log::info('IMAGE_UPLOAD_REQUEST', [
            'user_id' => auth()->id() ?? $user->id,
            'filename' => $file?->getClientOriginalName(),
            'size' => $file?->getSize(),
            'hash' => $file ? md5_file($file->getRealPath()) : null,
        ]);

        // 1. Validate the file
        $this->validateUpload->execute($file, $collection);
        $this->quota->assertCanUpload($user, (int) $file->getSize());

        // 2. Lock check for in-progress uploads
        $hash = md5_file($file->getRealPath());
        $lockKey = 'image-upload:'.$user->id.':'.$hash;
        $lock = Cache::lock($lockKey, 30);

        if (! $lock->get()) {
            Log::info('DUPLICATE_IMAGE_UPLOAD_BLOCKED', [
                'user_id' => $user->id,
                'hash' => $hash,
            ]);
            throw new HttpResponseException(response()->json([
                'message' => 'Duplicate upload ignored.',
            ], 409));
        }

        try {
            $fileContent = $file->get();
            $checksum = hash('sha256', $fileContent);

            $isAvatar = in_array($collection, ['avatar', 'community_avatar']);
            $isCover = in_array($collection, ['profile_cover', 'community_cover']);
            $is1to1 = $isAvatar || $isCover;

            $existingMedia = null;
            if ($is1to1) {
                $existingMedia = Media::where('user_id', $user->id)
                    ->where('collection', $collection)
                    ->where('checksum_sha256', $checksum)
                    ->where('status', 'ready')
                    ->first();
            }

            if ($existingMedia) {
                Log::info('IMAGE_ALREADY_UP_TO_DATE', [
                    'user_id' => $user->id,
                    'hash' => $checksum,
                ]);

                $url = app(GenerateMediaUrlAction::class)->execute($existingMedia, $isAvatar ? 'display' : ($isCover ? 'desktop' : 'original'), $user);

                if (request()->expectsJson() && ! request()->hasHeader('X-Livewire')) {
                    throw new HttpResponseException(response()->json([
                        'message' => 'Image already up to date.',
                        'url' => $url,
                    ], 200));
                }

                return $existingMedia;
            }

            // 3. Resolve default visibility if none provided
            $visibility = $options['visibility'] ?? match ($collection) {
                'avatar', 'profile_cover', 'post_image', 'comment_image', 'community_avatar', 'community_cover' => 'public',
                default => 'private',
            };

            // 4. Generate UUID and unique storage paths
            $uuid = (string) Str::uuid();
            $extension = strtolower($file->getClientOriginalExtension());
            $tempPath = "temp/{$user->id}/{$uuid}/original.{$extension}";

            // 5. Raw temporary uploads always start private; variants are routed later by collection visibility.
            $provider = $this->router->getPrimaryProvider($collection, 'private');
            $storedObject = $provider->put($tempPath, $fileContent);

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
        } finally {
            optional($lock)->release();
        }
    }
}
