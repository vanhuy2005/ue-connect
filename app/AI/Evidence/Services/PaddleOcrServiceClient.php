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

        $absolutePath = Storage::disk('private')->path($privateDiskPath);

        $response = Http::timeout(30)
            ->attach('file', file_get_contents($absolutePath), basename($absolutePath))
            ->post($serviceUrl.'/ocr');

        $response->throw();

        $data = $response->json();

        return (string) ($data['text'] ?? '');
    }
}
