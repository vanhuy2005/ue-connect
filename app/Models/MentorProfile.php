<?php

namespace App\Models;

use App\Enums\MentorAvailabilityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MentorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'headline',
        'bio',
        'expertise_topics',
        'career_paths',
        'skills',
        'help_topics',
        'preferred_request_types',
        'availability_status',
        'mentor_visibility',
        'max_pending_requests',
        'max_monthly_accepts',
        'response_expectation_text',
        'office_hours_text',
        'is_active',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'approved_by' => 'integer',
            'expertise_topics' => 'array',
            'career_paths' => 'array',
            'skills' => 'array',
            'help_topics' => 'array',
            'preferred_request_types' => 'array',
            'availability_status' => MentorAvailabilityStatus::class,
            'mentor_visibility' => 'boolean',
            'is_active' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return HasMany<MentorRequest, $this> */
    public function mentorRequests(): HasMany
    {
        return $this->hasMany(MentorRequest::class);
    }

    /** @return HasMany<MentorFeedback, $this> */
    public function mentorFeedback(): HasMany
    {
        return $this->hasMany(MentorFeedback::class, 'mentor_id', 'user_id');
    }

    /**
     * Scope: only active, visible, available mentors.
     */
    public function scopeDiscoverable($query)
    {
        return $query->where('is_active', true)
            ->where('mentor_visibility', true)
            ->where('availability_status', MentorAvailabilityStatus::Available);
    }

    /**
     * Check if this mentor profile can receive new requests.
     */
    public function isAvailableForRequests(): bool
    {
        if (! $this->is_active || ! $this->mentor_visibility) {
            return false;
        }

        if ($this->availability_status !== MentorAvailabilityStatus::Available) {
            return false;
        }

        $pendingCount = $this->mentorRequests()
            ->whereIn('status', ['submitted', 'accepted', 'need_more_info'])
            ->count();

        return $pendingCount < $this->max_pending_requests;
    }

    /**
     * Calculate profile completeness percentage (0–100).
     */
    public function getProfileCompletenessScore(): int
    {
        $fields = [
            'headline' => 20,
            'bio' => 20,
            'expertise_topics' => 25,
            'help_topics' => 20,
            'response_expectation_text' => 15,
        ];

        $score = 0;

        foreach ($fields as $field => $weight) {
            $value = $this->$field;
            if (! empty($value)) {
                $score += $weight;
            }
        }

        return $score;
    }
}
