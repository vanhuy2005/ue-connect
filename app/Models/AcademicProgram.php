<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
        'name',
        'slug',
        'degree_level',
        'description',
        'status',
    ];

    /**
     * Get the faculty that owns this academic program.
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }
}
