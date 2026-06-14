<?php

namespace App\Http\Controllers;

use App\Enums\CareerUserPathwayItemType;
use App\Models\CareerUserPathway;
use App\Models\CareerUserPathwayItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class CareerUserPathwayItemController extends Controller
{
    public function store(Request $request, CareerUserPathway $pathway)
    {
        if ($request->user()->id !== $pathway->user_id) {
            abort(403);
        }

        $validated = $request->validate([
            'item_type' => ['required', Rule::enum(CareerUserPathwayItemType::class)],
            'target_type' => 'nullable|string',
            'target_id' => 'nullable|integer',
            'semester_number' => 'nullable|integer|min:1',
            'title' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2000',
            'order_index' => 'integer|min:0',
        ]);

        $item = $pathway->items()->create($validated);

        return new JsonResource($item);
    }

    public function update(Request $request, CareerUserPathway $pathway, CareerUserPathwayItem $item)
    {
        if ($request->user()->id !== $pathway->user_id || $item->pathway_id !== $pathway->id) {
            abort(403);
        }

        $validated = $request->validate([
            'semester_number' => 'sometimes|nullable|integer|min:1',
            'title' => 'sometimes|nullable|string|max:255',
            'note' => 'sometimes|nullable|string|max:2000',
            'order_index' => 'sometimes|integer|min:0',
        ]);

        $item->update($validated);

        return new JsonResource($item);
    }

    public function destroy(Request $request, CareerUserPathway $pathway, CareerUserPathwayItem $item)
    {
        if ($request->user()->id !== $pathway->user_id || $item->pathway_id !== $pathway->id) {
            abort(403);
        }

        $item->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
