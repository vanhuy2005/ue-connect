<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurriculumCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'group_id',
        'semester',
        'course_code',
        'course_name',
        'normalized_course_name',
        'credits',
        'theory_hours',
        'practice_hours',
        'self_study_hours',
        'course_type',
        'is_required',
        'prerequisite',
        'note',
        'source_location',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    /**
     * Get the training program.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'program_id');
    }

    /**
     * Get the course group that contains this course.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(CurriculumCourseGroup::class, 'group_id');
    }
}
