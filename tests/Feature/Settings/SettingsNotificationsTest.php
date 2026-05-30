<?php

namespace Tests\Feature\Settings;

use App\Actions\Settings\EnsureUserSettingsExistAction;
use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class SettingsNotificationsTest extends TestCase
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

        // Provision settings
        app(EnsureUserSettingsExistAction::class)->execute($this->user);
    }

    public function test_updating_notification_preferences_persists(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.settings', ['section' => 'notifications'])
            ->set('message_notifications', false)
            ->set('greeting_notifications', false)
            ->call('saveNotifications')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->user->id,
            'message_notifications' => false,
            'greeting_notifications' => false,
        ]);
    }

    public function test_critical_notifications_cannot_be_disabled(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.settings', ['section' => 'notifications'])
            ->set('safety_notifications', false)
            ->set('moderation_notifications', false)
            ->set('system_notifications', false)
            ->call('saveNotifications');

        // Assert that critical settings are strictly forced true on the backend
        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->user->id,
            'safety_notifications' => true,
            'moderation_notifications' => true,
            'system_notifications' => true,
        ]);
    }
}
