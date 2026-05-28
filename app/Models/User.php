<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AccountStatus;
use App\Enums\IdentityType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'account_status', 'account_status_reason', 'account_restricted_until', 'last_login_at', 'intended_identity_type'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'account_status' => AccountStatus::class,
            'intended_identity_type' => IdentityType::class,
            'account_restricted_until' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Check if account is restricted.
     */
    public function isRestricted(): bool
    {
        return $this->account_status === AccountStatus::RESTRICTED;
    }

    /**
     * Check if account is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->account_status === AccountStatus::SUSPENDED;
    }

    /**
     * Check if account is banned.
     */
    public function isBanned(): bool
    {
        return $this->account_status === AccountStatus::BANNED;
    }

    /**
     * Check if account is active.
     */
    public function isActive(): bool
    {
        return $this->account_status === AccountStatus::ACTIVE;
    }

    /**
     * Check if account has been verified (active or profile_incomplete).
     */
    public function isVerified(): bool
    {
        return $this->account_status === AccountStatus::ACTIVE
            || $this->account_status === AccountStatus::PROFILE_INCOMPLETE;
    }

    /**
     * Get the user's profile.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Get the user's posts.
     *
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the user's comments.
     *
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the user's post likes.
     *
     * @return HasMany<PostLike, $this>
     */
    public function postLikes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    /**
     * Get the user's post saves.
     *
     * @return HasMany<PostSave, $this>
     */
    public function postSaves(): HasMany
    {
        return $this->hasMany(PostSave::class);
    }

    /**
     * Get the user's comment likes.
     *
     * @return HasMany<CommentLike, $this>
     */
    public function commentLikes(): HasMany
    {
        return $this->hasMany(CommentLike::class);
    }

    /**
     * Get the user's reports.
     *
     * @return HasMany<Report, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    /**
     * Get user's identity verification requests.
     */
    public function verificationRequests(): HasMany
    {
        return $this->hasMany(VerificationRequest::class);
    }

    /**
     * Get user's active verification request.
     */
    public function activeVerificationRequest(): HasOne
    {
        return $this->hasOne(VerificationRequest::class)
            ->whereNotIn('status', ['approved', 'rejected', 'cancelled', 'expired'])
            ->latestOfMany();
    }

    /**
     * Get user's audit logs.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }

    /**
     * Get user's social identity providers.
     */
    public function identityProviders(): HasMany
    {
        return $this->hasMany(UserIdentityProvider::class);
    }
}
