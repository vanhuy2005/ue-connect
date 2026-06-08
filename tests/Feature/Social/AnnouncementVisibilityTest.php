<?php

namespace Tests\Feature\Social;

use App\Enums\AccountStatus;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\SystemAnnouncementNotification;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AnnouncementVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        $this->user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->user->assignRole('student');
        $this->user->profile()->create([
            'display_name' => 'Active Student',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        $this->admin = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->admin->assignRole('admin');
        $this->admin->profile()->create([
            'display_name' => 'Admin User',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);
    }

    public function test_publishing_announcement_notifies_active_users(): void
    {
        $this->actingAs($this->admin);

        // Create announcement
        $announcement = Announcement::create([
            'title' => 'System Maintenance Alert',
            'body' => 'Maintenance tonight.',
            'type' => 'safety_notice',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        // Verify user has no notifications
        $this->assertEquals(0, $this->user->notifications()->count());

        // Publish announcement via post route
        $response = $this->post(route('admin.announcements.publish', $announcement));
        $response->assertRedirect();

        // Verify notification is created for user
        $this->assertEquals(1, $this->user->notifications()->count());

        $notification = $this->user->notifications()->first();
        $this->assertEquals('system_announcement', $notification->data['type']);
        $this->assertEquals('System Maintenance Alert', $notification->data['title']);
        $this->assertEquals('Maintenance tonight.', $notification->data['body']);
    }

    public function test_creating_published_announcement_notifies_active_users(): void
    {
        $this->actingAs($this->admin);

        // Verify user has no notifications
        $this->assertEquals(0, $this->user->notifications()->count());

        // Create published announcement via store route
        $response = $this->post(route('admin.announcements.store'), [
            'title' => 'New Immediate Release',
            'body' => 'Immediate release content.',
            'type' => 'system_announcement',
            'status' => 'published',
        ]);
        $response->assertRedirect();

        // Verify notification is created for user
        $this->assertEquals(1, $this->user->notifications()->count());

        $notification = $this->user->notifications()->first();
        $this->assertEquals('system_announcement', $notification->data['type']);
        $this->assertEquals('New Immediate Release', $notification->data['title']);
        $this->assertEquals('Immediate release content.', $notification->data['body']);
    }

    public function test_announcement_notification_is_visible_on_notifications_page(): void
    {
        $this->actingAs($this->user);

        // Directly notify user using class
        $announcement = Announcement::create([
            'title' => 'Feature Announcement',
            'body' => 'We launched a new feature!',
            'type' => 'feature_update',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);

        $this->user->notify(new SystemAnnouncementNotification($announcement));

        // Test component displays it under System tab
        Volt::test('pages.app.notifications')
            ->set('activeTab', 'system')
            ->assertSee('Feature Announcement')
            ->assertSee('We launched a new feature!');
    }
}
