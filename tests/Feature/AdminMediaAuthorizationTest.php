<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\Media;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminMediaAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_private_media_download_requires_authorization()
    {
        if (! Schema::hasTable('media')) {
            $this->markTestSkipped('Media table does not exist.');
        }

        $user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $otherUser = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);

        $media = Media::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'collection' => 'verification_evidence',
            'primary_disk' => 'local',
            'primary_path' => 'private-doc.pdf',
            'visibility' => 'private',
            'original_filename' => 'private-doc.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 2000,
            'status' => 'ready',
            'primary_provider' => 'local',
            'storage_strategy' => 'local',
            'checksum_sha256' => hash('sha256', 'test'),
        ]);

        // Other normal user cannot download
        $response = $this->actingAs($otherUser)->get(route('media.download', $media));
        $response->assertStatus(403);

        // Owner user can download
        $response2 = $this->actingAs($user)->get(route('media.download', $media));
        // Note: It might return 404 because file doesn't actually exist on disk in test, but not 403.
        $this->assertContains($response2->status(), [200, 404]);
    }
}
