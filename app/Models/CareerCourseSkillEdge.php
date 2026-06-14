<?php

namespace App\Models;

use App\Enums\CareerContributionSourceType;
use App\Enums\CareerSkillRelevanceLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerCourseSkillEdge extends Model
{
    use HasFactory;

    protected $fillable = [
        'career_course_id',
        'career_skill_id',
        'career_contribution_id',
        'source_type',
        'relevance_level',
        'is_active',
        'created_by',
        'verified_by',
        'verified_at',
        'metadata_json',
    ];

    protected $casts = [
        'source_type' => CareerContributionSourceType::class,
        'relevance_level' => CareerSkillRelevanceLevel::class,
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
        'metadata_json' => 'array',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(CareerCourse::class, 'career_course_id');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(CareerSkill::class, 'career_skill_id');
    }

    public function contribution(): BelongsTo
    {
        return $this->belongsTo(CareerContribution::class, 'career_contribution_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
