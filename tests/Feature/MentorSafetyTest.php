<?php

namespace Tests\Feature;

use App\Actions\Mentor\CreateMentorRequestAction;
use App\Models\BlockedUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Concerns\BuildsMentorFixtures;
use Tests\TestCase;

class MentorSafetyTest extends TestCase
{
    use BuildsMentorFixtures;
    use RefreshDatabase;

    public function test_blocked_users_cannot_request_each_other(): void
    {
        Notification::fake();
        $student = $this->activeUser('student');
        $profile = $this->mentorProfile();

        BlockedUser::create([
            'blocker_id' => $profile->user_id,
            'blocked_id' => $student->id,
            'reason' => 'safety',
        ]);

        $this->expectException(\Exception::class);

        app(CreateMentorRequestAction::class)->execute($student, $profile, [
            'topic' => 'Career',
            'goal' => 'Plan career',
            'question' => 'Can you help?',
            'urgency' => 'normal',
        ]);
    }
}
