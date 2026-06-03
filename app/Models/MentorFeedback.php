<?php

namespace App\Models;

use App\Enums\MentorFeedbackLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MentorFeedback extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mentor_feedback';

    protected $fillable = [
        'mentor_request_id',
        'student_id',
        'mentor_id',
        'helpfulness_level',
        'feedback_text',
        'is_private',
    ];

    protected function casts(): array
    {
        return [
            'mentor_request_id' => 'integer',
            'student_id' => 'integer',
            'mentor_id' => 'integer',
            'helpfulness_level' => MentorFeedbackLevel::class,
            'is_private' => 'boolean',
        ];
    }

    /** @return BelongsTo<MentorRequest, $this> */
    public function mentorRequest(): BelongsTo
    {
        return $this->belongsTo(MentorRequest::class);
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
}
