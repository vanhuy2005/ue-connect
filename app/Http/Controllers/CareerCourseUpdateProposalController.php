<?php

namespace App\Http\Controllers;

use App\Enums\CareerContributionStatus;
use App\Enums\CareerContributionType;
use App\Enums\CareerContributionVisibility;
use App\Models\CareerCourse;
use App\Models\CareerProgramCourse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CareerCourseUpdateProposalController extends Controller
{
    public function store(Request $request, CareerCourse $course): RedirectResponse
    {
        $validated = $request->validate([
            'program_course_id' => 'nullable|integer|exists:career_program_courses,id',
            'name' => 'nullable|string|max:255',
            'credits' => 'nullable|numeric|min:0|max:20',
            'description' => 'nullable|string|max:5000',
            'knowledge_block' => 'nullable|string|max:255',
            'is_mandatory' => 'nullable|boolean',
            'reason' => 'required|string|max:1000',
        ]);

        if (! empty($validated['program_course_id'])) {
            $programCourse = CareerProgramCourse::where('course_id', $course->id)
                ->whereKey($validated['program_course_id'])
                ->firstOrFail();

            $validated['program_id'] = $programCourse->program_id;
        }

        $course->contributions()->create([
            'user_id' => $request->user()->id,
            'contribution_type' => CareerContributionType::COURSE_UPDATE_PROPOSAL->value,
            'title' => 'Đề xuất cập nhật môn '.$course->code,
            'content' => $validated['reason'],
            'status' => CareerContributionStatus::PENDING_REVIEW->value,
            'visibility' => CareerContributionVisibility::PRIVATE->value,
            'metadata_json' => [
                'proposal_kind' => 'official_course_update',
                'program_course_id' => $validated['program_course_id'] ?? null,
                'program_id' => $validated['program_id'] ?? null,
                'proposed' => [
                    'name' => $validated['name'] ?? null,
                    'credits' => $validated['credits'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'knowledge_block' => $validated['knowledge_block'] ?? null,
                    'is_mandatory' => array_key_exists('is_mandatory', $validated) ? (bool) $validated['is_mandatory'] : null,
                ],
            ],
        ]);

        return back()->with('status', 'Đề xuất cập nhật môn học đã được lưu riêng tư và gửi vào hàng chờ quản trị.');
    }
}
