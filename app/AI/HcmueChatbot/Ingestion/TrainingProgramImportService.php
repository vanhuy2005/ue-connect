<?php

namespace App\AI\HcmueChatbot\Ingestion;

use App\Models\AdmissionCohort;
use App\Models\CurriculumCourse;
use App\Models\CurriculumCourseGroup;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\ProgramLearningOutcome;
use App\Models\TrainingProgram;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TrainingProgramImportService
{
    /**
     * Import a training program from structured data.
     *
     * @param  array  $data  Structured program data.
     */
    public function import(array $data): TrainingProgram
    {
        return DB::transaction(function () use ($data) {
            // 1. Get or create cohort
            $cohortData = $data['cohort'];
            $cohort = AdmissionCohort::firstOrCreate(
                ['year' => $cohortData['year']],
                [
                    'cohort_name' => $cohortData['cohort_name'],
                    'normalized_name' => $this->normalizeText($cohortData['cohort_name']),
                    'note' => $cohortData['note'] ?? null,
                ]
            );

            // 2. Get or create faculty
            $facultyData = $data['faculty'];
            $slug = Str::slug($facultyData['name']);
            $faculty = Faculty::where('code', $facultyData['code'])->first();
            if (! $faculty) {
                $faculty = Faculty::where('slug', $slug)->first();
            }

            if (! $faculty) {
                $faculty = Faculty::create([
                    'code' => $facultyData['code'],
                    'name' => $facultyData['name'],
                    'slug' => $slug,
                    'normalized_name' => $this->normalizeText($facultyData['name']),
                    'status' => 'active',
                ]);
            } else {
                if ((empty($faculty->code) || $faculty->code === 'OTHER') && ! empty($facultyData['code']) && $facultyData['code'] !== 'OTHER') {
                    $faculty->update(['code' => $facultyData['code']]);
                }
            }

            // 3. Get or create major
            $majorData = $data['major'];
            $major = Major::where('code', $majorData['code'])->first();
            if (! $major) {
                $major = Major::where('name', $majorData['name'])->first();
            }

            if (! $major) {
                $major = Major::create([
                    'code' => $majorData['code'],
                    'faculty_id' => $faculty->id,
                    'name' => $majorData['name'],
                    'normalized_name' => $this->normalizeText($majorData['name']),
                    'degree_level' => $majorData['degree_level'] ?? 'undergraduate',
                    'source_url' => $majorData['source_url'] ?? null,
                ]);
            } else {
                if (empty($major->code) && ! empty($majorData['code'])) {
                    $major->update(['code' => $majorData['code']]);
                }
            }

            // 4. Create or update training program
            $programData = $data['program'];
            $program = TrainingProgram::updateOrCreate(
                [
                    'cohort_id' => $cohort->id,
                    'faculty_id' => $faculty->id,
                    'major_id' => $major->id,
                ],
                [
                    'title' => $programData['title'],
                    'total_credits' => $programData['total_credits'] ?? 0,
                    'effective_from' => $programData['effective_from'] ?? $cohort->year,
                    'effective_to' => $programData['effective_to'] ?? ($cohort->year + 4),
                    'status' => 'published',
                    'source_url' => $programData['source_url'] ?? null,
                    'source_hash' => $programData['source_hash'] ?? null,
                    'published_at' => now(),
                ]
            );

            // Clear old data for a clean re-import
            CurriculumCourse::where('program_id', $program->id)->delete();
            CurriculumCourseGroup::where('program_id', $program->id)->delete();
            ProgramLearningOutcome::where('program_id', $program->id)->delete();

            // 5. Insert curriculum course groups and courses
            $groupsCache = [];
            if (! empty($data['courses'])) {
                foreach ($data['courses'] as $courseData) {
                    $groupId = null;
                    $groupName = $courseData['group_name'] ?? null;

                    if ($groupName) {
                        if (! isset($groupsCache[$groupName])) {
                            $group = CurriculumCourseGroup::create([
                                'program_id' => $program->id,
                                'name' => $groupName,
                                'group_type' => $courseData['course_type'] ?? null,
                            ]);
                            $groupsCache[$groupName] = $group->id;
                        }
                        $groupId = $groupsCache[$groupName];
                    }

                    CurriculumCourse::create([
                        'program_id' => $program->id,
                        'group_id' => $groupId,
                        'semester' => $courseData['semester'] ?? null,
                        'course_code' => $courseData['course_code'],
                        'course_name' => $courseData['course_name'],
                        'normalized_course_name' => $this->normalizeText($courseData['course_name']),
                        'credits' => $courseData['credits'],
                        'theory_hours' => $courseData['theory_hours'] ?? 0,
                        'practice_hours' => $courseData['practice_hours'] ?? 0,
                        'self_study_hours' => $courseData['self_study_hours'] ?? 0,
                        'course_type' => $courseData['course_type'] ?? 'required',
                        'is_required' => filter_var($courseData['is_required'] ?? true, FILTER_VALIDATE_BOOLEAN),
                        'prerequisite' => $courseData['prerequisite'] ?? null,
                        'note' => $courseData['note'] ?? null,
                    ]);
                }
            }

            // 6. Insert Program Learning Outcomes
            if (! empty($data['learning_outcomes'])) {
                foreach ($data['learning_outcomes'] as $ploData) {
                    ProgramLearningOutcome::create([
                        'program_id' => $program->id,
                        'code' => $ploData['code'],
                        'description' => $ploData['description'],
                        'category' => $ploData['category'] ?? null,
                    ]);
                }
            }

            Log::info("Successfully imported TrainingProgram: {$program->title}");

            return $program;
        });
    }

    /**
     * Standard text normalization (lowercases, removes extra spaces and accents for fuzzy searches).
     */
    protected function normalizeText(?string $text): ?string
    {
        if (! $text) {
            return null;
        }

        $text = mb_strtolower($text, 'UTF-8');

        // Convert common Vietnamese characters
        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        ];

        foreach ($unicode as $nonUnicode => $unicodePattern) {
            $text = preg_replace("/($unicodePattern)/i", $nonUnicode, $text);
        }

        // Clean extra spaces
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
