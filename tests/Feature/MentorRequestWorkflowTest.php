<?php

namespace Tests\Feature;

use App\Actions\Mentor\AcceptMentorRequestAction;
use App\Actions\Mentor\AskMentorRequestMoreInfoAction;
use App\Actions\Mentor\CancelMentorRequestAction;
use App\Actions\Mentor\CreateMentorRequestAction;
use App\Actions\Mentor\DeclineMentorRequestAction;
use App\Enums\MentorRequestStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Concerns\BuildsMentorFixtures;
use Tests\TestCase;

class MentorRequestWorkflowTest extends TestCase
{
    use BuildsMentorFixtures;
    use RefreshDatabase;

    public function test_student_can_send_structured_request_and_mentor_can_accept(): void
    {
        Notification::fake();
        $student = $this->activeUser('student');
        $profile = $this->mentorProfile();

        $request = app(CreateMentorRequestAction::class)->execute($student, $profile, [
            'topic' => 'Internship',
            'goal' => 'Find an internship',
            'question' => 'What should I prepare first?',
            'urgency' => 'normal',
        ]);

        $accepted = app(AcceptMentorRequestAction::class)->execute($profile->user, $request);

        $this->assertSame(MentorRequestStatus::Accepted, $accepted->status);
        $this->assertNotNull($accepted->conversation_id);
    }

    public function test_mentor_can_decline_or_ask_more_info_and_student_can_cancel(): void
    {
        Notification::fake();
        $student = $this->activeUser('student');
        $profile = $this->mentorProfile();

        $request = $this->mentorRequest($student, $profile);
        app(AskMentorRequestMoreInfoAction::class)->execute($profile->user, $request, [
            'more_info_question' => 'Please share your CV first.',
        ]);
        $this->assertSame(MentorRequestStatus::NeedMoreInfo, $request->fresh()->status);

        app(CancelMentorRequestAction::class)->execute($student, $request->fresh());
        $this->assertSame(MentorRequestStatus::Cancelled, $request->fresh()->status);

        $declined = $this->mentorRequest($student, $profile);
        app(DeclineMentorRequestAction::class)->execute($profile->user, $declined, [
            'decline_reason' => 'No capacity this week.',
        ]);
        $this->assertSame(MentorRequestStatus::Declined, $declined->fresh()->status);
    }
}
