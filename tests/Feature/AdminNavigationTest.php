<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
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

    public function test_admin_console_layout_shows_grouped_modules(): void
    {
        $admin = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);

        $response->assertSee('Tổng quan');
        $response->assertSee('Danh tính & Quyền hạn');
        $response->assertSee('Tin cậy & An toàn');
        $response->assertSee('Vận hành cộng đồng');
        $response->assertSee('Vận hành hệ thống');

        $response->assertSee('Tổng quan quản trị');
        $response->assertSee('Phân tích');
        $response->assertSee('Duyệt xác thực');
        $response->assertSee('Người dùng');
        $response->assertSee('Vai trò & Quyền');
        $response->assertSee('Kiểm duyệt');
        $response->assertSee('Báo cáo');
        $response->assertSee('Nhật ký thao tác');
        $response->assertSee('Quản lý cộng đồng');
        $response->assertSee('Quản lý Mentor');
        $response->assertSee('Thông báo');
        $response->assertSee('Thông báo hệ thống');
        $response->assertSee('Quản lý Media');
        $response->assertSee('Cài đặt hệ thống');
    }
}
