<?php

namespace App\Models;

use App\Enums\CareerSkillCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerSkill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'normalized_name',
        'category',
        'description',
        'created_by',
        'is_active',
        'metadata_json',
    ];

    protected $casts = [
        'category' => CareerSkillCategory::class,
        'is_active' => 'boolean',
        'metadata_json' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function courseSkillEdges(): HasMany
    {
        return $this->hasMany(CareerCourseSkillEdge::class);
    }
}
