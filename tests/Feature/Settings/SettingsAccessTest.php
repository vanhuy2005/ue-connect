<?php

namespace Tests\Feature\Settings;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class SettingsAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);

        $this->user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->user->assignRole('student');
    }

    public function test_verified_user_can_access_settings(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('settings'));
        $response->assertStatus(200);

        // Ensure settings are provisioned idempotently
        $this->assertDatabaseHas('profile_privacy_settings', [
            'user_id' => $this->user->id,
        ]);
        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_settings_gate_authorization(): void
    {
        $otherUser = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);

        $this->actingAs($this->user);

        // Standard gate authorization checks
        $this->assertTrue(Gate::allows('viewSettings', $this->user));

        // Cannot modify other user settings
        $this->assertFalse(Gate::forUser($this->user)->allows('updatePrivacy', $otherUser));
    }
}
