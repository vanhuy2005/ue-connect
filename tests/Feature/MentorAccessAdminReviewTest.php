<?php

namespace Tests\Feature;

use App\Actions\Mentor\GrantMentorAccessAction;
use App\Actions\Mentor\ReviewMentorAccessAction;
use App\Enums\MentorAccessStatus;
use App\Models\MentorAccessRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsMentorFixtures;
use Tests\TestCase;

class MentorAccessAdminReviewTest extends TestCase
{
    use BuildsMentorFixtures;
    use RefreshDatabase;

    public function test_admin_can_approve_mentor_access_and_create_profile(): void
    {
        $admin = $this->adminUser();
        $applicant = $this->activeUser('alumni');
        $this->actingAs($admin);

        $request = MentorAccessRequest::create([
            'user_id' => $applicant->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::Submitted,
            'motivation' => 'I want to mentor students.',
            'expertise_topics' => ['career'],
        ]);

        $profile = app(GrantMentorAccessAction::class)->execute($admin, $request, ['reason' => 'Qualified mentor.']);

        $this->assertTrue($profile->is_active);
        $this->assertDatabaseHas('mentor_access_requests', [
            'id' => $request->id,
            'status' => MentorAccessStatus::Approved->value,
        ]);
    }

    public function test_admin_can_reject_with_reason(): void
    {
        $admin = $this->adminUser();
        $applicant = $this->activeUser('alumni');
        $this->actingAs($admin);

        $request = MentorAccessRequest::create([
            'user_id' => $applicant->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::Submitted,
            'motivation' => 'I want to mentor students.',
        ]);

        app(ReviewMentorAccessAction::class)->execute($admin, $request, [
            'action' => 'reject',
            'reason' => 'Needs clearer experience.',
        ]);

        $this->assertDatabaseHas('mentor_access_requests', [
            'id' => $request->id,
            'status' => MentorAccessStatus::Rejected->value,
            'review_reason' => 'Needs clearer experience.',
        ]);
    }
}
