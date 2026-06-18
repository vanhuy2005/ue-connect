<?php

namespace App\Providers;

use App\Events\Notifications\UserNotificationCreated;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\BlockedUser;
use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityEvent;
use App\Models\CommunityJoinRequest;
use App\Models\CommunityResource;
use App\Models\CommunitySuggestion;
use App\Models\Connection;
use App\Models\Conversation;
use App\Models\Media;
use App\Models\MediaVariant;
use App\Models\MentorAccessRequest;
use App\Models\MentorFeedback;
use App\Models\MentorProfile;
use App\Models\MentorRequest;
use App\Models\Message;
use App\Models\PermissionGrant;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Report;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Policies\AnnouncementPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\CommentPolicy;
use App\Policies\CommunityEventPolicy;
use App\Policies\CommunityJoinRequestPolicy;
use App\Policies\CommunityPolicy;
use App\Policies\CommunityResourcePolicy;
use App\Policies\CommunitySuggestionPolicy;
use App\Policies\ConnectionPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\MediaPolicy;
use App\Policies\MentorAccessRequestPolicy;
use App\Policies\MentorFeedbackPolicy;
use App\Policies\MentorProfilePolicy;
use App\Policies\MentorRequestPolicy;
use App\Policies\MessagePolicy;
use App\Policies\PostPolicy;
use App\Policies\ProfilePolicy;
use App\Policies\SettingsPolicy;
use App\Policies\UserBlockPolicy;
use App\Policies\VerificationReviewPolicy;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Microsoft\MicrosoftExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            SocialiteWasCalled::class,
            MicrosoftExtendSocialite::class
        );

        VerifyEmail::toMailUsing(fn (object $notifiable, string $url) => (new MailMessage)
            ->subject('Xác minh email UEConnect')
            ->greeting('Xin chào '.$notifiable->name.'!')
            ->line('Vui lòng xác minh email đăng ký để tiếp tục gửi hồ sơ định danh trên UEConnect.')
            ->action('Xác minh email', $url)
            ->line('Nếu bạn không tạo tài khoản UEConnect, bạn có thể bỏ qua email này.'));

        Relation::morphMap([
            'post' => Post::class,
            'comment' => Comment::class,
            'media' => Media::class,
            'profile' => Profile::class,
            'message' => Message::class,
            'community' => Community::class,
            'mentor_profile' => MentorProfile::class,
        ]);

        // Non-scoped PermissionGrant records extend the Gate so that
        // permissions granted via the admin panel take effect alongside
        // the standard Spatie role-based checks.
        Gate::before(function (User $user, string $ability): ?bool {
            $hasGrant = PermissionGrant::where('user_id', $user->id)
                ->where('permission_key', $ability)
                ->whereNull('scope_type')
                ->whereNull('scope_id')
                ->where('status', 'active')
                ->where(function ($q): void {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($q): void {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->exists();

            return $hasGrant ? true : null;
        });

        Gate::policy(Connection::class, ConnectionPolicy::class);

        Gate::policy(BlockedUser::class, UserBlockPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(Message::class, MessagePolicy::class);
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Profile::class, ProfilePolicy::class);
        Gate::policy(User::class, SettingsPolicy::class);
        Gate::policy(VerificationRequest::class, VerificationReviewPolicy::class);
        Gate::policy(Announcement::class, AnnouncementPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Community::class, CommunityPolicy::class);
        Gate::policy(CommunityJoinRequest::class, CommunityJoinRequestPolicy::class);
        Gate::policy(CommunityResource::class, CommunityResourcePolicy::class);
        Gate::policy(CommunitySuggestion::class, CommunitySuggestionPolicy::class);
        Gate::policy(CommunityEvent::class, CommunityEventPolicy::class);
        Gate::policy(Media::class, MediaPolicy::class);
        Gate::policy(MentorAccessRequest::class, MentorAccessRequestPolicy::class);
        Gate::policy(MentorProfile::class, MentorProfilePolicy::class);
        Gate::policy(MentorRequest::class, MentorRequestPolicy::class);
        Gate::policy(MentorFeedback::class, MentorFeedbackPolicy::class);

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        DatabaseNotification::created(function (DatabaseNotification $notification) {
            try {
                UserNotificationCreated::dispatch($notification);
            } catch (\Exception $e) {
                \Log::warning('Real-time notification broadcasting failed: '.$e->getMessage(), [
                    'notification_id' => $notification->id,
                ]);
            }
        });

        // Clear admin dashboard cache when relevant models are modified
        $clearAdminDashboardCache = fn () => Cache::forget('admin_dashboard_data');

        VerificationRequest::saved($clearAdminDashboardCache);
        VerificationRequest::deleted($clearAdminDashboardCache);

        Report::saved($clearAdminDashboardCache);
        Report::deleted($clearAdminDashboardCache);

        Post::saved($clearAdminDashboardCache);
        Post::deleted($clearAdminDashboardCache);

        Comment::saved($clearAdminDashboardCache);
        Comment::deleted($clearAdminDashboardCache);

        User::saved($clearAdminDashboardCache);
        User::deleted($clearAdminDashboardCache);

        Media::saved($clearAdminDashboardCache);
        Media::deleted($clearAdminDashboardCache);

        MediaVariant::saved($clearAdminDashboardCache);
        MediaVariant::deleted($clearAdminDashboardCache);

        AuditLog::saved($clearAdminDashboardCache);
        AuditLog::deleted($clearAdminDashboardCache);
    }
}
