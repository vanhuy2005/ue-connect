<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CareerPositionStatus;
use App\Http\Controllers\Controller;
use App\Models\CareerPosition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class CareerPathwayPositionAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = CareerPosition::with(['creator']);

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $positions = $query->orderByDesc('created_at')->paginate(20);

        return JsonResource::collection($positions);
    }

    public function moderate(Request $request, CareerPosition $position)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(CareerPositionStatus::class)],
            'reason' => 'nullable|string|max:1000',
        ]);

        $position->update(['status' => $validated['status']]);

        // Option to add moderation logs here if desired

        return new JsonResource($position);
    }

    public function feature(Request $request, CareerPosition $position)
    {
        // Simple toggle for featuring logic (if metadata_json handles it)
        $meta = $position->metadata_json ?? [];
        $meta['is_featured'] = ! ($meta['is_featured'] ?? false);
        $position->update(['metadata_json' => $meta]);

        return new JsonResource($position);
    }
}
