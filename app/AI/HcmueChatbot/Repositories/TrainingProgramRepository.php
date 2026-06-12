<?php

namespace App\AI\HcmueChatbot\Repositories;

use App\Models\AdmissionCohort;
use App\Models\Major;
use App\Models\ProgramLearningOutcome;
use App\Models\TrainingProgram;
use Illuminate\Support\Collection;

class TrainingProgramRepository
{
    /**
     * Find program by Cohort Name and Major Name/Code.
     */
    public function findProgramByCohortAndMajor(string $cohortName, string $majorSearch): ?TrainingProgram
    {
        // 1. Find the cohort
        $cohort = null;

        $year = null;
        if (preg_match('/\b(20\d{2})\b/u', $cohortName, $yrMatches)) {
            $year = (int) $yrMatches[1];
        }

        $cleanCohortName = $year ? str_replace((string) $year, '', $cohortName) : $cohortName;

        $cohortNum = null;
        if (preg_match('/(\d{2})/u', $cleanCohortName, $numMatches)) {
            $cohortNum = $numMatches[1];
        }

        if ($cohortNum) {
            $cohort = AdmissionCohort::where('cohort_name', 'like', "%{$cohortNum}%")
                ->orWhere('normalized_name', 'like', "%{$cohortNum}%")
                ->first();
        }

        if (! $cohort && $year) {
            $cohort = AdmissionCohort::where('year', $year)->first();
        }

        if (! $cohort) {
            $cohort = AdmissionCohort::where('cohort_name', 'like', "%{$cohortName}%")
                ->orWhere('normalized_name', 'like', "%{$cohortName}%")
                ->first();
        }

        if (! $cohort) {
            return null;
        }

        // 2. Find the major
        $major = Major::where('code', $majorSearch)
            ->orWhere('name', 'like', "%{$majorSearch}%")
            ->orWhere('normalized_name', 'like', "%{$majorSearch}%")
            ->first();

        if (! $major) {
            return null;
        }

        // 3. Find the program
        return TrainingProgram::where('cohort_id', $cohort->id)
            ->where('major_id', $major->id)
            ->with(['cohort', 'faculty', 'major'])
            ->first();
    }

    /**
     * Get total credits of a program.
     */
    public function getProgramTotalCredits(int $programId): int
    {
        $program = TrainingProgram::find($programId);

        return $program ? $program->total_credits : 0;
    }

    /**
     * Get learning outcomes for a program.
     */
    public function getLearningOutcomes(int $programId): Collection
    {
        return ProgramLearningOutcome::where('program_id', $programId)->get();
    }

    /**
     * Get major and faculty info by major name.
     */
    public function getMajorFaculty(string $majorSearch): ?Major
    {
        return Major::where('name', 'like', "%{$majorSearch}%")
            ->orWhere('normalized_name', 'like', "%{$majorSearch}%")
            ->with('faculty')
            ->first();
    }

    /**
     * Find program by ID.
     */
    public function find(int $id): ?TrainingProgram
    {
        return TrainingProgram::with(['cohort', 'faculty', 'major'])->find($id);
    }
}
