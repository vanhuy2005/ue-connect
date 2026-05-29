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
        $engine = config('ai-verification.local_hybrid.ocr_engine', 'tesseract');

        return match ($engine) {
            'paddleocr' => $this->runPaddleOcr($privateDiskPath),
            default => $this->runTesseract($privateDiskPath),
        };
    }

    /**
     * @return array{text: string, engine: string, flags: list<EvidenceRiskFlag>}
     */
    private function runTesseract(string $privateDiskPath): array
    {
        $absolutePath = Storage::disk('private')->path($privateDiskPath);

        $process = new Process([
            'tesseract',
            $absolutePath,
            'stdout',
            '-l', 'vie+eng',
            '--psm', '6',
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
            // Do NOT log image path or OCR content
            Log::warning('StudentCardOcrService: Tesseract process failed.', [
                'error_code' => $process->getExitCode(),
            ]);

            return [
                'text' => '',
                'engine' => 'tesseract',
                'flags' => [EvidenceRiskFlag::OcrUnavailable],
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
