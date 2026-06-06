<?php

namespace App\AI\Evidence\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PaddleOcrServiceClient
{
    public function extractText(string $privateDiskPath): string
    {
        $serviceUrl = config('ai-verification.local_hybrid.paddleocr_service_url');

        if (empty($serviceUrl)) {
            throw new \RuntimeException('PaddleOCR service URL is not configured.');
        }

        $diskName = config('media.private_disk', 'private');
        $fileContents = Storage::disk($diskName)->get($privateDiskPath);

        if ($fileContents === null) {
            throw new \RuntimeException("File not found on private storage disk [{$diskName}]: {$privateDiskPath}");
        }

        $response = Http::timeout(30)
            ->attach('file', $fileContents, basename($privateDiskPath))
            ->post($serviceUrl.'/ocr');

        $response->throw();

        $data = $response->json();

        return (string) ($data['text'] ?? '');
    }
}
