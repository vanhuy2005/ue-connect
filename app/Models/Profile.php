<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'display_name',
        'avatar_media_file_id',
        'bio',
        'role_type',
        'profile_status',
        'visibility',
        'discoverable',
        'profile_completed_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'discoverable' => 'boolean',
        'profile_completed_at' => 'datetime',
    ];

    /**
     * Get the user that owns this profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the avatar media file for this profile.
     */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'avatar_media_file_id');
    }

    /**
     * Get the student-specific profile.
     */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    /**
     * Get the alumni-specific profile.
     */
    public function alumniProfile(): HasOne
    {
        return $this->hasOne(AlumniProfile::class);
    }

    /**
     * Get the advisor-specific profile.
     */
    public function advisorProfile(): HasOne
    {
        return $this->hasOne(AdvisorProfile::class);
    }

    /**
     * Get the faculty name dynamically.
     */
    public function getFacultyAttribute(): ?string
    {
        if ($this->role_type === 'student' && $this->studentProfile && $this->studentProfile->faculty) {
            return $this->studentProfile->faculty->name;
        }

        if ($this->role_type === 'alumni' && $this->alumniProfile && $this->alumniProfile->faculty) {
            return $this->alumniProfile->faculty->name;
        }

        if ($this->role_type === 'advisor' && $this->advisorProfile && $this->advisorProfile->faculty) {
            return $this->advisorProfile->faculty->name;
        }

        return null;
    }
}
