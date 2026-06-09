<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'cohort_id',
        'faculty_id',
        'major_id',
        'title',
        'total_credits',
        'effective_from',
        'effective_to',
        'status',
        'source_url',
        'source_file_id',
        'source_hash',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Get the cohort that owns this training program.
     */
    public function cohort(): BelongsTo
    {
        return $this->belongsTo(AdmissionCohort::class, 'cohort_id');
    }

    /**
     * Get the faculty that owns this training program.
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Get the major that owns this training program.
     */
    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    /**
     * Get the course groups in this program.
     */
    public function courseGroups(): HasMany
    {
        return $this->hasMany(CurriculumCourseGroup::class, 'program_id');
    }

    /**
     * Get all curriculum courses in this program.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(CurriculumCourse::class, 'program_id');
    }

    /**
     * Get the learning outcomes for this program.
     */
    public function learningOutcomes(): HasMany
    {
        return $this->hasMany(ProgramLearningOutcome::class, 'program_id');
    }
}
