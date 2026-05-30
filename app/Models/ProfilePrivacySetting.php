<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfilePrivacySetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'profile_visibility',
        'discovery_visibility',
        'show_faculty',
        'show_major',
        'show_cohort',
        'show_class_code',
        'show_bio',
        'show_interests',
        'show_connection_goals',
        'show_communities',
        'show_career_info',
        'show_mentor_topics',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'show_faculty' => 'boolean',
        'show_major' => 'boolean',
        'show_cohort' => 'boolean',
        'show_class_code' => 'boolean',
        'show_bio' => 'boolean',
        'show_interests' => 'boolean',
        'show_connection_goals' => 'boolean',
        'show_communities' => 'boolean',
        'show_career_info' => 'boolean',
        'show_mentor_topics' => 'boolean',
    ];

    /**
     * Get the user that owns these privacy settings.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
