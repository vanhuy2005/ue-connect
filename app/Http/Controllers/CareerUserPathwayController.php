<?php

namespace App\Http\Controllers;

use App\Enums\CareerUserPathwayStatus;
use App\Enums\CareerUserPathwayVisibility;
use App\Models\CareerUserPathway;
use App\Models\CareerUserPathwayReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CareerUserPathwayController extends Controller
{
    public function index(Request $request)
    {
        $query = CareerUserPathway::with(['user', 'program', 'position'])
            ->where('status', CareerUserPathwayStatus::PUBLISHED->value)
            ->where('visibility', CareerUserPathwayVisibility::PUBLIC->value);

        if ($request->has('q')) {
            $query->where('title', 'like', '%'.$request->query('q').'%');
        }

        if ($request->has('program_id')) {
            $query->where('program_id', $request->query('program_id'));
        }

        $pathways = $query->orderByDesc('saves_count')
            ->orderByDesc('published_at')
            ->paginate(15);

        return JsonResource::collection($pathways);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'story' => 'nullable|string',
            'program_id' => 'nullable|exists:career_programs,id',
            'career_position_id' => 'nullable|exists:career_positions,id',
        ]);

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 1;
        while (CareerUserPathway::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $pathway = CareerUserPathway::create(array_merge($validated, [
            'user_id' => $request->user()->id,
            'slug' => $slug,
            'status' => CareerUserPathwayStatus::DRAFT->value,
            'visibility' => CareerUserPathwayVisibility::PRIVATE->value,
        ]));

        return new JsonResource($pathway);
    }

    public function show(CareerUserPathway $pathway, Request $request)
    {
        // Check visibility/status
        if ($pathway->status !== CareerUserPathwayStatus::PUBLISHED->value) {
            if (! $request->user() || ($request->user()->id !== $pathway->user_id && ! $request->user()->is_admin)) {
                abort(404);
            }
        } elseif ($pathway->visibility === CareerUserPathwayVisibility::PRIVATE->value) {
            if (! $request->user() || $request->user()->id !== $pathway->user_id) {
                abort(404);
            }
        }

        $pathway->load(['user', 'program', 'position', 'items.target']);

        return new JsonResource($pathway);
    }

    public function update(Request $request, CareerUserPathway $pathway)
    {
        if ($request->user()->id !== $pathway->user_id && ! $request->user()->is_admin) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'story' => 'sometimes|nullable|string',
            'program_id' => 'sometimes|nullable|exists:career_programs,id',
            'career_position_id' => 'sometimes|nullable|exists:career_positions,id',
            'visibility' => ['sometimes', Rule::enum(CareerUserPathwayVisibility::class)],
        ]);

        if (isset($validated['title']) && $validated['title'] !== $pathway->title) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $counter = 1;
            while (CareerUserPathway::where('slug', $slug)->where('id', '!=', $pathway->id)->exists()) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $pathway->update($validated);

        return new JsonResource($pathway);
    }

    public function destroy(Request $request, CareerUserPathway $pathway)
    {
        if ($request->user()->id !== $pathway->user_id && ! $request->user()->is_admin) {
            abort(403);
        }

        $pathway->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function publish(Request $request, CareerUserPathway $pathway)
    {
        if ($request->user()->id !== $pathway->user_id) {
            abort(403);
        }

        if (empty($pathway->title) || empty($pathway->story)) {
            return response()->json(['message' => 'Pathway must have a title and a story.'], 400);
        }

        if ($pathway->items()->count() === 0) {
            return response()->json(['message' => 'Pathway must have at least one timeline item.'], 400);
        }

        $pathway->update([
            'status' => CareerUserPathwayStatus::PUBLISHED->value,
            'visibility' => CareerUserPathwayVisibility::PUBLIC->value,
            'published_at' => $pathway->published_at ?? now(),
        ]);

        return new JsonResource($pathway);
    }

    public function save(Request $request, CareerUserPathway $pathway)
    {
        $userId = $request->user()->id;
        $save = $pathway->saves()->where('user_id', $userId)->first();

        if (! $save) {
            $pathway->saves()->create(['user_id' => $userId]);
            $pathway->increment('saves_count');
        }

        return response()->json(['saves_count' => $pathway->saves_count]);
    }

    public function unsave(Request $request, CareerUserPathway $pathway)
    {
        $userId = $request->user()->id;
        $save = $pathway->saves()->where('user_id', $userId)->first();

        if ($save) {
            $save->delete();
            $pathway->decrement('saves_count');
        }

        return response()->json(['saves_count' => $pathway->saves_count]);
    }

    public function report(Request $request, CareerUserPathway $pathway)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'description' => 'nullable|string|max:1000',
        ]);

        $existing = CareerUserPathwayReport::where('reporter_id', $request->user()->id)
            ->where('pathway_id', $pathway->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Already reported'], 400);
        }

        CareerUserPathwayReport::create([
            'reporter_id' => $request->user()->id,
            'pathway_id' => $pathway->id,
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
        ]);

        $pathway->increment('reports_count');

        if ($pathway->reports_count >= 5) {
            $pathway->update(['status' => CareerUserPathwayStatus::HIDDEN_BY_MODERATION->value]);
        }

        return response()->json(['message' => 'Reported successfully']);
    }
}
