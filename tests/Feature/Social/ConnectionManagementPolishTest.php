<?php

namespace Tests\Feature\Social;

use App\Actions\Connections\AcceptGreeting;
use App\Actions\Connections\SendGreeting;
use App\Enums\AccountStatus;
use App\Models\Connection;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ConnectionManagementPolishTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $connectedUser;

    protected Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        // Main User
        $this->user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $this->user->assignRole('student');
        $this->user->profile()->create([
            'display_name' => 'Active User',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);

        // Connected User
        $this->connectedUser = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $this->connectedUser->assignRole('student');
        $this->connectedUser->profile()->create([
            'display_name' => 'John Scholar',
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);

        // Connect them
        $sendAction = resolve(SendGreeting::class);
        $greeting = $sendAction->execute($this->user, $this->connectedUser);
        $acceptAction = resolve(AcceptGreeting::class);
        $this->connection = $acceptAction->execute($this->connectedUser, $greeting);
    }

    public function test_can_search_active_connections(): void
    {
        $this->actingAs($this->user);

        $firstName = explode(' ', $this->connectedUser->name)[0];

        Volt::test('pages.app.connections')
            ->set('activeTab', 'connections')
            ->assertSee($this->connectedUser->name)
            ->set('connectionSearch', 'Nonexistent')
            ->assertDontSee($this->connectedUser->name)
            ->set('connectionSearch', $firstName)
            ->assertSee($this->connectedUser->name);
    }

    public function test_can_disconnect_from_user(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.connections')
            ->set('activeTab', 'connections')
            ->call('removeConnection', $this->connection->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('connections', [
            'id' => $this->connection->id,
            'status' => 'active',
        ]);
    }

    public function test_can_block_user_from_connections_tab(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.connections')
            ->set('activeTab', 'connections')
            ->call('blockUser', $this->connectedUser->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('blocked_users', [
            'blocker_id' => $this->user->id,
            'blocked_id' => $this->connectedUser->id,
        ]);
    }
}
