<?php

namespace Tests\Feature\Social;

use App\Actions\Connections\SendGreeting;
use App\Enums\AccountStatus;
use App\Enums\GreetingStatus;
use App\Models\Greeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class GreetingInboxTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);

        // Main User
        $this->user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $this->user->assignRole('student');
        $this->user->profile()->create([
            'display_name' => 'Active User',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);

        // Other User
        $this->otherUser = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $this->otherUser->assignRole('student');
        $this->otherUser->profile()->create([
            'display_name' => 'Other User',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);
    }

    public function test_tabs_render_correctly(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.connections')
            ->assertSee('Lời mời đã nhận')
            ->assertSee('Lời mời đã gửi')
            ->assertSee('Bạn bè/kết nối');
    }

    public function test_received_greeting_card_renders_and_accept_works(): void
    {
        // Send a greeting to main user
        $sendAction = resolve(SendGreeting::class);
        $greeting = $sendAction->execute($this->otherUser, $this->user, ['message' => 'Hello there!']);

        $this->actingAs($this->user);

        Volt::test('pages.app.connections')
            ->set('activeTab', 'received')
            ->assertSee($this->otherUser->name)
            ->assertSee('Hello there!')
            ->call('acceptGreeting', $greeting->id)
            ->assertHasNoErrors();

        $this->assertEquals(GreetingStatus::ACCEPTED, $greeting->fresh()->status);
        $this->assertDatabaseHas('connections', [
            'status' => 'active',
        ]);
    }

    public function test_received_greeting_card_decline_works(): void
    {
        // Send a greeting to main user
        $sendAction = resolve(SendGreeting::class);
        $greeting = $sendAction->execute($this->otherUser, $this->user, ['message' => 'Hello there!']);

        $this->actingAs($this->user);

        Volt::test('pages.app.connections')
            ->set('activeTab', 'received')
            ->assertSee($this->otherUser->name)
            ->call('declineGreeting', $greeting->id)
            ->assertHasNoErrors();

        $this->assertEquals(GreetingStatus::DECLINED, $greeting->fresh()->status);
    }

    public function test_sent_greeting_card_renders_and_cancel_works(): void
    {
        // Send a greeting from main user to other user
        $sendAction = resolve(SendGreeting::class);
        $greeting = $sendAction->execute($this->user, $this->otherUser, ['message' => 'Greeting from me!']);

        $this->actingAs($this->user);

        Volt::test('pages.app.connections')
            ->set('activeTab', 'sent')
            ->assertSee($this->otherUser->name)
            ->assertSee('Greeting from me!')
            ->call('cancelGreeting', $greeting->id)
            ->assertHasNoErrors();

        $this->assertEquals(GreetingStatus::CANCELLED, $greeting->fresh()->status);
    }
}
