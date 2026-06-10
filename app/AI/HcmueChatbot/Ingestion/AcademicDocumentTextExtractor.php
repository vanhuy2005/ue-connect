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

        // Escapeshellarg ensures path is safe for shell execution on Windows/Linux
        $escapedPath = escapeshellarg($filePath);

        // We run pdftotext and output to stdout (-)
        // pdftotext by default adds a Form Feed (\f) page marker at the end of each page.
        $command = "pdftotext {$escapedPath} -";

        $output = shell_exec($command);

        if ($output === null) {
            // Check if pdftotext is available
            $check = shell_exec('pdftotext -v');
            if (empty($check)) {
                throw new \Exception('pdftotext utility is not installed or not available in the system PATH.');
            }
            throw new \Exception('Failed to extract text from PDF: Command returned null.');
        }

        return $output;
    }
}
