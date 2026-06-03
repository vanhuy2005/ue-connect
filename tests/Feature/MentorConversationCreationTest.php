<?php

namespace Tests\Feature;

use App\Actions\Mentor\CreateMentorConversationAction;
use App\Enums\ConversationStatus;
use App\Enums\ConversationType;
use App\Enums\MentorRequestStatus;
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
}
