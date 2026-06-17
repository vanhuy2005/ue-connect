<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\CommunityMemberRole;
use App\Enums\ConnectionStatus;
use App\Enums\GreetingStatus;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response
            ->assertOk()
            ->assertSeeVolt('profile.update-profile-information-form')
            ->assertSeeVolt('profile.update-password-form')
            ->assertSeeVolt('profile.delete-user-form');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('profile.update-profile-information-form')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->call('updateProfileInformation');

        $component
            ->assertHasNoErrors()
            ->assertNoRedirect();

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('profile.update-profile-information-form')
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfileInformation');

        $component
            ->assertHasNoErrors()
            ->assertNoRedirect();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('profile.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertSoftDeleted($user);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('profile.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        $component
            ->assertHasErrors('password')
            ->assertNoRedirect();

        $this->assertNotNull($user->fresh());
    }

    public function test_profile_connection_actions(): void
    {
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        $user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $user->assignRole('student');
        $user->profile()->create([
            'display_name' => $user->name,
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);

        $otherUser = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $otherUser->assignRole('student');
        $otherUser->profile()->create([
            'display_name' => $otherUser->name,
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);

        $this->actingAs($user);

        // Test none state (initiate greeting)
        $component = Volt::test('pages.app.profile', ['user' => $otherUser])
            ->assertSet('connectionStatus', 'none')
            ->call('sendGreeting')
            ->assertSet('connectionStatus', 'pending_sent');

        $this->assertDatabaseHas('greetings', [
            'sender_id' => $user->id,
            'receiver_id' => $otherUser->id,
            'status' => GreetingStatus::PENDING,
        ]);

        // Test cancel greeting
        $component->call('cancelGreeting')
            ->assertSet('connectionStatus', 'none');

        $this->assertDatabaseHas('greetings', [
            'sender_id' => $user->id,
            'receiver_id' => $otherUser->id,
            'status' => GreetingStatus::CANCELLED,
        ]);

        // Re-send greeting
        $component->call('sendGreeting')
            ->assertSet('connectionStatus', 'pending_sent');

        // Login as otherUser to accept greeting
        $this->actingAs($otherUser);

        $otherComponent = Volt::test('pages.app.profile', ['user' => $user])
            ->assertSet('connectionStatus', 'pending_received')
            ->call('acceptGreeting')
            ->assertSet('connectionStatus', 'connected');

        $this->assertDatabaseHas('connections', [
            'user_one_id' => min($user->id, $otherUser->id),
            'user_two_id' => max($user->id, $otherUser->id),
            'status' => ConnectionStatus::ACTIVE,
        ]);

        // Unfriend (remove connection)
        $otherComponent->call('removeConnection')
            ->assertSet('connectionStatus', 'none');

        $this->assertSoftDeleted('connections', [
            'user_one_id' => min($user->id, $otherUser->id),
            'user_two_id' => max($user->id, $otherUser->id),
            'status' => ConnectionStatus::REMOVED,
        ]);
    }

    public function test_profile_communities_tab_shows_active_memberships(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $user->profile()->create([
            'display_name' => $user->name,
            'role_type' => 'student',
            'profile_status' => 'complete',
            'discoverable' => true,
        ]);

        $community = Community::factory()
            ->active()
            ->forOwner($user)
            ->create([
                'name' => 'Cong dong Profile Test',
                'members_count' => 1,
            ]);

        CommunityMember::factory()
            ->owner()
            ->active()
            ->create([
                'community_id' => $community->id,
                'user_id' => $user->id,
                'role' => CommunityMemberRole::Owner->value,
                'joined_at' => now(),
            ]);

        $this->actingAs($user);

        Volt::test('pages.app.profile', ['user' => $user])
            ->set('activeTab', 'communities')
            ->assertSee('Cong dong Profile Test')
            ->assertSeeHtml(route('community.show', $community->id));
    }
}
