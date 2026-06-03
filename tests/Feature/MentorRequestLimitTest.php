<?php

namespace Tests\Feature;

use App\Actions\Mentor\CreateMentorRequestAction;
use App\Enums\MentorRequestStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Concerns\BuildsMentorFixtures;
use Tests\TestCase;

class MentorRequestLimitTest extends TestCase
{
    use BuildsMentorFixtures;
    use RefreshDatabase;

    public function test_duplicate_pending_request_is_blocked(): void
    {
        Notification::fake();
        $student = $this->activeUser('student');
        $profile = $this->mentorProfile();
        $this->mentorRequest($student, $profile);

        $this->expectException(\Exception::class);

        app(CreateMentorRequestAction::class)->execute($student, $profile, [
            'topic' => 'Internship',
            'goal' => 'Find an internship',
            'question' => 'Can you help?',
            'urgency' => 'normal',
        ]);
    }

    public function test_mentor_workload_limit_is_enforced(): void
    {
        Notification::fake();
        $student = $this->activeUser('student');
        $profile = $this->mentorProfile(null, ['max_pending_requests' => 1]);
        $this->mentorRequest($this->activeUser('student'), $profile, ['status' => MentorRequestStatus::Submitted]);

        $this->expectException(\Exception::class);

        app(CreateMentorRequestAction::class)->execute($student, $profile, [
            'topic' => 'Career',
            'goal' => 'Plan next semester',
            'question' => 'What should I do?',
            'urgency' => 'normal',
        ]);
    }
}
