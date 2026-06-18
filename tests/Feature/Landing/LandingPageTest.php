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
        $response->assertSee('id="main-content"', false);
        $response->assertSee('hero-img-ue-connect.png');
        $response->assertSee(route('login'));
        $response->assertSee(route('register'));
        $response->assertDontSee(route('dashboard'));
    }

    public function test_landing_page_renders_successfully_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee(route('dashboard'));
        $response->assertDontSee(route('login'));
    }

    public function test_landing_page_keeps_scroll_on_the_document_viewport(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('overflow-x: clip', false);
        $response->assertSee('overflow-y: visible', false);
    }
}
