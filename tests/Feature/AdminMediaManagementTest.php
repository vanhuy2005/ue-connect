<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\Media;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminMediaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlReferenceSeeder::class);
    }

    public function test_admin_can_view_media_usage()
    {
        if (! Schema::hasTable('media')) {
            $this->markTestSkipped('Media table does not exist.');
        }

        $admin = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.media.usage'));
        $response->assertStatus(200);
        $response->assertSee('Dung lượng & Hạn mức lưu trữ', false);
    }

    public function test_normal_user_cannot_view_media_usage()
    {
        if (! Schema::hasTable('media')) {
            $this->markTestSkipped('Media table does not exist.');
        }

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.media.usage'));
        $response->assertStatus(403);
    }

    public function test_admin_can_quarantine_media()
    {
        if (! Schema::hasTable('media')) {
            $this->markTestSkipped('Media table does not exist.');
        }

        $admin = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $admin->assignRole('admin');

        $media = Media::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $admin->id,
            'collection' => 'post_image',
            'primary_disk' => 'local',
            'primary_path' => 'test.jpg',
            'visibility' => 'public',
            'original_filename' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => 1000,
            'status' => 'ready',
            'primary_provider' => 'local',
            'storage_strategy' => 'local',
            'checksum_sha256' => hash('sha256', 'test'),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.media.quarantine', $media));
        $response->assertStatus(302);

        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'status' => 'quarantined',
        ]);
    }
}
