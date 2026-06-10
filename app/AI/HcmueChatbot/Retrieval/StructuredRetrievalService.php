<?php

namespace App\AI\HcmueChatbot\Retrieval;

use App\AI\HcmueChatbot\Repositories\CurriculumCourseRepository;
use App\AI\HcmueChatbot\Repositories\TrainingProgramRepository;
use App\Models\TrainingProgram;
use Illuminate\Support\Facades\Log;

class StructuredRetrievalService
{
    public function __construct(
        protected TrainingProgramRepository $programRepository,
        protected CurriculumCourseRepository $courseRepository
    ) {}

    /**
     * Execute structured retrieval based on the provided query plan.
     *
     * @param  array  $queryPlan  Structured query plan parsed from LLM.
     */
    public function retrieve(array $queryPlan): array
    {
        Log::info('Executing Structured Retrieval for query type: '.($queryPlan['query_type'] ?? 'unknown'));

        if (! empty($queryPlan['requires_clarification'])) {
            return [
                'success' => false,
                'data' => null,
                'metadata' => [],
                'requires_clarification' => true,
                'clarification_question' => $queryPlan['clarification_question'] ?? 'Xin vui lòng cung cấp thêm thông tin về ngành hoặc khóa học.',
            ];
        }

        $queryType = $queryPlan['query_type'] ?? null;
        $filters = $queryPlan['filters'] ?? [];

        try {
            return match ($queryType) {
                'find_training_program' => $this->handleFindTrainingProgram($filters),
                'list_curriculum_courses' => $this->handleListCurriculumCourses($filters),
                'get_program_total_credits' => $this->handleGetProgramTotalCredits($filters),
                'list_courses_by_semester' => $this->handleListCoursesBySemester($filters),
                'find_course_detail' => $this->handleFindCourseDetail($filters),
                'list_elective_courses' => $this->handleListElectiveCourses($filters),
                'list_required_courses' => $this->handleListRequiredCourses($filters),
                'get_major_faculty' => $this->handleGetMajorFaculty($filters),
                'get_learning_outcomes' => $this->handleGetLearningOutcomes($filters),
                'compare_programs' => $this->handleComparePrograms($filters),
                default => [
                    'success' => false,
                    'data' => null,
                    'metadata' => [],
                    'message' => "Unsupported structured query type: {$queryType}",
                ]
            };
        } catch (\Exception $e) {
            Log::error('Structured retrieval failed: '.$e->getMessage(), ['exception' => $e]);

            return [
                'success' => false,
                'data' => null,
                'metadata' => [],
                'message' => 'Internal retrieval error occurred: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Find a program and return its basic details.
     */
    protected function handleFindTrainingProgram(array $filters): array
    {
        $program = $this->resolveProgram($filters);

        if (! $program) {
            return $this->programNotFoundResponse($filters);
        }

        return [
            'success' => true,
            'data' => $program,
            'metadata' => [
                'type' => 'training_program',
                'id' => $program->id,
                'title' => $program->title,
                'cohort' => $program->cohort?->cohort_name,
                'major' => $program->major?->name,
                'faculty' => $program->faculty?->name,
            ],
        ];
    }

    /**
     * List courses in a training program.
     */
    protected function handleListCurriculumCourses(array $filters): array
    {
        $program = $this->resolveProgram($filters);

        if (! $program) {
            return $this->programNotFoundResponse($filters);
        }

        $courses = $this->courseRepository->listCoursesByProgram($program->id);

        return [
            'success' => true,
            'data' => $courses,
            'metadata' => [
                'type' => 'courses_list',
                'program_id' => $program->id,
                'program_title' => $program->title,
                'count' => $courses->count(),
            ],
        ];
    }

    /**
     * Get total credits for a training program.
     */
    protected function handleGetProgramTotalCredits(array $filters): array
    {
        $program = $this->resolveProgram($filters);

        if (! $program) {
            return $this->programNotFoundResponse($filters);
        }

        $credits = $this->programRepository->getProgramTotalCredits($program->id);

        return [
            'success' => true,
            'data' => ['total_credits' => $credits],
            'metadata' => [
                'type' => 'total_credits',
                'program_id' => $program->id,
                'program_title' => $program->title,
            ],
        ];
    }

    /**
     * List courses in a specific semester.
     */
    protected function handleListCoursesBySemester(array $filters): array
    {
        $program = $this->resolveProgram($filters);

        if (! $program) {
            return $this->programNotFoundResponse($filters);
        }

        $semester = (int) ($filters['semester'] ?? 1);
        $courses = $this->courseRepository->listCoursesBySemester($program->id, $semester);

        return [
            'success' => true,
            'data' => $courses,
            'metadata' => [
                'type' => 'courses_by_semester',
                'program_id' => $program->id,
                'program_title' => $program->title,
                'semester' => $semester,
                'count' => $courses->count(),
            ],
        ];
    }

    /**
     * Find a course detail by code or name within a program.
     */
    protected function handleFindCourseDetail(array $filters): array
    {
        $program = $this->resolveProgram($filters);

        if (! $program) {
            return $this->programNotFoundResponse($filters);
        }

        $search = $filters['course_code'] ?? $filters['course_name'] ?? '';
        if (empty($search)) {
            return [
                'success' => false,
                'data' => null,
                'metadata' => [],
                'message' => 'Vui lòng cung cấp mã hoặc tên học phần để tìm kiếm.',
            ];
        }

        $course = $this->courseRepository->findCourseInProgram($program->id, $search);

        if (! $course) {
            return [
                'success' => false,
                'data' => null,
                'metadata' => [],
                'message' => "Không tìm thấy học phần '{$search}' trong chương trình đào tạo của {$program->title}.",
            ];
        }

        return [
            'success' => true,
            'data' => $course,
            'metadata' => [
                'type' => 'course_detail',
                'program_id' => $program->id,
                'program_title' => $program->title,
                'course_code' => $course->course_code,
                'course_name' => $course->course_name,
            ],
        ];
    }

    /**
     * List elective courses in a program.
     */
    protected function handleListElectiveCourses(array $filters): array
    {
        $program = $this->resolveProgram($filters);

        if (! $program) {
            return $this->programNotFoundResponse($filters);
        }

        $courses = $this->courseRepository->listElectiveCourses($program->id);

        return [
            'success' => true,
            'data' => $courses,
            'metadata' => [
                'type' => 'elective_courses',
                'program_id' => $program->id,
                'program_title' => $program->title,
                'count' => $courses->count(),
            ],
        ];
    }

    /**
     * List required courses in a program.
     */
    protected function handleListRequiredCourses(array $filters): array
    {
        $program = $this->resolveProgram($filters);

        if (! $program) {
            return $this->programNotFoundResponse($filters);
        }

        $courses = $this->courseRepository->listRequiredCourses($program->id);

        return [
            'success' => true,
            'data' => $courses,
            'metadata' => [
                'type' => 'required_courses',
                'program_id' => $program->id,
                'program_title' => $program->title,
                'count' => $courses->count(),
            ],
        ];
    }

    /**
     * Get faculty info of a major.
     */
    protected function handleGetMajorFaculty(array $filters): array
    {
        $majorSearch = $filters['major'] ?? '';
        if (empty($majorSearch)) {
            return [
                'success' => false,
                'data' => null,
                'metadata' => [],
                'message' => 'Vui lòng cung cấp tên ngành học.',
            ];
        }

        $major = $this->programRepository->getMajorFaculty($majorSearch);

        if (! $major) {
            return [
                'success' => false,
                'data' => null,
                'metadata' => [],
                'message' => "Không tìm thấy thông tin ngành học '{$majorSearch}'.",
            ];
        }

        return [
            'success' => true,
            'data' => $major,
            'metadata' => [
                'type' => 'major_faculty',
                'major_name' => $major->name,
                'faculty_name' => $major->faculty?->name,
            ],
        ];
    }

    /**
     * Get learning outcomes of a program.
     */
    protected function handleGetLearningOutcomes(array $filters): array
    {
        $program = $this->resolveProgram($filters);

        if (! $program) {
            return $this->programNotFoundResponse($filters);
        }

        $outcomes = $this->programRepository->getLearningOutcomes($program->id);

        return [
            'success' => true,
            'data' => $outcomes,
            'metadata' => [
                'type' => 'learning_outcomes',
                'program_id' => $program->id,
                'program_title' => $program->title,
                'count' => $outcomes->count(),
            ],
        ];
    }

    /**
     * Compare two training programs.
     */
    protected function handleComparePrograms(array $filters): array
    {
        // For comparison, the prompt/filters might contain arrays or multiple cohorts/majors
        // E.g., filters.cohort could be "Khóa 48", but they want to compare with "Khóa 47"
        // Let's resolve the primary program first.
        $program1 = $this->resolveProgram($filters);

        // Try to figure out the second program parameters
        // E.g. we might have 'compare_cohort' or 'compare_major'
        $compareCohort = $filters['compare_cohort'] ?? null;
        $compareMajor = $filters['compare_major'] ?? null;

        if (! $compareCohort && ! $compareMajor) {
            // Fallback: If comparing different cohorts and cohort is something like "Khóa 48 và Khóa 47"
            // Let's extract them
            $cohortStr = $filters['cohort'] ?? '';
            if (preg_match('/(45|46|47|48|49|50|51)/', $cohortStr, $matches)) {
                // Try to guess the other one
                $currentCohortNum = (int) $matches[1];
                $compareCohort = 'Khóa '.($currentCohortNum - 1);
            }
        }

        $program2Filters = array_merge($filters, [
            'cohort' => $compareCohort ?? $filters['cohort'] ?? '',
            'major' => $compareMajor ?? $filters['major'] ?? '',
        ]);

        $program2 = $this->resolveProgram($program2Filters);

        if (! $program1) {
            return $this->programNotFoundResponse($filters);
        }

        if (! $program2) {
            return [
                'success' => true,
                'data' => [
                    'program1' => [
                        'program' => $program1,
                        'total_credits' => $this->programRepository->getProgramTotalCredits($program1->id),
                    ],
                    'program2' => null,
                ],
                'metadata' => [
                    'type' => 'compare_programs_partial',
                    'program1_title' => $program1->title,
                ],
                'message' => 'Không tìm thấy chương trình đối sánh thứ 2 để so sánh.',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'program1' => [
                    'program' => $program1,
                    'total_credits' => $this->programRepository->getProgramTotalCredits($program1->id),
                ],
                'program2' => [
                    'program' => $program2,
                    'total_credits' => $this->programRepository->getProgramTotalCredits($program2->id),
                ],
            ],
            'metadata' => [
                'type' => 'compare_programs',
                'program1_title' => $program1->title,
                'program2_title' => $program2->title,
            ],
        ];
    }

    /**
     * Resolve TrainingProgram from filters.
     */
    protected function resolveProgram(array $filters): ?TrainingProgram
    {
        $cohort = (string) ($filters['cohort'] ?? $filters['admission_year'] ?? '');
        $major = (string) ($filters['major'] ?? '');

        if (empty($cohort) || empty($major)) {
            return null;
        }

        return $this->programRepository->findProgramByCohortAndMajor($cohort, $major);
    }

    /**
     * Return standard program not found response.
     */
    protected function programNotFoundResponse(array $filters): array
    {
        $cohort = $filters['cohort'] ?? $filters['admission_year'] ?? 'chưa rõ';
        $major = $filters['major'] ?? 'chưa rõ';

        return [
            'success' => false,
            'data' => null,
            'metadata' => [],
            'message' => "Không tìm thấy chương trình đào tạo phù hợp cho ngành '{$major}' của khóa '{$cohort}'.",
        ];
    }
}
