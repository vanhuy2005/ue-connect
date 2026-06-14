<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CareerContributionStatus;
use App\Enums\CareerContributionType;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\CareerContribution;
use App\Models\CareerCourse;
use App\Models\CareerCourseDescription;
use App\Models\CareerProgramCourse;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class CareerPathwayContributionAdminController extends Controller
{
    /**
     * Get list of all contributions for moderation.
     */
    public function index(Request $request)
    {
        $query = CareerContribution::with(['user', 'target']);

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('type')) {
            $query->where('contribution_type', $request->query('type'));
        }

        $contributions = $query->orderByDesc('created_at')->paginate(20);

        return JsonResource::collection($contributions);
    }

    /**
     * Moderate a contribution (approve, reject, hide).
     */
    public function moderate(Request $request, CareerContribution $contribution)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                CareerContributionStatus::APPROVED->value,
                CareerContributionStatus::REJECTED->value,
                CareerContributionStatus::HIDDEN_BY_MODERATION->value,
                CareerContributionStatus::PUBLISHED->value,
            ])],
            'reason' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $contribution->status;
        $newStatus = $validated['status'];

        $contribution->update(['status' => $newStatus]);

        if (
            $newStatus === CareerContributionStatus::APPROVED->value
            && $contribution->contribution_type === CareerContributionType::COURSE_UPDATE_PROPOSAL
        ) {
            $this->applyCourseUpdateProposal($contribution);
        }

        // Log moderation action
        $contribution->moderationLogs()->create([
            'admin_id' => $request->user()->id,
            'action' => 'moderate',
            'reason' => $validated['reason'] ?? null,
            'previous_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        return new JsonResource($contribution);
    }

    private function applyCourseUpdateProposal(CareerContribution $contribution): void
    {
        if ($contribution->target_type !== CareerCourse::class) {
            return;
        }

        $course = CareerCourse::find($contribution->target_id);
        $metadata = $contribution->metadata_json ?? [];
        $proposed = $metadata['proposed'] ?? [];

        if (! $course || ! is_array($proposed)) {
            return;
        }

        $coursePayload = array_filter([
            'name' => $proposed['name'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        if ($coursePayload !== []) {
            $course->update($coursePayload);
        }

        if (! empty($proposed['description'])) {
            CareerCourseDescription::updateOrCreate(
                ['course_id' => $course->id],
                [
                    'description_text' => $proposed['description'],
                ]
            );
        }

        if (! empty($metadata['program_course_id'])) {
            $programCourse = CareerProgramCourse::where('course_id', $course->id)
                ->whereKey($metadata['program_course_id'])
                ->first();

            if ($programCourse) {
                $programCoursePayload = [];

                if (array_key_exists('credits', $proposed) && $proposed['credits'] !== null && $proposed['credits'] !== '') {
                    $programCoursePayload['credits'] = $proposed['credits'];
                }

                if (array_key_exists('knowledge_block', $proposed) && $proposed['knowledge_block'] !== null && $proposed['knowledge_block'] !== '') {
                    $programCoursePayload['knowledge_block'] = $proposed['knowledge_block'];
                }

                if (array_key_exists('is_mandatory', $proposed) && $proposed['is_mandatory'] !== null) {
                    $programCoursePayload['is_mandatory'] = (bool) $proposed['is_mandatory'];
                }

                if ($programCoursePayload !== []) {
                    $programCourse->update($programCoursePayload);
                    $programCourse->program?->invalidateWorktreeCache();
                }
            }
        }
    }

    /**
     * Verify a contribution (mark as highly trusted).
     */
    public function verify(Request $request, CareerContribution $contribution)
    {
        $oldStatus = $contribution->status;
        $newStatus = CareerContributionStatus::VERIFIED->value;

        $contribution->update([
            'status' => $newStatus,
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ]);

        $contribution->moderationLogs()->create([
            'admin_id' => $request->user()->id,
            'action' => 'verify',
            'reason' => 'Verified by admin',
            'previous_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        return new JsonResource($contribution);
    }

    /**
     * Get contribution reports.
     */
    public function reports(Request $request)
    {
        $reports = Report::with(['reporter', 'target'])
            ->where('target_type', CareerContribution::class)
            ->when($request->query('status'), function ($q, $status) {
                $q->where('status', $status);
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return JsonResource::collection($reports);
    }

    /**
     * Resolve a report.
     */
    public function resolveReport(Request $request, Report $report)
    {
        if ($report->target_type !== CareerContribution::class) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::enum(ReportStatus::class)],
        ]);

        $report->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Report resolved', 'report' => $report]);
    }
}
