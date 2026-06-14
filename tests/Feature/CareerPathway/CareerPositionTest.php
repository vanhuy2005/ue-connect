<?php

namespace Tests\Feature\CareerPathway;

use App\Enums\AccountStatus;
use App\Enums\CareerPositionImportanceLevel;
use App\Enums\CareerPositionItemType;
use App\Enums\CareerPositionSectionType;
use App\Enums\CareerPositionSourceType;
use App\Enums\CareerPositionStatus;
use App\Enums\CareerPositionVisibility;
use App\Models\CareerCourse;
use App\Models\CareerPosition;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerPositionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_position_draft()
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);

        $response = $this->actingAs($user)->postJson(route('career-pathway.positions.store'), [
            'title' => 'Software Engineer',
            'description' => 'A great career path.',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('career_positions', [
            'title' => 'Software Engineer',
            'status' => CareerPositionStatus::DRAFT->value,
            'created_by' => $user->id,
        ]);
    }

    public function test_user_can_publish_valid_position_but_not_empty_one()
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $position = CareerPosition::create([
            'title' => 'Empty Position',
            'description' => 'Nothing here',
            'created_by' => $user->id,
            'slug' => 'empty-position',
            'status' => CareerPositionStatus::DRAFT->value,
        ]);

        // Attempt publish empty -> should fail
        $response = $this->actingAs($user)->postJson(route('career-pathway.positions.publish', $position->id));
        $response->assertStatus(400);

        // Add section and item
        $section = $position->sections()->create([
            'title' => 'Skills',
            'section_type' => CareerPositionSectionType::REQUIRED_SKILLS->value,
        ]);

        $section->items()->create([
            'position_id' => $position->id,
            'item_type' => CareerPositionItemType::CUSTOM->value,
            'title' => 'PHP',
            'importance_level' => CareerPositionImportanceLevel::CORE->value,
            'source_type' => CareerPositionSourceType::USER_CREATED->value,
        ]);

        // Attempt publish again -> should succeed
        $response2 = $this->actingAs($user)->postJson(route('career-pathway.positions.publish', $position->id));
        $response2->assertStatus(200);

        $this->assertEquals(CareerPositionStatus::PUBLISHED, $position->fresh()->status);
    }

    public function test_non_author_cannot_edit()
    {
        $user1 = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $user2 = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);

        $position = CareerPosition::create([
            'title' => 'My Position',
            'created_by' => $user1->id,
            'slug' => 'my-position',
        ]);

        $response = $this->actingAs($user2)->patchJson(route('career-pathway.positions.update', $position->id), [
            'title' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_hidden_position_disappears_from_public_list()
    {
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        $admin = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $admin->givePermissionTo('review_verification');
        $author = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);

        $position = CareerPosition::create([
            'title' => 'Bad Content',
            'created_by' => $author->id,
            'slug' => 'bad-content',
            'status' => CareerPositionStatus::PUBLISHED->value,
            'visibility' => CareerPositionVisibility::PUBLIC->value,
        ]);

        // Verify it's visible initially
        $response1 = $this->getJson(route('career-pathway.positions.index'));
        $this->assertCount(1, $response1->json('data'));

        // Admin hides it
        $this->actingAs($admin)->patchJson(route('admin.career-pathway.positions.moderate', $position->id), [
            'status' => CareerPositionStatus::HIDDEN_BY_MODERATION->value,
        ])->assertStatus(200);

        // Verify it's hidden
        $response2 = $this->getJson(route('career-pathway.positions.index'));
        $this->assertCount(0, $response2->json('data'));
    }

    public function test_duplicate_slug_handled_safely()
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);

        $this->actingAs($user)->postJson(route('career-pathway.positions.store'), [
            'title' => 'Data Scientist',
        ])->assertStatus(201);

        $response = $this->actingAs($user)->postJson(route('career-pathway.positions.store'), [
            'title' => 'Data Scientist',
        ]);

        $response->assertStatus(201);
        $this->assertNotEquals('data-scientist', $response->json('slug'));
        $this->assertStringStartsWith('data-scientist-', $response->json('data.slug') ?? $response->json('slug'));
    }

    public function test_user_can_attach_official_course()
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $course = CareerCourse::create(['code' => 'TEST101', 'name' => 'Test', 'credits' => 3]);

        $position = CareerPosition::create([
            'title' => 'Position',
            'created_by' => $user->id,
            'slug' => 'position',
        ]);

        $section = $position->sections()->create([
            'title' => 'Courses',
            'section_type' => CareerPositionSectionType::RECOMMENDED_COURSES->value,
        ]);

        $response = $this->actingAs($user)->postJson(route('career-pathway.positions.items.store', $position->id), [
            'section_id' => $section->id,
            'item_type' => CareerPositionItemType::COURSE->value,
            'target_type' => CareerCourse::class,
            'target_id' => $course->id,
            'importance_level' => CareerPositionImportanceLevel::RECOMMENDED->value,
            'source_type' => CareerPositionSourceType::OFFICIAL_COURSE->value,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('career_position_items', [
            'target_type' => CareerCourse::class,
            'target_id' => $course->id,
        ]);

        // Ensure course itself remains unchanged
        $this->assertDatabaseHas('career_courses', [
            'id' => $course->id,
            'name' => 'Test',
        ]);
    }
}
