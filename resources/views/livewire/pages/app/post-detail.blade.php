<?php

use App\Actions\Comments\CreateComment;
use App\Actions\Comments\DeleteComment;
use App\Actions\Comments\UpdateComment;
use App\Actions\Posts\TogglePostLike;
use App\Actions\Posts\TogglePostSave;
use App\Actions\Posts\DeletePost;
use App\Actions\Posts\UpdatePost;
use App\Actions\Reports\CreateReport;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Enums\ReportReason;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Volt\Component;

new class extends Component
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
        $this->post = $post;
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
        if (! Auth::user()->can('update', $this->post)) {
            $this->feedbackMessage = 'Bạn không có quyền chỉnh sửa bài viết này.';
            return;
        }

        $this->isEditingPost = true;
        $this->editingPostBody = $this->post->body;
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

            $this->isEditingPost = false;
            $this->editingPostBody = '';
            $this->feedbackMessage = 'Đã cập nhật bài viết thành công.';
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
     * Start comment editing.
     */
    public function startCommentEdit(int $commentId): void
    {
        $comment = Comment::findOrFail($commentId);

        if (! Auth::user()->can('update', $comment)) {
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
};

?>

<div class="max-w-[640px] mx-auto px-4 py-6 sm:py-8 space-y-6">

    {{-- Back button row --}}
    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-ue-brand mb-2 transition-colors font-semibold">
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
            $isLiked = $post->likes->where('user_id', $currentUser->id)->isNotEmpty();
            $isSaved = $post->saves->where('user_id', $currentUser->id)->isNotEmpty();
            $likeCount = $post->likes->count();
            $isOwner = $post->user_id === $currentUser->id;
        @endphp

        <div class="bg-white border border-slate-150 rounded-2xl p-4 sm:p-5 shadow-xs relative">
            
            {{-- Header --}}
            <div class="flex items-start gap-3 mb-4">
                <div class="w-9 h-9 rounded-full bg-ue-brand-soft border border-slate-100 flex items-center justify-center font-bold text-ue-brand text-xs shadow-xs select-none flex-shrink-0">
                    {{ mb_substr($author->name, 0, 2) }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-bold text-slate-800">{{ $author->name }}</span>
                        <x-ui.icon name="check-circle" size="xs" class="text-ue-brand flex-shrink-0" />
                        
                        <span class="text-xxs text-slate-400 font-semibold" title="{{ $post->published_at->format('H:i d/m/Y') }}">
                            · {{ $post->published_at->diffForHumans() }}
                        </span>
                    </div>
                    @if ($profile)
                        <div class="text-xxs text-slate-400 font-medium">
                            {{ Str::ucfirst($profile->role_type) }}
                            @if ($profile->faculty)
                                · {{ $profile->faculty }}
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Post Actions dropdown menu --}}
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <x-ui.icon-button
                        icon="more-horizontal"
                        label="Tùy chọn bài viết"
                        variant="ghost"
                        size="sm"
                        @click="open = !open"
                        class="text-slate-400 hover:text-slate-600"
                    />
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-1 rounded-xl bg-white border border-slate-150 shadow-lg py-1 z-10"
                        style="display: none; width: 200px;"
                    >
                        @if ($isOwner)
                            @if (! $isEditingPost)
                                <button
                                    type="button"
                                    wire:click="startPostEdit"
                                    @click="open = false"
                                    class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-ue-brand flex items-center gap-2 transition-colors"
                                >
                                    <x-ui.icon name="edit" size="xs" class="text-slate-400" />
                                    Chỉnh sửa
                                </button>
                            @endif
                            <button
                                type="button"
                                wire:click="openPostDeleteModal({{ $post->id }})"
                                @click="open = false"
                                class="w-full text-left px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 flex items-center gap-2 transition-colors"
                            >
                                <x-ui.icon name="trash" size="xs" class="text-red-400" />
                                Xóa bài viết
                            </button>
                        @else
                            <button
                                type="button"
                                wire:click="openPostReport({{ $post->id }})"
                                @click="open = false"
                                class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-yellow-50 hover:text-yellow-700 flex items-center gap-2 transition-colors"
                            >
                                <x-ui.icon name="flag" size="xs" class="text-slate-400" />
                                Báo cáo bài viết
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Post Body / Editing UI --}}
            @if ($isEditingPost)
                <div class="space-y-3 bg-slate-50 p-3 rounded-xl border border-slate-100 ue-animate-fade-in mb-4">
                    <label for="edit-post-body" class="sr-only">Nội dung chỉnh sửa bài viết</label>
                    <textarea
                        id="edit-post-body"
                        wire:model="editingPostBody"
                        rows="3"
                        class="w-full border-0 focus:ring-0 p-0 text-slate-700 text-sm resize-none bg-transparent"
                        maxlength="3000"
                    ></textarea>
                    @error('editingPostBody')
                        <p class="text-xs text-red-600 font-semibold">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                        <span class="text-xxs text-slate-400 font-semibold">
                            {{ mb_strlen($editingPostBody) }}/3000
                        </span>
                        <div class="flex items-center gap-2">
                            <button 
                                type="button" 
                                wire:click="$set('isEditingPost', false)" 
                                class="px-3 py-1.5 text-xxs font-bold text-slate-500 hover:text-slate-700 transition-colors"
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
                <div class="text-slate-800 text-sm sm:text-base whitespace-pre-wrap leading-relaxed mb-4">
                    {{ $post->body }}
                </div>
                
                {{-- Edited Indicator --}}
                @if ($post->status === PostStatus::EDITED)
                    <div class="mb-4">
                        <span class="inline-block text-[10px] font-bold text-slate-400 bg-slate-50 border border-slate-100 rounded px-1.5 py-0.5">
                            Đã chỉnh sửa
                        </span>
                    </div>
                @endif
            @endif

            {{-- Action Row --}}
            <div class="flex items-center justify-between pt-3 border-t border-slate-100/65 text-slate-500">
                <div class="flex items-center gap-6">
                    {{-- Like button --}}
                    <button
                        type="button"
                        wire:click="togglePostLike({{ $post->id }})"
                        class="flex items-center gap-1 text-xxs font-bold hover:text-rose-600 transition-colors py-1 px-1.5 rounded-lg hover:bg-rose-50/50 {{ $isLiked ? 'text-rose-600' : '' }}"
                        aria-pressed="{{ $isLiked ? 'true' : 'false' }}"
                    >
                        <x-ui.icon name="heart" size="xs" class="transition-transform active:scale-125 {{ $isLiked ? 'fill-rose-600 text-rose-600' : '' }}" />
                        <span>{{ $likeCount }} Thích</span>
                    </button>

                    <span class="text-xxs text-slate-400 font-bold flex items-center gap-1">
                        <x-ui.icon name="message-square" size="xs" />
                        <span>Bình luận</span>
                    </span>
                </div>

                {{-- Save button --}}
                <button
                    type="button"
                    wire:click="togglePostSave({{ $post->id }})"
                    class="flex items-center gap-1 text-xxs font-bold hover:text-amber-600 transition-colors py-1 px-1.5 rounded-lg hover:bg-amber-50/50 {{ $isSaved ? 'text-amber-600' : '' }}"
                    aria-pressed="{{ $isSaved ? 'true' : 'false' }}"
                >
                    <x-ui.icon name="bookmark" size="xs" class="{{ $isSaved ? 'fill-amber-600 text-amber-600' : '' }}" />
                    <span>{{ $isSaved ? 'Đã lưu' : 'Lưu' }}</span>
                </button>
            </div>
        </div>

        {{-- 2. COMMENT COMPOSER --}}
        @if ($currentUser->isActive())
            <div class="bg-white border border-slate-150 rounded-2xl p-4 sm:p-5 shadow-xs">
                @if ($replyingToCommentId)
                    <div class="mb-3 px-3 py-1.5 rounded-lg bg-blue-50 border border-blue-100 text-xxs text-ue-brand font-bold flex items-center justify-between ue-animate-fade-in">
                        <span>Đang phản hồi một bình luận</span>
                        <button type="button" wire:click="setReplyingTo(null)" class="text-slate-400 hover:text-slate-600 transition-colors">
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
                            placeholder="{{ $replyingToCommentId ? 'Nhập phản hồi của bạn...' : 'Viết bình luận công khai...' }}"
                            rows="2"
                            class="w-full border-0 focus:ring-0 p-0 text-slate-700 placeholder-slate-400 text-sm resize-none bg-transparent"
                            maxlength="1000"
                        ></textarea>
                        @error('commentBody')
                            <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 mt-2">
                        <span class="text-xxs text-slate-400 font-semibold">
                            {{ mb_strlen($commentBody) }}/1000
                        </span>

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
        @endif

        {{-- 3. COMMENTS LIST (THREADS-STYLE THREADING) --}}
        <div class="bg-white border border-slate-150 rounded-2xl p-4 sm:p-5 shadow-xs space-y-6">
            <h3 class="text-xs font-bold text-slate-800 flex items-center gap-1.5 pb-3 border-b border-slate-100">
                <x-ui.icon name="message-circle" size="xs" class="text-ue-brand" />
                Thảo luận cộng đồng
            </h3>

            <div class="space-y-6 divide-y divide-slate-100">
                @forelse ($comments as $comment)
                    @php
                        $commentAuthor = $comment->user;
                        $commentProfile = $commentAuthor->profile;
                        $commentLikes = $comment->likes->count();
                        $isCommentLiked = $comment->likes->where('user_id', $currentUser->id)->isNotEmpty();
                        $isCommentOwner = $comment->user_id === $currentUser->id;
                        $isDeleted = in_array($comment->status, [CommentStatus::DELETED_BY_OWNER, CommentStatus::DELETED_BY_MODERATION, CommentStatus::HIDDEN_BY_MODERATION]);
                    @endphp

                    <div class="pt-5 first:pt-0 ue-animate-fade-in" wire:key="comment-thread-{{ $comment->id }}">
                        
                        {{-- Standard active comment or placeholder --}}
                        @if ($isDeleted)
                            {{-- Placeholder for deleted comment with replies --}}
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-50 border border-slate-150 flex items-center justify-center text-slate-400 flex-shrink-0">
                                    <x-ui.icon name="eye-off" size="xs" />
                                </div>
                                <div class="flex-1 bg-slate-50 border border-slate-100 rounded-xl p-3 text-slate-400 text-xs italic font-medium">
                                    Bình luận này không còn khả dụng.
                                </div>
                            </div>
                        @else
                            {{-- Comment Editing or standard render --}}
                            @if ($editingCommentId === $comment->id)
                                <div class="space-y-3 bg-slate-50 p-3 rounded-xl border border-slate-100 ue-animate-fade-in">
                                    <label for="edit-comment-{{ $comment->id }}" class="sr-only">Nội dung bình luận chỉnh sửa</label>
                                    <textarea
                                        id="edit-comment-{{ $comment->id }}"
                                        wire:model="editingCommentBody"
                                        rows="2"
                                        class="w-full border-0 focus:ring-0 p-0 text-slate-700 text-sm resize-none bg-transparent"
                                        maxlength="1000"
                                    ></textarea>
                                    @error('editingCommentBody')
                                        <p class="text-xs text-red-600 font-semibold">{{ $message }}</p>
                                    @enderror

                                    <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                                        <span class="text-xxs text-slate-400 font-semibold">
                                            {{ mb_strlen($editingCommentBody) }}/1000
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <button 
                                                type="button" 
                                                wire:click="cancelCommentEdit" 
                                                class="px-3 py-1.5 text-xxs font-bold text-slate-500 hover:text-slate-700 transition-colors"
                                            >
                                                Hủy
                                            </button>
                                            <x-ui.button
                                                type="button"
                                                wire:click="saveCommentEdit"
                                                variant="primary"
                                                size="xs"
                                                icon="check"
                                            >
                                                Lưu
                                            </x-ui.button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-600 text-xs shadow-xs select-none flex-shrink-0">
                                            {{ mb_substr($commentAuthor->name, 0, 2) }}
                                        </div>

                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-sm font-bold text-slate-800">{{ $commentAuthor->name }}</span>
                                                <x-ui.icon name="check-circle" size="xs" class="text-ue-brand flex-shrink-0" />
                                            </div>
                                            @if ($commentProfile)
                                                <div class="text-xxs text-slate-400 font-medium -mt-0.5">
                                                    {{ Str::ucfirst($commentProfile->role_type) }}
                                                    @if ($commentProfile->faculty)
                                                        · {{ $commentProfile->faculty }}
                                                    @endif
                                                </div>
                                            @endif
                                            <div class="text-slate-700 text-sm mt-1 leading-relaxed">
                                                {{ $comment->body }}
                                            </div>

                                            {{-- Comment Interactions --}}
                                            <div class="flex items-center gap-4 mt-2 text-slate-400 text-xxs font-bold">
                                                <span>{{ $comment->created_at->diffForHumans() }}</span>

                                                <button
                                                    type="button"
                                                    wire:click="toggleCommentLike({{ $comment->id }})"
                                                    class="hover:text-rose-600 transition-colors flex items-center gap-0.5 {{ $isCommentLiked ? 'text-rose-600' : '' }}"
                                                >
                                                    <x-ui.icon name="heart" size="xs" class="{{ $isCommentLiked ? 'fill-rose-600 text-rose-600' : '' }}" />
                                                    <span>{{ $commentLikes }} Thích</span>
                                                </button>

                                                @if ($currentUser->isActive())
                                                    <button
                                                        type="button"
                                                        wire:click="setReplyingTo({{ $comment->id }})"
                                                        class="hover:text-ue-brand transition-colors flex items-center gap-0.5"
                                                    >
                                                        <x-ui.icon name="reply" size="xs" />
                                                        <span>Phản hồi</span>
                                                    </button>
                                                @endif

                                                @if ($comment->status === CommentStatus::EDITED)
                                                    <span class="text-slate-350">· Đã chỉnh sửa</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Custom Alpine dropdown options --}}
                                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                        <x-ui.icon-button
                                            icon="more-horizontal"
                                            label="Tùy chọn bình luận"
                                            variant="ghost"
                                            size="sm"
                                            @click="open = !open"
                                            class="text-slate-400 hover:text-slate-600"
                                        />
                                            x-show="open"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute right-0 mt-1 rounded-xl bg-white border border-slate-150 shadow-lg py-1 z-10"
                                            style="display: none; width: 180px;"
                                        >
                                            @if ($isCommentOwner)
                                                <button
                                                    type="button"
                                                    wire:click="startCommentEdit({{ $comment->id }})"
                                                    @click="open = false"
                                                    class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-ue-brand flex items-center gap-2 transition-colors"
                                                >
                                                    <x-ui.icon name="edit" size="xs" class="text-slate-400" />
                                                    Chỉnh sửa
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="openCommentDeleteModal({{ $comment->id }})"
                                                    @click="open = false"
                                                    class="w-full text-left px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 flex items-center gap-2 transition-colors"
                                                >
                                                    <x-ui.icon name="trash" size="xs" class="text-red-400" />
                                                    Xóa bình luận
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    wire:click="openCommentReport({{ $comment->id }})"
                                                    @click="open = false"
                                                    class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-yellow-50 hover:text-yellow-700 flex items-center gap-2 transition-colors"
                                                >
                                                    <x-ui.icon name="flag" size="xs" class="text-slate-400" />
                                                    Báo cáo
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{-- 4. REPLIES LIST (1-LEVEL INDENT ONLY) --}}
                        @if ($comment->replies->isNotEmpty())
                            <div class="ml-[16px] pl-6 border-l-2 border-slate-100 mt-3 space-y-4 relative">
                                @foreach ($comment->replies as $reply)
                                    @php
                                        $replyAuthor = $reply->user;
                                        $replyProfile = $replyAuthor->profile;
                                        $replyLikes = $reply->likes->count();
                                        $isReplyLiked = $reply->likes->where('user_id', $currentUser->id)->isNotEmpty();
                                        $isReplyOwner = $reply->user_id === $currentUser->id;
                                    @endphp

                                    <div class="relative ue-animate-fade-in" wire:key="reply-thread-{{ $reply->id }}">
                                        {{-- Horizontal connector line --}}
                                        <div class="absolute -left-6 top-3.5 w-6 h-[2px] bg-slate-100"></div>

                                        @if ($editingCommentId === $reply->id)
                                            <div class="space-y-3 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                                <label for="edit-reply-{{ $reply->id }}" class="sr-only">Nội dung phản hồi chỉnh sửa</label>
                                                <textarea
                                                    id="edit-reply-{{ $reply->id }}"
                                                    wire:model="editingCommentBody"
                                                    rows="2"
                                                    class="w-full border-0 focus:ring-0 p-0 text-slate-700 text-sm resize-none bg-transparent"
                                                    maxlength="1000"
                                                ></textarea>
                                                @error('editingCommentBody')
                                                    <p class="text-xs text-red-600 font-semibold">{{ $message }}</p>
                                                @enderror

                                                <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                                                    <span class="text-xxs text-slate-400 font-semibold">
                                                        {{ mb_strlen($editingCommentBody) }}/1000
                                                    </span>
                                                    <div class="flex items-center gap-2">
                                                        <button 
                                                            type="button" 
                                                            wire:click="cancelCommentEdit" 
                                                            class="px-3 py-1.5 text-xxs font-bold text-slate-500 hover:text-slate-700 transition-colors"
                                                        >
                                                            Hủy
                                                        </button>
                                                        <x-ui.button
                                                            type="button"
                                                            wire:click="saveCommentEdit"
                                                            variant="primary"
                                                            size="xs"
                                                            icon="check"
                                                        >
                                                            Lưu
                                                        </x-ui.button>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex items-start justify-between">
                                                <div class="flex items-start gap-2.5">
                                                    <div class="w-7 h-7 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center font-bold text-slate-500 text-xxs shadow-xs select-none flex-shrink-0">
                                                        {{ mb_substr($replyAuthor->name, 0, 2) }}
                                                    </div>

                                                    <div>
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="text-xs font-bold text-slate-800">{{ $replyAuthor->name }}</span>
                                                            <x-ui.icon name="check-circle" size="xs" class="text-ue-brand flex-shrink-0" />
                                                        </div>
                                                        @if ($replyProfile)
                                                            <div class="text-[10px] text-slate-400 font-medium -mt-0.5">
                                                                {{ Str::ucfirst($replyProfile->role_type) }}
                                                                @if ($replyProfile->faculty)
                                                                    · {{ $replyProfile->faculty }}
                                                                @endif
                                                            </div>
                                                        @endif
                                                        <div class="text-slate-700 text-sm mt-1 leading-relaxed">
                                                            {{ $reply->body }}
                                                        </div>

                                                        <div class="flex items-center gap-4 mt-1.5 text-slate-400 text-xxs font-bold">
                                                            <span>{{ $reply->created_at->diffForHumans() }}</span>

                                                            <button
                                                                type="button"
                                                                wire:click="toggleCommentLike({{ $reply->id }})"
                                                                class="hover:text-rose-600 transition-colors flex items-center gap-0.5 {{ $isReplyLiked ? 'text-rose-600' : '' }}"
                                                            >
                                                                <x-ui.icon name="heart" size="xs" class="{{ $isReplyLiked ? 'fill-rose-600 text-rose-600' : '' }}" />
                                                                <span>{{ $replyLikes }} Thích</span>
                                                            </button>

                                                            @if ($reply->status === CommentStatus::EDITED)
                                                                <span class="text-slate-350">· Đã chỉnh sửa</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Reply Option Dropdown Menu --}}
                                                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                                    <x-ui.icon-button
                                                        icon="more-horizontal"
                                                        label="Tùy chọn phản hồi"
                                                        variant="ghost"
                                                        size="sm"
                                                        @click="open = !open"
                                                        class="text-slate-400 hover:text-slate-600"
                                                    />
                                                    <div
                                                        x-show="open"
                                                        x-transition:enter="transition ease-out duration-100"
                                                        x-transition:enter-start="transform opacity-0 scale-95"
                                                        x-transition:enter-end="transform opacity-100 scale-100"
                                                        x-transition:leave="transition ease-in duration-75"
                                                        x-transition:leave-start="transform opacity-100 scale-100"
                                                        x-transition:leave-end="transform opacity-0 scale-95"
                                                        class="absolute right-0 mt-1 rounded-xl bg-white border border-slate-150 shadow-lg py-1 z-10"
                                                        style="display: none; width: 180px;"
                                                    >
                                                        @if ($isReplyOwner)
                                                            <button
                                                                type="button"
                                                                wire:click="startCommentEdit({{ $reply->id }})"
                                                                @click="open = false"
                                                                class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-ue-brand flex items-center gap-2 transition-colors"
                                                            >
                                                                <x-ui.icon name="edit" size="xs" class="text-slate-400" />
                                                                Chỉnh sửa
                                                            </button>
                                                            <button
                                                                type="button"
                                                                wire:click="openCommentDeleteModal({{ $reply->id }})"
                                                                @click="open = false"
                                                                class="w-full text-left px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 flex items-center gap-2 transition-colors"
                                                            >
                                                                <x-ui.icon name="trash" size="xs" class="text-red-400" />
                                                                Xóa phản hồi
                                                            </button>
                                                        @else
                                                            <button
                                                                type="button"
                                                                wire:click="openCommentReport({{ $reply->id }})"
                                                                @click="open = false"
                                                                class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-yellow-50 hover:text-yellow-700 flex items-center gap-2 transition-colors"
                                                            >
                                                                <x-ui.icon name="flag" size="xs" class="text-slate-400" />
                                                                Báo cáo
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-sm">
                        Chưa có bình luận nào cho bài viết này. Hãy chia sẻ ý kiến của bạn!
                    </div>
                @endforelse
            </div>
        </div>
    @endif

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
                    <div class="w-12 h-12 rounded-full bg-red-50 border border-red-100 flex items-center justify-center mx-auto text-red-600">
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

</div>
