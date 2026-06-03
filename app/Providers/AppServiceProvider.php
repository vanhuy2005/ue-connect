<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\BlockedUser;
use App\Models\Comment;
use App\Models\Community;
use App\Models\Connection;
use App\Models\Conversation;
use App\Models\Media;
use App\Models\Message;
use App\Models\Post;
use App\Models\Profile;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Policies\AnnouncementPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\CommentPolicy;
use App\Policies\CommunityPolicy;
use App\Policies\ConnectionPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\MediaPolicy;
use App\Policies\MessagePolicy;
use App\Policies\PostPolicy;
use App\Policies\ProfilePolicy;
use App\Policies\SettingsPolicy;
use App\Policies\UserBlockPolicy;
use App\Policies\VerificationReviewPolicy;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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

        Relation::morphMap([
            'post' => Post::class,
            'comment' => Comment::class,
            'media' => Media::class,
            'profile' => Profile::class,
            'message' => Message::class,
        ]);

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
        Gate::policy(Media::class, MediaPolicy::class);
    }
}
