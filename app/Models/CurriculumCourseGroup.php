<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CurriculumCourseGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'name',
        'group_type',
        'min_credits_required',
        'note',
    ];

    /**
     * Get the training program.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'program_id');
    }

    /**
     * Get the courses in this group.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(CurriculumCourse::class, 'group_id');
    }
}
