<?php

namespace App\Http\Controllers;

use App\Enums\CareerContributionStatus;
use App\Enums\CareerContributionType;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\CareerContribution;
use App\Models\CareerCourse;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CareerPathwayContributionController extends Controller
{
    /**
     * Get contributions for a specific course.
     */
    public function index(Request $request, CareerCourse $course)
    {
        $contributions = $course->contributions()
            ->with(['user', 'verifier'])
            ->whereIn('status', [
                CareerContributionStatus::PUBLISHED->value,
                CareerContributionStatus::APPROVED->value,
                CareerContributionStatus::VERIFIED->value,
            ])
            ->where('visibility', '!=', 'hidden') // Just extra safety
            ->orderByDesc('upvotes_count')
            ->orderByDesc('created_at')
            ->paginate(15);

        return JsonResource::collection($contributions);
    }

    /**
     * Store a new contribution.
     */
    public function store(Request $request, CareerCourse $course)
    {
        // Enforce basic verified/active user rules here (assumed handled by middleware in route, but let's double check)
        $validated = $request->validate([
            'contribution_type' => ['required', Rule::enum(CareerContributionType::class)],
            'title' => 'required_unless:contribution_type,experience,difficulty_note|string|max:255|nullable',
            'content' => 'required|string|max:5000',
            'metadata_json' => 'nullable|array',
        ]);

        // Auto-sanitize HTML/markdown logic would exist here or on frontend.

        $contribution = $course->contributions()->create([
            'user_id' => $request->user()->id,
            'contribution_type' => $validated['contribution_type'],
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'status' => CareerContributionStatus::PUBLISHED->value, // Defaults to published for Phase 5 community content
            'metadata_json' => $validated['metadata_json'] ?? null,
        ]);

        return new JsonResource($contribution->load('user'));
    }

    /**
     * Show a single contribution.
     */
    public function show(CareerContribution $contribution)
    {
        // Author can see their own drafts. Others cannot.
        if (! in_array($contribution->status, [CareerContributionStatus::PUBLISHED, CareerContributionStatus::APPROVED, CareerContributionStatus::VERIFIED])) {
            if (Auth::id() !== $contribution->user_id) {
                abort(404);
            }
        }

        return new JsonResource($contribution->load(['user', 'verifier']));
    }

    /**
     * Update a contribution.
     */
    public function update(Request $request, CareerContribution $contribution)
    {
        if ($request->user()->id !== $contribution->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent editing if locked by moderation
        if (in_array($contribution->status, [CareerContributionStatus::HIDDEN_BY_MODERATION, CareerContributionStatus::REJECTED])) {
            abort(403, 'Cannot edit moderated content.');
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255|nullable',
            'content' => 'sometimes|required|string|max:5000',
            'status' => ['sometimes', Rule::in([CareerContributionStatus::DRAFT->value, CareerContributionStatus::PUBLISHED->value])],
            'metadata_json' => 'nullable|array',
        ]);

        $contribution->update($validated);

        return new JsonResource($contribution->fresh('user'));
    }

    /**
     * Delete a contribution.
     */
    public function destroy(Request $request, CareerContribution $contribution)
    {
        if ($request->user()->id !== $contribution->user_id) {
            abort(403, 'Unauthorized action.');
        }

        if (in_array($contribution->status, [CareerContributionStatus::HIDDEN_BY_MODERATION, CareerContributionStatus::REJECTED])) {
            abort(403, 'Cannot delete moderated content.');
        }

        $contribution->delete();

        return response()->json(['message' => 'Deleted']);
    }

    /**
     * Vote on a contribution.
     */
    public function vote(Request $request, CareerContribution $contribution)
    {
        $validated = $request->validate([
            'value' => 'required|in:1,-1',
        ]);

        if ($request->user()->id === $contribution->user_id) {
            abort(403, 'Cannot vote on your own contribution.');
        }

        $userId = $request->user()->id;

        $vote = $contribution->votes()->where('user_id', $userId)->first();

        if ($vote) {
            // Update existing vote
            if ($vote->value != $validated['value']) {
                $vote->update(['value' => $validated['value']]);

                if ($validated['value'] == 1) {
                    $contribution->decrement('downvotes_count');
                    $contribution->increment('upvotes_count');
                } else {
                    $contribution->decrement('upvotes_count');
                    $contribution->increment('downvotes_count');
                }
            }
        } else {
            // Create new vote
            $contribution->votes()->create([
                'user_id' => $userId,
                'value' => $validated['value'],
            ]);

            if ($validated['value'] == 1) {
                $contribution->increment('upvotes_count');
            } else {
                $contribution->increment('downvotes_count');
            }
        }

        return response()->json([
            'upvotes_count' => $contribution->upvotes_count,
            'downvotes_count' => $contribution->downvotes_count,
        ]);
    }

    /**
     * Remove vote.
     */
    public function unvote(Request $request, CareerContribution $contribution)
    {
        $vote = $contribution->votes()->where('user_id', $request->user()->id)->first();

        if ($vote) {
            if ($vote->value == 1) {
                $contribution->decrement('upvotes_count');
            } else {
                $contribution->decrement('downvotes_count');
            }
            $vote->delete();
        }

        return response()->json([
            'upvotes_count' => $contribution->upvotes_count,
            'downvotes_count' => $contribution->downvotes_count,
        ]);
    }

    /**
     * Report a contribution.
     */
    public function report(Request $request, CareerContribution $contribution)
    {
        $validated = $request->validate([
            'reason' => ['required', Rule::enum(ReportReason::class)],
            'description' => 'nullable|string|max:1000',
        ]);

        // Prevent duplicate reports from same user on same target
        $existing = Report::where('reporter_id', $request->user()->id)
            ->where('target_type', CareerContribution::class)
            ->where('target_id', $contribution->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Already reported'], 400);
        }

        Report::create([
            'reporter_id' => $request->user()->id,
            'target_type' => CareerContribution::class,
            'target_id' => $contribution->id,
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status' => ReportStatus::PENDING->value,
        ]);

        $contribution->increment('reports_count');

        // Optional: auto-hide if threshold reached (let's say 5 for Phase 5 MVP)
        if ($contribution->reports_count >= 5) {
            $contribution->update(['status' => CareerContributionStatus::HIDDEN_BY_MODERATION->value]);
        }

        return response()->json(['message' => 'Reported successfully']);
    }
}
