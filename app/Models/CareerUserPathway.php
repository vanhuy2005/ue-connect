<?php

namespace App\Models;

use App\Enums\CareerUserPathwayStatus;
use App\Enums\CareerUserPathwayVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerUserPathway extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'program_id',
        'career_position_id',
        'story',
        'status',
        'visibility',
        'published_at',
        'saves_count',
        'comments_count',
        'reports_count',
        'metadata_json',
    ];

    protected $casts = [
        'status' => CareerUserPathwayStatus::class,
        'visibility' => CareerUserPathwayVisibility::class,
        'published_at' => 'datetime',
        'metadata_json' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(CareerProgram::class, 'program_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(CareerPosition::class, 'career_position_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CareerUserPathwayItem::class, 'pathway_id')->orderBy('semester_number')->orderBy('order_index');
    }

    public function saves(): HasMany
    {
        return $this->hasMany(CareerUserPathwaySave::class, 'pathway_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CareerUserPathwayComment::class, 'pathway_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(CareerUserPathwayReport::class, 'pathway_id');
    }
}
