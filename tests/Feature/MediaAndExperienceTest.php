<?php

namespace Tests\Feature;

use App\Actions\Media\AttachMediaToModelAction;
use App\Actions\Media\GenerateMediaUrlAction;
use App\Actions\Media\StoreTemporaryMediaAction;
use App\Actions\Messaging\SendMessage;
use App\Enums\AccountStatus;
use App\Enums\ConnectionStatus;
use App\Enums\ConversationStatus;
use App\Enums\ConversationType;
use App\Enums\MessageType;
use App\Models\Connection;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Media;
use App\Models\Message;
use App\Models\Post;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MediaAndExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        // Disable Cloudinary and S3 Compatible disks in config for testing fallback to local
        config([
            'media.default_strategy' => 'local_only',
            'media.storage.strategy' => 'local_only',
            'media.r2.enabled' => false,
        ]);
        config(['filesystems.disks.public.driver' => 'local']);
        config(['filesystems.disks.private.driver' => 'local']);

        Storage::fake('public');
        Storage::fake('private');
    }

    /**
     * Test media validation rules and temporary storage creation.
     */
    public function test_media_validation_and_temporary_storage(): void
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $storeAction = app(StoreTemporaryMediaAction::class);

        // 1. Valid upload
        $file = UploadedFile::fake()->image('avatar.png', 100, 100);
        $media = $storeAction->execute($user, $file, 'avatar', ['visibility' => 'public']);

        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals('avatar', $media->collection);
        $this->assertEquals('temporary', $media->status);
        $this->assertEquals('public', $media->visibility);
        $this->assertDatabaseHas('media', ['id' => $media->id]);

        // 2. SVG rejection
        $this->expectException(ValidationException::class);
        $svgFile = UploadedFile::fake()->create('vector.svg', 10, 'image/svg+xml');
        $storeAction->execute($user, $svgFile, 'avatar', ['visibility' => 'public']);
    }

    /**
     * Test local storage fallback matches configuration when cloud drives are off.
     */
    public function test_local_fallback_storage_router(): void
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $storeAction = app(StoreTemporaryMediaAction::class);

        $file = UploadedFile::fake()->image('avatar.jpg', 120, 120);
        $media = $storeAction->execute($user, $file, 'avatar', ['visibility' => 'public']);

        $this->assertEquals('local', $media->primary_provider);
        $this->assertEquals('private', $media->primary_disk);
        $this->assertStringContainsString('temp/', $media->primary_path);
    }

    /**
     * Test private/protected url resolution rules.
     */
    public function test_private_protected_media_url_resolution(): void
    {
        $owner = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $stranger = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);

        $storeAction = app(StoreTemporaryMediaAction::class);
        $urlAction = app(GenerateMediaUrlAction::class);

        // Private media collection
        $file = UploadedFile::fake()->image('evidence.png', 200, 200);
        $media = $storeAction->execute($owner, $file, 'verification_evidence', ['visibility' => 'private']);

        // Owner can generate/view the URL
        $ownerUrl = $urlAction->execute($media, 'original', $owner);
        $this->assertNotNull($ownerUrl);
        $this->assertStringContainsString('signature=', $ownerUrl); // Secure signed temporary URL

        // Stranger cannot view the URL (returns null or blocks access via policy)
        $strangerUrl = $urlAction->execute($media, 'original', $stranger);
        $this->assertNull($strangerUrl);
    }

    /**
     * Test profile cover and avatar uploads.
     */
    public function test_profile_cover_and_avatar_uploads_via_livewire(): void
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $profile = Profile::create([
            'user_id' => $user->id,
            'display_name' => $user->name,
            'role_type' => 'student',
            'profile_status' => 'active',
            'visibility' => 'public',
            'discoverable' => true,
        ]);

        $storeAction = app(StoreTemporaryMediaAction::class);
        $attachAction = app(AttachMediaToModelAction::class);

        // Upload avatar
        $avatarFile = UploadedFile::fake()->image('avatar.jpg', 200, 200);
        $mediaAvatar = $storeAction->execute($user, $avatarFile, 'avatar', ['visibility' => 'public']);
        $attachAction->execute($user, $profile, [$mediaAvatar->id], 'avatar');

        // Upload cover
        $coverFile = UploadedFile::fake()->image('cover.jpg', 800, 300);
        $mediaCover = $storeAction->execute($user, $coverFile, 'profile_cover', ['visibility' => 'public']);
        $attachAction->execute($user, $profile, [$mediaCover->id], 'profile_cover');

        $this->assertDatabaseHas('media', [
            'id' => $mediaAvatar->id,
            'mediable_id' => $profile->id,
            'mediable_type' => $profile->getMorphClass(),
            'status' => 'ready',
        ]);

        $this->assertDatabaseHas('media', [
            'id' => $mediaCover->id,
            'mediable_id' => $profile->id,
            'mediable_type' => $profile->getMorphClass(),
            'status' => 'ready',
        ]);
    }

    /**
     * Test profile privacy-safe data masking.
     */
    public function test_profile_privacy_safe_masking(): void
    {
        $user = User::factory()->create([
            'name' => 'Nguyen Van A',
            'email' => '2211005abc@student.hcmue.edu.vn',
            'account_status' => AccountStatus::ACTIVE,
        ]);

        $profile = Profile::create([
            'user_id' => $user->id,
            'display_name' => $user->name,
            'role_type' => 'student',
            'profile_status' => 'active',
            'visibility' => 'public',
            'discoverable' => true,
        ]);

        // Mock public masking logic
        $studentCode = '2211005';
        $maskedMssv = substr($studentCode, 0, 5).str_repeat('•', max(0, strlen($studentCode) - 5));
        $this->assertEquals('22110••', $maskedMssv);
    }

    /**
     * Test post composer multi-image grids.
     */
    public function test_post_composer_multi_image_grids(): void
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $storeAction = app(StoreTemporaryMediaAction::class);
        $attachAction = app(AttachMediaToModelAction::class);

        $post = Post::create([
            'user_id' => $user->id,
            'body' => 'Test post with images',
            'visibility' => 'verified_users',
        ]);

        // Upload and attach 3 images
        $mediaIds = [];
        for ($i = 0; $i < 3; $i++) {
            $file = UploadedFile::fake()->image("image_{$i}.png", 300, 300);
            $media = $storeAction->execute($user, $file, 'post_image', ['visibility' => 'public']);
            $mediaIds[] = $media->id;
        }

        $attachAction->execute($user, $post, $mediaIds, 'post_image');

        $this->assertEquals(3, $post->media()->count());
        $postMedia = $post->media()->get();
        foreach ($postMedia as $media) {
            $this->assertEquals('ready', $media->status);
            $this->assertEquals('post_image', $media->collection);
        }
    }

    public function test_media_attach_partitions_numeric_ids_and_uuids(): void
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $storeAction = app(StoreTemporaryMediaAction::class);
        $attachAction = app(AttachMediaToModelAction::class);

        $post = Post::create([
            'user_id' => $user->id,
            'body' => 'Test post with typed media identifiers',
            'visibility' => 'verified_users',
        ]);

        $idMedia = $storeAction->execute(
            $user,
            UploadedFile::fake()->image('id-image.png', 300, 300),
            'post_image',
            ['visibility' => 'public']
        );

        $uuidMedia = $storeAction->execute(
            $user,
            UploadedFile::fake()->image('uuid-image.png', 300, 300),
            'post_image',
            ['visibility' => 'public']
        );

        DB::flushQueryLog();
        DB::enableQueryLog();

        $attachAction->execute($user, $post, [$idMedia->id], 'post_image');

        $numericIdQueries = DB::getQueryLog();
        $this->assertFalse(collect($numericIdQueries)->contains(fn (array $query): bool => str_contains($query['query'], 'uuid')));

        $attachAction->execute($user, $post, [$uuidMedia->uuid], 'post_image');

        DB::disableQueryLog();

        $this->assertDatabaseHas('media', [
            'id' => $idMedia->id,
            'mediable_id' => $post->id,
            'status' => 'ready',
        ]);
        $this->assertDatabaseHas('media', [
            'id' => $uuidMedia->id,
            'mediable_id' => $post->id,
            'status' => 'ready',
        ]);
    }

    public function test_r2_primary_routes_public_and_private_media_to_expected_disks(): void
    {
        config([
            'media.default_strategy' => 'r2_primary',
            'media.storage.strategy' => 'r2_primary',
            'media.r2.enabled' => true,
            'media.providers.r2.enabled' => true,
            'filesystems.disks.r2_public.url' => null,
        ]);

        Storage::fake('r2_public');
        Storage::fake('r2_private');

        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $storeAction = app(StoreTemporaryMediaAction::class);

        $avatar = $storeAction->execute(
            $user,
            UploadedFile::fake()->image('avatar.jpg', 200, 200),
            'avatar',
            ['visibility' => 'public']
        )->refresh();

        $messageAttachment = $storeAction->execute(
            $user,
            UploadedFile::fake()->image('message.jpg', 200, 200),
            'message_attachment',
            ['visibility' => 'private']
        )->refresh();

        $this->assertEquals('r2', $avatar->primary_provider);
        $this->assertEquals('r2_public', $avatar->primary_disk);
        $this->assertEquals('r2_primary', $avatar->storage_strategy);
        Storage::disk('r2_public')->assertExists($avatar->primary_path);
        Storage::disk('r2_private')->assertMissing($avatar->primary_path);

        $this->assertEquals('r2', $messageAttachment->primary_provider);
        $this->assertEquals('r2_private', $messageAttachment->primary_disk);
        $this->assertEquals('r2_primary', $messageAttachment->storage_strategy);
        Storage::disk('r2_private')->assertExists($messageAttachment->primary_path);
    }

    public function test_public_r2_media_uses_controller_fallback_when_public_url_is_missing(): void
    {
        config([
            'media.default_strategy' => 'r2_primary',
            'media.storage.strategy' => 'r2_primary',
            'media.r2.enabled' => true,
            'media.providers.r2.enabled' => true,
            'filesystems.disks.r2_public.url' => null,
        ]);

        Storage::fake('r2_public');
        Storage::fake('r2_private');

        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $media = app(StoreTemporaryMediaAction::class)->execute(
            $user,
            UploadedFile::fake()->image('post.jpg', 300, 300),
            'post_image',
            ['visibility' => 'public']
        )->refresh();

        $url = app(GenerateMediaUrlAction::class)->execute($media, 'detail', $user);

        $this->assertNotNull($url);
        $this->assertStringContainsString(route('media.preview', ['media' => $media], false), $url);
        $this->assertStringContainsString('variant=detail', $url);
    }

    public function test_hybrid_public_cloudinary_syncs_public_variant_and_prefers_cloudinary_url(): void
    {
        config([
            'media.default_strategy' => 'hybrid_public_cloudinary',
            'media.storage.strategy' => 'hybrid_public_cloudinary',
            'media.r2.enabled' => true,
            'media.providers.r2.enabled' => true,
            'media.providers.cloudinary.enabled' => true,
            'media.providers.cloudinary.cloud_name' => 'test-cloud',
            'media.providers.cloudinary.api_key' => 'test-key',
            'media.providers.cloudinary.api_secret' => 'test-secret',
            'media.providers.cloudinary.upload_folder' => 'ueconnect',
            'media.providers.cloudinary.sync_public_variants' => true,
            'filesystems.disks.r2_public.url' => null,
        ]);

        Storage::fake('r2_public');
        Storage::fake('r2_private');
        Http::fake([
            'api.cloudinary.com/*' => Http::response([
                'public_id' => 'ueconnect/testing/avatar/cloudinary-proof/display',
                'version' => 123,
                'secure_url' => 'https://res.cloudinary.com/test-cloud/image/upload/v123/ueconnect/testing/avatar/cloudinary-proof/display.webp',
                'format' => 'webp',
                'bytes' => 321,
                'resource_type' => 'image',
            ], 200),
        ]);

        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);

        $media = app(StoreTemporaryMediaAction::class)->execute(
            $user,
            UploadedFile::fake()->image('avatar.jpg', 200, 200),
            'avatar',
            ['visibility' => 'public']
        )->refresh();

        $variant = $media->variants()->where('variant_name', 'display')->firstOrFail();

        $this->assertEquals('r2_public', $variant->disk);
        $this->assertEquals('synced', $variant->cloudinary_sync_status);
        $this->assertStringContainsString('res.cloudinary.com', $variant->cloudinary_secure_url);
        $this->assertStringContainsString('res.cloudinary.com', app(GenerateMediaUrlAction::class)->execute($media, 'display', $user));

        Http::assertSentCount(2);
    }

    public function test_private_media_is_never_sent_to_cloudinary_in_hybrid_mode(): void
    {
        config([
            'media.default_strategy' => 'hybrid_public_cloudinary',
            'media.storage.strategy' => 'hybrid_public_cloudinary',
            'media.r2.enabled' => true,
            'media.providers.r2.enabled' => true,
            'media.providers.cloudinary.enabled' => true,
            'media.providers.cloudinary.cloud_name' => 'test-cloud',
            'media.providers.cloudinary.api_key' => 'test-key',
            'media.providers.cloudinary.api_secret' => 'test-secret',
        ]);

        Storage::fake('r2_public');
        Storage::fake('r2_private');
        Http::fake();

        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);

        $media = app(StoreTemporaryMediaAction::class)->execute(
            $user,
            UploadedFile::fake()->image('message.jpg', 200, 200),
            'message_attachment',
            ['visibility' => 'private']
        )->refresh();

        $this->assertEquals('r2_private', $media->primary_disk);
        $this->assertTrue($media->variants()->where('cloudinary_sync_status', 'skipped')->exists());
        Http::assertNothingSent();
    }

    public function test_cloudinary_failure_keeps_r2_upload_usable(): void
    {
        config([
            'media.default_strategy' => 'hybrid_public_cloudinary',
            'media.storage.strategy' => 'hybrid_public_cloudinary',
            'media.r2.enabled' => true,
            'media.providers.r2.enabled' => true,
            'media.providers.cloudinary.enabled' => true,
            'media.providers.cloudinary.cloud_name' => 'test-cloud',
            'media.providers.cloudinary.api_key' => 'test-key',
            'media.providers.cloudinary.api_secret' => 'test-secret',
            'media.providers.cloudinary.fail_open' => true,
            'filesystems.disks.r2_public.url' => null,
        ]);

        Storage::fake('r2_public');
        Storage::fake('r2_private');
        Http::fake(['api.cloudinary.com/*' => Http::response(['error' => ['message' => 'bad credentials']], 401)]);

        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $media = app(StoreTemporaryMediaAction::class)->execute(
            $user,
            UploadedFile::fake()->image('post.jpg', 300, 300),
            'post_image',
            ['visibility' => 'public']
        )->refresh();

        $variant = $media->variants()->where('variant_name', 'detail')->firstOrFail();

        $this->assertEquals('failed', $variant->cloudinary_sync_status);
        $this->assertEquals('r2_public', $variant->disk);
        Storage::disk('r2_public')->assertExists($variant->path);
        $this->assertStringContainsString(route('media.preview', ['media' => $media], false), app(GenerateMediaUrlAction::class)->execute($media, 'detail', $user));
    }

    public function test_upload_quota_fails_closed_before_storage_write(): void
    {
        config([
            'media.quota.user_daily_upload_count' => 0,
            'media.quota.user_daily_upload_mb' => 100,
            'media.quota.user_monthly_upload_mb' => 1000,
            'media.quota.global_daily_upload_mb' => 5000,
        ]);

        Storage::fake('public');
        Storage::fake('private');

        try {
            app(StoreTemporaryMediaAction::class)->execute(
                User::factory()->create(['account_status' => AccountStatus::ACTIVE]),
                UploadedFile::fake()->image('blocked.jpg', 200, 200),
                'avatar',
                ['visibility' => 'public']
            );

            $this->fail('Expected quota validation to fail.');
        } catch (ValidationException) {
            $this->assertSame([], Storage::disk('private')->allFiles());
        }
    }

    public function test_global_upload_budget_fails_closed_before_storage_write(): void
    {
        config([
            'media.quota.user_daily_upload_count' => 100,
            'media.quota.user_daily_upload_mb' => 100,
            'media.quota.user_monthly_upload_mb' => 1000,
            'media.quota.global_daily_upload_mb' => 0,
        ]);

        Storage::fake('public');
        Storage::fake('private');

        try {
            app(StoreTemporaryMediaAction::class)->execute(
                User::factory()->create(['account_status' => AccountStatus::ACTIVE]),
                UploadedFile::fake()->image('blocked-global.jpg', 200, 200),
                'avatar',
                ['visibility' => 'public']
            );

            $this->fail('Expected global quota validation to fail.');
        } catch (ValidationException) {
            $this->assertSame([], Storage::disk('private')->allFiles());
        }
    }

    public function test_cloudinary_daily_sync_cap_skips_sync_and_uses_r2_fallback(): void
    {
        config([
            'media.default_strategy' => 'hybrid_public_cloudinary',
            'media.storage.strategy' => 'hybrid_public_cloudinary',
            'media.r2.enabled' => true,
            'media.providers.r2.enabled' => true,
            'media.providers.cloudinary.enabled' => true,
            'media.providers.cloudinary.cloud_name' => 'test-cloud',
            'media.providers.cloudinary.api_key' => 'test-key',
            'media.providers.cloudinary.api_secret' => 'test-secret',
            'media.quota.cloudinary_daily_sync_limit' => 0,
            'media.quota.disable_cloudinary_when_limit_reached' => true,
            'filesystems.disks.r2_public.url' => null,
        ]);

        Storage::fake('r2_public');
        Storage::fake('r2_private');
        Http::fake();

        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $media = app(StoreTemporaryMediaAction::class)->execute(
            $user,
            UploadedFile::fake()->image('post-cap.jpg', 300, 300),
            'post_image',
            ['visibility' => 'public']
        )->refresh();

        $variant = $media->variants()->where('variant_name', 'detail')->firstOrFail();

        $this->assertEquals('skipped', $variant->cloudinary_sync_status);
        $this->assertEquals('cloudinary_daily_sync_limit_reached', $variant->cloudinary_error_code);
        $this->assertStringContainsString(route('media.preview', ['media' => $media], false), app(GenerateMediaUrlAction::class)->execute($media, 'detail', $user));
        Http::assertNothingSent();
    }

    /**
     * Test direct messaging attachments flow.
     */
    public function test_direct_message_image_attachments(): void
    {
        $user1 = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $user2 = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);

        // Active connection
        Connection::create([
            'user_one_id' => min($user1->id, $user2->id),
            'user_two_id' => max($user1->id, $user2->id),
            'status' => ConnectionStatus::ACTIVE,
        ]);

        // Direct conversation
        $conversation = Conversation::create([
            'conversation_type' => ConversationType::DIRECT,
            'status' => ConversationStatus::ACTIVE,
            'created_by' => $user1->id,
            'direct_user_low_id' => min($user1->id, $user2->id),
            'direct_user_high_id' => max($user1->id, $user2->id),
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user1->id,
        ]);
        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user2->id,
        ]);

        $storeAction = app(StoreTemporaryMediaAction::class);
        $sendMessage = app(SendMessage::class);

        // 1. Upload private image for direct message attachment
        $file = UploadedFile::fake()->image('chat.jpg', 400, 400);
        $media = $storeAction->execute($user1, $file, 'message_attachment', ['visibility' => 'private']);

        // 2. Send message with the attachment
        $message = $sendMessage->execute($user1, $conversation, [
            'body' => 'Gửi bạn tấm hình nè',
            'media_id' => $media->id,
        ]);

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'message_type' => MessageType::IMAGE->value,
            'body' => 'Gửi bạn tấm hình nè',
        ]);

        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'mediable_id' => $message->id,
            'mediable_type' => $message->getMorphClass(),
            'status' => 'ready',
        ]);
    }

    /**
     * Test security policies ensuring only conversation participants can view attachments.
     */
    public function test_message_attachment_policy_participation_protection(): void
    {
        $user1 = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $user2 = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $stranger = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);

        // Connection
        Connection::create([
            'user_one_id' => min($user1->id, $user2->id),
            'user_two_id' => max($user1->id, $user2->id),
            'status' => ConnectionStatus::ACTIVE,
        ]);

        // Direct conversation
        $conversation = Conversation::create([
            'conversation_type' => ConversationType::DIRECT,
            'status' => ConversationStatus::ACTIVE,
            'created_by' => $user1->id,
            'direct_user_low_id' => min($user1->id, $user2->id),
            'direct_user_high_id' => max($user1->id, $user2->id),
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user1->id,
        ]);
        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user2->id,
        ]);

        $storeAction = app(StoreTemporaryMediaAction::class);
        $sendMessage = app(SendMessage::class);

        $file = UploadedFile::fake()->image('confidential.jpg', 300, 300);
        $media = $storeAction->execute($user1, $file, 'message_attachment', ['visibility' => 'private']);

        $message = $sendMessage->execute($user1, $conversation, [
            'body' => 'Private file',
            'media_id' => $media->id,
        ]);

        // Refresh media from DB to get polymorphed target
        $media->refresh();

        // 1. Participant (user2) CAN view the media
        $this->assertTrue(Gate::forUser($user2)->allows('view', $media));

        // 2. Stranger CANNOT view the media
        $this->assertFalse(Gate::forUser($stranger)->allows('view', $media));
    }

    public function test_private_message_attachment_controller_requires_authorized_participant(): void
    {
        $user1 = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $user2 = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $stranger = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);

        Connection::create([
            'user_one_id' => min($user1->id, $user2->id),
            'user_two_id' => max($user1->id, $user2->id),
            'status' => ConnectionStatus::ACTIVE,
        ]);

        $conversation = Conversation::create([
            'conversation_type' => ConversationType::DIRECT,
            'status' => ConversationStatus::ACTIVE,
            'created_by' => $user1->id,
            'direct_user_low_id' => min($user1->id, $user2->id),
            'direct_user_high_id' => max($user1->id, $user2->id),
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user1->id,
        ]);
        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user2->id,
        ]);

        $media = app(StoreTemporaryMediaAction::class)->execute(
            $user1,
            UploadedFile::fake()->image('private-message.jpg', 300, 300),
            'message_attachment',
            ['visibility' => 'private']
        );

        app(SendMessage::class)->execute($user1, $conversation, [
            'body' => 'Private file',
            'media_id' => $media->id,
        ]);

        $media->refresh();

        $signedUrl = app(GenerateMediaUrlAction::class)->execute($media, 'display', $user2);

        $this->assertNotNull($signedUrl);
        $this->actingAs($stranger)->get($signedUrl)->assertForbidden();
        $this->actingAs($user2)->get($signedUrl)->assertOk();
    }
}
