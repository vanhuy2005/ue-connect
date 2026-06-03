<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_and_restore_system_settings(): void
    {
        Storage::fake('local');

        $this->seed(RoleAndPermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $updatePayload = [
            'app_name' => 'UEConnect Admin',
            'app_env' => 'testing',
            'app_url' => 'https://ueconnect.test',
            'timezone' => 'Asia/Ho_Chi_Minh',
            'queue_driver' => 'database',
            'mail_driver' => 'log',
            'broadcasting' => 'reverb',
            'session_driver' => 'database',
            'reason' => 'Update runtime settings for test coverage',
        ];

        $this->actingAs($admin)->post(route('admin.system-settings.update'), $updatePayload)
            ->assertRedirect(route('admin.system-settings.index'));

        $settings = json_decode(Storage::disk('local')->get('system_settings.json'), true);

        $this->assertSame('UEConnect Admin', $settings['app_name']);
        $this->assertSame('testing', $settings['app_env']);

        $snapshotPayload = [
            'name' => 'baseline',
            'reason' => 'Create snapshot before testing restore flow',
        ];

        $this->actingAs($admin)->post(route('admin.system-settings.snapshot'), $snapshotPayload)
            ->assertRedirect(route('admin.system-settings.index'));

        $modifiedPayload = $updatePayload;
        $modifiedPayload['app_name'] = 'UEConnect Admin Modified';
        $modifiedPayload['reason'] = 'Modify settings before restore test';

        $this->actingAs($admin)->post(route('admin.system-settings.update'), $modifiedPayload)
            ->assertRedirect(route('admin.system-settings.index'));

        $this->actingAs($admin)->post(route('admin.system-settings.snapshot.restore'), [
            'snapshot' => 'baseline.json',
            'reason' => 'Restore baseline snapshot after test modification',
        ])->assertRedirect(route('admin.system-settings.index'));

        $restored = json_decode(Storage::disk('local')->get('system_settings.json'), true);

        $this->assertSame('UEConnect Admin', $restored['app_name']);
        $this->assertSame('testing', $restored['app_env']);

        $this->assertDatabaseHas('audit_logs', [
            'action_key' => 'update_system_settings',
            'target_type' => 'system_settings',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action_key' => 'create_system_settings_snapshot',
            'target_type' => 'system_settings_snapshot',
            'target_id' => 'baseline.json',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action_key' => 'restore_system_settings_snapshot',
            'target_type' => 'system_settings_snapshot',
            'target_id' => 'baseline.json',
        ]);
    }
}
