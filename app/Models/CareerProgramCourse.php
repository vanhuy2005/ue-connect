<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerProgramCourse extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(CareerProgram::class, 'program_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(CareerSemester::class, 'semester_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(CareerCourse::class, 'course_id');
    }

    public function sourceDocument(): BelongsTo
    {
        return $this->belongsTo(CareerSourceDocument::class, 'source_document_id');
    }
}
