<?php

use App\Actions\Posts\CreatePost;
use App\Actions\Posts\DeletePost;
use App\Actions\Posts\UpdatePost;
use App\Actions\Posts\TogglePostLike;
use App\Actions\Posts\TogglePostRepost;
use App\Actions\Posts\TogglePostSave;
use App\Actions\Posts\HidePostFromFeed;
use App\Actions\Reports\CreateReport;
use App\Actions\Messaging\SendSharedPostMessage;
use App\Actions\Messaging\FindOrCreateDirectConversation;
use App\Actions\Media\AttachMediaToModelAction;
use App\Actions\Media\DeleteMediaAction;
use App\Actions\Media\GenerateMediaUrlAction;
use App\Actions\Media\StoreTemporaryMediaAction;
use App\Actions\Follows\FollowUser;
use App\Actions\Follows\UnfollowUser;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Enums\ReportReason;
use App\Enums\ConnectionStatus;
use App\Models\Post;
use App\Models\PostRepost;
use App\Models\User;
use App\Models\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination, WithFileUploads;

    private const FEED_PAGE_SIZE = 10;

    // Composer properties
    public string $body = '';
    public string $visibility = 'verified_users';
    public int $perPage = self::FEED_PAGE_SIZE;
    public string $activeFeedTab = 'for_you';
    public ?int $selectedCommunityId = null;

    // Multi-image upload properties
    public $imageFiles = [];
    public array $composerImages = []; // Holds items like [['id' => 123, 'uuid' => '...', 'url' => '...']]

    // Edit post properties
    public ?int $editingPostId = null;
    public string $editingBody = '';

    // Report properties
    public ?Post $reportingPost = null;
    public string $reportReason = 'spam';
    public string $reportDescription = '';
    public bool $showReportModal = false;

    // Custom delete modal properties
    public ?int $deletingPostId = null;
    public bool $showDeleteModal = false;

    // Feedback message
    public ?string $feedbackMessage = null;

    // Locally hidden posts for this session
    public array $locallyHiddenPostIds = [];

    // Sharing post properties
    public bool $showShareModal = false;
    public ?int $sharingPostId = null;
    public string $shareSearch = '';
    public array $selectedShareUserIds = [];
    public string $shareOptionalMessage = '';

    /**
     * Rules for validation.
     */
    protected array $rules = [
        'body' => 'required|string|max:3000',
        'visibility' => 'required|string|in:verified_users,connections_only,community,private',
        'selectedCommunityId' => 'nullable|integer|exists:communities,id',
    ];

    /**
     * Clear community selection when switching away from community audience.
     */
    public function updatedVisibility(string $value): void
    {
        if ($value !== PostVisibility::COMMUNITY->value) {
            $this->selectedCommunityId = null;
        }
    }

    /**
     * Handle temporary composer image uploads.
     */
    public function updatedImageFiles(): void
    {
        $this->validate([
            'imageFiles.*' => 'image|max:10240', // Max 10MB per image
        ]);

        $maxImages = config('media.limits.post_max_images', 4);
        if (count($this->composerImages) + count($this->imageFiles) > $maxImages) {
            $this->addError('imageFiles', "Bạn chỉ được phép tải lên tối đa {$maxImages} hình ảnh.");
            $this->imageFiles = [];
            return;
        }

        $storeAction = app(StoreTemporaryMediaAction::class);

        foreach ($this->imageFiles as $file) {
            try {
                $media = $storeAction->execute(Auth::user(), $file, 'post_image', ['visibility' => 'public']);
                
                $this->composerImages[] = [
                    'id' => $media->id,
                    'uuid' => $media->uuid,
                    'url' => app(GenerateMediaUrlAction::class)->execute($media, 'thumb', Auth::user()),
                ];
            } catch (\Exception $e) {
                $this->addError('imageFiles', 'Lỗi tải ảnh lên: ' . $e->getMessage());
            }
        }

        $this->imageFiles = [];
    }

    /**
     * Remove a drafted image from the composer before posting.
     */
    public function removeComposerImage(int $index): void
    {
        if (isset($this->composerImages[$index])) {
            $mediaData = $this->composerImages[$index];
            $media = \App\Models\Media::find($mediaData['id']);
            if ($media) {
                app(DeleteMediaAction::class)->execute($media);
            }
            array_splice($this->composerImages, $index, 1);
        }
    }

    /**
     * Submit a new post.
     */
    public function submitPost(CreatePost $createPost): void
    {
        $this->validate([
            'body' => 'required|string|max:3000',
            'visibility' => 'required|string|in:verified_users,connections_only,community,private',
            'selectedCommunityId' => 'nullable|required_if:visibility,community|integer|exists:communities,id',
        ], [
            'selectedCommunityId.required_if' => 'Vui lòng chọn cộng đồng để đăng bài.',
            'selectedCommunityId.exists' => 'Cộng đồng đã chọn không khả dụng.',
        ]);

        $post = $createPost->execute(Auth::user(), [
            'body' => $this->body,
            'visibility' => $this->visibility,
            'community_id' => $this->selectedCommunityId,
        ]);

        // Attach composer images polymorphically
        if (!empty($this->composerImages)) {
            $mediaIds = array_column($this->composerImages, 'id');
            app(AttachMediaToModelAction::class)->execute(Auth::user(), $post, $mediaIds, 'post_image');
        }

        $this->body = '';
        $this->visibility = PostVisibility::VERIFIED_USERS->value;
        $this->selectedCommunityId = null;
        $this->composerImages = [];
        $this->imageFiles = [];
        $this->feedbackMessage = 'Đăng bài viết thành công.';
        $this->dispatch('post-created');
        $this->perPage = self::FEED_PAGE_SIZE;
        $this->resetPage(); // Re-render feed at page 1
    }

    /**
     * Toggle post like using policy action.
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
     * Toggle post save using policy action.
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
            $this->feedbackMessage = 'Báo cáo của bạn đã được gửi. Cảm ơn bạn đã góp phần xây dựng cộng đồng HCMUE an toàn.';
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
     * Hide post from current user's feed.
     */
    public function hidePost(int $postId, HidePostFromFeed $hidePostFromFeed): void
    {
        try {
            $post = Post::findOrFail($postId);
            $hidePostFromFeed->execute(Auth::user(), $post);
            $this->locallyHiddenPostIds[] = $postId;
            $this->feedbackMessage = 'Đã ẩn bài viết khỏi bảng tin của bạn.';
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Undo hiding post.
     */
    public function undoHidePost(int $postId): void
    {
        $user = Auth::user();
        
        \App\Models\PostHide::where('post_id', $postId)
            ->where('user_id', $user->id)
            ->delete();

        $this->locallyHiddenPostIds = array_diff($this->locallyHiddenPostIds, [$postId]);
        
        $this->feedbackMessage = 'Đã hoàn tác ẩn bài viết.';
    }

    /**
     * Hide post globally (moderator option).
     */
    public function hidePostGlobally(int $postId): void
    {
        if (! Auth::user()->hasPermissionTo('moderate_content')) {
            $this->feedbackMessage = 'Bạn không có quyền thực hiện hành động này.';
            return;
        }

        $post = Post::findOrFail($postId);
        $post->status = PostStatus::HIDDEN_BY_MODERATION;
        $post->save();
        $this->feedbackMessage = 'Đã ẩn bài viết khỏi cộng đồng.';
    }

    /**
     * Copy link feedback helper.
     */
    public function copyLinkFeedback(): void
    {
        $this->feedbackMessage = 'Đã sao chép liên kết bài viết vào bộ nhớ tạm.';
    }

    /**
     * Toggle follow status for a user.
     */
    public function toggleFollow(int $userId, FollowUser $followUser, UnfollowUser $unfollowUser): void
    {
        try {
            $targetUser = User::findOrFail($userId);
            $currentUser = Auth::user();

            if ($currentUser->id === $targetUser->id) {
                $this->feedbackMessage = 'Bạn không thể tự theo dõi chính mình.';
                return;
            }

            $isFollowing = \App\Models\UserFollow::where('follower_id', $currentUser->id)
                ->where('following_id', $targetUser->id)
                ->exists();

            if ($isFollowing) {
                $unfollowUser->execute($currentUser, $targetUser);
                $this->feedbackMessage = 'Đã bỏ theo dõi ' . $targetUser->name . '.';
            } else {
                $followUser->execute($currentUser, $targetUser);
                $this->feedbackMessage = 'Đã theo dõi ' . $targetUser->name . '.';
            }
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
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
     * Execute post sharing to selected conversations.
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
     * Increase the feed window while keeping already-rendered posts in place.
     */
    public function loadMore(): void
    {
        $this->perPage += self::FEED_PAGE_SIZE;
    }

    /**
     * Follow a post author directly from the feed.
     */
    public function quickFollowAuthor(int $authorId, FollowUser $followUser): void
    {
        $author = User::findOrFail($authorId);

        try {
            $followUser->execute(Auth::user(), $author);
            $this->feedbackMessage = 'Đã theo dõi '.$author->name.'.';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->feedbackMessage = collect($e->errors())->flatten()->first() ?: 'Không thể theo dõi người dùng này.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Toggle a public repost for the selected post.
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
     * Switch between the personalized and following-only feeds.
     */
    public function setFeedTab(string $tab): void
    {
        if (! in_array($tab, ['for_you', 'following'], true)) {
            return;
        }

        if ($this->activeFeedTab === $tab) {
            return;
        }

        $this->activeFeedTab = $tab;
        $this->perPage = self::FEED_PAGE_SIZE;
        $this->resetPage();
    }

    /**
     * Build the base visible feed query shared by both tabs.
     */
    private function baseFeedQuery(User $user): Builder
    {
        $query = Post::with(['user.profile', 'media.variants'])
            ->withCount([
                'likes',
                'reposts',
                'comments as published_comments_count' => function ($query): void {
                    $query->where('status', CommentStatus::PUBLISHED->value);
                },
            ])
            ->withCount([
                'likes as liked_by_current_user_count' => function ($query) use ($user): void {
                    $query->where('user_id', $user->id);
                },
                'saves as saved_by_current_user_count' => function ($query) use ($user): void {
                    $query->where('user_id', $user->id);
                },
                'reposts as reposted_by_current_user_count' => function ($query) use ($user): void {
                    $query->where('user_id', $user->id);
                },
            ]);

        return $this->applyVisibleFeedPostConstraints($query, $user);
    }

    /**
     * Apply post status, privacy, and local hide filters to a feed post query.
     */
    private function applyVisibleFeedPostConstraints($query, User $user)
    {
        return $query
            ->whereIn('status', [PostStatus::PUBLISHED, PostStatus::EDITED])
            ->visibleTo($user)
            ->where(function ($query) use ($user) {
                $query->whereDoesntHave('hides', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->orWhereIn('id', $this->locallyHiddenPostIds);
            });
    }

    /**
     * Build visible repost events for the feed.
     */
    private function baseRepostQuery(User $user): Builder
    {
        return PostRepost::with([
            'user.profile',
            'post' => function ($query) use ($user): void {
                $query->with(['user.profile', 'media.variants'])
                    ->withCount([
                        'likes',
                        'reposts',
                        'comments as published_comments_count' => function ($query): void {
                            $query->where('status', CommentStatus::PUBLISHED->value);
                        },
                    ])
                    ->withCount([
                        'likes as liked_by_current_user_count' => function ($query) use ($user): void {
                            $query->where('user_id', $user->id);
                        },
                        'saves as saved_by_current_user_count' => function ($query) use ($user): void {
                            $query->where('user_id', $user->id);
                        },
                        'reposts as reposted_by_current_user_count' => function ($query) use ($user): void {
                            $query->where('user_id', $user->id);
                        },
                    ]);

                $this->applyVisibleFeedPostConstraints($query, $user);
            },
        ])->whereHas('post', function (Builder $query) use ($user): void {
            $this->applyVisibleFeedPostConstraints($query, $user);
        });
    }

    /**
     * @return list<int>
     */
    private function followingUserIds(User $user): array
    {
        return $user->following()
            ->pluck('users.id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * @return list<int>
     */
    private function friendUserIds(User $user): array
    {
        return Connection::where(function ($query) use ($user) {
                $query->where('user_one_id', $user->id)
                    ->orWhere('user_two_id', $user->id);
            })
            ->where('status', ConnectionStatus::ACTIVE)
            ->get(['user_one_id', 'user_two_id'])
            ->map(fn (Connection $connection): int => $connection->user_one_id === $user->id
                ? (int) $connection->user_two_id
                : (int) $connection->user_one_id)
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    private function joinedCommunityIds(User $user): array
    {
        return $user->activeCommunityMemberships()
            ->pluck('community_id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * Rank For You items: friends, following, joined communities, then recent.
     */
    private function applyForYouRanking(Builder $query, User $user): Builder
    {
        $cases = [];
        $bindings = [];

        $friendIds = $this->friendUserIds($user);
        if ($friendIds !== []) {
            $cases[] = 'WHEN user_id IN ('.$this->placeholdersFor($friendIds).') THEN 0';
            array_push($bindings, ...$friendIds);
        }

        $followingIds = $this->followingUserIds($user);
        if ($followingIds !== []) {
            $cases[] = 'WHEN user_id IN ('.$this->placeholdersFor($followingIds).') THEN 1';
            array_push($bindings, ...$followingIds);
        }

        $communityIds = $this->joinedCommunityIds($user);
        if ($communityIds !== []) {
            $cases[] = 'WHEN scope_type = ? AND scope_id IN ('.$this->placeholdersFor($communityIds).') THEN 2';
            $bindings[] = 'community';
            array_push($bindings, ...$communityIds);
        }

        if ($cases !== []) {
            $query->orderByRaw('CASE '.implode(' ', $cases).' ELSE 3 END', $bindings);
        }

        return $query->latest('published_at');
    }

    /**
     * Rank a materialized feed item using the same For You buckets as the DB query.
     *
     * @param  list<int>  $friendIds
     * @param  list<int>  $followingIds
     * @param  list<int>  $communityIds
     */
    private function feedRankForPost(Post $post, array $friendIds, array $followingIds, array $communityIds): int
    {
        if (in_array((int) $post->user_id, $friendIds, true)) {
            return 0;
        }

        if (in_array((int) $post->user_id, $followingIds, true)) {
            return 1;
        }

        if ($post->scope_type === 'community' && in_array((int) $post->scope_id, $communityIds, true)) {
            return 2;
        }

        return 3;
    }

    /**
     * Rank a repost event by the user who reposted it.
     *
     * @param  list<int>  $friendIds
     * @param  list<int>  $followingIds
     */
    private function feedRankForRepost(PostRepost $repost, array $friendIds, array $followingIds): int
    {
        if (in_array((int) $repost->user_id, $friendIds, true)) {
            return 0;
        }

        if (in_array((int) $repost->user_id, $followingIds, true)) {
            return 1;
        }

        return 3;
    }

    /**
     * @param  list<int>  $values
     */
    private function placeholdersFor(array $values): string
    {
        return implode(', ', array_fill(0, count($values), '?'));
    }

    /**
     * Render the component view.
     */
    public function with(): array
    {
        $user = Auth::user();

        $query = $this->baseFeedQuery($user);
        $repostQuery = $this->baseRepostQuery($user);
        $friendIds = $this->friendUserIds($user);
        $followingIds = $this->followingUserIds($user);
        $communityIds = $this->joinedCommunityIds($user);

        if ($this->activeFeedTab === 'following') {
            if ($followingIds === []) {
                $query->whereRaw('1 = 0');
                $repostQuery->whereRaw('1 = 0');
            } else {
                $query->whereIn('user_id', $followingIds);
                $repostQuery->whereIn('user_id', $followingIds);
            }

            $query->latest('published_at');
            $repostQuery->latest('created_at');
        } else {
            $this->applyForYouRanking($query, $user);
            $repostQuery->latest('created_at');
        }

        $originalPosts = $query
            ->limit($this->perPage + 1)
            ->get()
            ->map(function (Post $post) use ($friendIds, $followingIds, $communityIds): Post {
                $post->setAttribute('feed_item_key', "post-{$post->id}");
                $post->setAttribute('feed_sort_at', $post->published_at ?? $post->created_at);
                $post->setAttribute('feed_rank', $this->activeFeedTab === 'for_you'
                    ? $this->feedRankForPost($post, $friendIds, $followingIds, $communityIds)
                    : 0);

                return $post;
            });

        $repostPosts = $repostQuery
            ->limit($this->perPage + 1)
            ->get()
            ->filter(fn (PostRepost $repost): bool => $repost->post !== null)
            ->map(function (PostRepost $repost) use ($friendIds, $followingIds): Post {
                $post = $repost->post;
                $post->setAttribute('feed_item_key', "repost-{$repost->id}");
                $post->setAttribute('feed_sort_at', $repost->created_at);
                $post->setAttribute('feed_rank', $this->activeFeedTab === 'for_you'
                    ? $this->feedRankForRepost($repost, $friendIds, $followingIds)
                    : 0);
                $post->setRelation('feedRepostedBy', $repost->user);
                $post->setAttribute('feed_reposted_at', $repost->created_at);

                return $post;
            });

        $feedItems = $originalPosts
            ->concat($repostPosts)
            ->sort(function (Post $firstPost, Post $secondPost): int {
                $rankComparison = (int) $firstPost->feed_rank <=> (int) $secondPost->feed_rank;

                if ($rankComparison !== 0) {
                    return $rankComparison;
                }

                return ($secondPost->feed_sort_at?->getTimestamp() ?? 0) <=> ($firstPost->feed_sort_at?->getTimestamp() ?? 0);
            })
            ->values();

        $posts = new Paginator(
            $feedItems->take($this->perPage + 1),
            $this->perPage,
            1,
            ['path' => request()->url()]
        );

        $postAuthorIds = $posts->getCollection()
            ->pluck('user_id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
        $followedAuthorIds = $postAuthorIds === []
            ? []
            : $user->following()
                ->whereIn('users.id', $postAuthorIds)
                ->pluck('users.id')
                ->map(fn ($id): int => (int) $id)
                ->all();
        $friendAuthorIds = array_values(array_intersect($this->friendUserIds($user), $postAuthorIds));
        $availableCommunities = $user->activeCommunityMemberships()
            ->with('community')
            ->get()
            ->pluck('community')
            ->filter(fn ($community): bool => $community?->isActive() ?? false)
            ->values();

        return [
            'posts' => $posts,
            'currentUser' => $user,
            'availableCommunities' => $availableCommunities,
            'followedAuthorIds' => $followedAuthorIds,
            'friendAuthorIds' => $friendAuthorIds,
        ];
    }
};
?>
 <div class="ue-feed-layout">
    <div class="ue-feed-column">
        {{-- Page-local Header --}}
        <header class="ue-feed-header">
            {{-- Desktop: Title + Tabs side-by-side --}}
            <div class="hidden sm:flex ue-feed-header__top">
                <div>
                    <h1 class="text-xl font-bold text-slate-800">Bảng tin</h1>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">HCMUE Student-verified community updates</p>
                </div>
                
                {{-- Tabs --}}
                <div class="ue-feed-tabs">
                    <button
                        type="button"
                        wire:click="setFeedTab('for_you')"
                        wire:loading.attr="disabled"
                        wire:target="setFeedTab"
                        class="px-3 py-1.5 rounded-full text-xxs font-bold transition-colors {{ $activeFeedTab === 'for_you' ? 'bg-ue-brand-soft text-ue-brand' : 'text-slate-400 hover:bg-slate-50' }}"
                    >
                        Dành cho bạn
                    </button>
                    <button
                        type="button"
                        wire:click="setFeedTab('following')"
                        wire:loading.attr="disabled"
                        wire:target="setFeedTab"
                        class="px-3 py-1.5 rounded-full text-xxs font-bold transition-colors {{ $activeFeedTab === 'following' ? 'bg-ue-brand-soft text-ue-brand' : 'text-slate-400 hover:bg-slate-50' }}"
                    >
                        Theo dõi
                    </button>
                </div>
            </div>

            {{-- Mobile: Threads-style centered tab strip only --}}
            <div class="flex sm:hidden items-center justify-center border-b border-slate-100 pb-1">
                <button
                    type="button"
                    wire:click="setFeedTab('for_you')"
                    wire:loading.attr="disabled"
                    wire:target="setFeedTab"
                    class="flex-1 py-2 text-xs text-center transition-colors {{ $activeFeedTab === 'for_you' ? 'font-bold text-slate-800 border-b-2 border-slate-800' : 'font-medium text-slate-400 border-b-2 border-transparent' }}"
                >
                    Dành cho bạn
                </button>
                <button
                    type="button"
                    wire:click="setFeedTab('following')"
                    wire:loading.attr="disabled"
                    wire:target="setFeedTab"
                    class="flex-1 py-2 text-xs text-center transition-colors {{ $activeFeedTab === 'following' ? 'font-bold text-slate-800 border-b-2 border-slate-800' : 'font-medium text-slate-400 border-b-2 border-transparent' }}"
                >
                    Theo dõi
                </button>
            </div>
        </header>

        {{-- Toast system component --}}
        <x-ui.toast />

        {{-- Feed Surface Area --}}
        <section class="ue-feed-surface">
            {{-- Inline Composer --}}
            @if ($currentUser->isActive())
                <div class="ue-feed-composer border-b border-ue-border/40">
                    <div class="ue-composer">
                        {{-- Left Column: Avatar --}}
                        <div class="flex justify-start">
                            <x-ui.avatar :user="$currentUser" size="md" />
                        </div>
                        
                        {{-- Right Column: Form content --}}
                        <div class="min-w-0">
                            <form wire:submit.prevent="submitPost">
                                <div>
                                    <label for="post-body" class="sr-only">Nội dung bài viết</label>
                                    <textarea
                                        id="post-body"
                                        wire:model="body"
                                        placeholder="Có gì mới trong cộng đồng HCMUE hôm nay?"
                                        rows="2"
                                        class="ue-composer__textarea focus:outline-none"
                                        maxlength="3000"
                                    ></textarea>
                                    @error('body')
                                        <p class="text-xs text-red-650 font-semibold mt-1">{{ $message }}</p>
                                    @enderror

                                    {{-- Image Previews inside composer --}}
                                    @if (!empty($composerImages))
                                        <div class="grid grid-cols-4 gap-2 mt-2 select-none">
                                            @foreach ($composerImages as $index => $img)
                                                <div class="relative aspect-square rounded-xl border border-slate-150 overflow-hidden bg-slate-50">
                                                    <img src="{{ $img['url'] }}" alt="Preview" class="object-cover w-full h-full" />
                                                    <button 
                                                        type="button" 
                                                        wire:click="removeComposerImage({{ $index }})" 
                                                        class="absolute top-1 right-1 w-5 h-5 rounded-full bg-slate-900/60 hover:bg-slate-900/80 text-white flex items-center justify-center transition-colors"
                                                    >
                                                        <x-ui.icon name="x" size="xs" />
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @error('imageFiles')
                                        <p class="text-xs text-red-650 font-semibold mt-1">{{ $message }}</p>
                                    @enderror

                                    {{-- Uploading spinner indicator --}}
                                    <div wire:loading wire:target="imageFiles" class="mt-2 text-xxs font-bold text-slate-400 flex items-center gap-1.5">
                                        <span class="animate-spin rounded-full h-3.5 w-3.5 border border-slate-300 border-t-ue-brand"></span>
                                        Đang tải ảnh và xử lý phiên bản tối ưu...
                                    </div>
                                </div>

                                <div class="ue-composer__toolbar">
                                    <div class="ue-composer__actions flex items-center gap-2">
                                        {{-- Image Upload Trigger Button --}}
                                        <label class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-lg cursor-pointer transition-colors shadow-2xs flex items-center justify-center">
                                            <x-ui.icon name="image" size="xs" />
                                            <input type="file" wire:model="imageFiles" multiple class="hidden" accept="image/*" />
                                        </label>

                                        <span class="ue-composer__counter text-slate-400 text-xxs font-semibold">
                                            {{ mb_strlen($body) }}/3000
                                        </span>
                                        @php
                                            $visibilityLabel = match ($visibility) {
                                                'connections_only' => 'Chỉ bạn bè',
                                                'community' => 'Cộng đồng',
                                                default => 'Chỉ sinh viên xác thực',
                                            };
                                        @endphp
                                        <div class="relative">
                                            <label for="post-visibility" class="sr-only">Quyền xem</label>
                                            <select
                                                id="post-visibility"
                                                wire:model="visibility"
                                                class="absolute inset-0 w-full h-full opacity-0 z-10 cursor-pointer"
                                            >
                                                <option value="verified_users">Chỉ sinh viên xác thực</option>
                                                <option value="connections_only">Chỉ bạn bè</option>
                                                <option value="community">Chỉ cộng đồng</option>
                                            </select>
                                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 text-slate-500 rounded-lg select-none pointer-events-none">
                                                <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand/10" />
                                                <span class="hidden sm:inline text-xxs font-bold">{{ $visibilityLabel }}</span>
                                                <span class="sm:hidden text-[10px] font-bold">{{ $visibility === 'verified_users' ? 'Xác thực' : $visibilityLabel }}</span>
                                                <x-ui.icon name="chevron-down" size="xs" class="text-slate-400" />
                                            </div>
                                        </div>
                                        @if ($visibility === 'community')
                                            <div class="relative min-w-[150px]">
                                                <label for="post-community" class="sr-only">Chọn cộng đồng</label>
                                                <select
                                                    id="post-community"
                                                    wire:model="selectedCommunityId"
                                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xxs font-bold text-slate-600 focus:border-ue-brand/40 focus:ring-ue-brand/20"
                                                >
                                                    <option value="">Chọn cộng đồng</option>
                                                    @foreach ($availableCommunities as $community)
                                                        <option value="{{ $community->id }}">{{ $community->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                    @error('selectedCommunityId')
                                        <p class="text-xs text-red-650 font-semibold mt-1">{{ $message }}</p>
                                    @enderror
                                    <x-ui.button
                                        type="submit"
                                        variant="primary"
                                        size="sm"
                                        icon="send"
                                        wire:loading.attr="disabled"
                                        wire:target="submitPost,imageFiles"
                                    >
                                        <span wire:loading.remove wire:target="submitPost">Đăng bài</span>
                                        <span wire:loading wire:target="submitPost">Đang đăng...</span>
                                    </x-ui.button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Posts list loop --}}
            <div class="ue-feed-list">
                @forelse ($posts as $post)
                    @php
                        $isLiked = (int) $post->liked_by_current_user_count > 0;
                        $isSaved = (int) $post->saved_by_current_user_count > 0;
                        $isReposted = (int) $post->reposted_by_current_user_count > 0;
                        $likeCount = (int) $post->likes_count;
                        $commentCount = (int) $post->published_comments_count;
                        $repostCount = (int) $post->reposts_count;
                        $repostedBy = $post->relationLoaded('feedRepostedBy') ? $post->feedRepostedBy : null;
                    @endphp

                    @if (in_array($post->id, $locallyHiddenPostIds))
                        {{-- Hidden Post Placeholder --}}
                        <article class="ue-feed-item p-4 sm:p-5 bg-slate-50/50 flex items-center justify-between gap-4 ue-animate-fade-in" wire:key="hidden-post-placeholder-{{ $post->id }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 flex-shrink-0">
                                    <x-ui.icon name="eye-off" size="xs" />
                                </div>
                                <div class="space-y-0.5 text-left">
                                    <h4 class="text-xs font-bold text-slate-800">Đã ẩn bài viết</h4>
                                    <p class="text-[10px] text-slate-500 leading-normal">Việc ẩn bài viết giúp UEConnect cá nhân hóa Bảng tin của bạn.</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                wire:click="undoHidePost({{ $post->id }})"
                                class="px-3 py-1.5 text-xs font-bold text-ue-brand bg-ue-brand-soft border border-ue-brand/10 rounded-xl hover:bg-ue-brand hover:text-white transition-all flex-shrink-0"
                            >
                                Hoàn tác
                            </button>
                        </article>
                    @else
                        @php
                            $showQuickFollow = $post->user_id !== $currentUser->id
                                && ! in_array($post->user_id, $followedAuthorIds, true)
                                && ! in_array($post->user_id, $friendAuthorIds, true);
                        @endphp
                        <article class="ue-feed-item" wire:key="post-item-{{ $post->feed_item_key ?? $post->id }}">
                            <x-ui.post-card
                                :post="$post"
                                :currentUser="$currentUser"
                                :isSaved="$isSaved"
                                :isLiked="$isLiked"
                                :isReposted="$isReposted"
                                :likeCount="$likeCount"
                                :commentCount="$commentCount"
                                :repostCount="$repostCount"
                                :editingPostId="$editingPostId"
                                :editingBody="$editingBody"
                                :showQuickFollow="$showQuickFollow"
                                :repostedBy="$repostedBy"
                                :repostedAt="$post->feed_reposted_at"
                                :feedItemKey="$post->feed_item_key"
                                :showRepostAction="true"
                            />
                        </article>
                    @endif
                @empty
                    <div class="p-8">
                        <x-ui.empty-state
                            icon="message-square"
                            title="Bảng tin chưa có bài viết nào"
                            description="Hãy là người đầu tiên chia sẻ điều hữu ích với cộng đồng HCMUE."
                        >
                            @if ($currentUser->isActive())
                                <x-ui.button
                                    type="button"
                                    variant="primary"
                                    size="md"
                                    icon="edit"
                                    onclick="document.getElementById('post-body').focus()"
                                >
                                    Viết bài đầu tiên
                                </x-ui.button>
                            @endif
                        </x-ui.empty-state>
                    </div>
                @endforelse
            </div>

            {{-- End state / Infinite scroll sentinel inside feed surface --}}
            <div class="ue-feed-end-state">
                <div class="w-full flex flex-col items-center justify-center gap-2">
                    @if ($posts->hasMorePages())
                        <div
                            wire:intersect="loadMore"
                            class="flex flex-col items-center gap-2 py-2 text-center"
                        >
                            <span wire:loading.remove wire:target="loadMore" class="text-xxs text-slate-400 font-semibold">
                                Đang tải thêm bài viết...
                            </span>
                            <span wire:loading wire:target="loadMore" class="inline-flex items-center gap-2 text-xxs text-slate-400 font-semibold">
                                <span class="ue-spinner"></span>
                                Đang tải...
                            </span>
                        </div>
                    @else
                        <span class="text-xxs text-slate-400 font-semibold mb-1">Bạn đã xem hết bài viết hiện có.</span>
                    @endif
                </div>
            </div>
        </section>
    </div>

    {{-- Mobile bottom nav padding buffer --}}
    <div class="ue-mobile-bottom-spacer"></div>

    {{-- Modals & Sheets --}}
    <x-ui.create-post-modal
        :body="$body"
        :visibility="$visibility"
        :selectedCommunityId="$selectedCommunityId"
        :communities="$availableCommunities"
    />
    <x-ui.floating-action-button />

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
