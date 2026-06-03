<?php

namespace App\Services\Media\Providers;

use App\Services\Media\Contracts\MediaStorageProvider;
use App\Services\Media\Contracts\StoredMediaObject;
use DateTimeInterface;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LocalMediaStorageProvider implements MediaStorageProvider
{
    public function __construct(public string $disk = 'public') {}

    public function put(string $path, string $contents, array $options = []): StoredMediaObject
    {
        Storage::disk($this->disk)->put($path, $contents, $options);

        return new StoredMediaObject(
            path: $path,
            disk: $this->disk,
            provider: 'local',
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
        throw new RuntimeException('Local private media URLs must be generated from a Media model.');
    }

    public function publicUrl(string $path): ?string
    {
        if ($this->disk === 'private') {
            return null;
        }

        return Storage::disk($this->disk)->url($path);
    }
}
