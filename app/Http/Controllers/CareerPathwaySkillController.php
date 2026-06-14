<?php

namespace App\Http\Controllers;

use App\Models\CareerCourse;
use App\Models\CareerSkill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CareerPathwaySkillController extends Controller
{
    /**
     * Get list of global active skills.
     */
    public function index(Request $request)
    {
        $skills = CareerSkill::where('is_active', true)
            ->when($request->query('q'), function ($query, $q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->when($request->query('category'), function ($query, $cat) {
                $query->where('category', $cat);
            })
            ->orderBy('name')
            ->paginate(20);

        return JsonResource::collection($skills);
    }

    /**
     * Get skills associated with a specific course.
     */
    public function courseSkills(CareerCourse $course)
    {
        // For Phase 5: we only expose active skill edges
        $edges = $course->skillEdges()
            ->with('skill')
            ->where('is_active', true)
            ->get();

        return JsonResource::collection($edges);
    }
}
