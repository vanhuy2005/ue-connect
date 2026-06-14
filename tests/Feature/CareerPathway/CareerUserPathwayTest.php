<?php

namespace Tests\Feature\CareerPathway;

use App\Enums\AccountStatus;
use App\Enums\CareerUserPathwayItemType;
use App\Enums\CareerUserPathwayStatus;
use App\Enums\CareerUserPathwayVisibility;
use App\Models\CareerProgram;
use App\Models\CareerUserPathway;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerUserPathwayTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_pathway_draft()
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);

        $response = $this->actingAs($user)->postJson(route('career-pathway.senior-pathways.store'), [
            'title' => 'My Journey to SWE',
            'story' => 'It all started when I was a freshman.',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('career_user_pathways', [
            'title' => 'My Journey to SWE',
            'status' => CareerUserPathwayStatus::DRAFT->value,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_link_program()
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $program = CareerProgram::factory()->create(['name' => 'IT Program']);

        $response = $this->actingAs($user)->postJson(route('career-pathway.senior-pathways.store'), [
            'title' => 'My Journey',
            'program_id' => $program->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('career_user_pathways', [
            'title' => 'My Journey',
            'program_id' => $program->id,
        ]);
    }

    public function test_user_can_add_semester_item()
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $pathway = CareerUserPathway::create([
            'title' => 'My Pathway',
            'slug' => 'my-pathway',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson(route('career-pathway.senior-pathways.items.store', $pathway->id), [
            'item_type' => CareerUserPathwayItemType::SEMESTER_NOTE->value,
            'semester_number' => 1,
            'title' => 'Semester 1 Advice',
            'note' => 'Study hard!',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('career_user_pathway_items', [
            'pathway_id' => $pathway->id,
            'semester_number' => 1,
            'title' => 'Semester 1 Advice',
        ]);
    }

    public function test_user_cannot_publish_empty_pathway()
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $pathway = CareerUserPathway::create([
            'title' => 'Empty Pathway',
            'slug' => 'empty-pathway',
            'story' => 'I have no items.',
            'user_id' => $user->id,
            'status' => CareerUserPathwayStatus::DRAFT->value,
        ]);

        $response = $this->actingAs($user)->postJson(route('career-pathway.senior-pathways.publish', $pathway->id));
        $response->assertStatus(400); // Because it has no items
        $this->assertEquals(CareerUserPathwayStatus::DRAFT, $pathway->fresh()->status);
    }

    public function test_user_can_publish_valid_pathway()
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $pathway = CareerUserPathway::create([
            'title' => 'Valid Pathway',
            'slug' => 'valid-pathway',
            'story' => 'I have items.',
            'user_id' => $user->id,
            'status' => CareerUserPathwayStatus::DRAFT->value,
        ]);

        $pathway->items()->create([
            'item_type' => CareerUserPathwayItemType::SEMESTER_NOTE->value,
            'semester_number' => 1,
            'title' => 'Note',
        ]);

        $response = $this->actingAs($user)->postJson(route('career-pathway.senior-pathways.publish', $pathway->id));
        $response->assertStatus(200);
        $this->assertEquals(CareerUserPathwayStatus::PUBLISHED, $pathway->fresh()->status);
        $this->assertEquals(CareerUserPathwayVisibility::PUBLIC, $pathway->fresh()->visibility);
    }

    public function test_hidden_pathway_disappears()
    {
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        $admin = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $admin->givePermissionTo('review_verification');
        $author = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);

        $pathway = CareerUserPathway::create([
            'title' => 'Bad Info',
            'slug' => 'bad-info',
            'user_id' => $author->id,
            'status' => CareerUserPathwayStatus::PUBLISHED->value,
            'visibility' => CareerUserPathwayVisibility::PUBLIC->value,
        ]);

        // Visible
        $response1 = $this->getJson(route('career-pathway.senior-pathways.index'));
        $this->assertCount(1, $response1->json('data'));

        // Moderate
        $this->actingAs($admin)->patchJson(route('admin.career-pathway.senior-pathways.moderate', $pathway->id), [
            'status' => CareerUserPathwayStatus::HIDDEN_BY_MODERATION->value,
        ]);

        // Hidden
        $response2 = $this->getJson(route('career-pathway.senior-pathways.index'));
        $this->assertCount(0, $response2->json('data'));
    }

    public function test_non_author_cannot_edit()
    {
        $author = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $other = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);

        $pathway = CareerUserPathway::create([
            'title' => 'My Journey',
            'slug' => 'my-journey',
            'user_id' => $author->id,
        ]);

        $response = $this->actingAs($other)->patchJson(route('career-pathway.senior-pathways.update', $pathway->id), [
            'title' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }
}
