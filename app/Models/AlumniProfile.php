<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlumniProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'faculty_id',
        'academic_program_id',
        'cohort',
        'graduation_year',
        'current_position',
        'current_organization',
        'industry',
        'career_summary',
        'willing_to_mentor',
    ];

    protected $casts = [
        'willing_to_mentor' => 'boolean',
    ];

    /**
     * Get the parent profile.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the faculty for this alumni.
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Get the academic program for this alumni.
     */
    public function academicProgram(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class);
    }
}
