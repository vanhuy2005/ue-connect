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
use App\Support\Media\MediaUrlResolver;
use App\Actions\Media\StoreTemporaryMediaAction;
use App\Actions\Follows\FollowUser;
use App\Actions\Follows\UnfollowUser;
use App\Enums\CommentStatus;
use App\Enums\ModerationStatus;
use App\Enums\PostStatus;
use App\Enums\PostType;
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

    private const FEED_PAGE_SIZE = 5;

    public bool $feedReady = false;

    public function loadInitialFeed(): void
    {
        $this->feedReady = true;
    }

    public function mount(): void
    {
        if (app()->environment('testing')) {
            $this->feedReady = true;
        }
    }

    // Composer properties
    public string $body = '';
    public string $visibility = 'verified_users';
    public int $perPage = self::FEED_PAGE_SIZE;
    public string $activeFeedTab = 'for_you';
    public string $activeTypeFilter = 'all';
    public ?int $selectedCommunityId = null;
    public array $selectedTags = [];
    
    // Modal state properties
    public bool $showVisModal = false;
    public bool $showTagModal = false;
    
    // Quick follow properties
    public bool $showQuickFollowModal = false;
    public ?int $quickFollowUserId = null;
    public bool $quickFollowCompleted = false;

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
        'selectedTags' => 'nullable|array',
        'selectedTags.*' => 'string|in:experience,opportunity,pedagogy',
    ];

    protected array $validationAttributes = [
        'body' => 'nội dung bài viết',
    ];

    protected array $messages = [
        'body.required' => 'Nội dung bài viết không được để trống.',
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
     * Hook to handle selected tags changes. If opportunity is selected, auto-suggest pedagogy if user is in pedagogy program/faculty.
     */
    public function updatedSelectedTags($value = null): void
    {
        $tags = $this->selectedTags;

        if (in_array('opportunity', $tags, true) && ! in_array('pedagogy', $tags, true)) {
            $user = Auth::user();
            $programName = $user->profile?->alumniProfile?->academicProgram?->name
                ?? $user->profile?->studentProfile?->academicProgram?->name
                ?? $user->profile?->advisorProfile?->academicProgram?->name
                ?? '';
            $facultyName = $user->profile?->faculty ?? '';

            $isPedagogy = str_contains(strtolower($programName), 'sư phạm') || str_contains(strtolower($facultyName), 'sư phạm');
            if ($isPedagogy) {
                $this->selectedTags[] = 'pedagogy';
            }
        }
    }

    /**
     * Handle temporary composer image uploads.
     */
    public function updatedImageFiles(): void
    {
        try {
            $this->validate([
                'imageFiles.*' => 'image|max:5120', // 5MB limit
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('imageFiles', $e->validator->errors()->first());
            $this->imageFiles = [];
            return;
        }

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
                    'url' => MediaUrlResolver::thumbnailUrl($media),
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
            'selectedTags' => 'nullable|array',
            'selectedTags.*' => 'string|in:experience,opportunity,pedagogy',
        ], [
            'body.required' => 'Nội dung bài viết không được để trống.',
            'selectedCommunityId.required_if' => 'Vui lòng chọn cộng đồng để đăng bài.',
            'selectedCommunityId.exists' => 'Cộng đồng đã chọn không khả dụng.',
        ]);

        $postData = [
            'body' => $this->body,
            'visibility' => $this->visibility,
            'community_id' => $this->selectedCommunityId,
            'tags' => $this->selectedTags,
        ];

        $post = $createPost->execute(Auth::user(), $postData);

        // Attach composer images polymorphically
        if (!empty($this->composerImages)) {
            $mediaIds = array_column($this->composerImages, 'id');
            app(AttachMediaToModelAction::class)->execute(Auth::user(), $post, $mediaIds, 'post_image');
        }

        $this->body = '';
        $this->visibility = PostVisibility::VERIFIED_USERS->value;
        $this->selectedCommunityId = null;
        $this->selectedTags = [];
        $this->composerImages = [];
        $this->imageFiles = [];
        $this->feedbackMessage = 'Đăng bài viết thành công.';
        $this->dispatch('post-created');
        $this->perPage = self::FEED_PAGE_SIZE;
        $this->resetPage(); // Re-render feed at page 1
    }

    /**
     * Mark an opportunity as expired.
     */
    public function markAsExpired(int $postId, \App\Actions\Posts\ModerateOpportunity $moderateOpportunity): void
    {
        try {
            $post = Post::findOrFail($postId);
            $moderateOpportunity->expire(Auth::user(), $post);
            $this->feedbackMessage = 'Đã đánh dấu cơ hội đã hết hạn.';
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
        }
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
        $this->dispatch('feed-updated');
    }

    /**
     * Open quick follow modal for the specified user.
     */
    public function openQuickFollowModal(int $authorId): void
    {
        $author = User::findOrFail($authorId);
        $currentUser = Auth::user();

        if ($author->id === $currentUser->id) {
            $this->feedbackMessage = 'Bạn không thể tự theo dõi chính mình.';
            return;
        }

        // Check if already active connections/friends
        $isFriend = Connection::where(function ($query) use ($currentUser, $author) {
                $query->where('user_one_id', $currentUser->id)->where('user_two_id', $author->id);
            })
            ->orWhere(function ($query) use ($currentUser, $author) {
                $query->where('user_one_id', $author->id)->where('user_two_id', $currentUser->id);
            })
            ->where('status', ConnectionStatus::ACTIVE)
            ->exists();

        if ($isFriend) {
            $this->feedbackMessage = 'Người dùng này đã kết nối bạn bè với bạn.';
            return;
        }

        $this->quickFollowUserId = $author->id;
        $this->quickFollowCompleted = \App\Models\UserFollow::where('follower_id', $currentUser->id)
            ->where('following_id', $author->id)
            ->exists();
        $this->showQuickFollowModal = true;
    }

    /**
     * Confirm quick follow from within the modal.
     */
    public function confirmQuickFollow(FollowUser $followUser): void
    {
        if (! $this->quickFollowUserId) {
            return;
        }

        $author = User::findOrFail($this->quickFollowUserId);

        try {
            $followUser->execute(Auth::user(), $author);
            $this->quickFollowCompleted = true;
            $this->feedbackMessage = 'Đã theo dõi ' . $author->name . '.';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->feedbackMessage = collect($e->errors())->flatten()->first() ?: 'Không thể theo dõi người dùng này.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Confirm quick unfollow from within the modal.
     */
    public function confirmQuickUnfollow(UnfollowUser $unfollowUser): void
    {
        if (! $this->quickFollowUserId) {
            return;
        }

        $author = User::findOrFail($this->quickFollowUserId);

        try {
            $unfollowUser->execute(Auth::user(), $author);
            $this->quickFollowCompleted = false;
            $this->feedbackMessage = 'Đã bỏ theo dõi ' . $author->name . '.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Close the quick follow modal.
     */
    public function closeQuickFollowModal(): void
    {
        $this->showQuickFollowModal = false;
        $this->quickFollowUserId = null;
        $this->quickFollowCompleted = false;
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

        $this->activeFeedTab = $tab;
        $this->activeTypeFilter = 'all';
        $this->perPage = self::FEED_PAGE_SIZE;
        $this->resetPage();
        $this->dispatch('feed-scroll-top');
    }

    /**
     * Switch the active post type filter.
     */
    public function setTypeFilter(string $filter): void
    {
        if (! in_array($filter, ['all', 'experience', 'career_insight', 'opportunity', 'pedagogy'], true)) {
            return;
        }

        if ($this->activeTypeFilter === $filter) {
            $this->activeTypeFilter = 'all';
        } else {
            $this->activeTypeFilter = $filter;
        }

        $this->perPage = self::FEED_PAGE_SIZE;
        $this->resetPage();
        $this->dispatch('feed-scroll-top');
    }

    /**
     * Build the base visible feed query shared by both tabs.
     */
    private function baseFeedQuery(User $user): Builder
    {
        $query = Post::with([
            'user.profile',
            'user' => function ($query) use ($user): void {
                $query->withCount(['followers as is_followed_by_current_user' => function ($q) use ($user): void {
                    $q->where('follower_id', $user->id);
                }]);
            },
            'media.variants'
        ])
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

        $query = $this->applyVisibleFeedPostConstraints($query, $user);

        if ($this->activeTypeFilter !== 'all') {
            if ($this->activeTypeFilter === 'experience') {
                $query->whereIn('post_type', ['experience', 'career_insight']);
            } elseif ($this->activeTypeFilter === 'pedagogy') {
                $query->where('post_type', 'opportunity')
                    ->whereHas('opportunity', fn ($q) => $q->where('category', 'pedagogy'));
            } else {
                $query->where('post_type', $this->activeTypeFilter);
            }
        }

        return $query;
    }

    /**
     * Apply post status, privacy, and local hide filters to a feed post query.
     */
    private function applyVisibleFeedPostConstraints($query, User $user)
    {
        $isAdminOrModerator = $user->hasRole('admin') || $user->can('manage_reports');

        return $query
            ->whereIn('status', [PostStatus::PUBLISHED, PostStatus::EDITED])
            ->visibleTo($user)
            ->where(function ($query) use ($user) {
                $query->whereDoesntHave('hides', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->orWhereIn('id', $this->locallyHiddenPostIds);
            })
            ->where(function ($query) use ($user, $isAdminOrModerator) {
                $query->where('post_type', '!=', PostType::OPPORTUNITY->value)
                    ->orWhereIn('moderation_status', [
                        ModerationStatus::APPROVED->value,
                        ModerationStatus::EXPIRED->value,
                    ])
                    ->orWhere('user_id', $user->id)
                    ->when($isAdminOrModerator, function ($q) {
                        $q->orWhere('moderation_status', ModerationStatus::PENDING->value);
                    });
            })
            ->where(function ($query) {
                $query->where('post_type', '!=', PostType::OPPORTUNITY->value)
                    ->orWhereIn('moderation_status', [
                        ModerationStatus::APPROVED->value,
                        ModerationStatus::EXPIRED->value,
                    ]);
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
                if ($this->activeTypeFilter !== 'all') {
                    if ($this->activeTypeFilter === 'experience') {
                        $query->whereIn('post_type', ['experience', 'career_insight']);
                    } elseif ($this->activeTypeFilter === 'pedagogy') {
                        $query->where('post_type', 'opportunity')
                            ->whereHas('opportunity', fn ($q) => $q->where('category', 'pedagogy'));
                    } else {
                        $query->where('post_type', $this->activeTypeFilter);
                    }
                }
            },
        ])->whereHas('post', function (Builder $query) use ($user): void {
            $this->applyVisibleFeedPostConstraints($query, $user);
            if ($this->activeTypeFilter !== 'all') {
                if ($this->activeTypeFilter === 'experience') {
                    $query->whereIn('post_type', ['experience', 'career_insight']);
                } elseif ($this->activeTypeFilter === 'pedagogy') {
                    $query->where('post_type', 'opportunity')
                        ->whereHas('opportunity', fn ($q) => $q->where('category', 'pedagogy'));
                } else {
                    $query->where('post_type', $this->activeTypeFilter);
                }
            }
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
    private function applyForYouRanking(
        Builder $query,
        array $friendIds,
        array $followingIds,
        array $communityIds
    ): Builder {
        $cases = [];
        $bindings = [];

        if ($friendIds !== []) {
            $cases[] = 'WHEN user_id IN ('.$this->placeholdersFor($friendIds).') THEN 0';
            array_push($bindings, ...$friendIds);
        }

        if ($followingIds !== []) {
            $cases[] = 'WHEN user_id IN ('.$this->placeholdersFor($followingIds).') THEN 1';
            array_push($bindings, ...$followingIds);
        }

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

        if (! $this->feedReady) {
            return [
                'posts' => new Paginator([], $this->perPage, 1, ['path' => request()->url()]),
                'currentUser' => $user,
                'availableCommunities' => $this->visibility === PostVisibility::COMMUNITY->value
                    ? $user->activeCommunityMemberships()
                        ->with('community')
                        ->get()
                        ->pluck('community')
                        ->filter(fn ($community): bool => $community?->isActive() ?? false)
                        ->values()
                    : collect(),
                'followedAuthorIds' => [],
                'friendAuthorIds' => [],
            ];
        }

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
            $this->applyForYouRanking($query, $friendIds, $followingIds, $communityIds);
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
        $friendAuthorIds = array_values(array_intersect($friendIds, $postAuthorIds));
        $availableCommunities = $this->visibility === PostVisibility::COMMUNITY->value
            ? $user->activeCommunityMemberships()
                ->with('community')
                ->get()
                ->pluck('community')
                ->filter(fn ($community): bool => $community?->isActive() ?? false)
                ->values()
            : collect();

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
<section data-home-feed-shell class="mx-auto flex flex-col h-full min-h-0 w-full max-w-[760px] overflow-hidden" wire:init="loadInitialFeed">
    {{-- Page-local Header --}}
    <header data-feed-header class="hidden lg:block min-h-0 pb-1 lg:pb-2 pt-3 lg:pt-4 bg-white lg:bg-transparent px-4 lg:px-0">
        <h1 class="text-xl lg:text-2xl font-bold text-ue-text">Bảng tin</h1>
        <p class="mt-0.5 lg:mt-1 text-[13px] lg:text-sm font-medium text-ue-text-muted hidden sm:block">
            HCMUE Student-verified community updates
        </p>
    </header>

    {{-- Feed tabs, separated from feed stream --}}
    <nav data-feed-tabs class="mb-0 lg:mb-3 overflow-hidden border-b border-slate-200 bg-white lg:rounded-[24px] lg:border lg:shadow-sm shadow-none rounded-none">
        <div class="flex h-11 lg:h-14 items-center overflow-x-auto ue-scrollbar-none px-4">
                {{-- Feed Tabs --}}
                <button
                    type="button"
                    wire:click="setFeedTab('for_you')"
                    class="relative flex h-full items-center px-4 sm:px-5 text-[15px] font-semibold transition-colors whitespace-nowrap {{ ($activeFeedTab === 'for_you' && $activeTypeFilter === 'all') ? 'text-ue-brand hover:text-ue-brand-active' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50/50' }}"
                >
                    Dành cho bạn
                    @if($activeFeedTab === 'for_you' && $activeTypeFilter === 'all')
                        <span class="absolute bottom-0 left-4 right-4 sm:left-5 sm:right-5 h-[2px] rounded-full bg-ue-brand"></span>
                    @endif
                </button>
                <button
                    type="button"
                    wire:click="setFeedTab('following')"
                    class="relative flex h-full items-center px-4 sm:px-5 text-[15px] font-semibold transition-colors whitespace-nowrap {{ ($activeFeedTab === 'following' && $activeTypeFilter === 'all') ? 'text-ue-brand hover:text-ue-brand-active' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50/50' }}"
                >
                    Theo dõi
                    @if($activeFeedTab === 'following' && $activeTypeFilter === 'all')
                        <span class="absolute bottom-0 left-4 right-4 sm:left-5 sm:right-5 h-[2px] rounded-full bg-ue-brand"></span>
                    @endif
                </button>

                <div class="h-4 w-px bg-slate-200 mx-2 flex-shrink-0"></div>

                {{-- Type Filters --}}
                <button
                    type="button"
                    wire:click="setTypeFilter('experience')"
                    class="relative flex h-full items-center px-4 text-[15px] font-semibold transition-colors whitespace-nowrap {{ $activeTypeFilter === 'experience' ? 'text-ue-brand hover:text-ue-brand-active' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50/50' }}"
                >
                    Kinh nghiệm
                    @if($activeTypeFilter === 'experience')
                        <span class="absolute bottom-0 left-4 right-4 h-[2px] rounded-full bg-ue-brand"></span>
                    @endif
                </button>
                <button
                    type="button"
                    wire:click="setTypeFilter('opportunity')"
                    class="relative flex h-full items-center px-4 text-[15px] font-semibold transition-colors whitespace-nowrap {{ $activeTypeFilter === 'opportunity' ? 'text-ue-brand hover:text-ue-brand-active' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50/50' }}"
                >
                    Cơ hội
                    @if($activeTypeFilter === 'opportunity')
                        <span class="absolute bottom-0 left-4 right-4 h-[2px] rounded-full bg-ue-brand"></span>
                    @endif
                </button>
                <button
                    type="button"
                    wire:click="setTypeFilter('pedagogy')"
                    class="relative flex h-full items-center px-4 text-[15px] font-semibold transition-colors whitespace-nowrap {{ $activeTypeFilter === 'pedagogy' ? 'text-ue-brand hover:text-ue-brand-active' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50/50' }}"
                >
                    Sư phạm
                    @if($activeTypeFilter === 'pedagogy')
                        <span class="absolute bottom-0 left-4 right-4 h-[2px] rounded-full bg-ue-brand"></span>
                    @endif
                </button>
        </div>
    </nav>

    {{-- Toast system component --}}
    <x-ui.toast />

    {{-- Feed Card Container (Fixed borders & background) --}}
    <div data-feed-card class="min-h-0 flex-1 flex flex-col border-0 bg-transparent shadow-none rounded-none overflow-visible lg:overflow-hidden lg:rounded-[28px] lg:border lg:border-slate-200 lg:bg-white lg:shadow-sm lg:mb-2">
        {{-- Scroll viewport --}}
        <div
            x-data="{
                loading: false,
                attempts: 0,
                checkScroll() {
                    const el = this.$refs.feedScroll;
                    if (!el) return;
                    
                    // If user scrolled near bottom and not loading
                    if (el.scrollTop + el.clientHeight >= el.scrollHeight - 500 && !this.loading) {
                        this.loading = true;
                        $wire.loadMore().then(() => {
                            this.loading = false;
                            this.ensureOverflow();
                        });
                    }
                },
                ensureOverflow() {
                    this.$nextTick(() => {
                        const el = this.$refs.feedScroll;
                        if (!el) return;

                        const tryLoad = () => {
                            if (this.attempts >= 3) return;

                            if (el.scrollHeight <= el.clientHeight) {
                                this.attempts++;
                                this.loading = true;
                                $wire.loadMore().then(() => {
                                    this.loading = false;
                                    setTimeout(tryLoad, 400);
                                });
                            }
                        };

                        tryLoad();
                    });
                }
            }"
            x-init="ensureOverflow()"
            x-on:feed-updated.window="ensureOverflow()"
            x-on:feed-scroll-top.window="$refs.feedScroll?.scrollTo({ top: 0, behavior: 'instant' })"
            x-ref="feedScroll"
            x-on:scroll.passive="checkScroll"
            data-feed-scroll
            class="feed-scroll min-h-0 overflow-y-auto overscroll-contain ue-scrollbar-none flex-1"
        >
            {{-- Feed stream lives here --}}
            <div data-feed-stream class="feed-stream flex flex-col lg:border-0 lg:bg-transparent lg:shadow-none">
            {{-- Inline Composer --}}
            @if ($currentUser->isActive())
                <div class="px-3 lg:px-4 py-3 lg:py-5 shrink-0">
                    <div class="ue-composer">
                        {{-- Left Column: Avatar --}}
                        <div class="flex justify-start">
                            <x-ui.avatar :user="$currentUser" size="md" />
                        </div>
                        
                        {{-- Right Column: Form content --}}
                        <div class="min-w-0">
                            <form wire:submit.prevent="submitPost">
                                {{-- Author name + controls row (Facebook-style) --}}
                                @php
                                    $visibilityLabel = match ($visibility) {
                                        'connections_only' => 'Chỉ bạn bè',
                                        'community'        => 'Cộng đồng',
                                        default            => 'Chỉ sinh viên xác thực',
                                    };
                                    $visibilityIcon = match ($visibility) {
                                        'connections_only' => 'users',
                                        'community'        => 'globe',
                                        default            => 'shield-check',
                                    };
                                    $postTypeLabel = 'Bài viết';
                                    if (! empty($selectedTags)) {
                                        $labelParts = [];
                                        if (in_array('experience', $selectedTags, true)) {
                                            $labelParts[] = 'Kinh nghiệm';
                                        }
                                        if (in_array('opportunity', $selectedTags, true)) {
                                            $labelParts[] = 'Cơ hội';
                                        }
                                        if (in_array('pedagogy', $selectedTags, true)) {
                                            $labelParts[] = 'Sư phạm';
                                        }
                                        if (! empty($labelParts)) {
                                            $postTypeLabel = implode(', ', $labelParts);
                                        }
                                    }
                                @endphp
                                <div class="flex flex-wrap items-center gap-1.5 mb-2 select-none">
                                    <span class="text-sm font-bold text-slate-800 mr-0.5">{{ $currentUser->name }}</span>

                                    {{-- Visibility custom panel (Facebook-style) --}}
                                    <div class="relative flex-shrink-0">
                                        <button
                                            type="button"
                                            wire:click="$set('showVisModal', true)"
                                            class="flex items-center gap-1 px-2 py-1 bg-slate-100 text-slate-600 border border-slate-200 rounded-lg whitespace-nowrap flex-shrink-0 hover:bg-slate-200 transition-colors cursor-pointer"
                                        >
                                            <x-ui.icon name="{{ $visibilityIcon }}" size="xs" class="text-ue-brand flex-shrink-0" />
                                            <span class="text-xxs font-bold">{{ $visibilityLabel }}</span>
                                            <x-ui.icon name="chevron-down" size="xs" class="text-slate-400 flex-shrink-0" />
                                        </button>

                                        @if ($showVisModal)
                                        <div x-data="{ selectedVis: @js($visibility) }" wire:ignore>
                                        {{-- Center Modal Overlay --}}
                                        <div
                                            x-show="true"
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0"
                                            x-transition:enter-end="opacity-100"
                                            x-transition:leave="transition ease-in duration-200"
                                            x-transition:leave-start="opacity-100"
                                            x-transition:leave-end="opacity-0"
                                            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
                                            @click.self="$wire.set('showVisModal', false)"
                                        >
                                            <div
                                                class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden ue-animate-scale-in"
                                                @click.stopPropagation()
                                            >
                                                {{-- Header --}}
                                                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                                                    <div class="flex items-center gap-2">
                                                        <h3 class="text-sm font-bold text-slate-900">Ai có thể xem bài viết của bạn?</h3>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        @click="$wire.set('showVisModal', false); $wire.set('visibility', selectedVis)"
                                                        class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                                                        aria-label="Đóng"
                                                    >
                                                        <x-ui.icon name="x" size="xs" />
                                                    </button>
                                                </div>

                                                {{-- Sub-header --}}
                                                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-100">
                                                    <p class="text-xs text-slate-500 font-semibold leading-relaxed">Bài viết sẽ hiển thị cho những người được chọn.</p>
                                                </div>

                                                {{-- Options --}}
                                                <div class="divide-y divide-slate-100">
                                                    <button
                                                        type="button"
                                                        @click="selectedVis = 'verified_users'"
                                                        class="w-full flex items-center justify-between px-5 py-4 hover:bg-slate-50 transition-colors"
                                                        x-bind:class="selectedVis === 'verified_users' ? 'bg-ue-brand-soft/40' : ''"
                                                    >
                                                        <div class="text-left min-w-0">
                                                            <p class="text-sm font-bold text-slate-800">Sinh viên xác thực</p>
                                                            <p class="text-xs text-slate-500 mt-0.5">Chỉ sinh viên đã xác thực tài khoản mới thấy</p>
                                                        </div>
                                                        <div class="flex-shrink-0 ml-3">
                                                            <div x-show="selectedVis === 'verified_users'" class="w-5 h-5 rounded-full border-2 border-ue-brand bg-ue-brand flex items-center justify-center">
                                                                <div class="w-2 h-2 rounded-full bg-white"></div>
                                                            </div>
                                                            <div x-show="selectedVis !== 'verified_users'" class="w-5 h-5 rounded-full border-2 border-slate-300"></div>
                                                        </div>
                                                    </button>

                                                    <button
                                                        type="button"
                                                        @click="selectedVis = 'connections_only'"
                                                        class="w-full flex items-center justify-between px-5 py-4 hover:bg-slate-50 transition-colors"
                                                        x-bind:class="selectedVis === 'connections_only' ? 'bg-ue-brand-soft/40' : ''"
                                                    >
                                                        <div class="text-left min-w-0">
                                                            <p class="text-sm font-bold text-slate-800">Chỉ bạn bè</p>
                                                            <p class="text-xs text-slate-500 mt-0.5">Chỉ những người bạn đã kết nối mới thấy</p>
                                                        </div>
                                                        <div class="flex-shrink-0 ml-3">
                                                            <div x-show="selectedVis === 'connections_only'" class="w-5 h-5 rounded-full border-2 border-ue-brand bg-ue-brand flex items-center justify-center">
                                                                <div class="w-2 h-2 rounded-full bg-white"></div>
                                                            </div>
                                                            <div x-show="selectedVis !== 'connections_only'" class="w-5 h-5 rounded-full border-2 border-slate-300"></div>
                                                        </div>
                                                    </button>

                                                    <button
                                                        type="button"
                                                        @click="selectedVis = 'community'"
                                                        class="w-full flex items-center justify-between px-5 py-4 hover:bg-slate-50 transition-colors"
                                                        x-bind:class="selectedVis === 'community' ? 'bg-ue-brand-soft/40' : ''"
                                                    >
                                                        <div class="text-left min-w-0">
                                                            <p class="text-sm font-bold text-slate-800">Cộng đồng</p>
                                                            <p class="text-xs text-slate-500 mt-0.5">Chỉ thành viên cộng đồng được chọn mới thấy</p>
                                                        </div>
                                                        <div class="flex-shrink-0 ml-3">
                                                            <div x-show="selectedVis === 'community'" class="w-5 h-5 rounded-full border-2 border-ue-brand bg-ue-brand flex items-center justify-center">
                                                                <div class="w-2 h-2 rounded-full bg-white"></div>
                                                            </div>
                                                            <div x-show="selectedVis !== 'community'" class="w-5 h-5 rounded-full border-2 border-slate-300"></div>
                                                        </div>
                                                    </button>
                                                </div>

                                                {{-- Footer --}}
                                                <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-end bg-slate-50">
                                                    <button
                                                        type="button"
                                                        @click="$wire.set('showVisModal', false); $wire.set('visibility', selectedVis)"
                                                        class="px-5 py-2 rounded-xl bg-ue-brand hover:bg-ue-brand-dark text-white text-xs font-bold transition-colors cursor-pointer"
                                                    >
                                                        Xong
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        @endif
                                    </div>

                                </div>

                                    {{-- Tag Modal Overlay (triggered from bottom section) --}}
                                    @if ($currentUser->canPostType(\App\Enums\PostType::EXPERIENCE))
                                        @if ($showTagModal)
                                        <div x-data="{ localTags: [...$wire.selectedTags] }" wire:ignore>
                                        {{-- Center Modal Overlay --}}
                                        <div
                                            x-show="true"
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0"
                                            x-transition:enter-end="opacity-100"
                                            x-transition:leave="transition ease-in duration-200"
                                            x-transition:leave-start="opacity-100"
                                            x-transition:leave-end="opacity-0"
                                            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
                                            @click.self="$wire.set('showTagModal', false)"
                                        >
                                                <div
                                                    class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden ue-animate-scale-in"
                                                    @click.stopPropagation()
                                                >
                                                    {{-- Header --}}
                                                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                                                        <div class="flex items-center gap-2">
                                                            <h3 class="text-sm font-bold text-slate-900">Chọn nhãn bài viết</h3>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            @click="$wire.set('showTagModal', false); $wire.set('selectedTags', localTags)"
                                                            class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                                                            aria-label="Đóng"
                                                        >
                                                            <x-ui.icon name="x" size="xs" />
                                                        </button>
                                                    </div>

                                                    {{-- Sub-header info --}}
                                                    <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-100">
                                                        <p class="text-xs text-slate-500 font-semibold leading-relaxed">Chọn các nhãn để người đọc dễ dàng lọc và tìm kiếm bài viết của bạn. Bạn có thể gắn nhiều nhãn cùng lúc.</p>
                                                    </div>

                                                    {{-- Content --}}
                                                    <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                                                        {{-- Option 1: Kinh nghiệm --}}
                                                        <label class="flex items-center justify-between px-5 py-4 hover:bg-slate-50/50 transition-colors cursor-pointer">
                                                            <div class="min-w-0">
                                                                <p class="text-sm font-bold text-slate-800">Kinh nghiệm</p>
                                                                <p class="text-xs text-slate-400 mt-0.5">Chia sẻ kinh nghiệm học tập hoặc công việc</p>
                                                            </div>
                                                            <input
                                                                type="checkbox"
                                                                x-model="localTags"
                                                                value="experience"
                                                                class="w-5 h-5 rounded border-slate-300 text-ue-brand focus:ring-ue-brand/20 cursor-pointer flex-shrink-0 ml-3"
                                                            />
                                                        </label>

                                                        {{-- Option 2: Cơ hội --}}
                                                        @if ($currentUser->canPostType(\App\Enums\PostType::OPPORTUNITY))
                                                            <label class="flex items-center justify-between px-5 py-4 hover:bg-slate-50/50 transition-colors cursor-pointer">
                                                                <div class="min-w-0">
                                                                    <p class="text-sm font-bold text-slate-800">Cơ hội</p>
                                                                    <p class="text-xs text-slate-400 mt-0.5">Đăng cơ hội việc làm, học bổng hoặc sự kiện</p>
                                                                </div>
                                                                <input
                                                                    type="checkbox"
                                                                    x-model="localTags"
                                                                    value="opportunity"
                                                                    class="w-5 h-5 rounded border-slate-300 text-ue-brand focus:ring-ue-brand/20 cursor-pointer flex-shrink-0 ml-3"
                                                                />
                                                            </label>
                                                        @endif

                                                        {{-- Option 3: Sư phạm --}}
                                                        @if ($currentUser->canPostType(\App\Enums\PostType::OPPORTUNITY))
                                                            <label class="flex items-center justify-between px-5 py-4 hover:bg-slate-50/50 transition-colors cursor-pointer">
                                                                <div class="min-w-0">
                                                                    <p class="text-sm font-bold text-slate-800">Sư phạm</p>
                                                                    <p class="text-xs text-slate-400 mt-0.5">Nội dung thuộc khối ngành Sư phạm</p>
                                                                </div>
                                                                <input
                                                                    type="checkbox"
                                                                    x-model="localTags"
                                                                    value="pedagogy"
                                                                    class="w-5 h-5 rounded border-slate-300 text-ue-brand focus:ring-ue-brand/20 cursor-pointer flex-shrink-0 ml-3"
                                                                />
                                                            </label>
                                                        @endif
                                                    </div>

                                                    {{-- Footer --}}
                                                    <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-end bg-slate-50">
                                                        <button
                                                            type="button"
                                                            @click="$wire.set('showTagModal', false); $wire.set('selectedTags', localTags)"
                                                            class="px-5 py-2 rounded-xl bg-ue-brand hover:bg-ue-brand-dark text-white text-xs font-bold transition-colors cursor-pointer"
                                                        >
                                                            Xong
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    @endif

                                <div>
                                    <div class="flex items-start gap-2">
                                        <div class="flex-1 min-w-0">
                                            <label for="post-body" class="sr-only">Nội dung bài viết</label>
                                            <textarea
                                                id="post-body"
                                                wire:model="body"
                                                placeholder="Có gì mới trong cộng đồng HCMUE hôm nay?"
                                                rows="2"
                                                class="ue-composer__textarea focus:outline-none ue-text-body"
                                                maxlength="3000"
                                            ></textarea>
                                    </div>
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
                                    <div class="ue-composer__actions flex items-center gap-2 flex-shrink-0">
                                        {{-- Image Upload Trigger Button --}}
                                        <label class="ue-composer__media-btn p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-lg cursor-pointer transition-colors shadow-2xs flex items-center justify-center flex-shrink-0">
                                            <x-ui.icon name="image" size="md" />
                                            <input type="file" wire:model="imageFiles" multiple class="hidden" accept="image/*" />
                                        </label>

                                        {{-- Tag Modal Trigger Button --}}
                                        @if ($currentUser->canPostType(\App\Enums\PostType::EXPERIENCE))
                                            <button
                                                type="button"
                                                wire:click="$set('showTagModal', true)"
                                                class="relative p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-lg cursor-pointer transition-colors shadow-2xs flex items-center justify-center flex-shrink-0 {{ !empty($selectedTags) ? 'text-ue-brand border-ue-brand/30 bg-ue-brand-soft/30' : '' }}"
                                                title="Gắn nhãn bài viết"
                                            >
                                                <x-ui.icon name="tag" size="md" />
                                                @if (!empty($selectedTags))
                                                    <span class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-ue-brand text-white text-[9px] font-bold rounded-full flex items-center justify-center border-2 border-white">
                                                        {{ count($selectedTags) }}
                                                    </span>
                                                @endif
                                            </button>
                                        @endif

                                        <span class="ue-composer__counter text-slate-400 text-xxs font-semibold whitespace-nowrap flex-shrink-0">
                                            {{ mb_strlen($body) }}/3000
                                        </span>

                                        {{-- Community selector (only shown if visibility = community) --}}
                                        @if ($visibility === 'community')
                                            <div class="relative min-w-[120px] flex-shrink-0">
                                                <label for="post-community" class="sr-only">Chọn cộng đồng</label>
                                                <select
                                                    id="post-community"
                                                    wire:model="selectedCommunityId"
                                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xxs font-bold text-slate-600 focus:border-ue-brand/40 focus:ring-ue-brand/20"
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
                                        class="ue-composer__submit-btn flex-shrink-0"
                                    >
                                        <span wire:loading.remove wire:target="submitPost">Đăng bài</span>
                                        <span wire:loading wire:target="submitPost">Đang đăng...</span>
                                    </x-ui.button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="h-px bg-slate-200 w-full mt-4 mb-2"></div>
            @endif

            {{-- Real-time New Posts Banner --}}
            <div 
                x-data="{ showBanner: false, count: 0 }"
                x-init="if (window.Echo) { window.Echo.private('feed').listen('.PostCreated', (e) => { if (e.author_id != {{ Auth::id() ?? 'null' }}) { count++; showBanner = true; } }); }"
                x-show="showBanner"
                x-collapse
                class="mb-4"
                style="display: none;"
            >
                <button 
                    type="button"
                    @click="showBanner = false; count = 0; $wire.$refresh();"
                    class="w-full py-2 px-4 bg-ue-brand text-white font-bold text-xxs rounded-xl shadow-md hover:bg-ue-brand-dark transition-all flex items-center justify-center gap-2 animate-bounce"
                >
                    <x-ui.icon name="arrow-up" size="xs" />
                    <span>Có <span x-text="count"></span> bài viết mới. Nhấn để tải lại</span>
                </button>
            </div>

            {{-- Posts list loop --}}
            @if (! $feedReady)
                <x-ui.feed-skeleton :count="5" />
            @else
                <div wire:loading wire:target="setFeedTab, setTypeFilter">
                    <x-ui.feed-skeleton :count="4" />
                </div>

                <div wire:loading.remove wire:target="setFeedTab, setTypeFilter">
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
                                    $authorId = (int) $post->user_id;
                                    $isSelfAuthor = $authorId === (int) $currentUser->id;
                                    $isFriendAuthor = in_array($authorId, $friendAuthorIds, true);
                                    $isFollowedAuthor = in_array($authorId, $followedAuthorIds, true);

                                    $showQuickFollow = ! $isSelfAuthor && ! $isFriendAuthor && ! $isFollowedAuthor;
                                    $showFollowCheck = ! $isSelfAuthor && ! $isFriendAuthor && $isFollowedAuthor;
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
                                        :showFollowCheck="$showFollowCheck"
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
                </div>
            @endif

            {{-- End state / Infinite scroll sentinel inside feed surface --}}
            <div class="flex min-h-[96px] items-center justify-center px-6 py-8 text-sm text-slate-500 {{ !$posts->hasMorePages() ? 'border-b border-slate-200 sm:border-b-0' : '' }}">
                @if ($posts->hasMorePages())
                    <div
                        class="flex w-full flex-col items-center gap-2"
                    >
                        <div wire:loading.remove wire:target="loadMore" class="h-5 w-5 animate-spin rounded-full border-2 border-slate-200 border-t-ue-brand"></div>
                        <div wire:loading wire:target="loadMore" class="flex flex-col items-center gap-3 w-full">
                            <span class="h-5 w-5 animate-spin rounded-full border-2 border-slate-200 border-t-ue-brand"></span>
                            <span class="text-xs font-medium">Đang tải thêm bài viết...</span>
                            <div class="w-full mt-2">
                                <x-ui.feed-skeleton :count="2" />
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center">
                        <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-ue-brand-soft/50 text-ue-brand">
                            <x-ui.icon name="check-circle" size="md" />
                        </div>
                        <p class="font-medium text-slate-700">Bạn đã xem hết bài viết hiện có.</p>
                        <p class="mt-1 text-xs text-slate-500">Quay lại sau để xem thêm cập nhật mới từ cộng đồng HCMUE.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

    {{-- Mobile bottom nav padding buffer --}}
    <div class="ue-mobile-bottom-spacer"></div>

    {{-- Modals & Sheets --}}
    <x-ui.create-post-modal
        :body="$body"
        :visibility="$visibility"
        :selectedCommunityId="$selectedCommunityId"
        :communities="$availableCommunities"
        :selectedTags="$selectedTags"
        :canPostExperience="$currentUser->canPostType(\App\Enums\PostType::EXPERIENCE)"
        :canPostOpportunity="$currentUser->canPostType(\App\Enums\PostType::OPPORTUNITY)"
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

    {{-- QUICK FOLLOW MODAL --}}
    @if ($showQuickFollowModal && $quickFollowUserId)
        @php
            $quickFollowUser = \App\Models\User::find($quickFollowUserId);
        @endphp
        @if ($quickFollowUser)
            @php
                $quickFollowFollowersCount = \App\Models\UserFollow::where('following_id', $quickFollowUser->id)->count();
                $quickFollowUserFaculty = $quickFollowUser->profile?->faculty;
                $quickFollowDisplayName = $quickFollowUser->profile?->display_name ?? $quickFollowUser->name;
                $quickFollowUsername = $quickFollowUser->username ?? \Illuminate\Support\Str::slug($quickFollowUser->name, '');
            @endphp
            <div
                class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs ue-animate-fade-in"
                role="dialog"
                aria-modal="true"
                aria-labelledby="follow-modal-title"
                x-data
                @keydown.escape.window="$wire.closeQuickFollowModal()"
            >
                <div 
                    class="bg-white rounded-2xl max-w-sm w-full border border-slate-200 shadow-2xl overflow-hidden ue-animate-scale-in"
                    @click.outside="$wire.closeQuickFollowModal()"
                >
                    {{-- Header with Close button --}}
                    <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Thông tin người dùng</span>
                        <button 
                            type="button" 
                            wire:click="closeQuickFollowModal" 
                            class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-50"
                            aria-label="Đóng modal"
                        >
                            <x-ui.icon name="x" size="xs" />
                        </button>
                    </div>

                    {{-- Body with user details --}}
                    <div class="p-6 flex flex-col items-center text-center">
                        <x-ui.avatar :user="$quickFollowUser" size="xl" class="mb-4 shadow-sm" />
                        
                        <h3 id="follow-modal-title" class="text-lg font-bold text-slate-800 flex items-center gap-1.5 justify-center leading-tight">
                            <span>{{ $quickFollowDisplayName }}</span>
                            @if ($quickFollowUser->isActive())
                                <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                            @endif
                        </h3>
                        
                        <p class="text-xs text-slate-400 font-semibold mt-1">
                            @<span>{{ $quickFollowUsername }}</span>
                        </p>

                        @if ($quickFollowUserFaculty)
                            <p class="text-[10px] text-slate-500 font-bold bg-slate-50 border border-slate-150 px-2 py-0.5 rounded-full mt-2.5">
                                Khoa {{ $quickFollowUserFaculty }}
                            </p>
                        @endif

                        <div class="mt-4 flex items-center gap-1 text-xs text-slate-500 font-medium">
                            <span class="font-bold text-slate-800">{{ $quickFollowFollowersCount }}</span>
                            <span>{{ $quickFollowFollowersCount > 1 ? 'người theo dõi' : 'người theo dõi' }}</span>
                        </div>
                    </div>

                    {{-- Footer with actions --}}
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col gap-2">
                        @if ($quickFollowCompleted)
                            <button
                                type="button"
                                wire:click="confirmQuickUnfollow"
                                wire:loading.attr="disabled"
                                wire:target="confirmQuickUnfollow"
                                class="w-full py-2.5 px-4 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-xl transition-all flex items-center justify-center gap-1.5"
                            >
                                <span wire:loading.remove wire:target="confirmQuickUnfollow" class="flex items-center gap-1.5 justify-center w-full">
                                    Bỏ theo dõi
                                </span>
                                <span wire:loading wire:target="confirmQuickUnfollow" class="flex items-center gap-1.5 justify-center w-full">
                                    <span class="animate-spin rounded-full h-3 w-3 border border-slate-500 border-t-transparent"></span>
                                    Đang xử lý...
                                </span>
                            </button>
                        @else
                            <button
                                type="button"
                                wire:click="confirmQuickFollow"
                                wire:loading.attr="disabled"
                                wire:target="confirmQuickFollow"
                                class="w-full py-2.5 px-4 text-xs font-bold text-white bg-ue-brand hover:bg-ue-brand-dark rounded-xl shadow-2xs hover:shadow-sm transition-all flex items-center justify-center gap-1.5"
                            >
                                <span wire:loading.remove wire:target="confirmQuickFollow" class="flex items-center gap-1.5 justify-center w-full">
                                    Theo dõi
                                </span>
                                <span wire:loading wire:target="confirmQuickFollow" class="flex items-center gap-1.5 justify-center w-full">
                                    <span class="animate-spin rounded-full h-3 w-3 border border-white border-t-transparent"></span>
                                    Đang xử lý...
                                </span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif
</section>
