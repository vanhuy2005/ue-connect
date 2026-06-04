<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_notification_center(): void
    {
        $this->seed(AccessControlReferenceSeeder::class);

        $admin = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.notifications.index'))
            ->assertStatus(200)
            ->assertSee('Trung tâm thông báo');
    }
}
