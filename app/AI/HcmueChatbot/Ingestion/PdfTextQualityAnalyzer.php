<?php

namespace App\AI\HcmueChatbot\Ingestion;

use Illuminate\Support\Facades\Log;

class PdfTextQualityAnalyzer
{
    public function __construct(
        protected AcademicDocumentTextExtractor $extractor
    ) {}

    /**
     * Analyze a PDF file to determine text quality and if it's scanned (needs OCR).
     *
     * @param  string  $filePath  Absolute path to the PDF file.
     * @return array{
     *   is_scanned: bool,
     *   needs_ocr: bool,
     *   text_length: int,
     *   text_sample: string,
     *   error_message: ?string
     * }
     */
    public function analyze(string $filePath): array
    {
        Log::info("Analyzing PDF text quality for: {$filePath}");

        try {
            $text = $this->extractor->extract($filePath);
            $trimmedText = trim($text);
            $length = mb_strlen($trimmedText, 'UTF-8');

            // Quality threshold: less than 500 characters usually means a scanned cover/image only,
            // or an entirely scanned document.
            $isScanned = $length < 500;

            // We can also analyze if the text contains high amount of binary gibberish or non-readable chars,
            // but pdftotext on scanned files typically returns empty page markers \f or empty space.
            $needsOcr = $isScanned;

            $textSample = mb_substr($trimmedText, 0, 100, 'UTF-8');

            return [
                'is_scanned' => $isScanned,
                'needs_ocr' => $needsOcr,
                'text_length' => $length,
                'text_sample' => $textSample,
                'error_message' => null,
            ];
        } catch (\Exception $e) {
            Log::warning("PdfTextQualityAnalyzer failed for {$filePath}: ".$e->getMessage());

            return [
                'is_scanned' => true,
                'needs_ocr' => true,
                'text_length' => 0,
                'text_sample' => '',
                'error_message' => $e->getMessage(),
            ];
        }
    }
}
