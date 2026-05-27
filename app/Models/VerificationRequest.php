<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VerificationRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'role_requested',
        'requested_identity_type',
        'status',
        'submitted_name',
        'submitted_student_code',
        'submitted_faculty_id',
        'submitted_academic_program_id',
        'submitted_cohort',
        'submitted_graduation_year',
        'submitted_email',
        'submitted_old_student_email',
        'submitted_note',
        'submitted_position',
        'submitted_organization',
        'assigned_admin_id',
        'submitted_at',
        'reviewed_at',
        'expires_at',
    ];

    protected $casts = [
        'status' => VerificationStatus::class,
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user who owns this request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin assigned to review this request.
     */
    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    /**
     * Get the submitted faculty.
     */
    public function submittedFaculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'submitted_faculty_id');
    }

    /**
     * Get the submitted academic program.
     */
    public function submittedAcademicProgram(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'submitted_academic_program_id');
    }

    /**
     * Get the evidence items for this verification request.
     */
    public function evidences(): HasMany
    {
        return $this->hasMany(VerificationEvidence::class);
    }

    /**
     * Get the review actions performed on this request.
     */
    public function reviewActions(): HasMany
    {
        return $this->hasMany(VerificationReviewAction::class);
    }
}
