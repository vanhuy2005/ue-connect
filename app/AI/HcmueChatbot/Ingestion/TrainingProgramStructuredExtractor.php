<?php

namespace App\AI\HcmueChatbot\Ingestion;

use App\Models\SourceDocument;
use App\Models\TrainingProgramExtractionCandidate;
use Illuminate\Support\Facades\Log;

class TrainingProgramStructuredExtractor
{
    public function __construct(
        protected TrainingProgramImportService $importService,
        protected AcademicMetadataExtractor $metadataExtractor
    ) {}

    /**
     * Extract training program details from a source document.
     */
    public function extractAndSave(SourceDocument $sourceDoc, string $text): array
    {
        Log::info("TrainingProgramStructuredExtractor: Starting structured extraction for Document ID: {$sourceDoc->id}");

        $patterns = [
            // High confidence patterns (>= 0.90)
            [
                'pattern' => '/tổng\s+số\s+tín\s+chỉ\s+(?:cho\s+)?toàn\s+khóa\s+học\s+là\s+(\d+)/iu',
                'confidence' => 0.95,
                'name' => 'tong_so_tin_chi_cho_toan_khoa_hoc_la',
            ],
            [
                'pattern' => '/tổng\s+số\s+tín\s+chỉ\s+(?:cho\s+)?toàn\s+khoá\s+học\s+là\s+(\d+)/iu',
                'confidence' => 0.95,
                'name' => 'tong_so_tin_chi_cho_toan_khoa_hoc_la_alt',
            ],
            [
                'pattern' => '/tổng\s+số\s+tín\s+chỉ\s+toàn\s+khóa\s*:\s*(\d+)/iu',
                'confidence' => 0.90,
                'name' => 'tong_so_tin_chi_toan_khoa',
            ],
            [
                'pattern' => '/tổng\s+số\s+tín\s+chỉ\s+toàn\s+khoá\s*:\s*(\d+)/iu',
                'confidence' => 0.90,
                'name' => 'tong_so_tin_chi_toan_khoa_alt',
            ],
            [
                'pattern' => '/khối\s+lượng\s+kiến\s+thức\s+toàn\s+khóa\s*:\s*(\d+)\s*(?:tín\s+chỉ|tc)/iu',
                'confidence' => 0.90,
                'name' => 'khoi_luong_kien_thuc_toan_khoa',
            ],
            [
                'pattern' => '/khối\s+lượng\s+kiến\s+thức\s+toàn\s+khoá\s*:\s*(\d+)\s*(?:tín\s+chỉ|tc)/iu',
                'confidence' => 0.90,
                'name' => 'khoi_luong_kien_thuc_toan_khoa_alt',
            ],
            [
                'pattern' => '/tổng\s+khối\s+lượng\s+kiến\s+thức\s+(?:toàn\s+khóa|toàn\s+khoá)\s*:\s*(\d+)\s*(?:tín\s+chỉ|tc)/iu',
                'confidence' => 0.90,
                'name' => 'tong_khoi_luong_kien_thuc_toan_khoa',
            ],
            // Medium confidence patterns (0.75 - 0.85)
            [
                'pattern' => '/tổng\s+số\s+tín\s+chỉ\s*:\s*(\d+)/iu',
                'confidence' => 0.85,
                'name' => 'tong_so_tin_chi',
            ],
            [
                'pattern' => '/tổng\s+số\s+tc\s*:\s*(\d+)/iu',
                'confidence' => 0.85,
                'name' => 'tong_so_tc',
            ],
            [
                'pattern' => '/tổng\s+tín\s+chỉ\s*:\s*(\d+)/iu',
                'confidence' => 0.85,
                'name' => 'tong_tin_chi',
            ],
            [
                'pattern' => '/yêu\s+cầu\s+tích\s+lũy\s+(\d+)\s+tín\s+chỉ/iu',
                'confidence' => 0.80,
                'name' => 'yeu_cau_tich_luy_tin_chi',
            ],
            [
                'pattern' => '/cần\s+tích\s+lũy\s+tối\s+thiểu\s+(\d+)\s+tín\s+chỉ/iu',
                'confidence' => 0.80,
                'name' => 'can_tich_luy_toi_thieu_tin_chi',
            ],
            // Low confidence patterns (< 0.75)
            [
                'pattern' => '/(\d+)\s+tín\s+chỉ\s+(?:để|yêu\s+cầu|tích\s+lũy|tốt\s+nghiệp)/iu',
                'confidence' => 0.70,
                'name' => 'credits_for_graduation',
            ],
            [
                'pattern' => '/Tổng\s+cộng.*?(\d+)\s*$/im',
                'confidence' => 0.60,
                'name' => 'tong_cong_end_of_line',
            ],
        ];

        $candidates = [];
        $filePath = base_path($sourceDoc->source_url ?: $sourceDoc->file_path);
        $meta = $this->metadataExtractor->extract($filePath);

        foreach ($patterns as $item) {
            // Find matches and record context
            if (preg_match_all($item['pattern'], $text, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $index => $matchObj) {
                    $matchText = $matchObj[0];
                    $offset = $matchObj[1];
                    $val = (int) $matches[1][$index][0];

                    // Heuristic check: typical program credits are between 110 and 160
                    $confidence = $item['confidence'];
                    if ($val < 110 || $val > 160) {
                        $confidence -= 0.30; // Reduce confidence for outlier values
                    }

                    // Extract a surrounding context block (evidence text)
                    $start = max(0, $offset - 100);
                    $length = min(mb_strlen($text) - $start, 250);
                    $evidenceText = mb_substr($text, $start, $length, 'UTF-8');

                    // Estimate page number based on form-feed characters (\f) before match offset
                    $page = substr_count(substr($text, 0, $offset), "\f") + 1;

                    $candidates[] = [
                        'source_document_id' => $sourceDoc->id,
                        'field_name' => 'total_credits',
                        'candidate_value' => (string) $val,
                        'confidence' => max(0.1, $confidence),
                        'evidence_text' => trim(preg_replace('/\s+/', ' ', $evidenceText)),
                        'page' => $page,
                        'metadata_json' => [
                            'pattern_name' => $item['name'],
                            'match_text' => $matchText,
                            'offset' => $offset,
                        ],
                    ];
                }
            }
        }

        // Sort candidates by confidence descending
        usort($candidates, fn ($a, $b) => $b['confidence'] <=> $a['confidence']);

        // Save candidates to database
        foreach ($candidates as $cand) {
            TrainingProgramExtractionCandidate::create($cand);
        }

        $bestCandidate = $candidates[0] ?? null;
        $extractedCredits = 0;
        $confidence = 0.0;
        $evidence = '';
        $page = null;

        if ($bestCandidate) {
            $extractedCredits = (int) $bestCandidate['candidate_value'];
            $confidence = $bestCandidate['confidence'];
            $evidence = $bestCandidate['evidence_text'];
            $page = $bestCandidate['page'];
        }

        $success = false;
        $status = 'needs_review';

        // Check if we meet the confidence threshold to automatically save/publish
        if ($confidence >= 0.75 && $extractedCredits > 0) {
            $status = 'active';

            // Reconstruct data array for TrainingProgramImportService
            $cohortName = $meta['cohort'] ?: 'Khóa chưa rõ';
            $year = $meta['academic_year'] ?: date('Y');

            // Format cohort_name cleanly: "2023 - Khóa 49"
            if (preg_match('/^K(\d+)$/i', $cohortName, $m)) {
                $cohortNum = $m[1];
                $cohortName = "{$year} - Khóa {$cohortNum}";
            }

            $facultyName = $meta['faculty'] ?: 'Khoa chưa xác định';
            $majorName = $meta['major'] ?: 'Ngành chưa xác định';

            $importData = [
                'cohort' => [
                    'year' => $year,
                    'cohort_name' => $cohortName,
                    'note' => 'Auto-structured extraction from batch',
                ],
                'faculty' => [
                    'code' => strtoupper(substr(str_replace('Khoa ', '', $facultyName), 0, 10)),
                    'name' => $facultyName,
                ],
                'major' => [
                    'code' => $meta['normalized_major'] ? strtoupper(str_replace(' ', '', $meta['normalized_major'])) : 'UNKNOWN',
                    'name' => $majorName,
                    'degree_level' => $meta['program_level'] ?: 'undergraduate',
                    'source_url' => $sourceDoc->source_url,
                ],
                'program' => [
                    'title' => $meta['title'] ?: ($majorName.' - '.$cohortName),
                    'total_credits' => $extractedCredits,
                    'effective_from' => $year,
                    'effective_to' => $year + 4,
                    'source_url' => $sourceDoc->source_url,
                    'source_hash' => $sourceDoc->source_hash,
                ],
                'courses' => [], // Regex extraction doesn't parse full course table (done via Gemini or RAG)
                'learning_outcomes' => [],
            ];

            // Save to database using ImportService
            $this->importService->import($importData);
            $success = true;
        }

        return [
            'success' => $success,
            'status' => $status,
            'total_credits' => $extractedCredits,
            'confidence' => $confidence,
            'evidence_text' => $evidence,
            'page' => $page,
            'candidates_count' => count($candidates),
        ];
    }
}
