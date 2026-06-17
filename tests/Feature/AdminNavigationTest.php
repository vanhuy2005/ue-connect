<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlReferenceSeeder::class);
    }

    public function test_normal_user_does_not_see_admin_console_link(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee(route('admin.dashboard'));
        $response->assertDontSee('Tổng quan quản trị');
    }

    public function test_admin_sees_admin_console_link_in_more_popover(): void
    {
        $admin = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('admin.dashboard'));
        $response->assertSee('Tổng quan quản trị');
    }

    public function test_admin_popover_shows_only_allowed_admin_shortcuts(): void
    {
        $admin = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Tổng quan quản trị');
        $response->assertSee('Duyệt xác thực');
        $response->assertSee('Báo cáo vi phạm');

        $response->assertDontSee('Cài đặt hệ thống');
    }

    public function test_admin_console_layout_shows_category_tabs_and_group_modules(): void
    {
        $admin = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->followingRedirects()
            ->get(route('admin.console', ['group' => 'people-access']));

        $response->assertStatus(200);

        $response->assertSee('Tổng quan');
        $response->assertSee('Người dùng & Quyền');
        $response->assertSee('An toàn & Nội dung');
        $response->assertSee('Hệ thống');
        $response->assertSee('Admin console');
        $response->assertSee('Xác thực, người dùng, mentor access và phân quyền.');

        $response->assertDontSee('Danh tính & Quyền hạn');
        $response->assertDontSee('Tin cậy & An toàn');
        $response->assertDontSee('Vận hành cộng đồng');
        $response->assertDontSee('Vận hành hệ thống');

        $response->assertSee('Duyệt xác thực');
        $response->assertSee('Người dùng');
        $response->assertSee('Vai trò & Quyền');
        $response->assertSee('Quản lý Mentor');

        $response->assertDontSee('Cài đặt hệ thống');
    }

    public function test_admin_sidebar_marks_current_category_only_in_sidebar_variants(): void
    {
        $admin = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.mentors.index'));

        $response->assertStatus(200);
        $response->assertSee('Quản lý Mentor');
        $response->assertSee(route('admin.verifications.queue'));

        $this->assertSame(2, substr_count($response->getContent(), 'aria-current="page"'));
    }

    public function test_admin_console_defaults_to_first_visible_category(): void
    {
        $admin = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->followingRedirects()
            ->get(route('admin.console'));

        $response->assertStatus(200);
        $response->assertSee('Sức khỏe hệ thống, analytics và các chỉ báo vận hành.');
        $response->assertSee('Tổng quan quản trị');
        $response->assertSee('Phân tích');
    }
}
