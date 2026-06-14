<?php

namespace App\Models;

use App\Enums\CareerContributionSourceType;
use App\Enums\CareerContributionStatus;
use App\Enums\CareerContributionType;
use App\Enums\CareerContributionVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerContribution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'target_type',
        'target_id',
        'contribution_type',
        'title',
        'content',
        'status',
        'visibility',
        'source_type',
        'upvotes_count',
        'downvotes_count',
        'reports_count',
        'verified_at',
        'verified_by',
        'metadata_json',
    ];

    protected $casts = [
        'contribution_type' => CareerContributionType::class,
        'status' => CareerContributionStatus::class,
        'visibility' => CareerContributionVisibility::class,
        'source_type' => CareerContributionSourceType::class,
        'verified_at' => 'datetime',
        'metadata_json' => 'array',
    ];

    /**
     * The target of the contribution (e.g., CareerCourse, CareerProgram)
     */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(CareerContributionVote::class);
    }

    public function moderationLogs(): HasMany
    {
        return $this->hasMany(CareerContributionModerationLog::class);
    }

    public function courseSkillEdges(): HasMany
    {
        return $this->hasMany(CareerCourseSkillEdge::class);
    }
}
