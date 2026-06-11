<?php

namespace Tests\Feature;

use App\Actions\Mentor\CompleteMentorRequestAction;
use App\Actions\Mentor\CreateMentorConversationAction;
use App\Enums\ConnectionStatus;
use App\Enums\ConversationStatus;
use App\Enums\ConversationType;
use App\Enums\MentorAvailabilityStatus;
use App\Enums\MentorRequestStatus;
use App\Models\Connection;
use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsMentorFixtures;
use Tests\TestCase;

class MentorConversationCreationTest extends TestCase
{
    use BuildsMentorFixtures;
    use RefreshDatabase;

    public function test_accepted_request_creates_conversation_with_system_message(): void
    {
        $student = $this->activeUser('student');
        $profile = $this->mentorProfile();
        $request = $this->mentorRequest($student, $profile, ['status' => MentorRequestStatus::Accepted]);

        $conversation = app(CreateMentorConversationAction::class)->execute($request);

        $this->assertSame($request->id, $conversation->mentor_request_id);
        $this->assertCount(2, $conversation->participants);
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'message_type' => 'system',
        ]);
    }

    public function test_declined_request_does_not_create_conversation(): void
    {
        $student = $this->activeUser('student');
        $profile = $this->mentorProfile();
        $request = $this->mentorRequest($student, $profile, ['status' => MentorRequestStatus::Declined]);

        $this->expectException(\Exception::class);

        app(CreateMentorConversationAction::class)->execute($request);
    }

    public function test_accepted_request_reuses_existing_direct_conversation(): void
    {
        $student = $this->activeUser('student');
        $profile = $this->mentorProfile();
        $mentor = $profile->user;
        $request = $this->mentorRequest($student, $profile, ['status' => MentorRequestStatus::Accepted]);

        $lowId = min($student->id, $mentor->id);
        $highId = max($student->id, $mentor->id);

        $preExistingConversation = Conversation::create([
            'conversation_type' => ConversationType::DIRECT,
            'status' => ConversationStatus::ACTIVE,
            'direct_user_low_id' => $lowId,
            'direct_user_high_id' => $highId,
        ]);

        $conversation = app(CreateMentorConversationAction::class)->execute($request);

        $this->assertSame($preExistingConversation->id, $conversation->id);
        $this->assertSame($request->id, $conversation->fresh()->mentor_request_id);
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'message_type' => 'system',
        ]);
    }

    public function test_conversation_is_locked_on_completion_and_reopened_on_new_accepted_request(): void
    {
        $student = $this->activeUser('student');
        $profile = $this->mentorProfile();
        $mentor = $profile->user;

        // 1. First request is accepted
        $request1 = $this->mentorRequest($student, $profile, ['status' => MentorRequestStatus::Accepted]);
        $conversation = app(CreateMentorConversationAction::class)->execute($request1);
        $request1->update(['conversation_id' => $conversation->id]);

        $this->assertEquals(ConversationStatus::ACTIVE, $conversation->status);
        $this->assertTrue($student->can('sendMessage', $conversation));
        $this->assertTrue($mentor->can('sendMessage', $conversation));

        // 2. First request is completed
        app(CompleteMentorRequestAction::class)->execute($mentor, $request1);

        $this->assertEquals(ConversationStatus::ARCHIVED, $conversation->fresh()->status);
        $this->assertFalse($student->can('sendMessage', $conversation->fresh()));
        $this->assertFalse($mentor->can('sendMessage', $conversation->fresh()));

        // 3. A new request is created and accepted
        $request2 = $this->mentorRequest($student, $profile, ['status' => MentorRequestStatus::Accepted]);
        $conversation2 = app(CreateMentorConversationAction::class)->execute($request2);
        $request2->update(['conversation_id' => $conversation2->id]);

        // Verify conversation is reused, opened back up, and messages can be sent again
        $this->assertSame($conversation->id, $conversation2->id);
        $this->assertEquals(ConversationStatus::ACTIVE, $conversation2->status);
        $this->assertTrue($student->can('sendMessage', $conversation2));
        $this->assertTrue($mentor->can('sendMessage', $conversation2));
    }

    public function test_friends_can_still_send_messages_when_mentor_session_is_completed(): void
    {
        $student = $this->activeUser('student');
        $profile = $this->mentorProfile();
        $mentor = $profile->user;

        // Create and complete a mentor request
        $request = $this->mentorRequest($student, $profile, ['status' => MentorRequestStatus::Accepted]);
        $conversation = app(CreateMentorConversationAction::class)->execute($request);
        $request->update(['conversation_id' => $conversation->id]);

        // Establish a connection (friendship) between student and mentor
        $userOneId = min($student->id, $mentor->id);
        $userTwoId = max($student->id, $mentor->id);
        Connection::create([
            'user_one_id' => $userOneId,
            'user_two_id' => $userTwoId,
            'status' => ConnectionStatus::ACTIVE,
        ]);

        // Complete the session (archives the conversation)
        app(CompleteMentorRequestAction::class)->execute($mentor, $request);

        $archivedConversation = $conversation->fresh();
        $this->assertEquals(ConversationStatus::ARCHIVED, $archivedConversation->status);

        // Friends can still send messages even in an archived mentor conversation
        $this->assertTrue($student->can('sendMessage', $archivedConversation));
        $this->assertTrue($mentor->can('sendMessage', $archivedConversation));
    }

    public function test_availability_auto_switches_to_full_when_slot_limit_reached(): void
    {
        $student1 = $this->activeUser('student');
        $student2 = $this->activeUser('student');
        $profile = $this->mentorProfile(attributes: ['max_pending_requests' => 1]);
        $mentor = $profile->user;

        // Accept first request — fills up the only slot
        $request1 = $this->mentorRequest($student1, $profile, ['status' => MentorRequestStatus::Accepted]);
        app(CreateMentorConversationAction::class)->execute($request1);

        // Trigger sync via accept action (simulate what AcceptMentorRequestAction does)
        $profile->syncAvailabilityFromPendingCount();

        $this->assertEquals(MentorAvailabilityStatus::Full, $profile->fresh()->availability_status);
    }

    public function test_availability_reverts_to_available_when_slot_freed_after_completion(): void
    {
        $student = $this->activeUser('student');
        $profile = $this->mentorProfile(attributes: ['max_pending_requests' => 1]);
        $mentor = $profile->user;

        // Accept a request — fills up the slot and sets Full
        $request = $this->mentorRequest($student, $profile, ['status' => MentorRequestStatus::Accepted]);
        $conversation = app(CreateMentorConversationAction::class)->execute($request);
        $request->update(['conversation_id' => $conversation->id]);
        $profile->syncAvailabilityFromPendingCount();
        $this->assertEquals(MentorAvailabilityStatus::Full, $profile->fresh()->availability_status);

        // Complete the session — slot freed, should revert to Available
        app(CompleteMentorRequestAction::class)->execute($mentor, $request);

        $this->assertEquals(MentorAvailabilityStatus::Available, $profile->fresh()->availability_status);
    }
}
