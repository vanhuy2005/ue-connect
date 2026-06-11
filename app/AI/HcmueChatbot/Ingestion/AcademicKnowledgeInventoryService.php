<?php

namespace App\AI\HcmueChatbot\Ingestion;

use Illuminate\Support\Facades\File;

class AcademicKnowledgeInventoryService
{
    public function __construct(
        protected AcademicMetadataExtractor $metadataExtractor,
        protected PdfTextQualityAnalyzer $qualityAnalyzer
    ) {}

    /**
     * Run inventory scan on the specified path.
     *
     * @param  string  $basePath  Path to scan (relative to project root).
     */
    public function runInventory(string $basePath, int $sampleLimit = 50): array
    {
        $absolutePath = base_path($basePath);

        if (! File::exists($absolutePath)) {
            throw new \InvalidArgumentException("Target inventory path does not exist: {$basePath}");
        }

        $allFiles = File::allFiles($absolutePath);

        $totalFiles = count($allFiles);
        $totalSizeBytes = 0;
        $fileTypes = [];
        $documentTypes = [
            'training_program' => 0,
            'learning_outcome' => 0,
            'student_handbook' => 0,
            'academic_regulation' => 0,
            'unknown' => 0,
        ];
        $cohorts = [];
        $faculties = [];
        $majors = [];
        $scannedPdfs = [];
        $readablePdfs = [];
        $samples = [];
        $unknownFilenames = [];

        foreach ($allFiles as $file) {
            // Fix Windows Unicode path issue
            $filePath = $file->getRealPath() ?: $file->getPathname();
            $size = $file->getSize();
            $totalSizeBytes += $size;

            $extension = strtolower($file->getExtension());
            $fileTypes[$extension] = ($fileTypes[$extension] ?? 0) + 1;

            $relPath = str_replace(str_replace('\\', '/', base_path().'/'), '', str_replace('\\', '/', $filePath));

            // Collect samples
            if (count($samples) < $sampleLimit) {
                $samples[] = $relPath;
            }

            if ($extension === 'pdf') {
                // Guess metadata
                $meta = $this->metadataExtractor->extract($filePath);
                $docType = $meta['document_type'] ?: 'unknown';
                $documentTypes[$docType] = ($documentTypes[$docType] ?? 0) + 1;

                if ($docType === 'unknown') {
                    $unknownFilenames[] = basename($filePath);
                }

                if ($meta['cohort']) {
                    $cohorts[$meta['cohort']] = ($cohorts[$meta['cohort']] ?? 0) + 1;
                } else {
                    $cohorts['unknown'] = ($cohorts['unknown'] ?? 0) + 1;
                }

                if ($meta['faculty']) {
                    $faculties[$meta['faculty']] = ($faculties[$meta['faculty']] ?? 0) + 1;
                } else {
                    $faculties['unknown'] = ($faculties['unknown'] ?? 0) + 1;
                }

                if ($meta['major']) {
                    $majors[$meta['major']] = ($majors[$meta['major']] ?? 0) + 1;
                } else {
                    $majors['unknown'] = ($majors['unknown'] ?? 0) + 1;
                }

                // Analyze PDF quality (extract check)
                $quality = $this->qualityAnalyzer->analyze($filePath);
                if ($quality['needs_ocr']) {
                    $scannedPdfs[] = [
                        'file_path' => $relPath,
                        'size_bytes' => $size,
                        'text_length' => $quality['text_length'],
                        'error' => $quality['error_message'],
                    ];
                } else {
                    $readablePdfs[] = [
                        'file_path' => $relPath,
                        'size_bytes' => $size,
                        'text_length' => $quality['text_length'],
                    ];
                }
            }
        }

        return [
            'total_files' => $totalFiles,
            'total_size_bytes' => $totalSizeBytes,
            'file_types' => $fileTypes,
            'document_types' => $documentTypes,
            'cohorts' => $cohorts,
            'faculties' => $faculties,
            'majors' => $majors,
            'total_scanned_pdfs' => count($scannedPdfs),
            'total_readable_pdfs' => count($readablePdfs),
            'scanned_pdfs' => $scannedPdfs,
            'samples' => $samples,
            'unknown_filenames' => array_slice($unknownFilenames, 0, 30),
        ];
    }
}
