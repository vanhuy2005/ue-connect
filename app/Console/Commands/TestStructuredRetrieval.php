<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Retrieval\StructuredRetrievalService;
use Illuminate\Console\Command;

class TestStructuredRetrieval extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:test-structured
                            {--major=Công nghệ thông tin : The name or code of the major}
                            {--cohort=Khóa 48 : The cohort name or year}
                            {--semester= : The semester number (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Phase 2 structured curriculum database retrieval service';

    public function __construct(
        protected StructuredRetrievalService $retrievalService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $major = $this->option('major');
        $cohort = $this->option('cohort');
        $semester = $this->option('semester');

        $this->info('=== Testing Structured Retrieval ===');
        $this->line("Major: {$major}");
        $this->line("Cohort: {$cohort}");
        if ($semester) {
            $this->line("Semester: {$semester}");
        }
        $this->line('------------------------------------');

        // 1. Check find_training_program
        $programPlan = [
            'query_type' => 'find_training_program',
            'filters' => [
                'cohort' => $cohort,
                'major' => $major,
            ],
        ];

        $programResult = $this->retrievalService->retrieve($programPlan);

        if (! $programResult['success']) {
            $this->error('[FAIL] '.($programResult['message'] ?? 'Failed to find training program.'));

            return Command::FAILURE;
        }

        $program = $programResult['data'];
        $this->info("[OK] Found program: {$program->title}");
        $this->line(' - Faculty: '.($programResult['metadata']['faculty'] ?? 'N/A'));
        $this->line(' - Major: '.($programResult['metadata']['major'] ?? 'N/A'));

        // 2. Check total credits
        $creditsPlan = [
            'query_type' => 'get_program_total_credits',
            'filters' => [
                'cohort' => $cohort,
                'major' => $major,
            ],
        ];

        $creditsResult = $this->retrievalService->retrieve($creditsPlan);
        if ($creditsResult['success']) {
            $this->info("[OK] Total credits: {$creditsResult['data']['total_credits']}");
        } else {
            $this->warn('[WARN] Could not retrieve total credits: '.($creditsResult['message'] ?? ''));
        }

        // 3. Check list_curriculum_courses
        $coursesPlan = [
            'query_type' => 'list_curriculum_courses',
            'filters' => [
                'cohort' => $cohort,
                'major' => $major,
            ],
        ];

        $coursesResult = $this->retrievalService->retrieve($coursesPlan);
        if ($coursesResult['success']) {
            $coursesCount = $coursesResult['metadata']['count'] ?? count($coursesResult['data']);
            $this->info("[OK] Courses count: {$coursesCount}");
            if ($coursesCount === 0) {
                $this->error('[FAIL] Training program has 0 courses in database.');

                return Command::FAILURE;
            }
        } else {
            $this->error('[FAIL] Failed to retrieve courses: '.($coursesResult['message'] ?? ''));

            return Command::FAILURE;
        }

        // 4. If semester option is provided, list courses for that semester
        if ($semester) {
            $semesterNum = (int) $semester;
            $semesterPlan = [
                'query_type' => 'list_courses_by_semester',
                'filters' => [
                    'cohort' => $cohort,
                    'major' => $major,
                    'semester' => $semesterNum,
                ],
            ];

            $semesterResult = $this->retrievalService->retrieve($semesterPlan);
            if ($semesterResult['success']) {
                $courses = $semesterResult['data'];
                $this->info("[OK] Semester {$semesterNum} courses ({$semesterResult['metadata']['count']}):");
                foreach ($courses as $c) {
                    $typeStr = $c->is_required ? 'Required' : 'Elective';
                    $this->line("  - [{$c->course_code}] {$c->course_name} ({$c->credits} credits, {$typeStr})");
                }
            } else {
                $this->error("[FAIL] Failed to retrieve courses for semester {$semesterNum}: ".($semesterResult['message'] ?? ''));
            }
        }

        return Command::SUCCESS;
    }
}
