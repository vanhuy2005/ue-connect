<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlReferenceSeeder::class);
    }

    public function test_admin_can_access_dashboard_and_data_is_cached_properly(): void
    {
        $admin = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $admin->assignRole('admin');

        // Create some audit logs
        AuditLog::create([
            'actor_id' => $admin->id,
            'actor_type' => 'user',
            'action' => 'test_action',
            'action_key' => 'test_key',
            'target_type' => 'user',
            'target_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Tổng quan quản trị');
        $response->assertSee('Test key');
    }
}
