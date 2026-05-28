<?php

namespace Tests\Feature\Landing;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_successfully_for_guest(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('UEConnect');
        $response->assertSee('Đăng nhập bằng Entra ID (Office 365)');
        $response->assertSee('Bảo mật');
        $response->assertSee('Quyền riêng tư');
        $response->assertDontSee('Vào Bảng điều khiển');
    }

    public function test_landing_page_renders_successfully_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('Vào Bảng điều khiển');
        $response->assertDontSee('Đăng nhập bằng Entra ID (Office 365)');
    }
}
