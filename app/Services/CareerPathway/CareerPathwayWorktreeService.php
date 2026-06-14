<?php

namespace App\Services\CareerPathway;

use App\Models\CareerProgram;
use Illuminate\Support\Facades\Cache;

class CareerPathwayWorktreeService
{
    /**
     * Get the full worktree for a program.
     * Includes semesters and courses.
     * Caches the result to avoid multiple heavy queries.
     *
     * @return array|null Returns null if program is not public-ready.
     */
    public function getWorktree(int $programId): ?array
    {
        // First, check if program exists and is public-ready
        $program = CareerProgram::publicReady()->find($programId);

        if (! $program) {
            return null;
        }

        // Define cache strategy
        $cacheKey = 'career_program_worktree_'.$programId;
        $useTags = config('cache.default') === 'redis' || config('cache.default') === 'memcached';

        $closure = function () use ($program) {
            $program->load([
                'semesters' => function ($query) {
                    $query->orderBy('semester_number', 'asc');
                },
                'semesters.programCourses.course.courseDescriptions',
                'sourceDocument',
                'dataQualityIssues' => function ($query) {
                    $query->select(['id', 'program_id', 'issue_type', 'severity', 'message']); // Exclude raw_context
                },
            ]);

            // Transform semesters and courses
            $semesters = $program->semesters->map(function ($semester) {
                return [
                    'id' => $semester->id,
                    'semester_number' => $semester->semester_number,
                    'title' => $semester->title,
                    'courses' => $semester->programCourses->sortBy('course_code')->map(function ($programCourse) {
                        return [
                            'id' => $programCourse->id,
                            'course_code' => $programCourse->course_code,
                            'is_mandatory' => $programCourse->is_mandatory,
                            'knowledge_block' => $programCourse->knowledge_block,
                            'course' => [
                                'id' => $programCourse->course->id,
                                'code' => $programCourse->course->code,
                                'name' => $programCourse->course->name,
                                'credits' => $programCourse->course->credits,
                                'description' => $programCourse->course->courseDescriptions->first()?->description_text,
                            ],
                        ];
                    })->values()->all(),
                ];
            })->all();

            return [
                'id' => $program->id,
                'name' => $program->name,
                'code' => $program->code,
                'cohort_id' => $program->cohort_id,
                'faculty_id' => $program->faculty_id,
                'major_id' => $program->major_id,
                'status' => $program->status,
                'total_credits' => $program->total_credits,
                'source_document' => $program->sourceDocument ? [
                    'id' => $program->sourceDocument->id,
                    'original_filename' => $program->sourceDocument->original_filename,
                    'extracted_at' => $program->sourceDocument->extracted_at,
                ] : null,
                'quality_warnings' => $program->dataQualityIssues->toArray(),
                'semesters' => $semesters,
            ];
        };

        if ($useTags) {
            return Cache::tags(['career_program:'.$programId])->remember($cacheKey, now()->addDays(7), $closure);
        }

        return Cache::remember($cacheKey, now()->addDays(7), $closure);
    }
}
