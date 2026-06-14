<?php

namespace App\Http\Resources\CareerPathway;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorktreeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Note: The resource expects an array returned from CareerPathwayWorktreeService.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'name' => $this['name'],
            'code' => $this['code'],
            'cohort_id' => $this['cohort_id'],
            'faculty_id' => $this['faculty_id'],
            'major_id' => $this['major_id'],
            'status' => $this['status']->value ?? $this['status'],
            'total_credits' => $this['total_credits'],
            'source_document' => $this['source_document'],
            'quality_warnings' => $this['quality_warnings'],
            'semesters' => $this['semesters'],
        ];
    }
}
