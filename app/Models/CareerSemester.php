<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerSemester extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(CareerProgram::class, 'program_id');
    }

    public function programCourses(): HasMany
    {
        return $this->hasMany(CareerProgramCourse::class, 'semester_id');
    }
}
