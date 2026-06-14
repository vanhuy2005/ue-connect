<?php

namespace App\Http\Controllers;

use App\Enums\CareerPositionSectionType;
use App\Models\CareerPosition;
use App\Models\CareerPositionSection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class CareerPathwayPositionSectionController extends Controller
{
    public function store(Request $request, CareerPosition $position)
    {
        if ($request->user()->id !== $position->created_by) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'section_type' => ['required', Rule::enum(CareerPositionSectionType::class)],
            'description' => 'nullable|string|max:1000',
            'order_index' => 'integer|min:0',
        ]);

        $section = $position->sections()->create($validated);

        return new JsonResource($section);
    }

    public function update(Request $request, CareerPosition $position, CareerPositionSection $section)
    {
        if ($request->user()->id !== $position->created_by || $section->position_id !== $position->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'section_type' => ['sometimes', Rule::enum(CareerPositionSectionType::class)],
            'description' => 'sometimes|nullable|string|max:1000',
            'order_index' => 'sometimes|integer|min:0',
        ]);

        $section->update($validated);

        return new JsonResource($section);
    }

    public function destroy(Request $request, CareerPosition $position, CareerPositionSection $section)
    {
        if ($request->user()->id !== $position->created_by || $section->position_id !== $position->id) {
            abort(403);
        }

        $section->items()->delete();
        $section->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
