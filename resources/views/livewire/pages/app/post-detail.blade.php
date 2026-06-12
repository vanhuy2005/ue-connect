<?php

use App\Actions\Comments\CreateComment;
use App\Actions\Comments\DeleteComment;
use App\Actions\Comments\UpdateComment;
use App\Actions\Posts\TogglePostLike;
use App\Actions\Posts\TogglePostSave;
use App\Actions\Posts\DeletePost;
use App\Actions\Posts\UpdatePost;
use App\Actions\Posts\TogglePostRepost;
use App\Actions\Posts\ModerateOpportunity;
use App\Actions\Reports\CreateReport;
use App\Actions\Messaging\SendSharedPostMessage;
use App\Actions\Messaging\FindOrCreateDirectConversation;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Enums\ModerationStatus;
use App\Models\Opportunity;
use App\Enums\ReportReason;
use App\Enums\ConnectionStatus;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Post;
use App\Models\User;
use App\Models\Connection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Post $post;

    // Comment composer fields
    public string $commentBody = '';
    public ?int $replyingToCommentId = null;

    // Comment edit fields
    public ?int $editingCommentId = null;
    public string $editingCommentBody = '';

    // Post edit fields
    public bool $isEditingPost = false;
    public string $editingPostBody = '';
    public bool $editingOppIsPedagogy = false;

    // Report properties
    public ?Comment $reportingComment = null;
    public ?Post $reportingPost = null;
    public string $reportReason = 'spam';
    public string $reportDescription = '';
    public bool $showReportModal = false;
    public string $reportType = 'comment'; // 'post' or 'comment'

    // Custom delete modal properties
    public ?int $deletingPostId = null;
    public ?int $deletingCommentId = null;
    public bool $showDeleteModal = false;
    public string $deleteType = 'comment'; // 'post' or 'comment'

    // Feedback message
    public ?string $feedbackMessage = null;

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
        'commentBody' => 'required|string|max:1000',
    ];

    /**
     * Initialize the component.
     */
    public function mount(Post $post): void
    {
        Gate::authorize('view', $post);
        $this->post = $post->loadMissing(['user.profile', 'likes', 'saves', 'media.variants', 'reposts']);
    }

    /**
     * Submit a comment or reply.
     */
    public function submitComment(CreateComment $createComment): void
    {
        $this->validate();

        try {
            $createComment->execute(Auth::user(), $this->post, [
                'body' => $this->commentBody,
                'parent_id' => $this->replyingToCommentId,
            ]);

            $this->commentBody = '';
            $this->replyingToCommentId = null;
            $this->feedbackMessage = 'Đăng bình luận thành công.';
            $this->dispatch('comment-created');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('commentBody', $e->getMessage());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Set reply focus comment.
     */
    public function setReplyingTo(?int $commentId): void
    {
        $this->replyingToCommentId = $commentId;
        $this->commentBody = '';
    }

    /**
     * Toggle post like from detail.
     */
    public function togglePostLike(int $postId, TogglePostLike $togglePostLike): void
    {
        try {
            $post = Post::findOrFail($postId);
            $togglePostLike->execute(Auth::user(), $post);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Toggle post save from detail.
     */
    public function togglePostSave(int $postId, TogglePostSave $togglePostSave): void
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
     * Toggle post repost from detail.
     */
    public function togglePostRepost(int $postId, TogglePostRepost $togglePostRepost): void
    {
        try {
            $post = Post::findOrFail($postId);
            $isReposted = $togglePostRepost->execute(Auth::user(), $post);
            
            // Reload post relations to update the UI
            $this->post->load('reposts');
            
            $this->feedbackMessage = $isReposted ? 'Đã đăng lại bài viết.' : 'Đã hủy đăng lại bài viết.';
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Toggle comment like.
     */
    public function toggleCommentLike(int $commentId): void
    {
        $user = Auth::user();
        if (! $user->isActive()) {
            return;
        }

        $comment = Comment::findOrFail($commentId);

        $existingLike = CommentLike::where('comment_id', $comment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            CommentLike::create([
                'comment_id' => $comment->id,
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Start post editing.
     */
    public function startPostEdit(): void
    {
        if (! Gate::allows('update', $this->post)) {
            $this->feedbackMessage = 'Bạn không có quyền chỉnh sửa bài viết này.';
            return;
        }

        $this->isEditingPost = true;
        $this->editingPostBody = $this->post->body;

        $opp = $this->post->opportunity;
        if ($opp) {
            $this->editingOppIsPedagogy = ($opp->category === 'pedagogy');
        }

        $this->feedbackMessage = null;
    }

    /**
     * Save post edits.
     */
    public function savePostEdit(UpdatePost $updatePost): void
    {
        try {
            $this->validate([
                'editingPostBody' => 'required|string|max:3000',
            ]);

            $updatePost->execute(Auth::user(), $this->post, [
                'body' => $this->editingPostBody,
            ]);

            $opp = $this->post->opportunity;
            if ($opp && $opp->exists) {
                $opp->update([
                    'category' => $this->editingOppIsPedagogy ? 'pedagogy' : 'non_pedagogy',
                ]);
            }

            $this->isEditingPost = false;
            $this->editingPostBody = '';
            $this->editingOppIsPedagogy = false;
            $this->feedbackMessage = 'Đã cập nhật bài viết thành công.';
            $this->post->refresh();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('editingPostBody', $e->getMessage());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
            $this->isEditingPost = false;
            $this->editingPostBody = '';
        }
    }

    /**
     * Trigger customized delete post modal.
     */
    public function openPostDeleteModal(int $postId): void
    {
        $this->deletingPostId = $postId;
        $this->deleteType = 'post';
        $this->showDeleteModal = true;
    }

    /**
     * Execute post delete.
     */
    public function executePostDelete(DeletePost $deletePost): void
    {
        try {
            $deletePost->execute(Auth::user(), $this->post);
            $this->feedbackMessage = 'Đã xóa bài viết thành công.';
            
            // Redirect back to dashboard since post is deleted
            $this->redirect(route('dashboard'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
        }

        $this->deletingPostId = null;
        $this->showDeleteModal = false;
    }

    /**
     * Mark the opportunity post as expired.
     */
    public function markAsExpired(ModerateOpportunity $moderateOpportunity, ?int $postId = null): void
    {
        try {
            $post = $postId ? Post::findOrFail($postId) : $this->post;
            $moderateOpportunity->expire(Auth::user(), $post);
            $this->feedbackMessage = 'Đã đánh dấu cơ hội đã hết hạn.';
            if ($post->id === $this->post->id) {
                $this->post->refresh();
            }
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Start comment editing.
     */
    public function startCommentEdit(int $commentId): void
    {
        $comment = Comment::findOrFail($commentId);

        if (! Gate::allows('update', $comment)) {
            $this->feedbackMessage = 'Bạn không có quyền chỉnh sửa bình luận này.';
            return;
        }

        $this->editingCommentId = $commentId;
        $this->editingCommentBody = $comment->body;
        $this->feedbackMessage = null;
    }

    /**
     * Save comment edits.
     */
    public function saveCommentEdit(UpdateComment $updateComment): void
    {
        if (! $this->editingCommentId) {
            return;
        }

        try {
            $comment = Comment::findOrFail($this->editingCommentId);
            
            $this->validate([
                'editingCommentBody' => 'required|string|max:1000',
            ]);

            $updateComment->execute(Auth::user(), $comment, [
                'body' => $this->editingCommentBody,
            ]);

            $this->editingCommentId = null;
            $this->editingCommentBody = '';
            $this->feedbackMessage = 'Đã cập nhật bình luận thành công.';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('editingCommentBody', $e->getMessage());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
            $this->editingCommentId = null;
            $this->editingCommentBody = '';
        }
    }

    /**
     * Cancel comment editing.
     */
    public function cancelCommentEdit(): void
    {
        $this->editingCommentId = null;
        $this->editingCommentBody = '';
    }

    /**
     * Trigger customized delete comment modal.
     */
    public function openCommentDeleteModal(int $commentId): void
    {
        $this->deletingCommentId = $commentId;
        $this->deleteType = 'comment';
        $this->showDeleteModal = true;
    }

    /**
     * Execute comment delete.
     */
    public function executeCommentDelete(DeleteComment $deleteComment): void
    {
        if (! $this->deletingCommentId) {
            return;
        }

        try {
            $comment = Comment::findOrFail($this->deletingCommentId);
            $deleteComment->execute(Auth::user(), $comment);
            $this->feedbackMessage = 'Đã xóa bình luận thành công.';
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
        }

        $this->deletingCommentId = null;
        $this->showDeleteModal = false;
    }

    /**
     * Open report comment modal.
     */
    public function openCommentReport(int $commentId): void
    {
        $this->reportingComment = Comment::findOrFail($commentId);
        $this->reportType = 'comment';
        $this->reportReason = 'spam';
        $this->reportDescription = '';
        $this->showReportModal = true;
        $this->feedbackMessage = null;
        $this->resetErrorBag();
    }

    /**
     * Open report post modal.
     */
    public function openPostReport(int $postId): void
    {
        $this->reportingPost = Post::findOrFail($postId);
        $this->reportType = 'post';
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
        if ($this->reportType === 'comment' && $this->reportingComment) {
            try {
                $createReport->execute(Auth::user(), $this->reportingComment, [
                    'reason' => $this->reportReason,
                    'description' => $this->reportDescription,
                ]);

                $this->showReportModal = false;
                $this->reportingComment = null;
                $this->feedbackMessage = 'Báo cáo của bạn đã được gửi. Cảm ơn bạn đã đóng góp xây dựng môi trường HCMUE an toàn.';
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->addError('report', $e->getMessage());
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->showReportModal = false;
                $this->reportingComment = null;
                $this->feedbackMessage = $e->getMessage();
            }
        } elseif ($this->reportType === 'post' && $this->reportingPost) {
            try {
                $createReport->execute(Auth::user(), $this->reportingPost, [
                    'reason' => $this->reportReason,
                    'description' => $this->reportDescription,
                ]);

                $this->showReportModal = false;
                $this->reportingPost = null;
                $this->feedbackMessage = 'Báo cáo của bạn đã được gửi. Cảm ơn bạn đã đóng góp xây dựng môi trường HCMUE an toàn.';
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->addError('report', $e->getMessage());
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->showReportModal = false;
                $this->reportingPost = null;
                $this->feedbackMessage = $e->getMessage();
            }
        }
    }

    /**
     * Close report modal.
     */
    public function closeReport(): void
    {
        $this->showReportModal = false;
        $this->reportingComment = null;
        $this->reportingPost = null;
    }

    /**
     * Render parameters.
     */
    public function with(): array
    {
        $user = Auth::user();

        // Check if post is visible to standard user
        $isPostVisible = Gate::forUser($user)->allows('view', $this->post);

        // Load active top-level comments with replies (filtering strictly by PUBLISHED and EDITED, or deleted comments with active replies using withTrashed())
        $comments = Comment::withTrashed()
            ->with(['user.profile', 'likes', 'replies' => function ($query) {
                $query->whereIn('status', [CommentStatus::PUBLISHED, CommentStatus::EDITED])->with(['user.profile', 'likes']);
            }])
            ->where('post_id', $this->post->id)
            ->whereNull('parent_id')
            ->where(function ($query) {
                $query->whereIn('status', [CommentStatus::PUBLISHED, CommentStatus::EDITED])
                    ->orWhere(function ($q) {
                        $q->whereIn('status', [
                            CommentStatus::DELETED_BY_OWNER, 
                            CommentStatus::DELETED_BY_MODERATION, 
                            CommentStatus::HIDDEN_BY_MODERATION
                        ])->whereHas('replies', function ($replyQuery) {
                            $replyQuery->whereIn('status', [CommentStatus::PUBLISHED, CommentStatus::EDITED]);
                        });
                    });
            })
            ->oldest()
            ->get();

        return [
            'comments' => $comments,
            'currentUser' => $user,
            'isPostVisible' => $isPostVisible,
        ];
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
     * Search users for mentions dropdown.
     */
    public function searchMentionUsers(string $search): array
    {
        $search = trim($search);
        if ($search === '') {
            return [];
        }

        $currentUserId = Auth::id();

        // Get active connection user IDs
        $friendIds = Connection::where(function ($query) use ($currentUserId) {
                $query->where('user_one_id', $currentUserId)
                    ->orWhere('user_two_id', $currentUserId);
            })
            ->where('status', ConnectionStatus::ACTIVE)
            ->get()
            ->map(function ($conn) use ($currentUserId) {
                return $conn->user_one_id === $currentUserId ? $conn->user_two_id : $conn->user_one_id;
            })
            ->push($currentUserId) // Allow self tagging
            ->toArray();

        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();

        // Get matching users from connections/self
        $users = User::whereIn('id', $friendIds)
            ->where(function ($query) use ($search, $driver) {
                if ($driver === 'sqlsrv') {
                    $query->whereRaw("name COLLATE Latin1_General_CI_AI LIKE ?", ["%{$search}%"])
                        ->orWhereHas('profile', function ($q) use ($search) {
                            $q->whereRaw("display_name COLLATE Latin1_General_CI_AI LIKE ?", ["%{$search}%"]);
                        });
                } else {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhereHas('profile', function ($q) use ($search) {
                            $q->where('display_name', 'like', "%{$search}%");
                        });
                }
            })
            ->with(['profile', 'profilePrivacySetting'])
            ->get();

        // Filter based on their privacy preferences
        $filteredUsers = $users->filter(function ($targetUser) use ($currentUserId) {
            if ($targetUser->id === $currentUserId) {
                return true;
            }

            // Respect the target user's privacy preference
            $privacy = $targetUser->profilePrivacySetting;
            $preference = $privacy ? $privacy->mentions_preference : 'everyone';

            if ($preference === 'nobody') {
                return false;
            }

            return true;
        });

        $results = $filteredUsers->take(5)
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'display_name' => $u->profile?->display_name ?? $u->name,
                'avatar_url' => $u->profile?->avatar_url ?? null,
            ])
            ->values()
            ->toArray();

        return $results;
    }
};
?>

<div class="ue-feed-layout">
    <div class="ue-feed-column">

        {{-- Back button row --}}
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-ue-brand mb-2 transition-colors font-semibold px-4 lg:px-0">
            <x-ui.icon name="arrow-left" size="xs" />
            Quay lại bảng tin
        </a>

        {{-- System feedback alerts --}}
        @if ($feedbackMessage)
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start gap-2 shadow-xs ue-animate-fade-in" role="alert">
                <x-ui.icon name="check-circle" size="sm" class="text-emerald-600 mt-0.5 flex-shrink-0" />
                <div class="flex-1 font-semibold">{{ $feedbackMessage }}</div>
                <button type="button" wire:click="$set('feedbackMessage', null)" class="text-emerald-400 hover:text-emerald-600 transition-colors">
                    <x-ui.icon name="x" size="xs" />
                </button>
            </div>
        @endif

        {{-- 1. POST DETAIL OR MODERATION PLACEHOLDER --}}
        @if (! $isPostVisible)
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-8 text-center text-slate-500 shadow-xs flex flex-col items-center gap-3 ue-animate-scale-in">
                <x-ui.icon name="alert-triangle" size="lg" class="text-slate-400" />
                <p class="text-sm font-semibold">Nội dung này không còn khả dụng hoặc đã bị ẩn do vi phạm quy chuẩn cộng đồng.</p>
            </div>
        @else
            @php
                $author = $post->user;
                $profile = $author->profile;
                $authorProfileUrl = route('profile.show', $author);
                $isLiked = $post->likes->where('user_id', $currentUser->id)->isNotEmpty();
                $isSaved = $post->saves->where('user_id', $currentUser->id)->isNotEmpty();
                $likeCount = $post->likes->count();
                $isOwner = $post->user_id === $currentUser->id;
                $mediaUrlAction = app(\App\Actions\Media\GenerateMediaUrlAction::class);
                $mediaItems = $post->relationLoaded('media')
                    ? $post->media->where('status', 'ready')->values()
                    : $post->media()->where('status', 'ready')->with('variants')->get();
                $mediaCount = $mediaItems->count();
                $commentCount = $post->comments()->whereIn('status', [\App\Enums\CommentStatus::PUBLISHED, \App\Enums\CommentStatus::EDITED])->count();
                $repostCount = $post->reposts->count();
                $isReposted = $post->reposts->where('user_id', $currentUser->id)->isNotEmpty();
            @endphp

            <div class="ue-feed-surface">
                <div class="ue-post-card">
                    <div class="ue-post-card__body">
                        {{-- Left Avatar Column --}}
                        <div class="flex-shrink-0 flex justify-start">
                            <a href="{{ $authorProfileUrl }}" class="block rounded-full focus:outline-none focus:ring-2 focus:ring-ue-brand/30" aria-label="Xem trang cá nhân của {{ $author->name }}">
                                <x-ui.avatar :user="$author" size="md" />
                            </a>
                        </div>

                        {{-- Right Content Column --}}
                        <div class="flex-1 min-w-0">
                            {{-- Header --}}
                            <div class="ue-post-card__header">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ $authorProfileUrl }}" class="text-[15px] font-bold text-slate-800 leading-tight hover:text-ue-brand hover:underline">
                                            {{ $author->name }}
                                        </a>
                                        <x-ui.icon name="check-circle" size="xs" class="text-ue-brand flex-shrink-0" />
                                        <span class="ue-post-card__meta" title="{{ $post->published_at->format('H:i d/m/Y') }}">
                                            · {{ $post->published_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    @if ($profile)
                                        <div class="text-xs text-slate-400 font-medium mt-1 leading-none">
                                            {{ Str::ucfirst($profile->role_type) }}
                                            @if ($profile->faculty)
                                                · {{ $profile->faculty }}
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Post Type Badge --}}
                                    @php
                                        $tags = $post->tags ?? [];
                                        if (empty($tags)) {
                                            if ($post->post_type && $post->post_type !== PostType::STANDARD) {
                                                if ($post->post_type === PostType::EXPERIENCE || $post->post_type === PostType::CAREER_INSIGHT) {
                                                    $tags[] = 'experience';
                                                } elseif ($post->post_type === PostType::OPPORTUNITY) {
                                                    $tags[] = 'opportunity';
                                                    if ($post->opportunity?->category === 'pedagogy') {
                                                        $tags[] = 'pedagogy';
                                                    }
                                                }
                                            }
                                        }
                                    @endphp

                                    @if (! empty($tags) || ($post->post_type === PostType::OPPORTUNITY && ($post->opportunity?->is_expired || $post->moderation_status !== ModerationStatus::NONE)))
                                        <div class="mt-1.5 flex items-center gap-2 flex-wrap">
                                            @foreach ($tags as $tag)
                                                @if ($tag === 'experience')
                                                    <x-ui.badge variant="experience" size="sm" no-icon>Kinh nghiệm</x-ui.badge>
                                                @elseif ($tag === 'opportunity')
                                                    <x-ui.badge variant="opportunity" size="sm" no-icon>Cơ hội</x-ui.badge>
                                                @elseif ($tag === 'pedagogy')
                                                    <x-ui.badge variant="pedagogy" size="sm" no-icon>Sư phạm</x-ui.badge>
                                                @endif
                                            @endforeach

                                            @if ($post->post_type === PostType::OPPORTUNITY)
                                                @if ($post->opportunity?->is_expired)
                                                    <x-ui.badge variant="danger" size="sm" no-icon>Đã hết hạn</x-ui.badge>
                                                @endif
                                                @if ($post->moderation_status === ModerationStatus::PENDING)
                                                    <x-ui.badge variant="pending" size="sm" no-icon>Chờ duyệt</x-ui.badge>
                                                @elseif ($post->moderation_status === ModerationStatus::REJECTED)
                                                    <x-ui.badge variant="danger" size="sm" no-icon>Đã từ chối</x-ui.badge>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Options dropdown --}}
                                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                    <button
                                        type="button"
                                        @click="open = !open"
                                        class="text-slate-400 hover:text-slate-605 focus:outline-none focus:ring-1 focus:ring-slate-100 rounded-full p-0.5"
                                        aria-label="Tùy chọn bài viết"
                                    >
                                        <x-ui.icon name="more-horizontal" size="xs" />
                                    </button>
                                    <div
                                        x-show="open"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute right-0 mt-1 rounded-xl bg-white border border-ue-border shadow-lg py-1 z-10"
                                        style="display: none; width: 180px;"
                                    >
                                        <a
                                            href="{{ $authorProfileUrl }}"
                                            class="w-full text-left px-3 py-1.5 text-xxs font-semibold text-slate-700 hover:bg-slate-50 hover:text-ue-brand flex items-center gap-2"
                                        >
                                            <x-ui.icon name="user" size="xs" class="text-slate-400" />
                                            <span>Xem trang cá nhân</span>
                                        </a>
                                        @if ($isOwner)
                                            {{-- Expire Opportunity --}}
                                            @if ($post->post_type === PostType::OPPORTUNITY && $post->opportunity && !$post->opportunity->is_expired)
                                                <button
                                                    type="button"
                                                    wire:click="markAsExpired({{ $post->id }})"
                                                    @click="open = false"
                                                    class="w-full text-left px-3 py-1.5 text-xxs font-semibold text-slate-700 hover:bg-slate-50 hover:text-ue-brand flex items-center gap-2"
                                                >
                                                    <x-ui.icon name="clock" size="xs" class="text-slate-400" />
                                                    <span>Đánh dấu hết hạn</span>
                                                </button>
                                            @endif

                                            @if (! $isEditingPost)
                                                <button
                                                    type="button"
                                                    wire:click="startPostEdit"
                                                    @click="open = false"
                                                    class="w-full text-left px-3 py-1.5 text-xxs font-semibold text-slate-700 hover:bg-slate-50 hover:text-ue-brand flex items-center gap-2"
                                                >
                                                    <x-ui.icon name="edit" size="xs" class="text-slate-400" />
                                                    <span>Chỉnh sửa</span>
                                                </button>
                                            @endif
                                            <button
                                                type="button"
                                                wire:click="openPostDeleteModal({{ $post->id }})"
                                                @click="open = false"
                                                class="w-full text-left px-3 py-1.5 text-xxs font-semibold text-red-600 hover:bg-red-50 flex items-center gap-2"
                                            >
                                                <x-ui.icon name="trash" size="xs" class="text-red-400" />
                                                <span>Xóa bài viết</span>
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                wire:click="openPostReport({{ $post->id }})"
                                                @click="open = false"
                                                class="w-full text-left px-3 py-1.5 text-xxs font-semibold text-slate-700 hover:bg-yellow-50 hover:text-yellow-750 flex items-center gap-2"
                                            >
                                                <x-ui.icon name="flag" size="xs" class="text-slate-400" />
                                                <span>Báo cáo bài viết</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Body Content --}}
                            @if ($isEditingPost)
                                <div class="mt-2 space-y-3 bg-slate-50 p-3 rounded-xl border border-slate-100 ue-animate-fade-in">
                                    <label for="edit-post-body" class="sr-only">Nội dung chỉnh sửa bài viết</label>
                                    <textarea
                                        id="edit-post-body"
                                        wire:model="editingPostBody"
                                        rows="3"
                                        class="w-full border-0 focus:ring-0 p-0 text-slate-700 text-sm resize-none bg-transparent"
                                        maxlength="3000"
                                    ></textarea>
                                    @error('editingPostBody')
                                        <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                                    @enderror

                                    @if ($post->post_type === PostType::OPPORTUNITY)
                                        <div class="border border-blue-200 bg-blue-50/40 rounded-lg p-3 space-y-2.5">
                                            <div class="flex items-center gap-2 text-slate-705">
                                                <input
                                                    type="checkbox"
                                                    id="edit-opp-is-pedagogy"
                                                    wire:model="editingOppIsPedagogy"
                                                    class="rounded border-blue-300 text-ue-brand focus:ring-ue-brand/20 focus:ring-2"
                                                />
                                                <label for="edit-opp-is-pedagogy" class="text-xs font-bold text-slate-700 cursor-pointer select-none">
                                                    Đây là cơ hội thuộc khối ngành Sư phạm
                                                </label>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                                        <span class="text-[10px] text-slate-400 font-semibold">
                                            {{ mb_strlen($editingPostBody) }}/3000
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <button 
                                                type="button" 
                                                wire:click="$set('isEditingPost', false)" 
                                                class="px-2.5 py-1.5 text-xxs font-bold text-slate-500 hover:text-slate-700 transition-colors"
                                            >
                                                Hủy
                                            </button>
                                            <x-ui.button
                                                type="button"
                                                wire:click="savePostEdit"
                                                variant="primary"
                                                size="xs"
                                                icon="check"
                                            >
                                                Lưu thay đổi
                                            </x-ui.button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="ue-post-card__content mt-2 text-slate-800 text-sm sm:text-base whitespace-pre-wrap leading-relaxed">{{ $post->body }}</div>
                                {{-- Polymorphic Media Grid --}}
                                @if ($mediaCount > 0)
                                    @php
                                        $lightboxImages = $mediaItems->map(function ($item) use ($mediaUrlAction, $currentUser) {
                                            return $mediaUrlAction->execute($item, 'detail', $currentUser) ?? $mediaUrlAction->execute($item, 'original', $currentUser);
                                        })->values()->toJson();
                                    @endphp
                                    <div class="mt-3 w-full max-w-lg select-none mr-auto">
                                        @if ($mediaCount === 1)
                                            {{-- 1 image: full width, smart ratio --}}
                                            <div class="ue-media-frame">
                                                <a href="#" @click.prevent="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { images: {!! htmlspecialchars($lightboxImages, ENT_QUOTES, 'UTF-8') !!}, index: 0 } }))" class="block">
                                                    <img
                                                        src="{{ $mediaUrlAction->execute($mediaItems[0], 'feed', $currentUser) }}"
                                                        alt="Hình ảnh đính kèm"
                                                        class="ue-media-image"
                                                        loading="lazy"
                                                    />
                                                </a>
                                            </div>
                                        @elseif ($mediaCount === 2)
                                            {{-- 2 images: two columns --}}
                                            <div class="grid grid-cols-2 gap-2 overflow-hidden rounded-2xl border border-slate-150 bg-slate-50">
                                                @foreach ($mediaItems as $mediaItem)
                                                    <a href="#" @click.prevent="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { images: {!! htmlspecialchars($lightboxImages, ENT_QUOTES, 'UTF-8') !!}, index: {{ $loop->index }} } }))" class="aspect-[4/3] overflow-hidden block">
                                                        <img 
                                                            src="{{ $mediaUrlAction->execute($mediaItem, 'feed', $currentUser) }}" 
                                                            alt="Hình ảnh đính kèm" 
                                                            class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-300 cursor-zoom-in"
                                                            loading="lazy"
                                                        />
                                                    </a>
                                                @endforeach
                                            </div>
                                        @elseif ($mediaCount === 3)
                                            {{-- 3 images: one large + two stacked --}}
                                            <div class="grid grid-cols-3 gap-2 overflow-hidden rounded-2xl border border-slate-150 bg-slate-50">
                                                <a href="#" @click.prevent="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { images: {!! htmlspecialchars($lightboxImages, ENT_QUOTES, 'UTF-8') !!}, index: 0 } }))" class="col-span-2 aspect-[4/3] overflow-hidden block">
                                                    <img 
                                                        src="{{ $mediaUrlAction->execute($mediaItems[0], 'feed', $currentUser) }}" 
                                                        alt="Hình ảnh" 
                                                        class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-300 cursor-zoom-in"
                                                        loading="lazy"
                                                    />
                                                </a>
                                                <div class="grid grid-rows-2 gap-2">
                                                    @foreach ($mediaItems->slice(1, 2) as $mediaItem)
                                                        <a href="#" @click.prevent="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { images: {!! htmlspecialchars($lightboxImages, ENT_QUOTES, 'UTF-8') !!}, index: {{ $loop->index }} } }))" class="aspect-square overflow-hidden block">
                                                            <img 
                                                                src="{{ $mediaUrlAction->execute($mediaItem, 'feed', $currentUser) }}" 
                                                                alt="Hình ảnh" 
                                                                class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-300 cursor-zoom-in"
                                                                loading="lazy"
                                                            />
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @elseif ($mediaCount >= 4)
                                            {{-- 4 images: 2x2 grid --}}
                                            <div class="grid grid-cols-2 gap-2 overflow-hidden rounded-2xl border border-slate-150 bg-slate-50">
                                                @foreach ($mediaItems->take(4) as $mediaItem)
                                                    <a href="#" @click.prevent="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { images: {!! htmlspecialchars($lightboxImages, ENT_QUOTES, 'UTF-8') !!}, index: {{ $loop->index }} } }))" class="aspect-[4/3] overflow-hidden block">
                                                        <img 
                                                            src="{{ $mediaUrlAction->execute($mediaItem, 'feed', $currentUser) }}" 
                                                            alt="Hình ảnh" 
                                                            class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-300 cursor-zoom-in"
                                                            loading="lazy"
                                                        />
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @elseif ($post->media_url)
                                    <div class="mt-3 w-full max-w-lg select-none mr-auto">
                                        <div class="ue-media-frame">
                                            <a href="#" @click.prevent="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { images: ['{{ $post->media_url }}'], index: 0 } }))" class="block">
                                                <img src="{{ $post->media_url }}" alt="Media post" class="ue-media-image" />
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                {{-- Edited Indicator --}}
                                @if ($post->status === PostStatus::EDITED)
                                    <span class="inline-block mt-2 text-[9px] font-bold text-slate-400 bg-slate-50 border border-slate-100 rounded px-1.5 py-0.5">
                                        Đã chỉnh sửa
                                    </span>
                                @endif
                            @endif

                            {{-- Actions row --}}
                            <div class="ue-post-card__actions mt-4 pt-3 border-t border-slate-100/65 gap-x-4 sm:gap-x-6">
                                {{-- Like --}}
                                <x-ui.post-action-button
                                    icon="heart"
                                    activeIcon="heart"
                                    label="Thích"
                                    :count="$likeCount"
                                    :selected="$isLiked"
                                    danger="true"
                                    wireClick="togglePostLike({{ $post->id }})"
                                />

                                {{-- Comments Link --}}
                                <button
                                    type="button"
                                    onclick="document.getElementById('comment-text').focus()"
                                    class="ue-action-button flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-ue-brand transition-colors"
                                >
                                    <x-ui.icon name="message-circle" size="md" class="ue-action-button__icon text-current" />
                                    <span class="ue-action-button__count">{{ $commentCount }}</span>
                                </button>

                                {{-- Repost --}}
                                @if (! $isOwner)
                                    <x-ui.post-action-button
                                        icon="repost"
                                        activeIcon="repost"
                                        label="Đăng lại"
                                        :count="$repostCount"
                                        :selected="$isReposted"
                                        wireClick="togglePostRepost({{ $post->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="togglePostRepost({{ $post->id }})"
                                    />
                                @endif

                                {{-- Share --}}
                                <x-ui.post-action-button
                                    icon="send"
                                    label="Chia sẻ"
                                    wireClick="startShare({{ $post->id }})"
                                />

                                {{-- Save Toggle --}}
                                <div class="ml-auto">
                                    <x-ui.post-action-button
                                        icon="bookmark"
                                        activeIcon="bookmark"
                                        label="Lưu"
                                        :selected="$isSaved"
                                        wireClick="togglePostSave({{ $post->id }})"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. COMMENT COMPOSER --}}
            @if ($currentUser->isActive())
                <div class="ue-feed-composer border border-ue-border/60 rounded-2xl bg-white shadow-xs mt-6">
                    <div class="ue-composer">
                        {{-- Left Column: Avatar --}}
                        <div class="flex justify-start">
                            <x-ui.avatar :user="$currentUser" size="md" />
                        </div>
                        
                        {{-- Right Column: Form --}}
                        <div class="min-w-0 relative" x-data="{
                            showDropdown: false,
                            suggestions: [],
                            searchQuery: '',
                            cursorPosition: 0,
                            selectedIndex: 0,
                            handleInput(event) {
                                const textarea = event.target;
                                const value = textarea.value;
                                const pos = textarea.selectionStart;
                                this.cursorPosition = pos;

                                const textBeforeCursor = value.slice(0, pos);
                                const lastAt = textBeforeCursor.lastIndexOf('@');

                                if (lastAt !== -1 && (lastAt === 0 || /\s/.test(textBeforeCursor[lastAt - 1]))) {
                                    const query = textBeforeCursor.slice(lastAt + 1);
                                    if (query.length > 0 && query.length <= 50 && !/\s{2,}/.test(query) && !/^\s/.test(query)) {
                                        this.searchQuery = query;
                                        this.$wire.searchMentionUsers(query).then(results => {
                                            this.suggestions = results;
                                            this.showDropdown = results.length > 0;
                                            this.selectedIndex = 0;
                                        });
                                        return;
                                    }
                                }
                                this.closeDropdown();
                            },
                            selectNext() {
                                if (!this.showDropdown) return;
                                this.selectedIndex = (this.selectedIndex + 1) % this.suggestions.length;
                            },
                            selectPrev() {
                                if (!this.showDropdown) return;
                                this.selectedIndex = (this.selectedIndex - 1 + this.suggestions.length) % this.suggestions.length;
                            },
                            confirmSelection() {
                                if (!this.showDropdown || this.suggestions.length === 0) return;
                                this.insertMention(this.suggestions[this.selectedIndex]);
                            },
                            insertMention(user) {
                                const textarea = document.getElementById('comment-text');
                                const value = textarea.value;
                                const pos = this.cursorPosition;
                                
                                const textBeforeCursor = value.slice(0, pos);
                                const lastAt = textBeforeCursor.lastIndexOf('@');
                                
                                const before = value.slice(0, lastAt);
                                const after = value.slice(pos);
                                
                                const mentionText = '@' + user.display_name + ' ';
                                const newValue = before + mentionText + after;
                                
                                textarea.value = newValue;
                                this.$wire.set('commentBody', newValue);
                                
                                textarea.focus();
                                const newPos = lastAt + mentionText.length;
                                this.$nextTick(() => {
                                    textarea.setSelectionRange(newPos, newPos);
                                });
                                
                                this.closeDropdown();
                            },
                            closeDropdown() {
                                this.showDropdown = false;
                                this.suggestions = [];
                                this.searchQuery = '';
                                this.selectedIndex = 0;
                            }
                        }">
                            @if ($replyingToCommentId)
                                <div class="mb-3 px-3 py-1.5 rounded-lg bg-blue-50 border border-blue-100 text-xxs text-ue-brand font-bold flex items-center justify-between ue-animate-fade-in">
                                    <span>Đang phản hồi một bình luận</span>
                                    <button type="button" wire:click="setReplyingTo(null)" class="text-slate-400 hover:text-slate-650 transition-colors">
                                        Hủy bỏ
                                    </button>
                                </div>
                            @endif

                            <form wire:submit.prevent="submitComment">
                                <div>
                                    <label for="comment-text" class="sr-only">Nội dung bình luận</label>
                                    <textarea
                                        id="comment-text"
                                        wire:model="commentBody"
                                        @input="handleInput($event)"
                                        @keydown.arrow-down.prevent="showDropdown ? selectNext() : true"
                                        @keydown.arrow-up.prevent="showDropdown ? selectPrev() : true"
                                        @keydown.enter="showDropdown ? ($event.preventDefault() || confirmSelection()) : true"
                                        @keydown.escape="showDropdown ? ($event.preventDefault() || closeDropdown()) : true"
                                        placeholder="{{ $replyingToCommentId ? 'Nhập phản hồi của bạn...' : 'Viết bình luận công khai...' }}"
                                        rows="2"
                                        class="ue-composer__textarea focus:outline-none"
                                        maxlength="1000"
                                    ></textarea>
                                    @error('commentBody')
                                        <p class="text-xs text-red-650 font-semibold mt-1">{{ $message }}</p>
                                    @enderror

                                    {{-- Suggestion Dropdown --}}
                                    <div 
                                        x-show="showDropdown" 
                                        x-transition
                                        class="absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg z-50 max-h-48 overflow-y-auto divide-y divide-slate-50"
                                        style="display: none;"
                                    >
                                        <template x-for="(user, index) in suggestions" :key="user.id">
                                            <button
                                                type="button"
                                                @click="insertMention(user)"
                                                @mouseenter="selectedIndex = index"
                                                class="w-full text-left px-4 py-2 flex items-center gap-3 transition-colors"
                                                x-bind:class="selectedIndex === index ? 'bg-slate-50 text-ue-brand' : 'text-slate-700'"
                                            >
                                                <img :src="user.avatar_url || 'https://www.gravatar.com/avatar/' + user.id + '?d=mp&s=100'" class="w-6 h-6 rounded-full object-cover border border-slate-100" />
                                                <div class="flex-1 min-w-0">
                                                    <span class="text-xxs font-bold block truncate" x-text="user.display_name"></span>
                                                    <span class="text-[9px] text-slate-400 block truncate" x-text="'@' + user.name"></span>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <div class="ue-composer__toolbar">
                                    <div class="ue-composer__actions">
                                        <span class="ue-composer__counter">
                                            {{ mb_strlen($commentBody) }}/1000
                                        </span>
                                    </div>

                                    <x-ui.button
                                        type="submit"
                                        variant="primary"
                                        size="sm"
                                        icon="send"
                                    >
                                        {{ $replyingToCommentId ? 'Gửi phản hồi' : 'Bình luận' }}
                                    </x-ui.button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 3. COMMENTS LIST (THREADS-STYLE THREADING) --}}
            <div class="bg-white border border-slate-150 rounded-2xl p-4 sm:p-5 shadow-xs space-y-6 mt-6">
                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-1.5 pb-3 border-b border-slate-100">
                    <x-ui.icon name="message-circle" size="xs" class="text-ue-brand" />
                    Thảo luận cộng đồng
                </h3>

                <div class="space-y-6 divide-y divide-slate-100">
                    @forelse ($comments as $comment)
                        <x-ui.comment-item
                            :comment="$comment"
                            :currentUser="$currentUser"
                            :replyingToCommentId="$replyingToCommentId"
                            :editingCommentId="$editingCommentId"
                            :editingCommentBody="$editingCommentBody"
                            :isReply="false"
                        />
                    @empty
                        <div class="py-8 text-center text-slate-400 text-sm">
                            Chưa có bình luận nào cho bài viết này. Hãy chia sẻ ý kiến của bạn!
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

    </div>

    {{-- Mobile bottom nav padding buffer --}}
    <div class="ue-mobile-bottom-spacer"></div>

    {{-- REPORT MODALS --}}
    @if ($showReportModal)
        @php
            $modalTitle = $reportType === 'post' ? 'Báo cáo bài viết vi phạm' : 'Báo cáo bình luận vi phạm';
        @endphp
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs ue-animate-fade-in" role="dialog" aria-modal="true" aria-labelledby="detail-report-title">
            <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden ue-animate-scale-in">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 id="detail-report-title" class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <x-ui.icon name="alert-triangle" size="xs" class="text-yellow-600" />
                        {{ $modalTitle }}
                    </h3>
                    <button type="button" wire:click="closeReport" class="text-slate-400 hover:text-slate-655 transition-colors">
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
                            placeholder="Mô tả cụ thể lý do..."
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
                        >
                            Gửi báo cáo
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- CUSTOM DELETE CONFIRMATION MODALS --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs ue-animate-fade-in" role="dialog" aria-modal="true" aria-labelledby="delete-confirm-title">
            <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden ue-animate-scale-in">
                <div class="p-6 text-center space-y-4">
                    <div class="w-12 h-12 rounded-full bg-red-50 border border-red-100 flex items-center justify-center mx-auto text-red-650">
                        <x-ui.icon name="trash" size="md" />
                    </div>
                    <div class="space-y-2">
                        @if ($deleteType === 'post')
                            <h3 id="delete-confirm-title" class="text-base font-bold text-slate-800">Xóa bài viết?</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Bài viết sẽ không còn hiển thị trong bảng tin. Bạn không thể hoàn tác thao tác này trong phiên bản hiện tại.
                            </p>
                        @else
                            <h3 id="delete-confirm-title" class="text-base font-bold text-slate-800">Xóa bình luận?</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Bình luận này sẽ bị ẩn khỏi bài viết. Bạn không thể hoàn tác thao tác này.
                            </p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button type="button" wire:click="$set('showDeleteModal', false)" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 transition-colors">
                        Hủy
                    </button>
                    @if ($deleteType === 'post')
                        <x-ui.button
                            type="button"
                            wire:click="executePostDelete"
                            variant="danger"
                            size="sm"
                            icon="trash"
                        >
                            Xóa bài viết
                        </x-ui.button>
                    @else
                        <x-ui.button
                            type="button"
                            wire:click="executeCommentDelete"
                            variant="danger"
                            size="sm"
                            icon="trash"
                        >
                            Xóa bình luận
                        </x-ui.button>
                    @endif
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
                        @if (empty($selectedShareUserIds)) disabled @endif
                        class="px-4 py-2 text-xs font-bold text-white bg-ue-brand hover:bg-ue-brand-dark rounded-xl shadow-2xs hover:shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1.5"
                    >
                        <x-ui.icon name="send" size="xs" />
                        Gửi chia sẻ
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
