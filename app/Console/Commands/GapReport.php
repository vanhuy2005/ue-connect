<?php

namespace App\Console\Commands;

use App\Models\AdmissionCohort;
use App\Models\AiQuestion;
use App\Models\AiRetrievedChunk;
use App\Models\Major;
use App\Models\SourceDocument;
use App\Models\TrainingProgram;
use Illuminate\Console\Command;

class GapReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:knowledge:gap-report';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Phát hiện các khoảng trống kiến thức (Ví dụ: Ngành học thiếu CTĐT, thiếu Chuẩn đầu ra, tài liệu xử lý lỗi)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->header('HCMUE CHATBOT KNOWLEDGE GAP REPORT');

        // 1. Majors without Curriculum
        $this->info("\nChecking Majors without Training Programs...");
        $majorsWithCdt = TrainingProgram::pluck('major_id')->unique()->toArray();
        $majorsWithoutCdt = Major::whereNotIn('id', $majorsWithCdt)->get();

        if ($majorsWithoutCdt->isEmpty()) {
            $this->components->twoColumnDetail('Ngành chưa có CTĐT', '<fg=green>Không có</>');
        } else {
            $this->warn('Tìm thấy '.$majorsWithoutCdt->count().' ngành chưa có Chương trình đào tạo:');
            $rows = [];
            foreach ($majorsWithoutCdt as $m) {
                $rows[] = [$m->code, $m->name, $m->faculty->name ?? 'N/A'];
            }
            $this->table(['Mã ngành', 'Tên ngành', 'Khoa quản lý'], $rows);
        }

        // 2. Curricula without Learning Outcomes
        $this->info("\nChecking Training Programs without Learning Outcomes...");
        $programs = TrainingProgram::withCount('learningOutcomes')->get();
        $programsWithoutLo = $programs->filter(fn ($p) => $p->learning_outcomes_count === 0);

        if ($programsWithoutLo->isEmpty()) {
            $this->components->twoColumnDetail('CTĐT thiếu Chuẩn đầu ra', '<fg=green>Không có</>');
        } else {
            $this->warn('Tìm thấy '.$programsWithoutLo->count().' CTĐT chưa cấu hình Chuẩn đầu ra (PLO):');
            $rows = [];
            foreach ($programsWithoutLo as $p) {
                $rows[] = [$p->id, $p->title, $p->major->name ?? 'N/A'];
            }
            $this->table(['ID CTĐT', 'Tiêu đề CTĐT', 'Ngành học'], $rows);
        }

        // 3. Cohorts with Handbook but no CTĐT
        $this->info("\nChecking Cohorts with Handbook but missing Training Programs...");
        $handbookCohorts = SourceDocument::where('document_type', 'student_handbook')
            ->whereNotNull('cohort')
            ->pluck('cohort')
            ->unique()
            ->toArray();
        $cohortsWithCdt = AdmissionCohort::whereHas('trainingPrograms')->pluck('cohort_name')->toArray();
        $cohortGaps = array_diff($handbookCohorts, $cohortsWithCdt);

        if (empty($cohortGaps)) {
            $this->components->twoColumnDetail('Khóa có Sổ tay nhưng thiếu CTĐT', '<fg=green>Không có</>');
        } else {
            $this->warn('Tìm thấy các khóa có Sổ tay sinh viên nhưng chưa có dữ liệu Chương trình đào tạo:');
            foreach ($cohortGaps as $cohort) {
                $this->line(" - Khóa: $cohort");
            }
        }

        // 4. Unknown Document Types
        $this->info("\nChecking for documents with unknown document type...");
        $unknownDocs = SourceDocument::where('document_type', 'unknown')
            ->orWhereNull('document_type')
            ->get();
        if ($unknownDocs->isEmpty()) {
            $this->components->twoColumnDetail('Tài liệu có loại Unknown', '<fg=green>Không có</>');
        } else {
            $this->warn('Tìm thấy '.$unknownDocs->count().' tài liệu chưa phân loại (unknown):');
            $rows = [];
            foreach ($unknownDocs as $doc) {
                $rows[] = [$doc->id, $doc->title, $doc->file_path];
            }
            $this->table(['ID', 'Tiêu đề', 'Đường dẫn'], $rows);
        }

        // 5. Missing Cohort/Faculty/Major
        $this->info("\nChecking for documents/chunks missing critical metadata...");
        $missingMetaDocs = SourceDocument::whereNull('cohort')
            ->orWhereNull('effective_year')
            ->get();
        if ($missingMetaDocs->isEmpty()) {
            $this->components->twoColumnDetail('Tài liệu thiếu khóa/năm hiệu lực', '<fg=green>Không có</>');
        } else {
            $this->warn('Tìm thấy '.$missingMetaDocs->count().' tài liệu thiếu khóa hoặc năm hiệu lực:');
            $rows = [];
            foreach ($missingMetaDocs as $doc) {
                $rows[] = [$doc->id, $doc->title, $doc->cohort ?: 'Thiếu Khóa', $doc->effective_year ?: 'Thiếu Năm'];
            }
            $this->table(['ID', 'Tiêu đề', 'Khóa', 'Năm hiệu lực'], $rows);
        }

        // 6. Top Fallback Queries
        $this->info("\nChecking top fallback questions from users...");
        $fallbackQueries = AiQuestion::join('ai_answers', 'ai_questions.id', '=', 'ai_answers.question_id')
            ->where(function ($q) {
                $q->where('ai_answers.answer_text', 'like', '%dữ liệu hiện tại không đề cập đến%')
                    ->orWhere('ai_answers.answer_text', 'like', '%chưa tìm thấy thông tin%')
                    ->orWhere('ai_answers.answer_text', 'like', '%ngoài phạm vi hỗ trợ%');
            })
            ->selectRaw('ai_questions.original_question, count(*) as count')
            ->groupBy('ai_questions.original_question')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        if ($fallbackQueries->isEmpty()) {
            $this->components->twoColumnDetail('Câu hỏi bị Fallback nhiều nhất', '<fg=green>Chưa có dữ liệu log</>');
        } else {
            $this->warn('Top 5 câu hỏi của người dùng bị rơi vào câu trả lời mặc định (fallback):');
            $rows = [];
            foreach ($fallbackQueries as $fq) {
                $rows[] = [$fq->original_question, $fq->count];
            }
            $this->table(['Câu hỏi', 'Số lần xuất hiện'], $rows);
        }

        // 7. Weakest Retrieval Document Types
        $this->info("\nChecking document types with weakest semantic retrieval scores...");
        try {
            $retrievalStats = AiRetrievedChunk::join('document_chunks', 'ai_retrieved_chunks.document_chunk_id', '=', 'document_chunks.id')
                ->join('source_documents', 'document_chunks.source_document_id', '=', 'source_documents.id')
                ->selectRaw('source_documents.document_type, avg(ai_retrieved_chunks.score) as avg_score, count(*) as count')
                ->groupBy('source_documents.document_type')
                ->orderBy('avg_score', 'asc')
                ->get();

            if ($retrievalStats->isEmpty()) {
                $this->components->twoColumnDetail('Điểm tương đồng theo loại tài liệu', '<fg=green>Chưa có dữ liệu truy vấn</>');
            } else {
                $rows = [];
                foreach ($retrievalStats as $stat) {
                    $rows[] = [str_replace('_', ' ', $stat->document_type), number_format($stat->avg_score, 4), $stat->count];
                }
                $this->table(['Loại tài liệu', 'Điểm Cosine trung bình', 'Số lần retrieved'], $rows);
            }
        } catch (\Exception $e) {
            $this->warn('Không thể chạy thống kê độ yếu retrieval: '.$e->getMessage());
        }

        // 8. Documents with processing status needs_ocr or failed
        $this->info("\nChecking source documents requiring attention...");
        $troubledDocs = SourceDocument::whereIn('status', ['needs_ocr', 'failed'])->get();
        if ($troubledDocs->isEmpty()) {
            $this->components->twoColumnDetail('Tài liệu lỗi/Cần OCR', '<fg=green>Không có</>');
        } else {
            $this->warn('Tìm thấy '.$troubledDocs->count().' tài liệu gặp sự cố hoặc cần quét OCR:');
            $rows = [];
            foreach ($troubledDocs as $doc) {
                $rows[] = [$doc->id, $doc->title, $doc->status, $doc->document_type];
            }
            $this->table(['ID', 'Tên tài liệu', 'Trạng thái', 'Loại tài liệu'], $rows);
        }

        return self::SUCCESS;
    }

    private function header(string $text): void
    {
        $len = strlen($text) + 8;
        $this->line(str_repeat('=', $len));
        $this->line('=== '.$text.' ===');
        $this->line(str_repeat('=', $len));
    }
}
