<?php

namespace Tests\Feature\Social;

use App\Actions\Connections\AcceptGreeting;
use App\Actions\Connections\BlockUser;
use App\Actions\Connections\CancelGreeting;
use App\Actions\Connections\DeclineGreeting;
use App\Actions\Connections\SendGreeting;
use App\Enums\AccountStatus;
use App\Enums\ConnectionStatus;
use App\Enums\GreetingStatus;
use App\Models\Connection;
use App\Models\Greeting;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ConnectionRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        // Main User
        $this->user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->user->assignRole('student');
        $this->user->profile()->create([
            'display_name' => 'Active User',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);

        // Other User
        $this->otherUser = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->otherUser->assignRole('student');
        $this->otherUser->profile()->create([
            'display_name' => 'Other User',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);
    }

    public function test_verified_user_can_send_connection_request(): void
    {
        $action = resolve(SendGreeting::class);
        $greeting = $action->execute($this->user, $this->otherUser, [
            'message' => 'Hello student friend!',
        ]);

        $this->assertInstanceOf(Greeting::class, $greeting);
        $this->assertEquals(GreetingStatus::PENDING, $greeting->status);
        $this->assertEquals('Hello student friend!', $greeting->message);
        $this->assertEquals($this->user->id, $greeting->sender_id);
        $this->assertEquals($this->otherUser->id, $greeting->receiver_id);
    }

    public function test_user_cannot_send_request_to_self(): void
    {
        $this->expectException(AuthorizationException::class);

        $action = resolve(SendGreeting::class);
        $action->execute($this->user, $this->user);
    }

    public function test_user_cannot_send_duplicate_pending_request(): void
    {
        $action = resolve(SendGreeting::class);
        $action->execute($this->user, $this->otherUser);

        $this->expectException(AuthorizationException::class);
        $action->execute($this->user, $this->otherUser);
    }

    public function test_receiver_can_accept_request_and_create_connection(): void
    {
        $sendAction = resolve(SendGreeting::class);
        $greeting = $sendAction->execute($this->user, $this->otherUser);

        $acceptAction = resolve(AcceptGreeting::class);
        $connection = $acceptAction->execute($this->otherUser, $greeting);

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals(ConnectionStatus::ACTIVE, $connection->status);
        $this->assertEquals(GreetingStatus::ACCEPTED, $greeting->fresh()->status);

        // Assert normalized pair
        $userOneId = min($this->user->id, $this->otherUser->id);
        $userTwoId = max($this->user->id, $this->otherUser->id);
        $this->assertEquals($userOneId, $connection->user_one_id);
        $this->assertEquals($userTwoId, $connection->user_two_id);
    }

    public function test_receiver_can_decline_request(): void
    {
        $sendAction = resolve(SendGreeting::class);
        $greeting = $sendAction->execute($this->user, $this->otherUser);

        $declineAction = resolve(DeclineGreeting::class);
        $declineAction->execute($this->otherUser, $greeting, [
            'reason' => 'Busy at the moment.',
        ]);

        $this->assertEquals(GreetingStatus::DECLINED, $greeting->fresh()->status);
        $this->assertEquals('Busy at the moment.', $greeting->fresh()->decline_reason);
    }

    public function test_sender_can_cancel_request(): void
    {
        $sendAction = resolve(SendGreeting::class);
        $greeting = $sendAction->execute($this->user, $this->otherUser);

        $cancelAction = resolve(CancelGreeting::class);
        $cancelAction->execute($this->user, $greeting);

        $this->assertEquals(GreetingStatus::CANCELLED, $greeting->fresh()->status);
    }

    public function test_blocked_user_cannot_send_request(): void
    {
        $blockAction = resolve(BlockUser::class);
        $blockAction->execute($this->otherUser, $this->user);

        $this->expectException(AuthorizationException::class);

        $sendAction = resolve(SendGreeting::class);
        $sendAction->execute($this->user, $this->otherUser);
    }

    public function test_volt_discovery_page_interaction(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.discovery')
            ->assertSee('Other User')
            ->call('startGreeting', $this->otherUser->id)
            ->assertSet('showGreetingModal', true)
            ->assertSet('targetUser.id', $this->otherUser->id)
            ->set('greetingMessage', 'Chao ban IT!')
            ->call('submitGreeting')
            ->assertHasNoErrors()
            ->assertSet('showGreetingModal', false)
            ->assertSet('feedbackMessage', 'Đã gửi lời chào kết nối thành công.');

        $this->assertDatabaseHas('greetings', [
            'sender_id' => $this->user->id,
            'receiver_id' => $this->otherUser->id,
            'message' => 'Chao ban IT!',
            'status' => GreetingStatus::PENDING->value,
        ]);
    }
}
