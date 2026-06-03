<?php

namespace App\Services\Media\Providers;

use App\Services\Media\Contracts\MediaStorageProvider;
use App\Services\Media\Contracts\StoredMediaObject;
use DateTimeInterface;
use Illuminate\Support\Facades\Storage;

class S3CompatibleMediaStorageProvider implements MediaStorageProvider
{
    public function __construct(public string $disk = 'r2_public') {}

    public function put(string $path, string $contents, array $options = []): StoredMediaObject
    {
        Storage::disk($this->disk)->put($path, $contents, $options);

        return new StoredMediaObject(
            path: $path,
            disk: $this->disk,
            provider: 'r2',
            url: $this->publicUrl($path)
        );
    }

    public function delete(string $path): void
    {
        if ($this->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    public function temporaryUrl(string $path, DateTimeInterface $expiresAt): string
    {
        return Storage::disk($this->disk)->temporaryUrl($path, $expiresAt);
    }

    public function publicUrl(string $path): ?string
    {
        if (str_contains($this->disk, 'private')) {
            return null;
        }

        if (empty(config("filesystems.disks.{$this->disk}.url"))) {
            return null;
        }

        return Storage::disk($this->disk)->url($path);
    }
}
