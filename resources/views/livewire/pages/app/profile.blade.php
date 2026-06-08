<?php

use App\Models\User;
use App\Models\Profile;
use App\Models\Connection;
use App\Models\BlockedUser;
use App\Models\Media;
use App\Models\UserFollow;
use App\Enums\ConnectionStatus;
use App\Enums\PostStatus;
use App\Enums\GreetingStatus;
use App\Models\Greeting;
use App\Actions\Connections\SendGreeting;
use App\Actions\Connections\CancelGreeting;
use App\Actions\Connections\AcceptGreeting;
use App\Actions\Connections\RemoveConnection;
use App\Actions\Connections\BlockUser;
use App\Actions\Follows\FollowUser;
use App\Actions\Follows\UnfollowUser;
use App\Actions\Media\StoreTemporaryMediaAction;
use App\Actions\Media\AttachMediaToModelAction;
use App\Actions\Media\DeleteMediaAction;
use App\Actions\Media\GenerateMediaUrlAction;
use App\Actions\Posts\DeletePost;
use App\Actions\Posts\UpdatePost;
use App\Actions\Posts\TogglePostLike;
use App\Actions\Posts\TogglePostRepost;
use App\Actions\Posts\TogglePostSave;
use App\Actions\Posts\HidePostFromFeed;
use App\Actions\Reports\CreateReport;
use App\Actions\Messaging\SendSharedPostMessage;
use App\Actions\Messaging\FindOrCreateDirectConversation;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public User $user;
    public string $activeTab = 'posts'; // posts, replies, media, communities, reposts
    public ?string $feedbackMessage = null;
    public bool $isFollowing = false;
    public int $followersCount = 0;
    public int $followingCount = 0;

    // Files inputs
    public $avatarFile;
    public $coverFile;

    // Post actions / Composer properties
    public ?int $editingPostId = null;
    public string $editingBody = '';

    // Report properties
    public ?\App\Models\Post $reportingPost = null;
    public string $reportReason = 'spam';
    public string $reportDescription = '';
    public bool $showReportModal = false;

    // Custom delete modal properties
    public ?int $deletingPostId = null;
    public bool $showDeleteModal = false;

    // Sharing post properties
    public bool $showShareModal = false;
    public ?int $sharingPostId = null;
    public string $shareSearch = '';
    public array $selectedShareUserIds = [];
    public string $shareOptionalMessage = '';

    public function mount(User $user): void
    {
        $this->user = $user;
        
        $profile = $user->profile()->withTrashed()->first();
        if (!$profile) {
            $user->profile()->create([
                'display_name' => $user->name,
                'role_type' => 'student',
                'profile_status' => 'active',
                'visibility' => 'public',
                'discoverable' => true,
            ]);
        } elseif ($profile->trashed()) {
            $profile->restore();
        }
        
        $this->user->load([
            'profile.media.variants',
            'profile.studentProfile.faculty',
            'profile.studentProfile.academicProgram',
            'profile.alumniProfile',
            'profile.advisorProfile',
            'profilePrivacySetting',
        ]);
        $this->refreshFollowState();
    }

    /**
     * Refresh follow aggregate state for the displayed profile.
     */
    public function refreshFollowState(): void
    {
        $viewerId = Auth::id();

        $this->isFollowing = $viewerId !== null
            && $viewerId !== $this->user->id
            && UserFollow::where('follower_id', $viewerId)
                ->where('following_id', $this->user->id)
                ->exists();

        $this->followersCount = UserFollow::where('following_id', $this->user->id)->count();
        $this->followingCount = UserFollow::where('follower_id', $this->user->id)->count();
    }

    /**
     * Get connections count.
     */
    public function getConnectionsCountProperty(): int
    {
        return Connection::where(function ($q) {
            $q->where('user_one_id', $this->user->id)
              ->orWhere('user_two_id', $this->user->id);
        })->where('status', ConnectionStatus::ACTIVE)->count();
    }

    /**
     * Get posts count.
     */
    public function getPostsCountProperty(): int
    {
        return $this->user->posts()
            ->whereIn('status', [PostStatus::PUBLISHED, PostStatus::EDITED])
            ->visibleTo(Auth::user())
            ->count();
    }

    /**
     * Handle Avatar photo uploads (stubbed to prevent auto-saving).
     */
    public function updatedAvatarFile(): void
    {
        // Handled via client-side cropper and saveAvatar action
    }

    /**
     * Save the cropped avatar photo.
     */
    public function saveAvatar(string $caption, bool $shareToFeed, string $duration, ?string $customExpiresAt = null): void
    {
        try {
            if ($this->user->id !== Auth::id()) {
                $this->feedbackMessage = 'Bạn chỉ có thể cập nhật hồ sơ của chính mình.';
                return;
            }

            if (!$this->avatarFile) {
                $this->feedbackMessage = 'Không tìm thấy tệp ảnh đại diện.';
                return;
            }

            $this->validate([
                'avatarFile' => 'image|max:5120', // 5MB limit
            ]);

            $storeAction = app(StoreTemporaryMediaAction::class);
            $attachAction = app(AttachMediaToModelAction::class);

            DB::transaction(function () use ($storeAction, $attachAction, $caption, $shareToFeed, $duration, $customExpiresAt) {
                $oldAvatar = $this->user->profile->avatar()->first();

                // 1. Store the new avatar media
                $media = $storeAction->execute(Auth::user(), $this->avatarFile, 'avatar', ['visibility' => 'public']);
                
                // 2. Attach new avatar to the Profile
                $attachAction->execute(Auth::user(), $this->user->profile, [$media->id], 'avatar');

                // 3. Demote old avatar to history instead of deleting it
                if ($oldAvatar) {
                    $oldAvatar->update(['collection' => 'avatar_history']);
                }

                // 4. Optionally create a feed post
                if ($shareToFeed) {
                    $createPostAction = app(\App\Actions\Posts\CreatePost::class);
                    $bodyText = $caption ?: 'đã cập nhật ảnh đại diện.';
                    
                    $post = $createPostAction->execute(Auth::user(), [
                        'body' => $bodyText,
                        'visibility' => \App\Enums\PostVisibility::VERIFIED_USERS->value,
                    ]);

                    // Store the avatar file again as post_image so it generates proper post variants
                    $postMedia = $storeAction->execute(Auth::user(), $this->avatarFile, 'post_image', ['visibility' => 'public']);
                    $attachAction->execute(Auth::user(), $post, [$postMedia->id], 'post_image');
                }

                // 5. Schedule restore if it is temporary
                if ($duration !== 'permanent') {
                    $expiresAt = match ($duration) {
                        '1_hour' => now()->addHour(),
                        '1_day' => now()->addDay(),
                        '7_days' => now()->addDays(7),
                        'custom' => $customExpiresAt ? \Carbon\Carbon::parse($customExpiresAt) : now()->addDay(),
                        default => now()->addDay(),
                    };

                    \App\Models\TemporaryAvatar::create([
                        'user_id' => Auth::id(),
                        'previous_media_id' => $oldAvatar?->id,
                        'current_media_id' => $media->id,
                        'expires_at' => $expiresAt,
                    ]);
                }
            });

            $this->avatarFile = null;
            $this->user->load('profile.media.variants');
            $this->feedbackMessage = 'Cập nhật ảnh đại diện thành công.';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->feedbackMessage = $e->validator->errors()->first();
            throw $e;
        } catch (\Exception $e) {
            $this->feedbackMessage = 'Lỗi lưu ảnh đại diện: ' . $e->getMessage();
        }
    }

    /**
     * Handle Cover photo uploads.
     */
    public function updatedCoverFile(): void
    {
        try {
            $this->validate([
                'coverFile' => 'image|max:5120', // 5MB limit
            ]);

            $storeAction = app(StoreTemporaryMediaAction::class);
            $attachAction = app(AttachMediaToModelAction::class);
            $deleteAction = app(DeleteMediaAction::class);

            // Delete old polymorphic cover if exists
            if ($this->user->id !== Auth::id()) {
                $this->feedbackMessage = 'Bạn chỉ có thể cập nhật hồ sơ của chính mình.';

                return;
            }

            $oldCover = $this->user->profile->cover()->first();
            if ($oldCover) {
                $deleteAction->execute($oldCover);
            }

            // Store new temporary media (public visibility)
            $media = $storeAction->execute(Auth::user(), $this->coverFile, 'profile_cover', ['visibility' => 'public']);

            // Attach to the Profile
            $attachAction->execute(Auth::user(), $this->user->profile, [$media->id], 'profile_cover');

            $this->user->load('profile.media.variants');
            $this->feedbackMessage = 'Cập nhật ảnh bìa thành công.';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->feedbackMessage = $e->validator->errors()->first();
            throw $e;
        } catch (\Exception $e) {
            $this->feedbackMessage = 'Lỗi tải ảnh lên: ' . $e->getMessage();
        }
    }

    /**
     * Get connection status for display.
     */
    public function getConnectionStatusProperty(): string
    {
        $currentUserId = Auth::id();
        if ($this->user->id === $currentUserId) {
            return 'self';
        }

        // Check blocks
        $isBlocked = BlockedUser::where(function ($q) use ($currentUserId) {
            $q->where('blocker_id', $currentUserId)->where('blocked_id', $this->user->id);
        })->orWhere(function ($q) use ($currentUserId) {
            $q->where('blocker_id', $this->user->id)->where('blocked_id', $currentUserId);
        })->exists();

        if ($isBlocked) {
            return 'blocked';
        }

        // Check active connection
        $userOneId = min($currentUserId, $this->user->id);
        $userTwoId = max($currentUserId, $this->user->id);
        $isConnected = Connection::where('user_one_id', $userOneId)
            ->where('user_two_id', $userTwoId)
            ->where('status', ConnectionStatus::ACTIVE)
            ->exists();

        if ($isConnected) {
            return 'connected';
        }

        // Check greetings
        $hasSent = \App\Models\Greeting::where('sender_id', $currentUserId)
            ->where('receiver_id', $this->user->id)
            ->where('status', \App\Enums\GreetingStatus::PENDING)
            ->exists();

        if ($hasSent) {
            return 'pending_sent';
        }

        $hasReceived = \App\Models\Greeting::where('sender_id', $this->user->id)
            ->where('receiver_id', $currentUserId)
            ->where('status', \App\Enums\GreetingStatus::PENDING)
            ->exists();

        if ($hasReceived) {
            return 'pending_received';
        }

        return 'none';
    }

    /**
     * Block the user.
     */
    public function blockUser(BlockUser $blockUser): void
    {
        try {
            $blockUser->execute(Auth::user(), $this->user, [
                'reason' => 'Blocked from profile page.',
            ]);
            $this->feedbackMessage = 'Đã chặn người dùng này thành công.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Unblock the user.
     */
    public function unblockUser(\App\Actions\Connections\UnblockUser $unblockUser): void
    {
        try {
            $unblockUser->execute(Auth::user(), $this->user);
            $this->feedbackMessage = 'Đã bỏ chặn người dùng này thành công.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Follow the displayed user.
     */
    public function followUser(FollowUser $followUser): void
    {
        try {
            $followUser->execute(Auth::user(), $this->user);

            $this->isFollowing = true;
            $this->followersCount++;
            $this->feedbackMessage = 'Đã theo dõi người dùng này.';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->refreshFollowState();
            $this->feedbackMessage = collect($e->errors())->flatten()->first() ?: 'Không thể theo dõi người dùng này.';
        } catch (\Exception $e) {
            $this->refreshFollowState();
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Unfollow the displayed user.
     */
    public function unfollowUser(UnfollowUser $unfollowUser): void
    {
        try {
            $unfollowUser->execute(Auth::user(), $this->user);

            $this->isFollowing = false;
            $this->followersCount = max(0, $this->followersCount - 1);
            $this->feedbackMessage = 'Đã bỏ theo dõi người dùng này.';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->refreshFollowState();
            $this->feedbackMessage = collect($e->errors())->flatten()->first() ?: 'Không thể bỏ theo dõi người dùng này.';
        } catch (\Exception $e) {
            $this->refreshFollowState();
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Send connection greeting request.
     */
    public function sendGreeting(SendGreeting $sendGreeting): void
    {
        try {
            $sendGreeting->execute(Auth::user(), $this->user);
            $this->feedbackMessage = 'Đã gửi lời mời kết nối thành công.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Cancel connection greeting request.
     */
    public function cancelGreeting(CancelGreeting $cancelGreeting): void
    {
        try {
            $greeting = Greeting::where('sender_id', Auth::id())
                ->where('receiver_id', $this->user->id)
                ->where('status', GreetingStatus::PENDING)
                ->first();

            if ($greeting) {
                $cancelGreeting->execute(Auth::user(), $greeting);
                $this->feedbackMessage = 'Đã hủy yêu cầu kết nối.';
            }
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Accept connection greeting request.
     */
    public function acceptGreeting(AcceptGreeting $acceptGreeting): void
    {
        try {
            $greeting = Greeting::where('sender_id', $this->user->id)
                ->where('receiver_id', Auth::id())
                ->where('status', GreetingStatus::PENDING)
                ->first();

            if ($greeting) {
                $acceptGreeting->execute(Auth::user(), $greeting);
                $this->feedbackMessage = 'Đã chấp nhận lời mời kết nối.';
            }
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Remove existing connection.
     */
    public function removeConnection(RemoveConnection $removeConnection): void
    {
        try {
            $userOneId = min(Auth::id(), $this->user->id);
            $userTwoId = max(Auth::id(), $this->user->id);

            $connection = Connection::where('user_one_id', $userOneId)
                ->where('user_two_id', $userTwoId)
                ->where('status', ConnectionStatus::ACTIVE)
                ->first();

            if ($connection) {
                $removeConnection->execute(Auth::user(), $connection);
                $this->feedbackMessage = 'Đã hủy kết bạn.';
            }
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Determine if I blocked this user.
     */
    public function getIsBlockedByMeProperty(): bool
    {
        return BlockedUser::where('blocker_id', Auth::id())
            ->where('blocked_id', $this->user->id)
            ->exists();
    }

    /**
     * Determine if they blocked me.
     */
    public function getIsBlockingMeProperty(): bool
    {
        return BlockedUser::where('blocker_id', $this->user->id)
            ->where('blocked_id', Auth::id())
            ->exists();
    }

    /**
     * Determine if the viewer is authorized to see this user's online status.
     * Reuses connectionStatus computed property to avoid extra database queries.
     */
    public function getCanSeeOnlineStatusProperty(): bool
    {
        $viewer = Auth::user();
        if (! $viewer) {
            return false;
        }

        if ($this->user->id === $viewer->id) {
            return true;
        }

        $privacy = $this->user->profilePrivacySetting;
        $visibility = $privacy ? $privacy->online_status_visibility : 'connections';

        if ($visibility === 'nobody') {
            return false;
        }

        $isConnected = $this->connectionStatus === 'connected';

        if ($visibility === 'connections') {
            return $isConnected;
        }

        if ($visibility === 'mutual_connections') {
            if ($isConnected) {
                return true;
            }

            return $this->user->canSeeOnlineStatus($viewer);
        }

        return false;
    }

    /**
     * Report user.
     */
    public function reportUser(): void
    {
        $this->feedbackMessage = 'Báo cáo người dùng thành công. Ban quản trị sẽ sớm xem xét xử lý.';
    }

    /**
     * Toggle post like.
     */
    public function toggleLike(int $postId, TogglePostLike $togglePostLike): void
    {
        try {
            $post = Post::findOrFail($postId);
            $togglePostLike->execute(Auth::user(), $post);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Toggle post save.
     */
    public function toggleSave(int $postId, TogglePostSave $togglePostSave): void
    {
        try {
            $post = Post::findOrFail($postId);
            $togglePostSave->execute(Auth::user(), $post);
            $this->feedbackMessage = 'Đã cập nhật lưu bài viết.';
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Toggle repost.
     */
    public function toggleRepost(int $postId, TogglePostRepost $togglePostRepost): void
    {
        $post = Post::findOrFail($postId);

        try {
            $isReposted = $togglePostRepost->execute(Auth::user(), $post);
            $this->feedbackMessage = $isReposted ? 'Đã đăng lại bài viết.' : 'Đã hủy đăng lại bài viết.';
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Hide post.
     */
    public function hidePost(int $postId, HidePostFromFeed $hidePostFromFeed): void
    {
        try {
            $post = Post::findOrFail($postId);
            $hidePostFromFeed->execute(Auth::user(), $post);
            $this->feedbackMessage = 'Đã ẩn bài viết khỏi bảng tin của bạn.';
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Start post editing.
     */
    public function startEdit(int $postId): void
    {
        $post = Post::findOrFail($postId);

        if (! Gate::allows('update', $post)) {
            $this->feedbackMessage = 'Bạn không có quyền chỉnh sửa bài viết này.';
            return;
        }

        $this->editingPostId = $postId;
        $this->editingBody = $post->body;
        $this->feedbackMessage = null;
    }

    /**
     * Save post edits.
     */
    public function saveEdit(UpdatePost $updatePost): void
    {
        if (! $this->editingPostId) {
            return;
        }

        try {
            $post = Post::findOrFail($this->editingPostId);

            $this->validate([
                'editingBody' => 'required|string|max:3000',
            ]);

            $updatePost->execute(Auth::user(), $post, [
                'body' => $this->editingBody,
            ]);

            $this->editingPostId = null;
            $this->editingBody = '';
            $this->feedbackMessage = 'Đã cập nhật bài viết thành công.';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('editingBody', $e->getMessage());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
            $this->editingPostId = null;
            $this->editingBody = '';
        }
    }

    /**
     * Cancel editing.
     */
    public function cancelEdit(): void
    {
        $this->editingPostId = null;
        $this->editingBody = '';
    }

    /**
     * Trigger customized delete modal.
     */
    public function openDeleteModal(int $postId): void
    {
        $this->deletingPostId = $postId;
        $this->showDeleteModal = true;
    }

    /**
     * Execute post delete.
     */
    public function executeDelete(DeletePost $deletePost): void
    {
        if (! $this->deletingPostId) {
            return;
        }

        try {
            $post = Post::findOrFail($this->deletingPostId);
            $deletePost->execute(Auth::user(), $post);
            $this->feedbackMessage = 'Đã xóa bài viết thành công.';
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
        }

        $this->deletingPostId = null;
        $this->showDeleteModal = false;
    }

    /**
     * Open report modal.
     */
    public function openReport(int $postId): void
    {
        $this->reportingPost = Post::findOrFail($postId);
        $this->reportReason = 'spam';
        $this->reportDescription = '';
        $this->showReportModal = true;
        $this->feedbackMessage = null;
        $this->resetErrorBag();
    }

    /**
     * Submit report.
     */
    public function submitReport(CreateReport $createReport): void
    {
        if (! $this->reportingPost) {
            return;
        }

        try {
            $createReport->execute(Auth::user(), $this->reportingPost, [
                'reason' => $this->reportReason,
                'description' => $this->reportDescription,
            ]);

            $this->showReportModal = false;
            $this->reportingPost = null;
            $this->feedbackMessage = 'Báo cáo của bạn đã được gửi.';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('report', $e->getMessage());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->showReportModal = false;
            $this->reportingPost = null;
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Close report modal.
     */
    public function closeReport(): void
    {
        $this->showReportModal = false;
        $this->reportingPost = null;
    }

    /**
     * Start post sharing flow.
     */
    public function startShare(int $postId): void
    {
        $post = Post::findOrFail($postId);

        if (! Gate::allows('share', $post)) {
            $this->feedbackMessage = 'Bạn không có quyền chia sẻ bài viết này.';
            return;
        }

        $this->sharingPostId = $postId;
        $this->shareSearch = '';
        $this->selectedShareUserIds = [];
        $this->shareOptionalMessage = '';
        $this->showShareModal = true;
    }

    /**
     * Toggle a recipient in the share modal.
     */
    public function toggleShareRecipient(int $userId): void
    {
        $selectedUserIds = collect($this->selectedShareUserIds)
            ->map(fn ($selectedUserId) => (int) $selectedUserId)
            ->unique()
            ->values();

        if ($selectedUserIds->contains($userId)) {
            $this->selectedShareUserIds = $selectedUserIds
                ->reject(fn ($selectedUserId) => $selectedUserId === $userId)
                ->values()
                ->all();

            return;
        }

        $this->selectedShareUserIds = $selectedUserIds
            ->push($userId)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Execute post sharing.
     */
    public function executeShare(
        SendSharedPostMessage $sendSharedPostMessage,
        FindOrCreateDirectConversation $findOrCreateDirectConversation
    ): void {
        $selectedUserIds = collect($this->selectedShareUserIds)
            ->map(fn ($selectedUserId) => (int) $selectedUserId)
            ->filter()
            ->unique()
            ->values();

        if (! $this->sharingPostId || $selectedUserIds->isEmpty()) {
            return;
        }

        $post = Post::findOrFail($this->sharingPostId);
        $sentCount = 0;
        $failedRecipients = [];

        foreach ($selectedUserIds as $recipientId) {
            try {
                $recipient = User::findOrFail($recipientId);
                $conversation = $findOrCreateDirectConversation->execute(Auth::user(), $recipient);
                $sendSharedPostMessage->execute(Auth::user(), $conversation, $post, [
                    'body' => $this->shareOptionalMessage ?: null,
                ]);
                $sentCount++;
            } catch (\Exception $e) {
                $failedRecipients[] = User::find($recipientId)?->name ?? "ID {$recipientId}";
            }
        }

        if ($sentCount > 0) {
            $this->showShareModal = false;
            $this->sharingPostId = null;
            $this->selectedShareUserIds = [];
            $this->shareOptionalMessage = '';
            $this->shareSearch = '';

            if ($failedRecipients === []) {
                $this->feedbackMessage = "Đã chia sẻ bài viết qua tin nhắn cho {$sentCount} người nhận.";
                return;
            }

            $this->feedbackMessage = "Đã gửi cho {$sentCount} người nhận. Không gửi được cho: ".implode(', ', $failedRecipients).'.';
            return;
        }

        $this->feedbackMessage = 'Không gửi được bài viết cho người nhận đã chọn: '.implode(', ', $failedRecipients).'.';
    }

    /**
     * Get connections list for sharing posts.
     */
    public function getShareConnections(): \Illuminate\Support\Collection
    {
        $userId = Auth::id();
        $search = trim($this->shareSearch);

        $query = Connection::where(function ($q) use ($userId) {
                $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
            })
            ->where('status', ConnectionStatus::ACTIVE)
            ->with(['userOne.profile', 'userTwo.profile']);

        $connections = $query->get()->map(function ($connection) use ($userId) {
            return $connection->user_one_id === $userId ? $connection->userTwo : $connection->userOne;
        });

        if (! empty($search)) {
            $connections = $connections->filter(function ($user) use ($search) {
                return \Illuminate\Support\Str::contains(strtolower($user->name), strtolower($search)) ||
                       ($user->profile && \Illuminate\Support\Str::contains(strtolower($user->profile->display_name), strtolower($search)));
            });
        }

        return $connections->values();
    }

    /**
     * Get safe avatar URL.
     */
    public function getAvatarUrlProperty(): string
    {
        return \App\Support\Media\MediaUrlResolver::avatarUrl($this->user, 'display') ?: asset('images/default-avatar.svg');
    }

    /**
     * Get safe cover URL.
     */
    public function getCoverUrlProperty(): ?string
    {
        return \App\Support\Media\MediaUrlResolver::coverUrl($this->user, 'desktop');
    }

    public function with(): array
    {
        $profilePosts = collect();
        $profileComments = collect();
        $profileMedia = collect();
        $profileReposts = collect();

        $isOwn = $this->user->id === Auth::id();
        $targetPrivacy = $this->user->profilePrivacySetting;
        $isPrivateProfile = $targetPrivacy && in_array($targetPrivacy->profile_visibility, ['connections_only', 'private'], true);
        $isConnected = $this->connectionStatus === 'connected';
        $canViewContent = $isOwn || !$isPrivateProfile || $isConnected;

        if ($canViewContent) {
            if ($this->activeTab === 'posts') {
                $profilePosts = $this->user->posts()
                    ->with('media.variants')
                    ->withCount([
                        'likes',
                        'reposts',
                        'comments as published_comments_count' => function ($query): void {
                            $query->where('status', \App\Enums\CommentStatus::PUBLISHED->value);
                        },
                    ])
                    ->withCount([
                        'likes as liked_by_current_user_count' => function ($query): void {
                            $query->where('user_id', Auth::id());
                        },
                        'saves as saved_by_current_user_count' => function ($query): void {
                            $query->where('user_id', Auth::id());
                        },
                        'reposts as reposted_by_current_user_count' => function ($query): void {
                            $query->where('user_id', Auth::id());
                        },
                    ])
                    ->whereIn('status', [PostStatus::PUBLISHED, PostStatus::EDITED])
                    ->visibleTo(Auth::user())
                    ->latest()
                    ->take(10)
                    ->get();
            } elseif ($this->activeTab === 'replies') {
                $profileComments = $this->user->comments()
                    ->with('post.user')
                    ->latest()
                    ->take(10)
                    ->get();
            } elseif ($this->activeTab === 'media') {
                $profileMedia = Media::query()
                    ->with('variants')
                    ->where('user_id', $this->user->id)
                    ->where('collection', 'post_image')
                    ->where('status', 'ready')
                    ->latest()
                    ->take(30)
                    ->get();
            } elseif ($this->activeTab === 'reposts') {
                $profileReposts = $this->user->postReposts()
                    ->with([
                        'user.profile',
                        'post.user.profile',
                        'post.media.variants',
                        'post' => function ($query) {
                            $query->withCount([
                                'likes',
                                'reposts',
                                'comments as published_comments_count' => function ($q): void {
                                    $q->where('status', \App\Enums\CommentStatus::PUBLISHED->value);
                                },
                            ])->withCount([
                                'likes as liked_by_current_user_count' => function ($q): void {
                                    $q->where('user_id', Auth::id());
                                },
                                'saves as saved_by_current_user_count' => function ($q): void {
                                    $q->where('user_id', Auth::id());
                                },
                                'reposts as reposted_by_current_user_count' => function ($q): void {
                                    $q->where('user_id', Auth::id());
                                },
                            ]);
                        }
                    ])
                    ->whereHas('post', function ($query): void {
                        $query->whereIn('status', [PostStatus::PUBLISHED, PostStatus::EDITED])
                            ->visibleTo(Auth::user());
                    })
                    ->latest()
                    ->take(10)
                    ->get();
            }
        }

        return [
            'profilePosts' => $profilePosts,
            'profileComments' => $profileComments,
            'profileMedia' => $profileMedia,
            'profileReposts' => $profileReposts,
        ];
    }
}; ?>

<div>
    {{-- Feedback Message Toast --}}
    @if ($feedbackMessage)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => { show = false; $wire.set('feedbackMessage', null); }, 3000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-20 left-4 right-4 md:left-auto md:right-8 md:w-96 z-50 bg-slate-900 text-white rounded-xl shadow-xl px-4 py-3 border border-slate-800 flex items-center gap-3"
        >
            <x-ui.icon name="info" size="sm" class="text-ue-brand flex-shrink-0" />
            <span class="text-xxs font-semibold flex-1 leading-normal">{{ $feedbackMessage }}</span>
            <button @click="show = false" class="text-slate-400 hover:text-white transition-colors">
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>
    @endif

    <div class="py-6 px-4 max-w-4xl mx-auto space-y-8">

    {{-- Graceful blocked states handling --}}
    @if ($this->isBlockingMe)
        <div class="py-16 text-center bg-white border border-slate-200 rounded-3xl p-8 max-w-lg mx-auto shadow-2xs space-y-4 select-none">
            <div class="w-16 h-16 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto text-slate-400">
                <x-ui.icon name="eye-off" size="lg" />
            </div>
            <div class="space-y-2">
                <h2 class="text-base font-bold text-slate-800">Hồ sơ không khả dụng</h2>
                <p class="text-xxs text-slate-400 leading-relaxed max-w-md mx-auto">
                    Tài khoản này hiện không khả dụng hoặc bạn không có quyền xem thông tin hồ sơ này.
                </p>
            </div>
            <div class="pt-3">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-850 text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-2xs transition-colors">
                    <x-ui.icon name="home" size="xs" />
                    Quay lại Trang chủ
                </a>
            </div>
        </div>
    @elseif ($this->isBlockedByMe)
        <div class="py-16 text-center bg-white border border-slate-200 rounded-3xl p-8 max-w-lg mx-auto shadow-2xs space-y-4">
            <div class="w-16 h-16 rounded-full bg-red-50 border border-red-100 flex items-center justify-center mx-auto text-red-500">
                <x-ui.icon name="shield" size="lg" />
            </div>
            <div class="space-y-2">
                <h2 class="text-base font-bold text-slate-850">Bạn đã chặn tài khoản này</h2>
                <p class="text-xxs text-slate-400 leading-relaxed max-w-md mx-auto">
                    Mọi bài đăng và thông tin học tập của {{ $user->profile?->display_name ?? $user->name }} đang bị ẩn để bảo vệ trải nghiệm của bạn. Bạn có thể bỏ chặn để khôi phục quyền tương tác.
                </p>
            </div>
            <div class="pt-3">
                <button 
                    type="button" 
                    wire:click="unblockUser"
                    wire:loading.attr="disabled"
                    wire:target="unblockUser"
                    class="bg-slate-900 hover:bg-slate-850 text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-2xs transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="unblockUser">Bỏ chặn tài khoản</span>
                    <span wire:loading wire:target="unblockUser">Đang xử lý...</span>
                </button>
            </div>
        </div>
    @else
        {{-- Normal Profile --}}
        @php
            $isOwn = $user->id === Auth::id();
            $targetPrivacy = $user->profilePrivacySetting;
            
            $isPrivateProfile = $targetPrivacy && in_array($targetPrivacy->profile_visibility, ['connections_only', 'private'], true);
            $isConnected = $this->connectionStatus === 'connected';
            $canViewContent = $isOwn || !$isPrivateProfile || $isConnected;

            $showFaculty = $canViewContent && ($isOwn || ($targetPrivacy ? (bool)$targetPrivacy->show_faculty : true));
            $showMajor = $canViewContent && ($isOwn || ($targetPrivacy ? (bool)$targetPrivacy->show_major : true));
            $showCohort = $canViewContent && ($isOwn || ($targetPrivacy ? (bool)$targetPrivacy->show_cohort : true));
            $showClassCode = $canViewContent && ($isOwn || ($targetPrivacy ? (bool)$targetPrivacy->show_class_code : false));
            $showBio = $canViewContent && ($isOwn || ($targetPrivacy ? (bool)$targetPrivacy->show_bio : true));
        @endphp

        {{-- MOBILE HEADER LAYOUT (No Card, Instagram Threads style layout) --}}
        <div class="block sm:hidden bg-transparent space-y-4 px-2 relative z-10">
            <div class="flex items-start justify-between gap-4">
                {{-- Info Column --}}
                <div class="space-y-1.5 min-w-0 flex-1">
                    <h1 class="text-xl font-extrabold text-slate-900 leading-tight flex items-center gap-1.5">
                        <span class="truncate">{{ $user->profile?->display_name ?? $user->name }}</span>
                        @if ($user->isActive())
                            <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                        @endif
                    </h1>
                    
                    <div class="flex items-center gap-2 text-xxs text-slate-500 font-medium">
                        <span>{{ '@' . ($user->username ?? Str::slug($user->name, '')) }}</span>
                        <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-605 font-bold uppercase text-[8px] tracking-wide">
                            @if (($user->profile?->role_type ?? 'student') === 'student') Sinh viên
                            @elseif (in_array(($user->profile?->role_type ?? ''), ['teacher', 'advisor'], true)) Giảng viên
                            @elseif (($user->profile?->role_type ?? '') === 'alumni') Cựu sinh viên
                            @else Thành viên
                            @endif
                        </span>
                    </div>

                    @if ($showFaculty && $user->profile?->faculty)
                        <div class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider pt-0.5">
                            Khoa: {{ $user->profile?->faculty }}
                        </div>
                    @endif
                </div>

                {{-- Avatar Column --}}
                <div class="relative w-16 h-16 rounded-full overflow-visible flex-shrink-0">
                    <x-ui.avatar :user="$user" size="lg" class="w-full h-full border border-slate-100 shadow-2xs" />
                    
                    @if ($isOwn)
                        <label class="absolute -bottom-1 -right-1 bg-white text-slate-800 w-6 h-6 rounded-full flex items-center justify-center border border-slate-200 shadow-xs cursor-pointer z-10">
                            <x-ui.icon name="plus" size="xxs" />
                            <input type="file" id="avatar-upload-input-1" class="hidden" accept="image/*" @change="window.dispatchEvent(new CustomEvent('avatar-selected', { detail: { files: $event.target.files } }))" />
                        </label>
                    @else
                        @if (!$isFollowing)
                            <button
                                type="button"
                                wire:click="followUser"
                                wire:loading.attr="disabled"
                                wire:target="followUser"
                                class="absolute -bottom-1 -right-1 bg-slate-950 text-white w-6 h-6 rounded-full flex items-center justify-center border border-white hover:scale-110 active:scale-95 transition-all shadow-xs z-10"
                                title="Theo dõi {{ $user->name }}"
                            >
                                <x-ui.icon name="plus" size="xxs" />
                            </button>
                        @endif
                    @endif

                    @if ($user->isOnline() && $this->canSeeOnlineStatus)
                        <span class="absolute bottom-0 right-0 block h-3.5 w-3.5 rounded-full bg-green-500 border-2 border-white ring-1 ring-slate-100" title="Trực tuyến"></span>
                    @endif
                </div>
            </div>

            {{-- Bio --}}
            @if ($showBio)
                <div class="text-xs text-slate-700 leading-relaxed max-w-xl">
                    @if ($user->profile?->bio)
                        <p>{{ $user->profile?->bio }}</p>
                    @elseif ($isOwn)
                        <p class="italic text-slate-400">Chưa cập nhật giới thiệu cá nhân.</p>
                    @endif
                </div>
            @endif

            {{-- Stats / Followers --}}
            <div class="flex items-center gap-1.5 text-[11px] text-slate-400 select-none">
                <span>{{ $followersCount }} người theo dõi</span>
                @if ($this->connectionsCount > 0)
                    <span>•</span>
                    <span>{{ $this->connectionsCount }} bạn bè</span>
                @endif
                <span>•</span>
                <span>{{ $this->postsCount }} bài viết</span>
            </div>

            {{-- Action Buttons Row --}}
            @if ($isOwn)
                <div class="grid grid-cols-2 gap-2.5 pt-1">
                    <a href="{{ route('profile.edit') }}" class="w-full bg-white hover:bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold py-2 rounded-xl transition-colors flex items-center justify-center gap-1.5 shadow-3xs">
                        <x-ui.icon name="edit" size="xs" class="text-slate-500" />
                        Chỉnh sửa trang cá nhân
                    </a>
                    <button 
                        type="button" 
                        @click="navigator.clipboard.writeText(window.location.href); $wire.set('feedbackMessage', 'Đã sao chép liên kết hồ sơ');" 
                        class="w-full bg-white hover:bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold py-2 rounded-xl transition-colors flex items-center justify-center gap-1.5 shadow-3xs"
                    >
                        <x-ui.icon name="share-2" size="xs" class="text-slate-500" />
                        Chia sẻ trang cá nhân
                    </button>
                </div>
            @else
                <div class="flex items-center gap-2 pt-1">
                    {{-- Follow / Unfollow --}}
                    @if ($isFollowing)
                        <button
                            type="button"
                            wire:click="unfollowUser"
                            wire:loading.attr="disabled"
                            wire:target="unfollowUser"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white py-2 text-xs font-bold text-slate-800 shadow-3xs transition-colors hover:bg-slate-50 disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="unfollowUser">Đang theo dõi</span>
                            <span wire:loading wire:target="unfollowUser">Đang xử lý...</span>
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="followUser"
                            wire:loading.attr="disabled"
                            wire:target="followUser"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-950 py-2 text-xs font-bold text-white shadow-3xs transition-colors hover:bg-slate-900 disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="followUser">Theo dõi</span>
                            <span wire:loading wire:target="followUser">Đang xử lý...</span>
                        </button>
                    @endif

                    {{-- Message --}}
                    @if ($this->connectionStatus === 'connected')
                        <a href="{{ route('messages.index', ['conversation' => \App\Models\Conversation::where('conversation_type', \App\Enums\ConversationType::DIRECT)->whereHas('participants', function($q) { $q->where('user_id', $this->user->id); })->first()?->id]) }}" class="flex-1 bg-white hover:bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold py-2 rounded-xl shadow-3xs transition-colors flex items-center justify-center gap-1">
                            <x-ui.icon name="message-square" size="xs" class="text-slate-500" />
                            Nhắn tin
                        </a>
                    @else
                        <button
                            type="button"
                            wire:click="sendGreeting"
                            wire:loading.attr="disabled"
                            wire:target="sendGreeting"
                            class="flex-1 bg-white hover:bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold py-2 rounded-xl shadow-3xs transition-colors flex items-center justify-center gap-1"
                        >
                            <x-ui.icon name="message-square" size="xs" class="text-slate-500" />
                            Nhắn tin
                        </button>
                    @endif

                    {{-- Friend Connection Status Icon Button --}}
                    @if ($this->connectionStatus === 'connected')
                        <div class="relative" x-data="{ openFriendMenu: false }" @click.away="openFriendMenu = false">
                            <button
                                type="button"
                                @click="openFriendMenu = !openFriendMenu"
                                class="w-12 h-9 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 hover:bg-slate-200 hover:text-slate-900 transition-colors flex items-center justify-center flex-shrink-0 shadow-3xs gap-0.5"
                                title="Bạn bè"
                            >
                                <x-ui.icon name="user-check" size="sm" />
                                <x-ui.icon name="chevron-down" size="xxs" class="text-slate-400 -ml-0.5" />
                            </button>
                            <div x-show="openFriendMenu" x-transition class="absolute right-0 mt-1 bg-white border border-slate-150 rounded-xl shadow-lg py-1 z-30 w-36 text-left" style="display: none;">
                                <button
                                    type="button"
                                    wire:click="removeConnection"
                                    wire:loading.attr="disabled"
                                    wire:target="removeConnection"
                                    class="w-full text-left px-3 py-2 text-xxs font-semibold text-red-600 hover:bg-red-50 flex items-center gap-1.5 transition-colors disabled:opacity-60"
                                >
                                    <x-ui.icon name="user-minus" size="xs" class="text-red-400" />
                                    <span>Hủy kết bạn</span>
                                </button>
                            </div>
                        </div>
                    @elseif ($this->connectionStatus === 'pending_sent')
                        <button
                            type="button"
                            wire:click="cancelGreeting"
                            wire:loading.attr="disabled"
                            wire:target="cancelGreeting"
                            class="w-9 h-9 rounded-xl bg-slate-50 text-slate-550 border border-slate-200 hover:bg-slate-100 hover:text-slate-700 transition-colors flex items-center justify-center flex-shrink-0 shadow-3xs"
                            title="Hủy lời mời kết nối"
                        >
                            <x-ui.icon name="clock" size="sm" />
                        </button>
                    @elseif ($this->connectionStatus === 'pending_received')
                        <button
                            type="button"
                            wire:click="acceptGreeting"
                            wire:loading.attr="disabled"
                            wire:target="acceptGreeting"
                            class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 border border-blue-100 hover:bg-blue-100 hover:text-blue-800 transition-colors flex items-center justify-center animate-pulse flex-shrink-0 shadow-3xs"
                            title="Đồng ý kết nối"
                        >
                            <x-ui.icon name="user-plus" size="sm" />
                        </button>
                    @elseif ($this->connectionStatus === 'blocked')
                        <span class="px-2.5 h-9 rounded-xl bg-slate-50 text-slate-400 border border-slate-200 text-[10px] font-bold flex items-center justify-center flex-shrink-0">
                            Đã chặn
                        </span>
                    @else
                        <button
                            type="button"
                            wire:click="sendGreeting"
                            wire:loading.attr="disabled"
                            wire:target="sendGreeting"
                            class="w-9 h-9 rounded-xl bg-white text-slate-850 border border-slate-200 hover:bg-slate-50 transition-colors flex items-center justify-center flex-shrink-0 shadow-3xs"
                            title="Gửi lời mời kết nối"
                        >
                            <x-ui.icon name="user-plus" size="sm" />
                        </button>
                    @endif

                    {{-- More Options --}}
                    <div class="relative" x-data="{ openOptions: false }" @click.away="openOptions = false">
                        <button @click="openOptions = !openOptions" class="w-9 h-9 text-slate-450 hover:text-slate-655 hover:bg-slate-50 border border-slate-200 rounded-xl transition-colors flex items-center justify-center shadow-3xs">
                            <x-ui.icon name="more-horizontal" size="sm" />
                        </button>
                        <div x-show="openOptions" x-transition class="absolute right-0 mt-1 bg-white border border-slate-150 rounded-xl shadow-lg py-1 z-30 w-40 text-left" style="display: none;">
                            <button type="button" wire:click="blockUser" wire:loading.attr="disabled" wire:target="blockUser" class="w-full text-left px-3 py-2 text-xxs font-semibold text-red-600 hover:bg-red-50 flex items-center gap-1.5 transition-colors">
                                <x-ui.icon name="shield-x" size="xs" class="text-red-400" />
                                <span>Chặn thành viên</span>
                            </button>
                            <button type="button" wire:click="reportUser" wire:loading.attr="disabled" wire:target="reportUser" class="w-full text-left px-3 py-2 text-xxs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors">
                                <x-ui.icon name="flag" size="xs" class="text-slate-400" />
                                <span>Báo cáo tài khoản</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- DESKTOP HEADER LAYOUT (Keep Card, Cover Image) --}}
        <div class="hidden sm:block relative z-10 bg-white border border-slate-150 rounded-3xl shadow-2xs">
            {{-- Cover Photo Section --}}
            <div class="relative h-44 sm:h-64 w-full bg-gradient-to-tr from-slate-200 to-slate-100 overflow-hidden rounded-t-[22px]">
                @if ($coverFile)
                    <img src="{{ $coverFile->temporaryUrl() }}" alt="Cover picture preview" class="w-full h-full object-cover" />
                @elseif ($this->coverUrl)
                    <img src="{{ $this->coverUrl }}" alt="Cover picture" class="w-full h-full object-cover" />
                @else
                    <div class="w-full h-full bg-slate-900 relative overflow-hidden flex items-center justify-center select-none">
                        {{-- Futuristic grid overlay --}}
                        <div class="absolute inset-0 opacity-15 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px]"></div>
                        
                        {{-- Contained ambient color layer. Keep it inside the cover to avoid mobile overflow. --}}
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_24%_20%,rgba(59,130,246,0.28)_0%,transparent_34%),radial-gradient(circle_at_78%_88%,rgba(99,102,241,0.2)_0%,transparent_38%)]"></div>
                        
                        {{-- Sophisticated tech design overlay --}}
                        <svg class="absolute w-full h-full text-blue-500/10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 300" fill="none">
                            <circle cx="400" cy="150" r="120" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 8" />
                            <circle cx="400" cy="150" r="180" stroke="currentColor" stroke-width="0.8" />
                            <line x1="0" y1="150" x2="800" y2="150" stroke="currentColor" stroke-width="0.8" stroke-dasharray="10 10" />
                            <line x1="400" y1="0" x2="400" y2="300" stroke="currentColor" stroke-width="0.8" stroke-dasharray="10 10" />
                            <path d="M 250 80 L 300 150 L 350 150" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            <path d="M 550 220 L 500 150 L 450 150" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>

                        {{-- Branding Header --}}
                        <div class="relative z-10 flex flex-col items-center gap-1.5 text-center">
                            <span class="text-white/35 font-extrabold tracking-[0.25em] text-[10px] sm:text-xs uppercase">UEConnect</span>
                            <span class="text-white/15 font-semibold text-[8px] sm:text-[9px] tracking-wide max-w-xs sm:max-w-md px-4 leading-normal">Hệ thống mạng xã hội học thuật tích hợp & kết nối sinh viên</span>
                        </div>
                    </div>
                @endif

                {{-- Change Cover Button (Own Profile) --}}
                @if ($isOwn)
                    <label class="absolute bottom-3 right-3 bg-slate-900/60 hover:bg-slate-900/80 text-white p-2 sm:px-3 sm:py-1.5 rounded-xl cursor-pointer backdrop-blur-xs transition-colors flex items-center gap-1.5 text-[10px] font-bold z-10">
                        <x-ui.icon name="camera" size="xs" />
                        <span class="hidden sm:inline">Thay đổi ảnh bìa</span>
                        <input type="file" wire:model="coverFile" class="hidden" accept="image/*" />
                    </label>
                    @error('coverFile')
                        <span class="absolute bottom-14 right-3 bg-red-500 text-white text-[10px] font-semibold px-2 py-1 rounded-md shadow-sm z-20">
                            {{ $message }}
                        </span>
                    @enderror
                @endif
            </div>

            {{-- Profile Metadata Area --}}
            <div class="relative px-4 sm:px-6 pb-6 pt-0 md:pt-6 text-center md:text-left">
                {{-- Round Avatar Photo --}}
                <div class="relative -mt-14 mx-auto md:absolute md:-top-16 md:left-6 md:mt-0 md:mx-0 w-28 h-28 sm:w-32 sm:h-32 rounded-full border-4 border-white bg-slate-50 shadow-md group">
                    <div class="w-full h-full rounded-full overflow-hidden">
                        <x-ui.avatar :user="$user" size="2xl" class="w-full h-full border-none rounded-none shadow-none text-2xl font-bold bg-slate-100 flex items-center justify-center" />
                    </div>

                    @if ($isOwn)
                        <label class="absolute inset-0 rounded-full bg-slate-900/40 opacity-0 group-hover:opacity-100 cursor-pointer flex flex-col items-center justify-center text-white text-[9px] font-bold transition-opacity z-10">
                            <x-ui.icon name="camera" size="sm" />
                            Đổi ảnh
                            <input type="file" id="avatar-upload-input-2" class="hidden" accept="image/*" @change="window.dispatchEvent(new CustomEvent('avatar-selected', { detail: { files: $event.target.files } }))" />
                        </label>
                    @endif

                    @if ($user->isOnline() && $this->canSeeOnlineStatus)
                        <span class="absolute bottom-1 right-1 block h-6 w-6 rounded-full bg-green-500 border-4 border-white ring-1 ring-slate-100" title="Trực tuyến"></span>
                    @endif

                    @error('avatarFile')
                        <div class="absolute inset-0 bg-red-950/80 text-white text-[9px] font-semibold flex flex-col items-center justify-center p-2 text-center z-20 rounded-full">
                            <x-ui.icon name="alert-triangle" size="xs" class="text-red-400 mb-0.5" />
                            <span class="leading-normal">{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                {{-- Profile Info Section --}}
                <div class="space-y-3.5 w-full mt-4 md:mt-0 md:pl-40 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 justify-center md:justify-start">
                        <h1 class="text-base sm:text-lg font-bold text-slate-800 flex items-center justify-center md:justify-start gap-1.5 min-w-0">
                            <span class="truncate">{{ $user->profile?->display_name ?? $user->name }}</span>
                            @if ($user->isActive())
                                <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                            @endif
                        </h1>

                        {{-- Role Badge --}}
                        <span class="inline-flex self-center px-2 py-0.5 rounded-full text-[9px] font-bold tracking-wide uppercase bg-slate-100 text-slate-500">
                            @if (($user->profile?->role_type ?? 'student') === 'student') Sinh viên
                            @elseif (in_array(($user->profile?->role_type ?? ''), ['teacher', 'advisor'], true)) Giảng viên
                            @elseif (($user->profile?->role_type ?? '') === 'alumni') Cựu sinh viên
                            @else Thành viên
                            @endif
                        </span>
                    </div>

                    <div class="flex items-center justify-center md:justify-start gap-6 py-1 text-slate-700 select-none text-xxs font-medium">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-slate-800">{{ $this->postsCount }}</span>
                            <span class="text-slate-400">Bài viết</span>
                        </div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-slate-800">{{ $this->connectionsCount }}</span>
                            <span class="text-slate-400">Bạn bè</span>
                        </div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-slate-800">{{ $followersCount }}</span>
                            <span class="text-slate-400">Người theo dõi</span>
                        </div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold text-slate-800">{{ $followingCount }}</span>
                            <span class="text-slate-400">Đang theo dõi</span>
                        </div>
                    </div>

                    {{-- Credentials & Bio --}}
                    <div class="space-y-1 text-slate-500 text-xxs font-medium max-w-xl mx-auto md:mx-0">
                        <p class="text-slate-450 tracking-wide uppercase text-[9px] font-bold">
                            @if ($showFaculty && $user->profile?->faculty)
                                Khoa: {{ $user->profile?->faculty }}
                            @endif
                        </p>
                        @if ($showBio)
                            @if ($user->profile?->bio)
                                <p class="leading-relaxed text-slate-655">{{ $user->profile?->bio }}</p>
                            @else
                                <p class="italic text-slate-350">Chưa cập nhật giới thiệu cá nhân.</p>
                            @endif
                        @endif
                    </div>

                {{-- Action Buttons Row --}}
                <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-center md:justify-start gap-3 w-full flex-wrap">
                    @if ($isOwn)
                        <a href="{{ route('profile.edit') }}" class="flex-1 sm:flex-none bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xxs font-bold px-4 py-2 rounded-xl transition-colors flex items-center justify-center gap-1.5 shadow-2xs">
                            <x-ui.icon name="edit" size="xs" />
                            Chỉnh sửa hồ sơ
                        </a>
                    @else
                        {{-- Follow / Unfollow --}}
                        @if ($isFollowing)
                            <button
                                type="button"
                                wire:click="unfollowUser"
                                wire:loading.attr="disabled"
                                wire:target="unfollowUser"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 rounded-xl border border-slate-250 bg-white px-5 py-2 text-xxs font-bold text-slate-700 shadow-2xs transition-colors hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <span wire:loading.remove wire:target="unfollowUser">Đang theo dõi</span>
                                <span wire:loading wire:target="unfollowUser">Đang xử lý...</span>
                            </button>
                        @else
                            <button
                                type="button"
                                wire:click="followUser"
                                wire:loading.attr="disabled"
                                wire:target="followUser"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-900 px-5 py-2 text-xxs font-bold text-white shadow-2xs transition-colors hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <span wire:loading.remove wire:target="followUser">Theo dõi</span>
                                <span wire:loading wire:target="followUser">Đang xử lý...</span>
                            </button>
                        @endif

                        {{-- Message --}}
                        @if ($this->connectionStatus === 'connected')
                            <a href="{{ route('messages.index', ['conversation' => \App\Models\Conversation::where('conversation_type', \App\Enums\ConversationType::DIRECT)->whereHas('participants', function($q) { $q->where('user_id', $this->user->id); })->first()?->id]) }}" class="flex-1 sm:flex-none bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xxs font-bold px-5 py-2 rounded-xl shadow-2xs transition-colors flex items-center justify-center gap-1.5">
                                <x-ui.icon name="message-square" size="xs" />
                                Nhắn tin
                            </a>
                        @else
                            {{-- Message button acts as connect (send greeting) when not connected --}}
                            <button
                                type="button"
                                wire:click="sendGreeting"
                                wire:loading.attr="disabled"
                                wire:target="sendGreeting"
                                class="flex-1 sm:flex-none bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xxs font-bold px-5 py-2 rounded-xl shadow-2xs transition-colors flex items-center justify-center gap-1.5"
                            >
                                <x-ui.icon name="message-square" size="xs" />
                                Nhắn tin
                            </button>
                        @endif

                        {{-- Connection status --}}
                        @if ($this->connectionStatus === 'connected')
                            {{-- Friends dropdown menu --}}
                            <div class="relative" x-data="{ openFriendMenu: false }" @click.away="openFriendMenu = false">
                                <button
                                    type="button"
                                    @click="openFriendMenu = !openFriendMenu"
                                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 text-slate-800 border border-slate-200 hover:bg-slate-200 hover:text-slate-900 transition-colors text-xxs font-bold"
                                    title="Bạn bè"
                                >
                                    <x-ui.icon name="user-check" size="xs" />
                                    <span>Bạn bè</span>
                                    <x-ui.icon name="chevron-down" size="xs" class="text-slate-500" />
                                </button>
                                <div x-show="openFriendMenu" x-transition class="absolute left-0 sm:left-auto sm:right-0 mt-1 bg-white border border-slate-150 rounded-xl shadow-lg py-1 z-30 w-40 text-left" style="display: none;">
                                    <button
                                        type="button"
                                        wire:click="removeConnection"
                                        wire:loading.attr="disabled"
                                        wire:target="removeConnection"
                                        class="w-full text-left px-3 py-2 text-xxs font-semibold text-red-650 hover:bg-red-50 flex items-center gap-1.5 transition-colors disabled:opacity-60"
                                    >
                                        <x-ui.icon name="user-minus" size="xs" class="text-red-400" />
                                        <span>Hủy kết bạn</span>
                                    </button>
                                </div>
                            </div>
                        @elseif ($this->connectionStatus === 'pending_sent')
                            {{-- Pending Sent (Click to cancel) --}}
                            <button
                                type="button"
                                wire:click="cancelGreeting"
                                wire:loading.attr="disabled"
                                wire:target="cancelGreeting"
                                class="px-3.5 py-2 rounded-xl bg-slate-50 text-slate-655 border border-slate-200 hover:bg-slate-100 hover:text-slate-700 transition-colors flex items-center justify-center"
                                title="Đang chờ phản hồi (Click để hủy yêu cầu)"
                            >
                                <x-ui.icon name="clock" size="xs" />
                            </button>
                        @elseif ($this->connectionStatus === 'pending_received')
                            {{-- Pending Received (Click to accept) --}}
                            <button
                                type="button"
                                wire:click="acceptGreeting"
                                wire:loading.attr="disabled"
                                wire:target="acceptGreeting"
                                class="px-3.5 py-2 rounded-xl bg-blue-50 text-blue-700 border border-blue-100 hover:bg-blue-100 hover:text-blue-800 transition-colors flex items-center justify-center animate-pulse"
                                title="Lời mời kết nối mới (Click để đồng ý)"
                            >
                                <x-ui.icon name="user-plus" size="xs" />
                            </button>
                        @elseif ($this->connectionStatus === 'blocked')
                            <span class="px-3.5 py-2 rounded-xl bg-slate-50 text-slate-400 border border-slate-200 text-xxs font-bold flex items-center justify-center">
                                Đã chặn
                            </span>
                        @else
                            {{-- None: send greeting request --}}
                            <button
                                type="button"
                                wire:click="sendGreeting"
                                wire:loading.attr="disabled"
                                wire:target="sendGreeting"
                                class="px-3.5 py-2 rounded-xl bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 transition-colors flex items-center justify-center"
                                title="Gửi lời mời kết nối"
                            >
                                <x-ui.icon name="user-plus" size="xs" />
                            </button>
                        @endif

                        {{-- More Safety controls --}}
                        <div class="relative" x-data="{ openOptions: false }" @click.away="openOptions = false">
                            <button @click="openOptions = !openOptions" class="p-2 text-slate-450 hover:text-slate-655 hover:bg-slate-50 border border-slate-200 rounded-xl transition-colors shadow-2xs flex items-center justify-center">
                                <x-ui.icon name="more-horizontal" size="xs" />
                            </button>
                            <div x-show="openOptions" x-transition class="absolute right-0 mt-1 bg-white border border-slate-150 rounded-xl shadow-lg py-1 z-30 w-40 text-left" style="display: none;">
                                <button type="button" wire:click="blockUser" wire:loading.attr="disabled" wire:target="blockUser" class="w-full text-left px-3 py-2 text-xxs font-semibold text-red-600 hover:bg-red-50 flex items-center gap-1.5 transition-colors disabled:opacity-60">
                                    <x-ui.icon name="shield-x" size="xs" class="text-red-400" />
                                    <span wire:loading.remove wire:target="blockUser">Chặn thành viên</span>
                                    <span wire:loading wire:target="blockUser">Đang chặn...</span>
                                </button>
                                <button type="button" wire:click="reportUser" wire:loading.attr="disabled" wire:target="reportUser" class="w-full text-left px-3 py-2 text-xxs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors disabled:opacity-60">
                                    <x-ui.icon name="flag" size="xs" class="text-slate-400" />
                                    <span wire:loading.remove wire:target="reportUser">Báo cáo tài khoản</span>
                                    <span wire:loading wire:target="reportUser">Đang gửi...</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

        {{-- Modern Profile Tabs --}}
        @if (!$canViewContent)
            <div class="bg-white border border-slate-150 rounded-3xl p-8 py-16 text-center shadow-2xs space-y-4 max-w-lg mx-auto">
                <div class="w-16 h-16 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto text-slate-400">
                    <x-ui.icon name="lock" size="lg" />
                </div>
                <div class="space-y-2">
                    <h3 class="text-base font-bold text-slate-800">Trang cá nhân riêng tư</h3>
                    <p class="text-xxs text-slate-400 leading-relaxed max-w-md mx-auto">
                        Chỉ những người bạn kết nối với {{ $user->profile?->display_name ?? $user->name }} mới có thể xem các bài viết và thông tin học đường chi tiết của thành viên này.
                    </p>
                </div>
                @if ($this->connectionStatus === 'none')
                    <div class="pt-3">
                        <a href="{{ route('discovery.index') }}" class="inline-flex items-center gap-1.5 bg-ue-brand hover:bg-ue-brand-dark text-white text-xxs font-bold px-5 py-2.5 rounded-xl shadow-2xs transition-colors">
                            <x-ui.icon name="user-plus" size="xs" />
                            Gửi lời chào kết nối
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div class="flex flex-col space-y-4">
            <div class="flex border-b border-slate-150 overflow-x-auto pb-px justify-start px-4 sm:px-0 gap-4 sm:gap-6 select-none scrollbar-none">
                <button 
                    type="button" 
                    wire:click="$set('activeTab', 'posts')" 
                    class="pb-3 text-xxs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'posts' ? 'border-slate-800 text-slate-800 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600' }}"
                >
                    Bài viết
                </button>
                <button 
                    type="button" 
                    wire:click="$set('activeTab', 'replies')" 
                    class="pb-3 text-xxs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'replies' ? 'border-slate-800 text-slate-800 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600' }}"
                >
                    Phản hồi
                </button>
                <button 
                    type="button" 
                    wire:click="$set('activeTab', 'media')" 
                    class="pb-3 text-xxs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'media' ? 'border-slate-800 text-slate-800 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600' }}"
                >
                    Phương tiện
                </button>
                <button 
                    type="button" 
                    wire:click="$set('activeTab', 'communities')" 
                    class="pb-3 text-xxs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'communities' ? 'border-slate-800 text-slate-800 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600' }}"
                >
                    Cộng đồng
                </button>
                <button
                    type="button"
                    wire:click="$set('activeTab', 'reposts')"
                    class="pb-3 text-xxs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'reposts' ? 'border-slate-800 text-slate-800 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600' }}"
                >
                    Đăng lại
                </button>
                <button 
                    type="button" 
                    wire:click="$set('activeTab', 'about')" 
                    class="pb-3 text-xxs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'about' ? 'border-slate-800 text-slate-800 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600' }}"
                >
                    Giới thiệu
                </button>
            </div>

            {{-- Tab Content rendering --}}
            <div>
                @if ($activeTab === 'posts')
                    <div class="space-y-4">
                        @forelse ($profilePosts as $post)
                            <x-ui.post-card
                                :post="$post"
                                :currentUser="Auth::user()"
                                :isSaved="(int) $post->saved_by_current_user_count > 0"
                                :isLiked="(int) $post->liked_by_current_user_count > 0"
                                :isReposted="(int) $post->reposted_by_current_user_count > 0"
                                :likeCount="(int) $post->likes_count"
                                :commentCount="(int) $post->published_comments_count"
                                :repostCount="(int) $post->reposts_count"
                                :editingPostId="$editingPostId"
                                :editingBody="$editingBody"
                                :showQuickFollow="false"
                                :showFollowCheck="false"
                                :repostedBy="null"
                                :repostedAt="null"
                                :feedItemKey="'profile-post-' . $post->id"
                                :showRepostAction="true"
                            />
                        @empty
                            <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 border border-dashed border-slate-200 rounded-2xl">
                                <x-ui.icon name="edit" size="lg" class="text-slate-300" />
                                <h3 class="text-xs font-bold text-slate-700">Chưa có bài đăng nào</h3>
                                <p class="text-xxs text-slate-400 max-w-xs">Các chia sẻ cá nhân của thành viên sẽ xuất hiện tại đây.</p>
                            </div>
                        @endforelse
                    </div>

                @elseif ($activeTab === 'replies')
                    <div class="space-y-4">
                        @forelse ($profileComments as $comment)
                            <div class="bg-white border border-slate-150 rounded-2xl p-4 shadow-2xs space-y-2">
                                <div class="flex items-center gap-2 text-slate-400 text-[10px] font-medium">
                                    <x-ui.icon name="corner-down-right" size="xs" />
                                    <span>Đã phản hồi bài viết của {{ $comment->post?->user?->name }}</span>
                                </div>
                                <p class="text-xxs font-medium text-slate-655 leading-relaxed">{{ $comment->body }}</p>
                                <span class="text-[9px] text-slate-400 block">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                        @empty
                            <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 border border-dashed border-slate-200 rounded-2xl">
                                <x-ui.icon name="message-square" size="lg" class="text-slate-300" />
                                <h3 class="text-xs font-bold text-slate-700">Chưa có phản hồi nào</h3>
                                <p class="text-xxs text-slate-400 max-w-xs">Các câu trả lời, thảo luận của thành viên sẽ xuất hiện tại đây.</p>
                            </div>
                        @endforelse
                    </div>

                @elseif ($activeTab === 'media')
                    @if ($profileMedia->isNotEmpty())
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($profileMedia as $mediaItem)
                                <div class="aspect-square bg-slate-100 border border-slate-150 rounded-xl overflow-hidden group relative flex items-center justify-center">
                                    <img src="{{ app(GenerateMediaUrlAction::class)->execute($mediaItem, 'thumb', Auth::user()) }}" alt="Grid image" class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300" />
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 border border-dashed border-slate-200 rounded-2xl">
                            <x-ui.icon name="image" size="lg" class="text-slate-300" />
                            <h3 class="text-xs font-bold text-slate-700">Chưa có ảnh chia sẻ</h3>
                            <p class="text-xxs text-slate-400 max-w-xs">Các hình ảnh được thành viên chia sẻ sẽ hiển thị tại đây.</p>
                        </div>
                    @endif

                @elseif ($activeTab === 'communities')
                    <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 border border-dashed border-slate-200 rounded-2xl">
                        <x-ui.icon name="users" size="lg" class="text-slate-300" />
                        <h3 class="text-xs font-bold text-slate-700">Chưa tham gia cộng đồng nào</h3>
                        <p class="text-xxs text-slate-400 max-w-xs">Cộng đồng và câu lạc bộ học thuật sẽ hiển thị tại đây khi tham gia.</p>
                    </div>

                @elseif ($activeTab === 'reposts')
                    <div class="space-y-4">
                        @forelse ($profileReposts as $repost)
                            @if ($repost->post)
                                <x-ui.post-card
                                    :post="$repost->post"
                                    :currentUser="Auth::user()"
                                    :isSaved="(int) $repost->post->saved_by_current_user_count > 0"
                                    :isLiked="(int) $repost->post->liked_by_current_user_count > 0"
                                    :isReposted="(int) $repost->post->reposted_by_current_user_count > 0"
                                    :likeCount="(int) $repost->post->likes_count"
                                    :commentCount="(int) $repost->post->published_comments_count"
                                    :repostCount="(int) $repost->post->reposts_count"
                                    :editingPostId="$editingPostId"
                                    :editingBody="$editingBody"
                                    :showQuickFollow="false"
                                    :showFollowCheck="false"
                                    :repostedBy="$user"
                                    :repostedAt="$repost->created_at"
                                    :feedItemKey="'profile-repost-' . $repost->id"
                                    :showRepostAction="true"
                                />
                            @endif
                        @empty
                            <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 border border-dashed border-slate-200 rounded-2xl">
                                <x-ui.icon name="repost" size="lg" class="text-slate-300" />
                                <h3 class="text-xs font-bold text-slate-700">Chưa có bài đăng lại nào</h3>
                                <p class="text-xxs text-slate-400 max-w-xs">Các bài viết thành viên đăng lại công khai sẽ xuất hiện tại đây.</p>
                            </div>
                        @endforelse
                    </div>

                @elseif ($activeTab === 'about')
                    <div class="bg-white border border-slate-150 rounded-2xl p-5 space-y-4 shadow-2xs">
                        <h3 class="text-xs font-bold text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-1.5">
                            <x-ui.icon name="user" size="xs" class="text-slate-400" />
                            Thông tin học đường xác thực
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xxs text-slate-600 font-medium">
                            <div class="space-y-1">
                                <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Họ và tên xác thực</span>
                                <span class="text-slate-850 font-bold">{{ $user->name }}</span>
                            </div>

                            @if ($isOwn)
                                <div class="space-y-1">
                                    <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Địa chỉ email học đường</span>
                                    <span class="text-slate-850 font-bold">{{ $user->email }}</span>
                                </div>
                            @endif

                            @if (($user->profile?->role_type ?? 'student') === 'student' && $user->profile?->studentProfile)
                                <div class="space-y-1">
                                    <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Mã số sinh viên</span>
                                    <span class="text-slate-850 font-bold">
                                        @if ($isOwn)
                                            {{ $user->profile?->studentProfile?->student_code }}
                                        @else
                                            {{ substr($user->profile?->studentProfile?->student_code, 0, 5) }}•••••
                                        @endif
                                    </span>
                                </div>
                                @if ($showMajor && $user->profile?->studentProfile?->academicProgram)
                                    <div class="space-y-1">
                                        <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Ngành học</span>
                                        <span class="text-slate-850 font-bold">{{ $user->profile?->studentProfile?->academicProgram->name }}</span>
                                    </div>
                                @endif
                                @if ($showFaculty && $user->profile?->studentProfile?->faculty)
                                    <div class="space-y-1">
                                        <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Khoa</span>
                                        <span class="text-slate-850 font-bold">{{ $user->profile?->studentProfile?->faculty->name }}</span>
                                    </div>
                                @endif
                                @if ($showCohort && $user->profile?->studentProfile?->cohort)
                                    <div class="space-y-1">
                                        <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Khóa tuyển sinh</span>
                                        <span class="text-slate-850 font-bold">{{ $user->profile?->studentProfile?->cohort }}</span>
                                    </div>
                                @endif
                            @elseif (($user->profile?->role_type ?? '') === 'alumni' && $user->profile?->alumniProfile)
                                @if ($showCohort && $user->profile?->alumniProfile?->cohort)
                                    <div class="space-y-1">
                                        <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Khóa tuyển sinh / Năm tốt nghiệp</span>
                                        <span class="text-slate-850 font-bold">
                                            {{ $user->profile?->alumniProfile?->cohort }} / {{ $user->profile?->alumniProfile?->graduation_year }}
                                        </span>
                                    </div>
                                @endif
                            @elseif (in_array(($user->profile?->role_type ?? ''), ['teacher', 'advisor'], true) && $user->profile?->advisorProfile)
                                <div class="space-y-1">
                                    <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Chức vụ / Học vị</span>
                                    <span class="text-slate-850 font-bold">{{ $user->profile?->advisorProfile?->title ?: 'Giảng viên' }}</span>
                                </div>
                            @endif

                            <div class="space-y-1">
                                <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Ngày tham gia</span>
                                <span class="text-slate-850 font-bold">{{ $user->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif
    @endif

    {{-- 4. REPORT MODAL --}}
    @if ($showReportModal && $reportingPost)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs ue-animate-fade-in" role="dialog" aria-modal="true" aria-labelledby="report-modal-title">
            <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden ue-animate-scale-in">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 id="report-modal-title" class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <x-ui.icon name="alert-triangle" size="xs" class="text-yellow-600" />
                        Báo cáo vi phạm cộng đồng
                    </h3>
                    <button type="button" wire:click="closeReport" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <x-ui.icon name="x" size="xs" />
                    </button>
                </div>

                <form wire:submit.prevent="submitReport" class="p-6 space-y-4">
                    @error('report')
                        <div class="p-3 bg-red-50 border border-red-200 text-red-800 text-xs rounded-xl font-semibold">
                            {{ $message }}
                        </div>
                    @enderror

                    <div>
                        <label for="report-reason" class="block text-xs font-bold text-slate-500 mb-1.5">Lý do báo cáo</label>
                        <select
                            id="report-reason"
                            wire:model="reportReason"
                            class="w-full rounded-xl border-slate-200 text-sm text-slate-800 focus:border-ue-brand focus:ring-ue-brand-soft"
                        >
                            <option value="spam">Tin rác / Spam</option>
                            <option value="harassment">Quấy rối / Công kích cá nhân</option>
                            <option value="inappropriate_content">Nội dung không phù hợp quy chuẩn trường học</option>
                            <option value="misinformation">Thông tin sai lệch / Thất thiệt</option>
                            <option value="privacy_violation">Xâm phạm quyền riêng tư</option>
                            <option value="other">Lý do khác</option>
                        </select>
                    </div>

                    <div>
                        <label for="report-desc" class="block text-xs font-bold text-slate-500 mb-1.5">Chi tiết bổ sung (không bắt buộc)</label>
                        <textarea
                            id="report-desc"
                            wire:model="reportDescription"
                            placeholder="Mô tả cụ thể hành vi vi phạm giúp Ban kiểm duyệt xử lý chính xác..."
                            rows="3"
                            class="w-full rounded-xl border-slate-200 text-sm text-slate-800 focus:border-ue-brand focus:ring-ue-brand-soft resize-none"
                            maxlength="500"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" wire:click="closeReport" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 transition-colors">
                            Hủy bỏ
                        </button>
                        <x-ui.button
                            type="submit"
                            variant="danger"
                            size="sm"
                            icon="flag"
                            wire:loading.attr="disabled"
                            wire:target="submitReport"
                        >
                            <span wire:loading.remove wire:target="submitReport">Gửi báo cáo</span>
                            <span wire:loading wire:target="submitReport">Đang gửi...</span>
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- CUSTOM DELETE CONFIRMATION MODAL --}}
    @if ($showDeleteModal && $deletingPostId)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs ue-animate-fade-in" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
            <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden ue-animate-scale-in">
                <div class="p-6 text-center space-y-4">
                    <div class="w-12 h-12 rounded-full bg-red-50 border border-red-100 flex items-center justify-center mx-auto text-red-650">
                        <x-ui.icon name="trash" size="md" />
                    </div>
                    <div class="space-y-2">
                        <h3 id="delete-modal-title" class="text-base font-bold text-slate-800">Xóa bài viết?</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Bài viết sẽ không còn hiển thị trong bảng tin. Bạn không thể hoàn tác thao tác này trong phiên bản hiện tại.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button type="button" wire:click="$set('showDeleteModal', false)" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 transition-colors">
                        Hủy
                    </button>
                    <x-ui.button
                        type="button"
                        wire:click="executeDelete"
                        variant="danger"
                        size="sm"
                        icon="trash"
                        wire:loading.attr="disabled"
                        wire:target="executeDelete"
                    >
                        <span wire:loading.remove wire:target="executeDelete">Xóa bài viết</span>
                        <span wire:loading wire:target="executeDelete">Đang xóa...</span>
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif

    {{-- 5. SHARE POST MODAL --}}
    @if ($showShareModal && $sharingPostId)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs ue-animate-fade-in" role="dialog" aria-modal="true" aria-labelledby="share-modal-title">
            <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden ue-animate-scale-in flex flex-col max-h-[85vh]">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
                    <h3 id="share-modal-title" class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <x-ui.icon name="send" size="xs" class="text-ue-brand" />
                        Chia sẻ bài viết qua tin nhắn
                    </h3>
                    <button type="button" wire:click="$set('showShareModal', false)" class="text-slate-400 hover:text-slate-655 transition-colors">
                        <x-ui.icon name="x" size="xs" />
                    </button>
                </div>

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    {{-- Search Recipient --}}
                    <div class="space-y-1.5">
                        <label for="share-search" class="block text-xs font-bold text-slate-500">Tìm kiếm người nhận (Bạn bè)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-ui.icon name="search" size="xs" class="text-slate-400" />
                            </span>
                            <input
                                id="share-search"
                                type="text"
                                wire:model.live.debounce.150ms="shareSearch"
                                placeholder="Nhập tên bạn bè..."
                                class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 focus:border-ue-brand/40 bg-slate-50/60 placeholder-slate-400 text-slate-700"
                            />
                        </div>
                    </div>

                    {{-- Recipient List --}}
                    <div class="space-y-1 max-h-48 overflow-y-auto border border-slate-100 rounded-xl divide-y divide-slate-50">
                        @php
                            $shareConnections = $this->getShareConnections();
                        @endphp
                        @forelse ($shareConnections as $connUser)
                            @php
                                $isSelectedShareRecipient = in_array($connUser->id, $selectedShareUserIds, true);
                            @endphp
                            <button
                                type="button"
                                wire:click="toggleShareRecipient({{ $connUser->id }})"
                                class="w-full text-left p-3 hover:bg-slate-50 flex items-center justify-between transition-colors {{ $isSelectedShareRecipient ? 'bg-slate-50' : '' }}"
                            >
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :user="$connUser" size="xs" />
                                    <div>
                                        <p class="text-xxs font-bold text-slate-800 leading-tight">{{ $connUser->name }}</p>
                                        @if ($connUser->profile && $connUser->profile->faculty)
                                            <p class="text-[9px] text-slate-400 font-semibold leading-none mt-0.5">{{ $connUser->profile->faculty }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if ($isSelectedShareRecipient)
                                    <x-ui.icon name="check" size="xs" class="text-ue-brand fill-ue-brand" />
                                @endif
                            </button>
                        @empty
                            <div class="p-4 text-center text-xxs text-slate-400 italic">
                                Không tìm thấy bạn bè phù hợp. Hãy chắc chắn bạn đã kết nối bạn bè với người nhận.
                            </div>
                        @endforelse
                    </div>

                    {{-- Optional Message --}}
                    <div class="space-y-1.5">
                        <label for="share-msg" class="block text-xs font-bold text-slate-500">Tin nhắn kèm theo (không bắt buộc)</label>
                        <textarea
                            id="share-msg"
                            wire:model="shareOptionalMessage"
                            placeholder="Nhập nội dung tin nhắn gửi kèm..."
                            rows="2"
                            class="w-full rounded-xl border border-slate-200 text-xxs font-medium p-3 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 focus:border-ue-brand/40 resize-none bg-slate-50 placeholder-slate-400 text-slate-700"
                            maxlength="200"
                        ></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100 flex-shrink-0">
                    <p class="mr-auto text-[11px] font-semibold text-slate-400">
                        Đã chọn {{ count($selectedShareUserIds) }} người nhận
                    </p>
                    <button type="button" wire:click="$set('showShareModal', false)" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 transition-colors">
                        Hủy bỏ
                    </button>
                    <button
                        type="button"
                        wire:click="executeShare"
                        wire:loading.attr="disabled"
                        wire:target="executeShare"
                        @if (empty($selectedShareUserIds)) disabled @endif
                        class="px-4 py-2 text-xs font-bold text-white bg-ue-brand hover:bg-ue-brand-dark rounded-xl shadow-2xs hover:shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1.5"
                    >
                        <span wire:loading.remove wire:target="executeShare" class="flex items-center gap-1.5">
                            <x-ui.icon name="send" size="xs" />
                            Gửi chia sẻ
                        </span>
                        <span wire:loading wire:target="executeShare" class="flex items-center gap-1.5">
                            <span class="ue-spinner"></span>
                            Đang gửi...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

    <!-- Crop Modal -->
    <div 
        x-data="avatarCropper()" 
        x-on:avatar-selected.window="handleFileSelect($event.detail.files)"
        x-show="open" 
        class="fixed inset-0 z-modal overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs ue-animate-fade-in"
        role="dialog" 
        aria-modal="true" 
        aria-labelledby="crop-modal-title"
        style="display: none;"
        @keydown.escape.window="cancel()"
    >
        <!-- CSS styles specifically for circular crop box and zoom slider -->
        <style>
            .cropper-view-box,
            .cropper-face {
                border-radius: 50% !important;
                outline: 2px solid #fff !important;
                outline-color: rgba(255, 255, 255, 0.85) !important;
            }
            .cropper-modal {
                background-color: rgba(0, 0, 0, 0.6) !important;
            }
            .cropper-line, .cropper-point {
                display: none !important;
            }

            /* Custom styling for range input to fix double-rendering/overlap */
            .avatar-zoom-slider {
                -webkit-appearance: none;
                appearance: none;
                width: 100%;
                background: transparent !important; /* invisible container */
                height: 16px !important; /* thumb height target area */
                outline: none !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
            }
            .avatar-zoom-slider::-webkit-slider-runnable-track {
                width: 100%;
                height: 4px !important;
                background: #e2e8f0 !important; /* single track bg */
                border-radius: 9999px !important;
                border: none !important;
            }
            .avatar-zoom-slider::-webkit-slider-thumb {
                -webkit-appearance: none;
                appearance: none;
                width: 18px !important;
                height: 18px !important;
                border-radius: 50% !important;
                background: #1877f2 !important; /* Facebook / Instagram brand blue */
                border: 3.5px solid #ffffff !important; /* thick white border as in Image 2 */
                cursor: pointer !important;
                margin-top: -7px !important; /* center thumb vertically: (4px / 2) - (18px / 2) = 2 - 9 = -7px */
                transition: transform 0.1s ease !important;
                box-shadow: 0 1.5px 4px rgba(0, 0, 0, 0.18) !important;
            }
            .avatar-zoom-slider::-webkit-slider-thumb:hover {
                transform: scale(1.15) !important;
            }
            .avatar-zoom-slider::-moz-range-track {
                width: 100%;
                height: 4px !important;
                background: #e2e8f0 !important;
                border-radius: 9999px !important;
                border: none !important;
            }
            .avatar-zoom-slider::-moz-range-thumb {
                width: 18px !important;
                height: 18px !important;
                border-radius: 50% !important;
                background: #1877f2 !important;
                border: 3.5px solid #ffffff !important;
                cursor: pointer !important;
                transition: transform 0.1s ease !important;
                box-shadow: 0 1.5px 4px rgba(0, 0, 0, 0.18) !important;
            }
            .avatar-zoom-slider::-moz-range-thumb:hover {
                transform: scale(1.15) !important;
            }
        </style>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl w-full max-w-lg overflow-hidden flex flex-col ue-animate-scale-in text-slate-800">
            <!-- Modal Header -->
            <div class="relative flex items-center justify-center px-6 py-4 border-b border-slate-100 bg-white">
                <h3 id="crop-modal-title" class="text-sm font-extrabold text-slate-800">Chọn ảnh đại diện</h3>
                <button type="button" @click="cancel()" class="absolute right-6 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors">
                    <x-ui.icon name="x" size="xs" />
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4 space-y-4 bg-white">
                <!-- Caption Input -->
                <div class="flex flex-col gap-1.5">
                    <textarea
                        id="crop-caption"
                        x-model="caption"
                        placeholder="Mô tả"
                        rows="3"
                        class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-semibold rounded-xl px-3.5 py-3 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600/20 transition-all resize-none shadow-2xs"
                        maxlength="500"
                    ></textarea>
                </div>

                <!-- Crop Preview Area -->
                <div class="relative w-full h-64 sm:h-80 bg-slate-50 flex items-center justify-center overflow-hidden rounded-2xl border border-slate-100 shadow-inner">
                    <img x-ref="cropImage" :src="imageSrc" class="max-w-full max-h-full" alt="Avatar Crop Target" />
                </div>

                <!-- Zoom Slider Control -->
                <div class="flex items-center justify-center gap-3 max-w-sm mx-auto w-full px-4 pt-1.5 bg-white">
                    <button type="button" @click="zoomValue = Math.max(0, parseInt(zoomValue) - 5); zoom();" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <x-ui.icon name="minus" size="xs" />
                    </button>
                    <input 
                        type="range" 
                        min="0" 
                        max="100" 
                        x-model="zoomValue" 
                        @input="zoom()" 
                        class="flex-1 avatar-zoom-slider cursor-pointer" 
                    />
                    <button type="button" @click="zoomValue = Math.min(100, parseInt(zoomValue) + 5); zoom();" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <x-ui.icon name="plus" size="xs" />
                    </button>
                </div>

                <!-- Action Controls under slider -->
                <div class="flex items-center justify-center gap-3.5 pt-1 relative bg-white" x-data="{ openDurationMenu: false }">
                    <!-- Cắt ảnh / Reset button -->
                    <button 
                        type="button" 
                        @click="reset()" 
                        class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold py-2 px-4 rounded-xl border border-slate-200 transition-colors shadow-3xs"
                    >
                        <x-ui.icon name="refresh-cw" size="xs" class="text-slate-500" />
                        <span>Cắt ảnh</span>
                    </button>

                    <!-- Để tạm thời / Duration button -->
                    <div class="relative">
                        <button 
                            type="button" 
                            @click="openDurationMenu = !openDurationMenu" 
                            class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold py-2 px-4 rounded-xl border border-slate-200 transition-colors shadow-3xs"
                        >
                            <x-ui.icon name="clock" size="xs" class="text-slate-500" />
                            <span x-text="
                                duration === 'permanent' ? 'Để tạm thời' : 
                                duration === '1_hour' ? 'Tạm thời: 1 giờ' : 
                                duration === '1_day' ? 'Tạm thời: 1 ngày' : 
                                duration === '7_days' ? 'Tạm thời: 7 ngày' : 
                                'Tạm thời: Tùy chỉnh'
                            ">Để tạm thời</span>
                        </button>

                        <!-- Duration Dropdown Popover -->
                        <div 
                            x-show="openDurationMenu" 
                            @click.away="openDurationMenu = false" 
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-45"
                            style="display: none;"
                        >
                            <button 
                                type="button" 
                                @click="duration = 'permanent'; openDurationMenu = false" 
                                class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 flex items-center justify-between"
                                :class="duration === 'permanent' ? 'text-ue-brand font-bold bg-slate-50/50' : ''"
                            >
                                <span>Vĩnh viễn</span>
                                <template x-if="duration === 'permanent'">
                                    <x-ui.icon name="check" size="xxs" class="text-ue-brand" />
                                </template>
                            </button>
                            <button 
                                type="button" 
                                @click="duration = '1_hour'; openDurationMenu = false" 
                                class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 flex items-center justify-between"
                                :class="duration === '1_hour' ? 'text-ue-brand font-bold bg-slate-50/50' : ''"
                            >
                                <span>1 giờ</span>
                                <template x-if="duration === '1_hour'">
                                    <x-ui.icon name="check" size="xxs" class="text-ue-brand" />
                                </template>
                            </button>
                            <button 
                                type="button" 
                                @click="duration = '1_day'; openDurationMenu = false" 
                                class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 flex items-center justify-between"
                                :class="duration === '1_day' ? 'text-ue-brand font-bold bg-slate-50/50' : ''"
                            >
                                <span>1 ngày</span>
                                <template x-if="duration === '1_day'">
                                    <x-ui.icon name="check" size="xxs" class="text-ue-brand" />
                                </template>
                            </button>
                            <button 
                                type="button" 
                                @click="duration = '7_days'; openDurationMenu = false" 
                                class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 flex items-center justify-between"
                                :class="duration === '7_days' ? 'text-ue-brand font-bold bg-slate-50/50' : ''"
                            >
                                <span>7 ngày</span>
                                <template x-if="duration === '7_days'">
                                    <x-ui.icon name="check" size="xxs" class="text-ue-brand" />
                                </template>
                            </button>
                            <div class="h-px bg-slate-100 my-1"></div>
                            <button 
                                type="button" 
                                @click="duration = 'custom'; openDurationMenu = false" 
                                class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 flex items-center justify-between"
                                :class="duration === 'custom' ? 'text-ue-brand font-bold bg-slate-50/50' : ''"
                            >
                                <span>Tùy chỉnh...</span>
                                <template x-if="duration === 'custom'">
                                    <x-ui.icon name="check" size="xxs" class="text-ue-brand" />
                                </template>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Custom expiration picker -->
                <div x-show="duration === 'custom'" class="mt-3.5 flex flex-col gap-1.5 p-3.5 bg-slate-50 rounded-2xl border border-slate-100 ue-animate-fade-in" style="display: none;">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Thời gian hết hạn cụ thể</label>
                    <input 
                        type="datetime-local" 
                        x-model="customExpiresAt" 
                        min="{{ now()->format('Y-m-d\TH:i') }}" 
                        class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-semibold rounded-xl px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 shadow-2xs" 
                    />
                </div>

                <p class="text-[10px] text-slate-500 flex items-center justify-center gap-1.5 font-bold pt-2 select-none">
                    <x-ui.icon name="globe" size="xxs" class="text-slate-400" />
                    <span>Ảnh đại diện của bạn luôn hiển thị công khai.</span>
                </p>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-white border-t border-slate-150 flex items-center justify-between relative" x-data="{ openMoreMenu: false }">
                <!-- Left side: More options button (...) -->
                <div class="relative">
                    <button 
                        type="button" 
                        @click="openMoreMenu = !openMoreMenu" 
                        class="w-8 h-8 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-550 transition-colors flex items-center justify-center shadow-3xs"
                        title="Tùy chọn khác"
                    >
                        <x-ui.icon name="more-horizontal" size="sm" />
                    </button>

                    <!-- More options menu -->
                    <div 
                        x-show="openMoreMenu" 
                        @click.away="openMoreMenu = false" 
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute bottom-full left-0 mb-2 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl p-3.5 z-40 space-y-2.5"
                        style="display: none;"
                    >
                        <div class="flex items-center justify-between text-slate-700">
                            <span class="text-xs font-semibold">Chia sẻ lên bảng tin</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="shareToFeed" class="sr-only peer" />
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-ue-brand"></div>
                            </label>
                        </div>
                        <p class="text-[10px] text-slate-400 leading-normal">
                            Bật tùy chọn này để tự động tạo một bài viết chia sẻ ảnh đại diện mới của bạn lên bảng tin.
                        </p>
                    </div>
                </div>

                <!-- Center/Right: Upload progress + Cancel + Save button -->
                <div class="flex items-center gap-3">
                    <template x-if="isUploading">
                        <div class="flex items-center gap-2 text-[10px] font-bold text-slate-500">
                            <svg class="animate-spin h-3.5 w-3.5 text-blue-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Đang tải lên (<span x-text="uploadProgress"></span>%)</span>
                        </div>
                    </template>
                    
                    <button type="button" @click="cancel()" class="text-xs font-bold text-slate-550 hover:text-slate-700 px-4 py-2 transition-colors" :disabled="isUploading">
                        Hủy
                    </button>
                    
                    <button 
                        type="button" 
                        @click="save()" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-200 disabled:text-slate-400 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5"
                        :disabled="isUploading"
                    >
                        <x-ui.icon name="check" size="xs" />
                        <span>Lưu</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script>
        function avatarCropper() {
            return {
                open: false,
                imageSrc: '',
                cropper: null,
                zoomValue: 0,
                initialRatio: null,
                caption: '',
                shareToFeed: true,
                duration: 'permanent',
                customExpiresAt: '',
                isUploading: false,
                uploadProgress: 0,
                
                handleFileSelect(files) {
                    if (files && files.length > 0) {
                        const file = files[0];
                        this.imageSrc = URL.createObjectURL(file);
                        this.open = true;
                        this.zoomValue = 0;
                        this.initialRatio = null;
                        this.caption = '';
                        this.shareToFeed = true;
                        this.duration = 'permanent';
                        this.customExpiresAt = '';
                        
                        this.$nextTick(() => {
                            this.initCropper();
                        });
                    }
                },
                
                initCropper() {
                    const image = this.$refs.cropImage;
                    if (this.cropper) {
                        this.cropper.destroy();
                    }
                    
                    if (typeof Cropper === 'undefined') {
                        return;
                    }
                    
                    this.cropper = new Cropper(image, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        restore: false,
                        guides: false,
                        center: false,
                        highlight: false,
                        cropBoxMovable: false,
                        cropBoxResizable: false,
                        toggleDragModeOnDblclick: false,
                        ready: () => {
                            const imageData = this.cropper.getImageData();
                            this.initialRatio = imageData.width / imageData.naturalWidth;
                            this.zoomValue = 0;
                        },
                        zoom: (e) => {
                            if (this.initialRatio) {
                                const ratio = e.detail.ratio;
                                let val = 100 * (ratio / this.initialRatio - 1) / 2;
                                val = Math.max(0, Math.min(100, val));
                                this.zoomValue = Math.round(val);
                            }
                        }
                    });
                },
                
                zoom() {
                    if (this.cropper && this.initialRatio) {
                        const val = parseFloat(this.zoomValue);
                        const targetRatio = this.initialRatio * (1 + (val / 100) * 2);
                        this.cropper.zoomTo(targetRatio);
                    }
                },
                
                reset() {
                    if (this.cropper) {
                        this.cropper.reset();
                        this.zoomValue = 0;
                    }
                },
                
                cancel() {
                    this.open = false;
                    if (this.cropper) {
                        this.cropper.destroy();
                        this.cropper = null;
                    }
                    const in1 = document.getElementById('avatar-upload-input-1');
                    if (in1) in1.value = '';
                    const in2 = document.getElementById('avatar-upload-input-2');
                    if (in2) in2.value = '';
                },
                
                save() {
                    if (this.isUploading) return;
                    
                    this.isUploading = true;
                    this.uploadProgress = 0;
                    
                    const handleUpload = (blob) => {
                        const croppedFile = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
                        
                        @this.upload('avatarFile', croppedFile, 
                            (uploadedUrl) => {
                                @this.call('saveAvatar', this.caption, this.shareToFeed, this.duration, this.customExpiresAt)
                                    .then(() => {
                                        this.isUploading = false;
                                        this.open = false;
                                        this.cancel();
                                    })
                                    .catch((err) => {
                                        this.isUploading = false;
                                    });
                            },
                            () => {
                                this.isUploading = false;
                                alert('Lỗi tải ảnh lên server.');
                            },
                            (event) => {
                                this.uploadProgress = event.detail.progress;
                            }
                        );
                    };

                    if (this.cropper) {
                        this.cropper.getCroppedCanvas({
                            width: 400,
                            height: 400,
                            imageSmoothingEnabled: true,
                            imageSmoothingQuality: 'high'
                        }).toBlob(handleUpload, 'image/jpeg', 0.9);
                    } else {
                        const in1 = document.getElementById('avatar-upload-input-1');
                        const in2 = document.getElementById('avatar-upload-input-2');
                        const files = (in1 && in1.files.length > 0) ? in1.files : ((in2 && in2.files.length > 0) ? in2.files : null);
                        if (files && files.length > 0) {
                            handleUpload(files[0]);
                        } else {
                            this.isUploading = false;
                        }
                    }
                }
            };
        }
    </script>
@endpush

