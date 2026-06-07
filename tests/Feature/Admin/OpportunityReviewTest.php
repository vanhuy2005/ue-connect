<?php

namespace Tests\Feature\Admin;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\OpportunityDetail;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunityReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);

        $this->admin = User::factory()->create(['account_status' => 'active']);
        $this->admin->assignRole('admin');

        $this->author = User::factory()->create(['account_status' => 'active']);
        $this->author->assignRole('alumni');
    }

    private function makeOpportunity(array $overrides = []): Post
    {
        $post = Post::factory()->create(array_merge([
            'user_id' => $this->author->id,
            'post_type' => PostType::OPPORTUNITY,
            'status' => PostStatus::PENDING_REVIEW,
            'published_at' => null,
            'body' => 'We are hiring backend engineers.',
        ], $overrides));

        OpportunityDetail::create([
            'post_id' => $post->id,
            'company' => 'UE Tech',
            'position' => 'Backend Engineer',
            'location' => 'Ho Chi Minh City',
            'application_url' => 'https://example.com/apply',
            'application_deadline' => now()->addMonth(),
            'field_tags' => ['Laravel', 'PHP'],
        ]);

        return $post->load('opportunityDetail');
    }

    public function test_detail_page_renders_for_opportunity(): void
    {
        $post = $this->makeOpportunity();

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.opportunities.detail', $post));

        $response->assertStatus(200);
        $response->assertSee('Kiểm duyệt cơ hội việc làm');
        $response->assertSee('UE Tech');
        $response->assertSee('Backend Engineer');
        $response->assertSee('Duyệt');
        $response->assertSee('Từ chối');
    }

    public function test_approve_changes_status_to_published(): void
    {
        $post = $this->makeOpportunity();

        $this->actingAs($this->admin);

        $this->post(route('admin.opportunities.detail', $post), [
            'action' => 'approve',
            'reason' => 'Looks good',
        ]);

        $post->refresh();
        $this->assertEquals(PostStatus::PUBLISHED, $post->status);
        $this->assertNotNull($post->published_at);
    }

    public function test_reject_changes_status_to_rejected(): void
    {
        $post = $this->makeOpportunity();

        $this->actingAs($this->admin);

        $this->post(route('admin.opportunities.detail', $post), [
            'action' => 'reject',
            'reason' => 'Not suitable',
        ]);

        $post->refresh();
        $this->assertEquals(PostStatus::REJECTED, $post->status);
    }

    public function test_approve_creates_audit_log(): void
    {
        $post = $this->makeOpportunity();

        $this->actingAs($this->admin);

        $this->post(route('admin.opportunities.detail', $post), [
            'action' => 'approve',
            'reason' => 'Looks good',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_type' => 'admin',
            'actor_id' => $this->admin->id,
            'target_type' => 'posts',
            'target_id' => $post->id,
            'action' => 'opportunity.approve',
        ]);
    }

    public function test_reject_creates_audit_log(): void
    {
        $post = $this->makeOpportunity();

        $this->actingAs($this->admin);

        $this->post(route('admin.opportunities.detail', $post), [
            'action' => 'reject',
            'reason' => 'Invalid content',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_type' => 'admin',
            'actor_id' => $this->admin->id,
            'target_type' => 'posts',
            'target_id' => $post->id,
            'action' => 'opportunity.reject',
        ]);
    }

    public function test_detail_route_returns_404_for_non_opportunity_post(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->author->id,
            'post_type' => PostType::STANDARD,
            'status' => PostStatus::PUBLISHED,
        ]);

        $this->actingAs($this->admin);

        $this->get(route('admin.opportunities.detail', $post))
            ->assertStatus(404);
    }

    public function test_queue_page_uses_admin_detail_link(): void
    {
        $post = $this->makeOpportunity();

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.opportunities.queue'));

        $response->assertStatus(200);
        $response->assertSee(route('admin.opportunities.detail', $post));
    }
}
