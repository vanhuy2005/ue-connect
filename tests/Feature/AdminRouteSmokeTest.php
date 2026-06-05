<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminRouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlReferenceSeeder::class);
    }

    public function test_named_admin_routes_exist()
    {
        $this->assertTrue(Route::has('admin.dashboard'));
        $this->assertTrue(Route::has('admin.users.index'));
        $this->assertTrue(Route::has('admin.verifications.queue'));
        $this->assertTrue(Route::has('admin.audit-logs.index'));
    }

    public function test_normal_users_receive_403()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(403);

        $response2 = $this->actingAs($user)->post(route('admin.system-settings.update'), [
            'key' => 'value',
        ]);
        $response2->assertStatus(403);
    }

    public function test_admin_can_access_get_admin_pages()
    {
        $admin = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }
}
