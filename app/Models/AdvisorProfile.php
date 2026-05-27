<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvisorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'faculty_id',
        'department',
        'title',
        'office_location',
        'advising_areas',
    ];

    /**
     * Get the parent profile.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the faculty for this advisor.
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }
}
