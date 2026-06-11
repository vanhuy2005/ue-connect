<?php

namespace App\Models;

use App\Enums\MentorRequestStatus;
use App\Enums\MentorUrgency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MentorRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'mentor_id',
        'mentor_profile_id',
        'topic',
        'goal',
        'question',
        'urgency',
        'context',
        'expected_outcome',
        'status',
        'mentor_response',
        'decline_reason',
        'more_info_question',
        'conversation_id',
        'accepted_at',
        'declined_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'student_id' => 'integer',
            'mentor_id' => 'integer',
            'mentor_profile_id' => 'integer',
            'conversation_id' => 'integer',
            'status' => MentorRequestStatus::class,
            'urgency' => MentorUrgency::class,
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /** @return BelongsTo<User, $this> */
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    /** @return BelongsTo<MentorProfile, $this> */
    public function mentorProfile(): BelongsTo
    {
        return $this->belongsTo(MentorProfile::class);
    }

    /** @return BelongsTo<Conversation, $this> */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /** @return HasOne<MentorFeedback, $this> */
    public function feedback(): HasOne
    {
        return $this->hasOne(MentorFeedback::class);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', MentorRequestStatus::Submitted);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', MentorRequestStatus::Accepted);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            MentorRequestStatus::Submitted->value,
            MentorRequestStatus::Accepted->value,
            MentorRequestStatus::NeedMoreInfo->value,
            MentorRequestStatus::UpdatedByStudent->value,
        ]);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            MentorRequestStatus::Submitted->value,
            MentorRequestStatus::NeedMoreInfo->value,
            MentorRequestStatus::UpdatedByStudent->value,
        ]);
    }

    public function isParticipant(User $user): bool
    {
        return $this->student_id === $user->id || $this->mentor_id === $user->id;
    }
}
