<?php

namespace App\Providers;

use App\Models\BlockedUser;
use App\Models\Comment;
use App\Models\Connection;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Post;
use App\Models\Profile;
use App\Models\User;
use App\Policies\CommentPolicy;
use App\Policies\ConnectionPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\MessagePolicy;
use App\Policies\PostPolicy;
use App\Policies\ProfilePolicy;
use App\Policies\SettingsPolicy;
use App\Policies\UserBlockPolicy;
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
        ]);

        Gate::policy(Connection::class, ConnectionPolicy::class);
        Gate::policy(BlockedUser::class, UserBlockPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(Message::class, MessagePolicy::class);
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Profile::class, ProfilePolicy::class);
        Gate::policy(User::class, SettingsPolicy::class);
    }
}
