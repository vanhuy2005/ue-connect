<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\MentorAccessStatus;
use App\Enums\MentorAvailabilityStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class MentorProfile extends Model
{
    use HasFactory, SoftDeletes;

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
        'is_public_ready',
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
            'is_public_ready' => 'boolean',
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
            ->where('is_public_ready', true)
            ->where('availability_status', MentorAvailabilityStatus::Available)
            ->whereHas('user', function ($query) {
                $query->where('account_status', AccountStatus::ACTIVE)
                    ->whereHas('mentorAccessRequests', function ($query) {
                        $query->where('status', MentorAccessStatus::Approved);
                    });
            });
    }

    /**
     * Automatically sync availability_status based on current active pending request count.
     *
     * Rules:
     * - If pending >= max AND status is Available or Paused → switch to Full.
     * - If pending < max AND status is Full or Paused → switch back to Available.
     * - Hidden is never auto-changed (mentor explicitly chose to hide).
     */
    public function syncAvailabilityFromPendingCount(): void
    {
        $activePending = $this->mentorRequests()
            ->whereIn('status', ['submitted', 'accepted', 'need_more_info', 'updated_by_student'])
            ->count();

        $isFull = $activePending >= $this->max_pending_requests;

        if ($isFull && in_array($this->availability_status, [MentorAvailabilityStatus::Available, MentorAvailabilityStatus::Paused])) {
            $this->update(['availability_status' => MentorAvailabilityStatus::Full]);
        } elseif (! $isFull && in_array($this->availability_status, [MentorAvailabilityStatus::Full, MentorAvailabilityStatus::Paused])) {
            $this->update(['availability_status' => MentorAvailabilityStatus::Available]);
        }
    }

    /**
     * Check if this mentor profile can receive new requests.
     */
    public function isAvailableForRequests(): bool
    {
        if (! $this->is_active || ! $this->mentor_visibility) {
            return false;
        }

        if (! $this->user || ! $this->user->isActive() || ! $this->user->hasApprovedMentorAccess()) {
            return false;
        }

        if ($this->availability_status !== MentorAvailabilityStatus::Available) {
            return false;
        }

        $pendingCount = $this->mentorRequests()
            ->whereIn('status', ['submitted', 'need_more_info', 'accepted', 'updated_by_student'])
            ->count();

        return $pendingCount < $this->max_pending_requests;
    }

    /**
     * Check if this mentor profile meets the minimum completeness/trust criteria to be public.
     */
    public function checkIfPublicReady(): bool
    {
        $hasAvatar = $this->user && $this->user->profile && $this->user->profile->avatar()->exists();

        return $hasAvatar
            && ! empty($this->headline)
            && ! empty($this->bio)
            && is_array($this->expertise_topics) && count($this->expertise_topics) >= 2
            && is_array($this->preferred_request_types) && count($this->preferred_request_types) >= 1
            && ! empty($this->response_expectation_text);
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

    public function scopeFilterByExpertise(Builder $query, ?string $topic): Builder
    {
        if (blank($topic)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($topic) {
            $q->whereJsonContains('expertise_topics', $topic)
                ->orWhereJsonContains('help_topics', $topic);
        });
    }

    public function scopeSearchFulltext(Builder $query, string $term): Builder
    {
        $like = '%'.$term.'%';

        return $query->where(function (Builder $q) use ($term, $like) {
            $q->where('headline', 'like', $like)
                ->orWhere('bio', 'like', $like)
                ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', $like))
                ->orWhereJsonContains('expertise_topics', $term)
                ->orWhereJsonContains('help_topics', $term);
        });
    }

    public static function getAllTopics(): array
    {
        return Cache::remember('mentor:all-topics', 3600, function () {
            return self::discoverable()
                ->get()
                ->flatMap(fn (self $p) => array_merge(
                    $p->expertise_topics ?? [],
                    $p->help_topics ?? [],
                ))
                ->unique()
                ->sort()
                ->values()
                ->toArray();
        });
    }

    public static function flushTopicsCache(): void
    {
        Cache::forget('mentor:all-topics');
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushTopicsCache());
        static::deleted(fn () => static::flushTopicsCache());
    }
}
