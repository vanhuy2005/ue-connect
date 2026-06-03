<?php

namespace App\Services\Media\Contracts;

class StoredMediaObject
{
    public function __construct(
        public string $path,
        public string $disk,
        public string $provider,
        public ?string $url = null
    ) {}
}
