<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CareerUserPathwayStatus;
use App\Http\Controllers\Controller;
use App\Models\CareerUserPathway;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class CareerUserPathwayAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = CareerUserPathway::with(['user', 'program', 'position']);

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $pathways = $query->orderByDesc('created_at')->paginate(20);

        return JsonResource::collection($pathways);
    }

    public function moderate(Request $request, CareerUserPathway $pathway)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(CareerUserPathwayStatus::class)],
            'reason' => 'nullable|string|max:1000',
        ]);

        $pathway->update(['status' => $validated['status']]);

        return new JsonResource($pathway);
    }
}
