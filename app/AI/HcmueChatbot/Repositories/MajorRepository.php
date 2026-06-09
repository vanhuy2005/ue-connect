<?php

namespace App\AI\HcmueChatbot\Repositories;

use App\Models\Major;
use Illuminate\Support\Collection;

class MajorRepository
{
    /**
     * Find major by ID.
     */
    public function find(int $id): ?Major
    {
        return Major::find($id);
    }

    /**
     * Find major by code.
     */
    public function findByCode(string $code): ?Major
    {
        return Major::where('code', $code)->first();
    }

    /**
     * Find major by name (fuzzy match).
     */
    public function findByName(string $name): ?Major
    {
        return Major::where('name', 'like', "%{$name}%")
            ->orWhere('normalized_name', 'like', "%{$name}%")
            ->first();
    }

    /**
     * Get majors belonging to a faculty.
     */
    public function getByFaculty(int $facultyId): Collection
    {
        return Major::where('faculty_id', $facultyId)->get();
    }
}
