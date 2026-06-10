<?php

namespace App\AI\HcmueChatbot\Repositories;

use App\Models\Faculty;
use Illuminate\Support\Collection;

class FacultyRepository
{
    /**
     * Find faculty by ID.
     */
    public function find(int $id): ?Faculty
    {
        return Faculty::find($id);
    }

    /**
     * Find faculty by code.
     */
    public function findByCode(string $code): ?Faculty
    {
        return Faculty::where('code', $code)->first();
    }

    /**
     * Find faculty by slug or name (fuzzy match).
     */
    public function findByNameOrSlug(string $search): ?Faculty
    {
        return Faculty::where('slug', $search)
            ->orWhere('name', 'like', "%{$search}%")
            ->orWhere('normalized_name', 'like', "%{$search}%")
            ->first();
    }

    /**
     * Get all active faculties.
     */
    public function getAllActive(): Collection
    {
        return Faculty::where('status', 'active')->get();
    }
}
