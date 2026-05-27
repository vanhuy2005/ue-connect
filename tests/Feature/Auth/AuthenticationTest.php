<?php

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Livewire\Actions\Logout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.login');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password');

        $component->call('login');

        $component
            ->assertHasErrors()
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_navigation_menu_can_be_rendered(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertOk();

        // The new UEConnect shell uses static Blade partials (not Livewire layout.navigation).
        // Assert that key shell landmarks are present in the rendered HTML.
        $response->assertSee('role="navigation"', false);      // Sidebar navigation landmark
        $response->assertSee('id="main-content"', false);      // Main content landmark
        $response->assertSee('Điều hướng chính', false);       // Sidebar nav aria-label
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $logout = new Logout;
        $logout();

        $this->assertGuest();
    }
}
