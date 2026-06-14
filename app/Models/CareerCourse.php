<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerCourse extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function programCourses(): HasMany
    {
        return $this->hasMany(CareerProgramCourse::class, 'course_id');
    }

    public function courseDescriptions(): HasMany
    {
        return $this->hasMany(CareerCourseDescription::class, 'course_id');
    }

    public function skillEdges(): HasMany
    {
        return $this->hasMany(CareerCourseSkillEdge::class, 'career_course_id');
    }

    public function contributions()
    {
        return $this->morphMany(CareerContribution::class, 'target');
    }
}
