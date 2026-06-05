<?php

namespace Tests\Feature\Social;

use App\Actions\Connections\AcceptGreeting;
use App\Actions\Connections\SendGreeting;
use App\Actions\Messaging\FindOrCreateDirectConversation;
use App\Actions\Messaging\SendMessage;
use App\Enums\AccountStatus;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class MessagingHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $connectedUser;

    protected Conversation $conversation;

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
        $acceptAction->execute($this->connectedUser, $greeting);

        // Find or create conversation
        $convoAction = resolve(FindOrCreateDirectConversation::class);
        $this->conversation = $convoAction->execute($this->user, $this->connectedUser);
    }

    public function test_conversation_list_searching(): void
    {
        $this->actingAs($this->user);

        $firstName = explode(' ', $this->connectedUser->name)[0];

        Volt::test('pages.app.messages')
            ->assertSee($this->connectedUser->name)
            ->set('conversationSearch', 'Nonexistent')
            ->assertDontSee($this->connectedUser->name)
            ->set('conversationSearch', $firstName)
            ->assertSee($this->connectedUser->name);
    }

    public function test_can_recall_own_message(): void
    {
        $msgAction = resolve(SendMessage::class);
        $message = $msgAction->execute($this->user, $this->conversation, ['body' => 'Secret text message']);

        $this->actingAs($this->user);

        Volt::test('pages.app.messages', ['activeConversation' => $this->conversation])
            ->assertSee('Secret text message')
            ->call('deleteMessage', $message->id)
            ->assertHasNoErrors()
            ->assertDontSee('Secret text message')
            ->assertSee('Tin nhắn đã bị thu hồi.');

        $this->assertSoftDeleted('messages', [
            'id' => $message->id,
        ]);
    }

    public function test_can_block_user_from_conversation(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.messages', ['activeConversation' => $this->conversation])
            ->call('blockRecipient')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('blocked_users', [
            'blocker_id' => $this->user->id,
            'blocked_id' => $this->connectedUser->id,
        ]);
    }

    public function test_can_report_message(): void
    {
        $msgAction = resolve(SendMessage::class);
        // Let recipient send message
        $message = $msgAction->execute($this->connectedUser, $this->conversation, ['body' => 'Violative content']);

        $this->actingAs($this->user);

        Volt::test('pages.app.messages', ['activeConversation' => $this->conversation])
            ->call('reportMessage', $message->id)
            ->assertHasNoErrors()
            ->assertSet('feedbackMessage', 'Báo cáo tin nhắn thành công. Nội dung vi phạm đã được gửi tới Ban kiểm duyệt.');
    }
}
