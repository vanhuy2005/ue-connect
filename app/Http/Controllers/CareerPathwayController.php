<?php

namespace App\Http\Controllers;

use App\Http\Resources\CareerPathway\WorktreeResource;
use App\Models\CareerCohort;
use App\Models\CareerCourse;
use App\Models\CareerFaculty;
use App\Models\CareerMajor;
use App\Models\CareerProgram;
use App\Services\CareerPathway\CareerPathwayWorktreeService;
use Illuminate\Http\Request;

class CareerPathwayController extends Controller
{
    /**
     * List cohorts that have at least one public-ready program.
     */
    public function cohorts()
    {
        $cohorts = CareerCohort::whereHas('programs', function ($query) {
            $query->publicReady();
        })->get();

        return response()->json(['data' => $cohorts]);
    }

    /**
     * List faculties that have at least one public-ready program.
     */
    public function faculties()
    {
        $faculties = CareerFaculty::whereHas('programs', function ($query) {
            $query->publicReady();
        })->get();

        return response()->json(['data' => $faculties]);
    }

    /**
     * List majors that have at least one public-ready program.
     */
    public function majors()
    {
        $majors = CareerMajor::whereHas('programs', function ($query) {
            $query->publicReady();
        })->get();

        return response()->json(['data' => $majors]);
    }

    /**
     * List public-ready programs, optionally filtered by cohort, faculty, major.
     */
    public function programs(Request $request)
    {
        $query = CareerProgram::publicReady()->with(['cohort', 'faculty', 'major']);

        if ($request->has('cohort_id')) {
            $query->where('cohort_id', $request->input('cohort_id'));
        }
        if ($request->has('faculty_id')) {
            $query->where('faculty_id', $request->input('faculty_id'));
        }
        if ($request->has('major_id')) {
            $query->where('major_id', $request->input('major_id'));
        }

        return response()->json(['data' => $query->get()]);
    }

    /**
     * Get the full worktree for a specific public-ready program.
     */
    public function worktree(CareerProgram $program, CareerPathwayWorktreeService $service)
    {
        $worktree = $service->getWorktree($program->id);

        if (! $worktree) {
            abort(404, 'Program is not public-ready or does not exist.');
        }

        return new WorktreeResource($worktree);
    }

    /**
     * Get course details, ensuring it only includes data relevant to public-ready programs.
     * We don't expose raw_context.
     */
    public function course(CareerCourse $course)
    {
        // Only load programs that are public ready
        $course->load(['programCourses.careerProgram' => function ($query) {
            $query->publicReady();
        }]);

        // Hide raw_context and related from any descriptions if they exist
        $course->makeHidden(['raw_context']);

        return response()->json(['data' => $course]);
    }
}
