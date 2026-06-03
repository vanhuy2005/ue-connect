<?php

namespace Tests\Feature;

use App\Enums\MentorRequestStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsMentorFixtures;
use Tests\TestCase;

class MentorPolicyTest extends TestCase
{
    use BuildsMentorFixtures;
    use RefreshDatabase;

    public function test_request_policy_limits_state_actions_to_participants(): void
    {
        $student = $this->activeUser('student');
        $profile = $this->mentorProfile();
        $otherMentor = $this->activeUser('alumni');
        $request = $this->mentorRequest($student, $profile);

        $this->assertTrue($profile->user->can('accept', $request));
        $this->assertFalse($otherMentor->can('accept', $request));
        $this->assertTrue($student->can('cancel', $request));

        $request->update(['status' => MentorRequestStatus::Accepted]);

        $this->assertTrue($student->can('complete', $request->fresh()));
        $this->assertTrue($profile->user->can('complete', $request->fresh()));
    }
}
