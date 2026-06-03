<?php

namespace App\Services\Media\Contracts;

use DateTimeInterface;

interface MediaStorageProvider
{
    /**
     * Store content to a path.
     */
    public function put(string $path, string $contents, array $options = []): StoredMediaObject;

    /**
     * Delete file from a path.
     */
    public function delete(string $path): void;

    /**
     * Check if a file exists.
     */
    public function exists(string $path): bool;

    /**
     * Generate a signed temporary URL.
     */
    public function temporaryUrl(string $path, DateTimeInterface $expiresAt): string;

    /**
     * Get a public URL if applicable.
     */
    public function publicUrl(string $path): ?string;
}
