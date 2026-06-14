<?php

namespace App\Http\Controllers;

use App\Enums\CareerPositionImportanceLevel;
use App\Enums\CareerPositionItemType;
use App\Enums\CareerPositionSourceType;
use App\Models\CareerPosition;
use App\Models\CareerPositionItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class CareerPathwayPositionItemController extends Controller
{
    public function store(Request $request, CareerPosition $position)
    {
        if ($request->user()->id !== $position->created_by) {
            abort(403);
        }

        $validated = $request->validate([
            'section_id' => 'nullable|exists:career_position_sections,id',
            'item_type' => ['required', Rule::enum(CareerPositionItemType::class)],
            'target_type' => 'nullable|string',
            'target_id' => 'nullable|integer',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'importance_level' => ['required', Rule::enum(CareerPositionImportanceLevel::class)],
            'source_type' => ['required', Rule::enum(CareerPositionSourceType::class)],
            'order_index' => 'integer|min:0',
        ]);

        // Security check for section belonging to this position
        if (! empty($validated['section_id'])) {
            $section = $position->sections()->find($validated['section_id']);
            if (! $section) {
                return response()->json(['message' => 'Invalid section.'], 400);
            }
        }

        $item = $position->items()->create($validated);

        return new JsonResource($item);
    }

    public function update(Request $request, CareerPosition $position, CareerPositionItem $item)
    {
        if ($request->user()->id !== $position->created_by || $item->position_id !== $position->id) {
            abort(403);
        }

        $validated = $request->validate([
            'section_id' => 'sometimes|nullable|exists:career_position_sections,id',
            'title' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'importance_level' => ['sometimes', Rule::enum(CareerPositionImportanceLevel::class)],
            'order_index' => 'sometimes|integer|min:0',
        ]);

        if (isset($validated['section_id']) && $validated['section_id']) {
            $section = $position->sections()->find($validated['section_id']);
            if (! $section) {
                return response()->json(['message' => 'Invalid section.'], 400);
            }
        }

        $item->update($validated);

        return new JsonResource($item);
    }

    public function destroy(Request $request, CareerPosition $position, CareerPositionItem $item)
    {
        if ($request->user()->id !== $position->created_by || $item->position_id !== $position->id) {
            abort(403);
        }

        $item->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
