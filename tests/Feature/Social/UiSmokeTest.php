<?php

namespace Tests\Feature\Social;

use App\Enums\AccountStatus;
use App\Models\User;
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
        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);

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
}
