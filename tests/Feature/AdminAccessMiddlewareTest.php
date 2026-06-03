<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminAccessMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authorized_user_with_specific_permission_has_access()
    {
        $user = User::factory()->create();

        $permission = Permission::findOrCreate('manage_users', 'web');
        $user->givePermissionTo($permission);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }

    public function test_unauthorized_user_without_permission_is_blocked()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }
}
