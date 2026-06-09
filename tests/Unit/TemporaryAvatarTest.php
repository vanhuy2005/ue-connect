<?php

namespace Tests\Unit;

use App\Models\Media;
use App\Models\TemporaryAvatar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TemporaryAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_expiration_and_media_relationships(): void
    {
        $user = User::factory()->create();
        $previousMedia = $this->createMedia($user);
        $currentMedia = $this->createMedia($user);
        $expiresAt = now()->addDay()->startOfSecond();

        $temporaryAvatar = TemporaryAvatar::create([
            'user_id' => $user->id,
            'previous_media_id' => $previousMedia->id,
            'current_media_id' => $currentMedia->id,
            'expires_at' => $expiresAt,
        ]);

        $this->assertTrue($temporaryAvatar->expires_at->equalTo($expiresAt));
        $this->assertTrue($temporaryAvatar->user->is($user));
        $this->assertTrue($temporaryAvatar->previousMedia->is($previousMedia));
        $this->assertTrue($temporaryAvatar->currentMedia->is($currentMedia));
    }

    private function createMedia(User $user): Media
    {
        return Media::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'collection' => 'avatar',
            'primary_provider' => 'local',
            'primary_disk' => 'public',
            'primary_path' => 'avatars/test.jpg',
            'storage_strategy' => 'single',
            'visibility' => 'public',
            'original_filename' => 'avatar.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => 1024,
            'checksum_sha256' => hash('sha256', (string) Str::uuid()),
            'status' => 'ready',
        ]);
    }
}
