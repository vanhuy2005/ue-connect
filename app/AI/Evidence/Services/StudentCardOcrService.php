<?php

namespace App\AI\Evidence\Services;

use App\Enums\EvidenceRiskFlag;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class StudentCardOcrService
{
    public function __construct(
        private readonly PaddleOcrServiceClient $paddleOcrClient,
    ) {}

    /**
     * Run OCR on a private-disk file path.
     *
     * @return array{text: string, engine: string, flags: list<EvidenceRiskFlag>}
     */
    public function extractText(string $privateDiskPath): array
    {
        $engine = config('ai-verification.local_hybrid.ocr_engine', 'ocr_space');

        return match ($engine) {
            'ocr_space' => $this->runOcrSpace($privateDiskPath),
            'paddleocr' => $this->runPaddleOcr($privateDiskPath),
            default => $this->runTesseract($privateDiskPath),
        };
    }

    /**
     * @return array{text: string, engine: string, flags: list<EvidenceRiskFlag>}
     */
    private function runOcrSpace(string $privateDiskPath): array
    {
        $absolutePath = Storage::disk('private')->path($privateDiskPath);
        $apiKey = config('ai-verification.local_hybrid.ocr_space_api_key');
        $apiUrl = config('ai-verification.local_hybrid.ocr_space_api_url', 'https://api.ocr.space/parse/image');

        if (empty($apiKey)) {
            Log::warning('StudentCardOcrService: OCR Space API Key is missing.');
            return [
                'text' => '',
                'engine' => 'ocr_space',
                'flags' => [EvidenceRiskFlag::OcrUnavailable],
            ];
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->attach('file', file_get_contents($absolutePath), basename($absolutePath))
                ->post($apiUrl, [
                    'apikey' => $apiKey,
                    'language' => 'eng',
                    'OCREngine' => 2
                ]);

            if ($response->failed()) {
                Log::warning('StudentCardOcrService: OCR Space request failed.', [
                    'status' => $response->status(),
                    'body' => config('ai-verification.privacy.redact_sensitive_fields_in_logs') ? 'REDACTED' : $response->body(),
                ]);
                return [
                    'text' => '',
                    'engine' => 'ocr_space',
                    'flags' => [EvidenceRiskFlag::OcrUnavailable],
                ];
            }

            $data = $response->json();
            
            if (!empty($data['IsErroredOnProcessing'])) {
                Log::warning('StudentCardOcrService: OCR Space processing error.', [
                    'error' => $data['ErrorMessage'] ?? 'Unknown error',
                ]);
                return [
                    'text' => '',
                    'engine' => 'ocr_space',
                    'flags' => [EvidenceRiskFlag::OcrUnavailable],
                ];
            }

            $text = '';
            if (!empty($data['ParsedResults']) && is_array($data['ParsedResults'])) {
                foreach ($data['ParsedResults'] as $result) {
                    $text .= ($result['ParsedText'] ?? '') . "\n";
                }
            }

            return [
                'text' => trim($text),
                'engine' => 'ocr_space',
                'flags' => [],
            ];
        } catch (\Throwable $e) {
            Log::warning('StudentCardOcrService: OCR Space service unavailable.', [
                'error' => $e->getMessage(),
            ]);

            return [
                'text' => '',
                'engine' => 'ocr_space',
                'flags' => [EvidenceRiskFlag::OcrUnavailable],
            ];
        }
    }

    /**
     * @return array{text: string, engine: string, flags: list<EvidenceRiskFlag>}
     */
    private function runTesseract(string $privateDiskPath): array
    {
        $absolutePath = Storage::disk('private')->path($privateDiskPath);

        $binary = config('ai-verification.local_hybrid.tesseract_binary', 'tesseract');
        $langs = config('ai-verification.local_hybrid.tesseract_langs', 'vie+eng');
        $psm = config('ai-verification.local_hybrid.tesseract_psm', '6');

        $process = new Process([
            $binary,
            $absolutePath,
            'stdout',
            '-l', $langs,
            '--psm', $psm,
        ]);
        $process->setTimeout(30);

        try {
            $process->mustRun();

            $text = trim($process->getOutput());

            return [
                'text' => $text,
                'engine' => 'tesseract',
                'flags' => [],
            ];
        } catch (ProcessFailedException $e) {
            $stderr = $process->getErrorOutput();
            // Do NOT log image path or OCR content
            Log::warning('StudentCardOcrService: Tesseract process failed.', [
                'error_code' => $process->getExitCode(),
                'stderr' => $stderr,
            ]);

            $flag = EvidenceRiskFlag::OcrUnavailable;
            if (str_contains($stderr, 'Error opening data file') || str_contains($stderr, 'traineddata')) {
                $flag = EvidenceRiskFlag::OcrLanguageMissing;
            }

            return [
                'text' => '',
                'engine' => 'tesseract',
                'flags' => [$flag],
            ];
        } catch (\Throwable $e) {
            Log::warning('StudentCardOcrService: Tesseract unavailable.', [
                'error' => $e->getMessage(),
            ]);

            return [
                'text' => '',
                'engine' => 'tesseract',
                'flags' => [EvidenceRiskFlag::OcrUnavailable],
            ];
        }
    }

    /**
     * @return array{text: string, engine: string, flags: list<EvidenceRiskFlag>}
     */
    private function runPaddleOcr(string $privateDiskPath): array
    {
        try {
            $result = $this->paddleOcrClient->extractText($privateDiskPath);

            return [
                'text' => $result,
                'engine' => 'paddleocr',
                'flags' => [],
            ];
        } catch (\Throwable $e) {
            Log::warning('StudentCardOcrService: PaddleOCR service unavailable.', [
                'error' => $e->getMessage(),
            ]);

            return [
                'text' => '',
                'engine' => 'paddleocr',
                'flags' => [EvidenceRiskFlag::OcrUnavailable],
            ];
        }
    }
}
