<?php

namespace App\Models;

use App\Enums\ProgramStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class CareerProgram extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => ProgramStatus::class,
    ];

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(CareerCohort::class, 'cohort_id');
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(CareerFaculty::class, 'faculty_id');
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(CareerMajor::class, 'major_id');
    }

    public function sourceDocument(): BelongsTo
    {
        return $this->belongsTo(CareerSourceDocument::class, 'source_document_id');
    }

    public function semesters(): HasMany
    {
        return $this->hasMany(CareerSemester::class, 'program_id');
    }

    public function programCourses(): HasMany
    {
        return $this->hasMany(CareerProgramCourse::class, 'program_id');
    }

    public function dataQualityIssues(): HasMany
    {
        return $this->hasMany(CareerDataQualityIssue::class, 'program_id');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(CareerCourse::class, 'career_program_courses', 'program_id', 'course_id')
            ->withPivot(['id', 'semester_id', 'credits', 'is_mandatory', 'knowledge_block'])
            ->withTimestamps();
    }

    /**
     * Scope a query to only include public-ready programs.
     */
    public function scopePublicReady($query)
    {
        return $query->whereIn('status', [
            ProgramStatus::READY->value,
            ProgramStatus::READY_WITH_MISSING_DESCRIPTIONS->value,
        ]);
    }

    /**
     * Invalidate the worktree cache for this program.
     */
    public function invalidateWorktreeCache(): void
    {
        if (config('cache.default') === 'redis' || config('cache.default') === 'memcached') {
            Cache::tags(['career_program:'.$this->id])->flush();
        } else {
            Cache::forget('career_program_worktree_'.$this->id);
        }
    }
}
