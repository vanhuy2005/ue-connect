<?php

namespace Tests\Feature;

use App\Actions\Mentor\AcceptMentorRequestAction;
use App\Actions\Mentor\CreateMentorRequestAction;
use App\Notifications\Mentor\MentorRequestAcceptedNotification;
use App\Notifications\Mentor\MentorRequestSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Concerns\BuildsMentorFixtures;
use Tests\TestCase;

class MentorNotificationTest extends TestCase
{
    use BuildsMentorFixtures;
    use RefreshDatabase;

    public function test_mentor_and_student_are_notified_during_request_flow(): void
    {
        Notification::fake();
        $student = $this->activeUser('student');
        $profile = $this->mentorProfile();

        $request = app(CreateMentorRequestAction::class)->execute($student, $profile, [
            'topic' => 'Internship',
            'goal' => 'Find internship',
            'question' => 'Where should I start?',
            'urgency' => 'normal',
        ]);

        Notification::assertSentTo($profile->user, MentorRequestSubmittedNotification::class);

        app(AcceptMentorRequestAction::class)->execute($profile->user, $request);

        Notification::assertSentTo($student, MentorRequestAcceptedNotification::class);
    }
}
