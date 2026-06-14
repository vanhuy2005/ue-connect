<?php

namespace App\Http\Controllers;

use App\Enums\CareerPositionStatus;
use App\Enums\CareerPositionVisibility;
use App\Enums\ReportStatus;
use App\Models\CareerPosition;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CareerPathwayPositionController extends Controller
{
    public function index(Request $request)
    {
        $query = CareerPosition::with(['creator', 'faculty', 'major', 'program'])
            ->where('status', CareerPositionStatus::PUBLISHED->value)
            ->where('visibility', CareerPositionVisibility::PUBLIC->value);

        if ($request->has('q')) {
            $query->where('title', 'like', '%'.$request->query('q').'%');
        }

        $positions = $query->orderByDesc('upvotes_count')
            ->orderByDesc('published_at')
            ->paginate(15);

        return JsonResource::collection($positions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'industry' => 'nullable|string|max:255',
            'target_audience' => 'nullable|string|max:255',
            'related_faculty_id' => 'nullable|exists:career_faculties,id',
            'related_major_id' => 'nullable|exists:career_majors,id',
            'related_program_id' => 'nullable|exists:career_programs,id',
        ]);

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 1;
        while (CareerPosition::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $position = CareerPosition::create(array_merge($validated, [
            'created_by' => $request->user()->id,
            'slug' => $slug,
            'status' => CareerPositionStatus::DRAFT->value,
            'visibility' => CareerPositionVisibility::PUBLIC->value, // Can be changed later
        ]));

        return new JsonResource($position);
    }

    public function show(CareerPosition $position, Request $request)
    {
        // Check visibility/status
        if ($position->status !== CareerPositionStatus::PUBLISHED->value) {
            if (! $request->user() || ($request->user()->id !== $position->created_by && ! $request->user()->is_admin)) {
                abort(404);
            }
        } elseif ($position->visibility === CareerPositionVisibility::PRIVATE->value) {
            if (! $request->user() || $request->user()->id !== $position->created_by) {
                abort(404);
            }
        }

        $position->load(['creator', 'faculty', 'major', 'program', 'sections.items.target']);

        return new JsonResource($position);
    }

    public function update(Request $request, CareerPosition $position)
    {
        if ($request->user()->id !== $position->created_by && ! $request->user()->is_admin) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string|max:2000',
            'industry' => 'sometimes|nullable|string|max:255',
            'target_audience' => 'sometimes|nullable|string|max:255',
            'related_faculty_id' => 'sometimes|nullable|exists:career_faculties,id',
            'related_major_id' => 'sometimes|nullable|exists:career_majors,id',
            'related_program_id' => 'sometimes|nullable|exists:career_programs,id',
            'visibility' => ['sometimes', Rule::enum(CareerPositionVisibility::class)],
        ]);

        if (isset($validated['title']) && $validated['title'] !== $position->title) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $counter = 1;
            while (CareerPosition::where('slug', $slug)->where('id', '!=', $position->id)->exists()) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $position->update($validated);

        return new JsonResource($position);
    }

    public function destroy(Request $request, CareerPosition $position)
    {
        if ($request->user()->id !== $position->created_by && ! $request->user()->is_admin) {
            abort(403);
        }

        $position->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function publish(Request $request, CareerPosition $position)
    {
        if ($request->user()->id !== $position->created_by) {
            abort(403);
        }

        // Validate completeness
        if (empty($position->title) || empty($position->description)) {
            return response()->json(['message' => 'Position must have a title and description.'], 400);
        }

        if ($position->sections()->count() === 0) {
            return response()->json(['message' => 'Position must have at least one section.'], 400);
        }

        // Set to published immediately (simpler rule per implementation plan)
        $position->update([
            'status' => CareerPositionStatus::PUBLISHED->value,
            'published_at' => $position->published_at ?? now(),
        ]);

        return new JsonResource($position);
    }

    public function save(Request $request, CareerPosition $position)
    {
        $userId = $request->user()->id;
        $save = $position->saves()->where('user_id', $userId)->first();

        if (! $save) {
            $position->saves()->create(['user_id' => $userId]);
            $position->increment('saves_count');
        }

        return response()->json(['saves_count' => $position->saves_count]);
    }

    public function unsave(Request $request, CareerPosition $position)
    {
        $userId = $request->user()->id;
        $save = $position->saves()->where('user_id', $userId)->first();

        if ($save) {
            $save->delete();
            $position->decrement('saves_count');
        }

        return response()->json(['saves_count' => $position->saves_count]);
    }

    public function report(Request $request, CareerPosition $position)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'description' => 'nullable|string|max:1000',
        ]);

        $existing = Report::where('reporter_id', $request->user()->id)
            ->where('target_type', CareerPosition::class)
            ->where('target_id', $position->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Already reported'], 400);
        }

        Report::create([
            'reporter_id' => $request->user()->id,
            'target_type' => CareerPosition::class,
            'target_id' => $position->id,
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status' => ReportStatus::PENDING->value,
        ]);

        $position->increment('reports_count');

        // Optional auto-hide threshold
        if ($position->reports_count >= 5) {
            $position->update(['status' => CareerPositionStatus::HIDDEN_BY_MODERATION->value]);
        }

        return response()->json(['message' => 'Reported successfully']);
    }
}
