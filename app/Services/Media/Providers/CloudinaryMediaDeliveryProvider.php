<?php

namespace App\Services\Media\Providers;

use App\Services\Media\Contracts\MediaStorageProvider;
use App\Services\Media\Contracts\StoredMediaObject;
use DateTimeInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CloudinaryMediaDeliveryProvider implements MediaStorageProvider
{
    protected string $cloudName;

    protected ?string $apiKey;

    protected ?string $apiSecret;

    protected bool $secure;

    protected string $uploadFolder;

    protected bool $failOpen;

    public function __construct()
    {
        $this->cloudName = (string) (config('media.providers.cloudinary.cloud_name') ?? '');
        $this->apiKey = config('media.providers.cloudinary.api_key');
        $this->apiSecret = config('media.providers.cloudinary.api_secret');
        $this->secure = (bool) config('media.providers.cloudinary.secure', true);
        $this->uploadFolder = trim((string) config('media.providers.cloudinary.upload_folder', 'ueconnect'), '/');
        $this->failOpen = (bool) config('media.providers.cloudinary.fail_open', true);
    }

    public function put(string $path, string $contents, array $options = []): StoredMediaObject
    {
        $publicId = $options['public_id'] ?? $this->publicIdForPath($path);

        try {
            $this->ensureConfigured();

            $response = $this->upload($contents, $publicId, $options['mime_type'] ?? 'image/png', $options);
            $data = $response->json();

            if ($response->successful()) {
                return new StoredMediaObject(
                    path: $data['public_id'] ?? $publicId,
                    disk: 'cloudinary',
                    provider: 'cloudinary',
                    url: $data['secure_url'] ?? $data['url'] ?? null
                );
            }

            throw new RuntimeException($this->errorMessage($response));
        } catch (Throwable $e) {
            Log::warning('Cloudinary upload failed', [
                'path' => $path,
                'public_id' => $publicId,
                'message' => Str::limit($e->getMessage(), 240),
            ]);

            if (! $this->failOpen) {
                throw $e;
            }
        }

        return new StoredMediaObject(
            path: $publicId,
            disk: 'cloudinary',
            provider: 'cloudinary',
            url: null
        );
    }

    /**
     * @return array{public_id: string, version: int|null, secure_url: string|null, format: string|null, bytes: int|null, resource_type: string|null}
     */
    public function uploadVariant(string $publicId, string $contents, string $mimeType = 'image/webp', array $options = []): array
    {
        $this->ensureConfigured();

        $response = $this->upload($contents, $publicId, $mimeType, $options);

        if (! $response->successful()) {
            throw new RuntimeException($this->errorMessage($response));
        }

        $data = $response->json();

        return [
            'public_id' => $data['public_id'] ?? $publicId,
            'version' => isset($data['version']) ? (int) $data['version'] : null,
            'secure_url' => $data['secure_url'] ?? $data['url'] ?? null,
            'format' => $data['format'] ?? null,
            'bytes' => isset($data['bytes']) ? (int) $data['bytes'] : null,
            'resource_type' => $data['resource_type'] ?? 'image',
        ];
    }

    public function delete(string $path): void
    {
        try {
            $this->ensureConfigured();
            $timestamp = time();
            $params = [
                'public_id' => $path,
                'timestamp' => $timestamp,
            ];

            Http::asMultipart()->post(
                "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy",
                [
                    'public_id' => $path,
                    'timestamp' => $timestamp,
                    'api_key' => $this->apiKey,
                    'signature' => $this->signature($params),
                ]
            );
        } catch (Throwable $e) {
            Log::warning('Cloudinary delete failed', [
                'public_id' => $path,
                'message' => Str::limit($e->getMessage(), 240),
            ]);
        }
    }

    public function exists(string $path): bool
    {
        // For delivery optimization providers, we assume it exists if generated
        return true;
    }

    public function temporaryUrl(string $path, DateTimeInterface $expiresAt): string
    {
        // Cloudinary is NEVER for private media, so temporaryUrl should not be used
        throw new RuntimeException('Cloudinary provider is for public delivery only.');
    }

    public function buildTransformationUrl(string $publicId, array $transformations = [], ?string $version = null): string
    {
        $this->ensureConfigured();

        $scheme = $this->secure ? 'https' : 'http';
        $transformationString = '';

        if (! empty($transformations)) {
            $parts = [];
            foreach ($transformations as $key => $val) {
                if (is_numeric($key)) {
                    $parts[] = $val;
                } else {
                    $parts[] = "{$key}_{$val}";
                }
            }
            $transformationString = implode(',', $parts).'/';
        }

        $versionPath = $version ? "v{$version}/" : '';

        return "{$scheme}://res.cloudinary.com/{$this->cloudName}/image/upload/{$transformationString}{$versionPath}{$publicId}";
    }

    public function publicUrl(string $path): ?string
    {
        if (empty($this->cloudName)) {
            return null;
        }

        return $this->buildTransformationUrl($path);
    }

    public function publicId(string $collection, string $mediaUuid, string $variantName): string
    {
        $environment = preg_replace('/[^a-zA-Z0-9_-]+/', '-', app()->environment());

        return "{$this->uploadFolder}/{$environment}/{$collection}/{$mediaUuid}/{$variantName}";
    }

    /**
     * @return array{ok: bool, public_id?: string, url?: string, message?: string}
     */
    public function healthCheck(): array
    {
        $publicId = $this->publicId('health-check', (string) Str::uuid(), 'tiny');

        try {
            $result = $this->uploadVariant($publicId, $this->tinyPng(), 'image/png');
            $this->delete($result['public_id']);

            return [
                'ok' => true,
                'public_id' => $result['public_id'],
                'url' => $result['secure_url'],
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'message' => Str::limit($e->getMessage(), 240),
            ];
        }
    }

    protected function upload(string $contents, string $publicId, string $mimeType, array $options = []): Response
    {
        $timestamp = time();
        $params = [
            'overwrite' => 'true',
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];

        if (isset($options['invalidate'])) {
            $params['invalidate'] = $options['invalidate'] ? 'true' : 'false';
        }
        if (isset($options['folder'])) {
            $params['folder'] = $options['folder'];
        }
        if (isset($options['overwrite'])) {
            $params['overwrite'] = $options['overwrite'] ? 'true' : 'false';
        }
        if (isset($options['public_id'])) {
            $params['public_id'] = $options['public_id'];
        }

        return Http::timeout(20)->asMultipart()->post(
            "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload",
            array_merge($params, [
                'file' => 'data:'.$mimeType.';base64,'.base64_encode($contents),
                'api_key' => $this->apiKey,
                'signature' => $this->signature($params),
            ])
        );
    }

    protected function signature(array $params): string
    {
        ksort($params);

        $signaturePayload = collect($params)
            ->map(fn (mixed $value, string $key): string => "{$key}={$value}")
            ->implode('&');

        return sha1($signaturePayload.$this->apiSecret);
    }

    protected function ensureConfigured(): void
    {
        if (blank($this->cloudName) || blank($this->apiKey) || blank($this->apiSecret)) {
            throw new RuntimeException('Cloudinary credentials are incomplete.');
        }
    }

    protected function publicIdForPath(string $path): string
    {
        $path = preg_replace('/\.[a-zA-Z0-9]+$/', '', $path) ?: $path;

        return $this->uploadFolder.'/'.trim($path, '/');
    }

    protected function errorMessage(Response $response): string
    {
        $json = $response->json();
        $message = data_get($json, 'error.message') ?: $response->body();

        return 'Cloudinary response '.$response->status().': '.Str::limit((string) $message, 240);
    }

    protected function tinyPng(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=') ?: '';
    }
}
