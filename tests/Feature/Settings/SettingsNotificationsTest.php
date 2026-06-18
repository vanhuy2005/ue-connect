<?php

namespace Tests\Feature\Settings;

use App\Actions\Settings\EnsureUserSettingsExistAction;
use App\Enums\AccountStatus;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
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

        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

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

        Volt::test('pages.app.settings.notifications')
            ->set('browser_push_enabled', true)
            ->set('email_messages', true)
            ->set('email_connections', false)
            ->set('push_messages', false)
            ->set('push_connections', false)
            ->call('saveNotifications')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->user->id,
            'browser_push_enabled' => true,
            'email_messages' => true,
            'email_connections' => false,
            'push_messages' => false,
            'push_connections' => false,
            'push_messages_enabled' => false,
            'push_greetings_enabled' => false,
        ]);
    }

    public function test_critical_notifications_cannot_be_disabled(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.settings.notifications')
            ->set('push_system', false)
            ->set('email_system', false)
            ->call('saveNotifications');

        // Assert that critical settings are strictly forced true on the backend
        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->user->id,
            'push_system' => true,
            'push_verification_enabled' => true,
            'push_admin_announcements_enabled' => true,
            'email_system' => true,
        ]);
    }

    public function test_browser_push_global_toggle_persists(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.settings.notifications')
            ->set('browser_push_enabled', true)
            ->call('saveNotifications')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->user->id,
            'browser_push_enabled' => true,
        ]);

        Volt::test('pages.app.settings.notifications')
            ->set('browser_push_enabled', false)
            ->call('saveNotifications')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->user->id,
            'browser_push_enabled' => false,
        ]);
    }
}
