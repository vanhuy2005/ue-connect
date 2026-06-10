<?php

namespace App\AI\HcmueChatbot\Repositories;

use App\Models\CurriculumCourse;
use Illuminate\Support\Collection;

class CurriculumCourseRepository
{
    /**
     * List all courses in a program.
     */
    public function listCoursesByProgram(int $programId): Collection
    {
        return CurriculumCourse::where('program_id', $programId)
            ->orderBy('semester')
            ->orderBy('course_name')
            ->get();
    }

    /**
     * List courses by semester in a program.
     */
    public function listCoursesBySemester(int $programId, int $semester): Collection
    {
        return CurriculumCourse::where('program_id', $programId)
            ->where('semester', $semester)
            ->orderBy('course_name')
            ->get();
    }

    /**
     * Find a specific course in a program by code or name.
     */
    public function findCourseInProgram(int $programId, string $search): ?CurriculumCourse
    {
        return CurriculumCourse::where('program_id', $programId)
            ->where(function ($query) use ($search) {
                $query->where('course_code', $search)
                    ->orWhere('course_name', 'like', "%{$search}%")
                    ->orWhere('normalized_course_name', 'like', "%{$search}%");
            })
            ->first();
    }

    /**
     * List elective courses in a program.
     */
    public function listElectiveCourses(int $programId): Collection
    {
        return CurriculumCourse::where('program_id', $programId)
            ->where(function ($query) {
                $query->where('course_type', 'elective')
                    ->orWhere('is_required', false);
            })
            ->orderBy('semester')
            ->get();
    }

    /**
     * List required courses in a program.
     */
    public function listRequiredCourses(int $programId): Collection
    {
        return CurriculumCourse::where('program_id', $programId)
            ->where(function ($query) {
                $query->where('course_type', 'required')
                    ->orWhere('is_required', true);
            })
            ->orderBy('semester')
            ->get();
    }
}
