<?php

namespace App\AI\HcmueChatbot\Ingestion;

use Illuminate\Support\Facades\Log;

class AcademicDocumentTextExtractor
{
    /**
     * Extract text from a local file path.
     *
     * @param  string  $filePath  Absolute path to the file.
     * @return string Extracted text content.
     *
     * @throws \Exception
     */
    public function extract(string $filePath): string
    {
        if (! file_exists($filePath)) {
            throw new \Exception("File not found for extraction: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'pdf') {
            return $this->extractPdf($filePath);
        }

        if (in_array($extension, ['md', 'markdown', 'txt', 'html', 'json'])) {
            return file_get_contents($filePath);
        }

        throw new \Exception("Unsupported file type for text extraction: .{$extension}");
    }

    /**
     * Extract text from a PDF file using pdftotext utility.
     */
    protected function extractPdf(string $filePath): string
    {
        Log::info("Extracting text from PDF using pdftotext: {$filePath}");

        $tempFile = null;
        $workPath = $filePath;

        // If path has non-ASCII characters and we are on Windows, copy to safe ASCII path
        $hasUnicode = preg_match('/[^\x00-\x7F]/', $filePath);
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($hasUnicode && $isWindows) {
            $tempDir = sys_get_temp_dir();
            $tempFile = $tempDir.DIRECTORY_SEPARATOR.'hcmue_tmp_'.md5($filePath).'.pdf';
            if (copy($filePath, $tempFile)) {
                Log::debug("Copied Unicode path to safe temp file: {$tempFile}");
                $workPath = $tempFile;
            } else {
                Log::warning('Failed to copy Unicode path to safe temp file, using original path.');
            }
        }

        // Escapeshellarg ensures path is safe for shell execution on Windows/Linux
        $escapedPath = escapeshellarg($workPath);

        // We run pdftotext with UTF-8 encoding and output to stdout (-)
        // pdftotext by default adds a Form Feed (\f) page marker at the end of each page.
        $command = "pdftotext -enc UTF-8 {$escapedPath} -";

        $output = shell_exec($command);

        // Clean up temporary file if created
        if ($tempFile && file_exists($tempFile)) {
            unlink($tempFile);
        }

        if ($output === null) {
            // Check if pdftotext is available
            $check = shell_exec('pdftotext -v');
            if (empty($check)) {
                throw new \Exception('pdftotext utility is not installed or not available in the system PATH.');
            }
            throw new \Exception('Failed to extract text from PDF: Command returned null.');
        }

        // Sanitize string to remove any invalid UTF-8 byte sequences
        return mb_convert_encoding($output, 'UTF-8', 'UTF-8');
    }
}
