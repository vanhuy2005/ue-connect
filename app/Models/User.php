<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\ConnectionStatus;
use App\Enums\IdentityType;
use App\Enums\MentorAccessStatus;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'account_status', 'account_status_reason', 'account_restricted_until', 'last_login_at', 'intended_identity_type', 'last_seen_at', 'show_activity_status'])]
#[Hidden(['password', 'remember_token'])]
/**
 * App\Models\User
 *
 * @method bool can(string $ability, mixed $arguments = null)
 */
class User extends Authenticatable implements MustVerifyEmail
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
            'last_seen_at' => 'datetime',
<<<<<<< HEAD
=======
            'show_activity_status' => 'boolean',
>>>>>>> cb631b47c033676f3c651a480c81b3751f9e2c96
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
     * Check if this user is allowed to see the presence/activity status of the target user.
     */
    public function canSeePresenceOf(User $targetUser): bool
    {
        if ($this->id === $targetUser->id) {
            return true;
        }

        if (! $this->show_activity_status || ! $targetUser->show_activity_status) {
            return false;
        }

        $userOneId = min($this->id, $targetUser->id);
        $userTwoId = max($this->id, $targetUser->id);
        $areFriends = Connection::where('user_one_id', $userOneId)
            ->where('user_two_id', $userTwoId)
            ->where('status', ConnectionStatus::ACTIVE)
            ->exists();

        if ($areFriends) {
            return true;
        }

        $shareConversation = Conversation::whereHas('participants', function ($q) {
            $q->where('user_id', $this->id);
        })
            ->whereHas('participants', function ($q) use ($targetUser) {
                $q->where('user_id', $targetUser->id);
            })
            ->exists();

        if ($shareConversation) {
            return true;
        }

        return false;
    }

    /**
     * Check if this user is currently online.
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(5));
    }

    /**
     * Get the user's profile.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Get the user's profile privacy settings.
     */
    public function profilePrivacySetting(): HasOne
    {
        return $this->hasOne(ProfilePrivacySetting::class);
    }

    /**
     * Get the user's notification preferences.
     */
    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
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
     * Get the user's reposts.
     *
     * @return HasMany<PostRepost, $this>
     */
    public function postReposts(): HasMany
    {
        return $this->hasMany(PostRepost::class);
    }

    /**
     * Get users followed by this user.
     *
     * @return BelongsToMany<User, $this>
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    /**
     * Get users following this user.
     *
     * @return BelongsToMany<User, $this>
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_follows', 'following_id', 'follower_id')
            ->withTimestamps();
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

    /**
     * Get user's hidden posts exclusions.
     *
     * @return HasMany<PostHide, $this>
     */
    public function hiddenPosts(): HasMany
    {
        return $this->hasMany(PostHide::class);
    }

    public function evidenceCaptureSessions(): HasMany
    {
        return $this->hasMany(EvidenceCaptureSession::class);
    }

    /**
     * Get mentor access requests submitted by this user.
     *
     * @return HasMany<MentorAccessRequest, $this>
     */
    public function mentorAccessRequests(): HasMany
    {
        return $this->hasMany(MentorAccessRequest::class);
    }

    /**
     * Check if the user has an approved mentor access grant.
     */
    public function hasApprovedMentorAccess(): bool
    {
        return $this->can('mentor_access')
            && $this->mentorAccessRequests()
                ->where('status', MentorAccessStatus::Approved)
                ->exists();
    }

    /**
     * Get the user's approved mentor profile.
     */
    public function mentorProfile(): HasOne
    {
        return $this->hasOne(MentorProfile::class);
    }

    /**
     * Check if this user is an approved active mentor.
     */
    public function isActiveMentor(): bool
    {
        return $this->isActive()
            && $this->mentorProfile !== null
            && $this->mentorProfile->is_active
            && $this->hasApprovedMentorAccess();
    }

    /**
     * Get mentor requests sent by this user (as student).
     *
     * @return HasMany<MentorRequest, $this>
     */
    public function sentMentorRequests(): HasMany
    {
        return $this->hasMany(MentorRequest::class, 'student_id');
    }

    /**
     * Get mentor requests received by this user (as mentor).
     *
     * @return HasMany<MentorRequest, $this>
     */
    public function receivedMentorRequests(): HasMany
    {
        return $this->hasMany(MentorRequest::class, 'mentor_id');
    }

    /**
     * Get all community memberships for this user.
     *
     * @return HasMany<CommunityMember, $this>
     */
    public function communityMemberships(): HasMany
    {
        return $this->hasMany(CommunityMember::class);
    }

    /**
     * Get the active community memberships.
     *
     * @return HasMany<CommunityMember, $this>
     */
    public function activeCommunityMemberships(): HasMany
    {
        return $this->communityMemberships()->where('status', 'active');
    }

    /**
     * Get pending community join requests submitted by this user.
     *
     * @return HasMany<CommunityJoinRequest, $this>
     */
    public function communityJoinRequests(): HasMany
    {
        return $this->hasMany(CommunityJoinRequest::class);
    }

    /**
     * Check if user is online based on last seen timestamp (within 5 minutes).
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(5));
    }

    /**
     * Determine if a viewer is authorized to see this user's online status.
     */
    public function canSeeOnlineStatus(User $viewer): bool
    {
        if ($this->id === $viewer->id) {
            return true;
        }

        $privacy = $this->profilePrivacySetting;
        $visibility = $privacy ? $privacy->online_status_visibility : 'connections';

        if ($visibility === 'nobody') {
            return false;
        }

        // Check if there is an active direct connection between this user and the viewer
        $isConnected = Connection::where(function ($q) use ($viewer) {
            $q->where('user_one_id', min($this->id, $viewer->id))
                ->where('user_two_id', max($this->id, $viewer->id));
        })->where('status', ConnectionStatus::ACTIVE)->exists();

        if ($visibility === 'connections') {
            return $isConnected;
        }

        if ($visibility === 'mutual_connections') {
            if ($isConnected) {
                return true;
            }

            // Get this user's active connection user IDs
            $myConnections = Connection::where(function ($q) {
                $q->where('user_one_id', $this->id)->orWhere('user_two_id', $this->id);
            })->where('status', ConnectionStatus::ACTIVE)
                ->get()
                ->map(fn ($c) => $c->user_one_id === $this->id ? $c->user_two_id : $c->user_one_id)
                ->toArray();

            // Get viewer's active connection user IDs
            $viewerConnections = Connection::where(function ($q) use ($viewer) {
                $q->where('user_one_id', $viewer->id)->orWhere('user_two_id', $viewer->id);
            })->where('status', ConnectionStatus::ACTIVE)
                ->get()
                ->map(fn ($c) => $c->user_one_id === $viewer->id ? $c->user_two_id : $c->user_one_id)
                ->toArray();

            $mutual = array_intersect($myConnections, $viewerConnections);

            return ! empty($mutual);
        }

        return false;
    }
}
