<?php

namespace Tests\Feature\Social;

use App\Enums\AccountStatus;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        // Main User
        $this->user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->user->assignRole('student');
        $this->user->profile()->create([
            'display_name' => 'Active Student',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        // Admin User
        $this->admin = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->admin->assignRole('admin');
        $this->admin->profile()->create([
            'display_name' => 'Admin User',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);
    }

    public function test_saved_posts_link_appears_in_navigation_lists(): void
    {
        $this->actingAs($this->user);

        // Sidebar rendering
        $view = $this->view('partials.app.sidebar');
        $view->assertSee('Đã lưu');
        $view->assertSee(route('posts.saved'));

        // Topbar rendering
        $topbar = $this->view('partials.app.topbar');
        $topbar->assertSee('Bài viết đã lưu');
        $topbar->assertSee(route('posts.saved'));
    }

    public function test_admin_reports_link_appears_only_for_authorized_users(): void
    {
        // Standard user sees no admin links in sidebar
        $this->actingAs($this->user);
        $view = $this->view('partials.app.sidebar');
        $view->assertDontSee('Báo cáo vi phạm');

        // Admin user sees admin links in sidebar
        $this->actingAs($this->admin);
        $viewAdmin = $this->view('partials.app.sidebar');
        $viewAdmin->assertSee('Báo cáo vi phạm');
        $viewAdmin->assertSee(route('admin.reports.index'));
    }

    public function test_global_link_hover_styles_do_not_override_button_text_utilities(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString(':where(a:not([class]))', $css);
        $this->assertStringContainsString(':where(a:not([class]):hover)', $css);
        $this->assertDoesNotMatchRegularExpression('/(?<!:where\\()\\ba:hover\\s*\\{/', $css);
    }

    public function test_app_shell_has_enterprise_loading_layer(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $uiEntrypoint = file_get_contents(resource_path('js/ui/index.js'));
        $pageLoading = file_get_contents(resource_path('js/ui/page-loading.js'));
        $livewireFeedback = file_get_contents(resource_path('js/ui/livewire-request-feedback.js'));
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('<x-ui.page-transition />', $layout);
        $this->assertStringContainsString('initPageLoading', $uiEntrypoint);
        $this->assertStringContainsString('initLivewireRequestFeedback', $uiEntrypoint);
        $this->assertStringContainsString("document.addEventListener('livewire:navigating', show)", $pageLoading);
        $this->assertStringContainsString("window.Livewire.hook('request'", $livewireFeedback);
        $this->assertStringContainsString('ue-livewire-busy', $livewireFeedback);
        $this->assertStringContainsString('livewire-upload-start', $livewireFeedback);
        $this->assertStringContainsString('ue:toast', $livewireFeedback);
        $this->assertStringContainsString('.ue-page-progress', $css);
        $this->assertStringContainsString('.ue-livewire-busy .ue-page-progress', $css);
        $this->assertStringNotContainsString('ue-show-route-skeleton', $pageLoading);
        $this->assertStringNotContainsString('.ue-route-skeleton', $css);
    }

    public function test_navigation_shell_uses_livewire_spa_navigation(): void
    {
        $this->actingAs($this->user);

        $sidebar = $this->view('partials.app.sidebar');
        $sidebar->assertSee('wire:navigate', false);

        $mobileNav = $this->view('partials.app.mobile-bottom-nav');
        $mobileNav->assertSee('wire:navigate', false);

        $topbar = $this->view('partials.app.topbar');
        $topbar->assertSee('wire:navigate', false);
    }

    public function test_navigation_badges_use_shared_navigation_metrics_service(): void
    {
        $sidebar = file_get_contents(resource_path('views/partials/app/sidebar.blade.php'));
        $mobileNav = file_get_contents(resource_path('views/partials/app/mobile-bottom-nav.blade.php'));
        $navigationMetrics = file_get_contents(app_path('Support/Navigation/UserNavigationMetrics.php'));

        $this->assertStringContainsString('UserNavigationMetrics::class', $sidebar);
        $this->assertStringContainsString('UserNavigationMetrics::class', $mobileNav);
        $this->assertStringContainsString('wire:navigate.hover', $sidebar);
        $this->assertStringNotContainsString('wire:navigate.hover', $mobileNav);
        $this->assertStringNotContainsString('unreadNotifications()->count()', $sidebar);
        $this->assertStringNotContainsString('unreadNotifications()->count()', $mobileNav);
        $this->assertStringContainsString('Cache::remember', $navigationMetrics);
        $this->assertStringContainsString('forgetForUser', $navigationMetrics);
    }

    public function test_realtime_assets_are_not_loaded_globally(): void
    {
        $appEntry = file_get_contents(resource_path('js/app.js'));
        $viteConfig = file_get_contents(base_path('vite.config.js'));
        $messages = file_get_contents(resource_path('views/livewire/pages/app/messages.blade.php'));
        $notifications = file_get_contents(resource_path('views/livewire/pages/app/notifications.blade.php'));

        $this->assertStringNotContainsString('import "./echo"', $appEntry);
        $this->assertStringContainsString('resources/js/realtime.js', $viteConfig);
        $this->assertStringContainsString("@vite('resources/js/realtime.js')", $messages);
        $this->assertStringContainsString("@vite('resources/js/realtime.js')", $notifications);
    }

    public function test_list_loading_states_are_bound_to_real_cards_not_fake_skeleton_grids(): void
    {
        $mentorList = file_get_contents(resource_path('views/livewire/pages/app/mentors.blade.php'));
        $discoveryList = file_get_contents(resource_path('views/livewire/pages/app/discovery.blade.php'));

        $this->assertStringContainsString('wire:loading.delay.class="ue-content-loading"', $mentorList);
        $this->assertStringContainsString('wire:loading.delay.class="ue-content-loading"', $discoveryList);
        $this->assertStringContainsString('ue-loadable-card', $mentorList);
        $this->assertStringContainsString('ue-loadable-card', $discoveryList);
        $this->assertStringNotContainsString('wire:loading.delay.grid', $mentorList);
        $this->assertStringNotContainsString('wire:loading.delay.grid', $discoveryList);
    }

    public function test_priority_livewire_actions_have_visible_feedback_targets(): void
    {
        $homeFeed = file_get_contents(resource_path('views/livewire/pages/app/home-feed.blade.php'));
        $messages = file_get_contents(resource_path('views/livewire/pages/app/messages.blade.php'));
        $notifications = file_get_contents(resource_path('views/livewire/pages/app/notifications.blade.php'));
        $postCard = file_get_contents(resource_path('views/components/ui/post-card.blade.php'));

        $this->assertStringContainsString('wire:target="submitPost,imageFiles"', $homeFeed);
        $this->assertStringContainsString('Đang đăng...', $homeFeed);
        $this->assertStringContainsString('wire:target="submitMessage"', $messages);
        $this->assertStringContainsString('wire:target="markAllAsRead"', $notifications);
        $this->assertStringContainsString('wire:target="toggleLike({{ $post->id }})"', $postCard);
        $this->assertStringContainsString('wire:target="toggleSave({{ $post->id }})"', $postCard);
    }
}
