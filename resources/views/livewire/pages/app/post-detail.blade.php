<?php

use App\Actions\Comments\CreateComment;
use App\Actions\Comments\DeleteComment;
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

    // Report properties
    public ?Comment $reportingComment = null;
    public string $reportReason = 'spam';
    public string $reportDescription = '';
    public bool $showReportModal = false;

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
        $this->post = $post;
    }

    /**
     * Submit a comment or reply.
     */
    public function submitComment(CreateComment $createComment): void
    {
        $this->validate();

        $createComment->execute(Auth::user(), $this->post, [
            'body' => $this->commentBody,
            'parent_id' => $this->replyingToCommentId,
        ]);

        $this->commentBody = '';
        $this->replyingToCommentId = null;
        $this->feedbackMessage = 'Đăng bình luận thành công.';
        $this->dispatch('comment-created');
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
     * Delete comment.
     */
    public function deleteComment(int $commentId, DeleteComment $deleteComment): void
    {
        $comment = Comment::findOrFail($commentId);
        $deleteComment->execute(Auth::user(), $comment);
        $this->feedbackMessage = 'Đã xóa bình luận thành công.';
    }

    /**
     * Open report comment modal.
     */
    public function openCommentReport(int $commentId): void
    {
        $this->reportingComment = Comment::findOrFail($commentId);
        $this->reportReason = 'spam';
        $this->reportDescription = '';
        $this->showReportModal = true;
        $this->feedbackMessage = null;
        $this->resetErrorBag();
    }

    /**
     * Submit comment report.
     */
    public function submitCommentReport(CreateReport $createReport): void
    {
        if (! $this->reportingComment) {
            return;
        }

        try {
            $createReport->execute(Auth::user(), $this->reportingComment, [
                'reason' => $this->reportReason,
                'description' => $this->reportDescription,
            ]);

            $this->showReportModal = false;
            $this->reportingComment = null;
            $this->feedbackMessage = 'Báo cáo của bạn đã được gửi. Cảm ơn bạn đã đóng góp xây dựng môi trường HCMUE an toàn.';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('comment_report', $e->getMessage());
        }
    }

    /**
     * Close report modal.
     */
    public function closeReport(): void
    {
        $this->showReportModal = false;
        $this->reportingComment = null;
    }

    /**
     * Render parameters.
     */
    public function with(): array
    {
        $user = Auth::user();

        // Check if post is visible to standard user
        $isPostVisible = Gate::forUser($user)->allows('view', $this->post);

        // Load active top-level comments with replies
        $comments = Comment::with(['user.profile', 'likes', 'replies.user.profile', 'replies.likes'])
            ->where('post_id', $this->post->id)
            ->whereNull('parent_id')
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

<div class="space-y-6">

    {{-- System feedback alerts --}}
    @if ($feedbackMessage)
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start gap-2 shadow-sm animate-fadeIn" role="alert">
            <x-ui.icon name="check-circle" size="sm" class="text-emerald-600 mt-0.5 flex-shrink-0" />
            <div class="flex-1 font-medium">{{ $feedbackMessage }}</div>
            <button type="button" wire:click="$set('feedbackMessage', null)" class="text-emerald-400 hover:text-emerald-600 transition-colors">
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>
    @endif

    {{-- 1. POST DETAIL OR MODERATION PLACEHOLDER --}}
    @if (! $isPostVisible)
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 text-center text-slate-500 shadow-sm flex flex-col items-center gap-3">
            <x-ui.icon name="alert-triangle" size="lg" class="text-slate-400" />
            <p class="text-sm font-semibold">Nội dung này đã bị ẩn do vi phạm quy chuẩn cộng đồng.</p>
        </div>
    @else
        @php
            $author = $post->user;
            $profile = $author->profile;
            $isLiked = $post->likes->where('user_id', $currentUser->id)->isNotEmpty();
            $likeCount = $post->likes->count();
        @endphp

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            {{-- Header --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-ue-brand-soft border border-slate-100 flex items-center justify-center font-bold text-ue-brand text-sm shadow-sm select-none">
                    {{ mb_substr($author->name, 0, 2) }}
                </div>

                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-bold text-slate-800">{{ $author->name }}</span>
                        <x-ui.icon name="check-circle" size="xs" class="text-ue-brand" />
                    </div>
                    @if ($profile)
                        <div class="text-xs text-slate-400 font-medium">
                            {{ Str::ucfirst($profile->role_type) }}
                            @if ($profile->faculty)
                                · {{ $profile->faculty }}
                            @endif
                        </div>
                    @endif
                </div>

                <span class="ml-auto text-xs text-slate-400 font-medium" title="{{ $post->published_at->format('H:i d/m/Y') }}">
                    {{ $post->published_at->diffForHumans() }}
                </span>
            </div>

            {{-- Body --}}
            <div class="text-slate-800 text-sm whitespace-pre-wrap leading-relaxed mb-6">
                {{ $post->body }}
            </div>

            {{-- Action Row --}}
            <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-slate-500">
                <button
                    type="button"
                    wire:click="toggleCommentLike({{ $post->id }})" {{-- Use PostLike logic via direct trigger in feed or detail --}}
                    class="flex items-center gap-1.5 text-xs font-semibold hover:text-rose-600 transition-colors py-1 px-2 rounded-lg hover:bg-rose-50"
                >
                    <x-ui.icon name="heart" size="xs" />
                    <span>{{ $likeCount }} Thích</span>
                </button>

                <span class="text-xs text-slate-400 font-semibold flex items-center gap-1">
                    <x-ui.icon name="message-square" size="xs" />
                    <span>Bình luận</span>
                </span>
            </div>
        </div>

        {{-- 2. COMMENT COMPOSER --}}
        @if ($currentUser->isActive())
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                @if ($replyingToCommentId)
                    <div class="mb-3 px-3 py-1.5 rounded-lg bg-blue-50/50 border border-blue-100 text-xs text-ue-brand font-semibold flex items-center justify-between">
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
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 mt-2">
                        <span class="text-xs text-slate-400 font-medium">
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

        {{-- 3. COMMENTS LIST --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-6">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-1.5 pb-3 border-b border-slate-100">
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
                        $isCommentActive = in_array($comment->status, [\App\Enums\CommentStatus::PUBLISHED, \App\Enums\CommentStatus::EDITED]);
                    @endphp

                    <div class="pt-5 first:pt-0" wire:key="comment-{{ $comment->id }}">
                        @if (! $isCommentActive)
                            {{-- Moderator placeholder for deleted comments --}}
                            @if ($comment->replies->isNotEmpty())
                                <div class="text-xs italic text-slate-400 bg-slate-50 p-3 rounded-xl border border-slate-150">
                                    Bình luận này không còn khả dụng.
                                </div>
                            @endif
                        @else
                            {{-- Standard active comment --}}
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-600 text-xs shadow-sm select-none">
                                        {{ mb_substr($commentAuthor->name, 0, 2) }}
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-sm font-bold text-slate-800">{{ $commentAuthor->name }}</span>
                                            <x-ui.icon name="check-circle" size="xs" class="text-ue-brand" />
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
                                        <div class="flex items-center gap-4 mt-2 text-slate-400 text-xs">
                                            <span>{{ $comment->created_at->diffForHumans() }}</span>

                                            <button
                                                type="button"
                                                wire:click="toggleCommentLike({{ $comment->id }})"
                                                class="font-semibold hover:text-rose-600 transition-colors flex items-center gap-0.5 {{ $isCommentLiked ? 'text-rose-600' : '' }}"
                                            >
                                                <span>{{ $commentLikes }}</span> Thích
                                            </button>

                                            @if ($currentUser->isActive())
                                                {{-- Reply triggers (No nesting replies to replies, so only for top-level) --}}
                                                <button
                                                    type="button"
                                                    wire:click="setReplyingTo({{ $comment->id }})"
                                                    class="font-semibold hover:text-ue-brand transition-colors"
                                                >
                                                    Phản hồi
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Comment actions --}}
                                <div class="flex items-center gap-1">
                                    @if ($isCommentOwner)
                                        <button
                                            type="button"
                                            wire:click="deleteComment({{ $comment->id }})"
                                            class="text-slate-400 hover:text-red-600 transition-colors p-1 rounded hover:bg-red-50"
                                            title="Xóa bình luận"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa bình luận này?') || event.stopImmediatePropagation()"
                                        >
                                            <x-ui.icon name="trash" size="xs" />
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            wire:click="openCommentReport({{ $comment->id }})"
                                            class="text-slate-400 hover:text-yellow-600 transition-colors p-1 rounded hover:bg-yellow-50"
                                            title="Báo cáo bình luận"
                                        >
                                            <x-ui.icon name="flag" size="xs" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- 4. REPLIES LIST (1-LEVEL INDENT ONLY) --}}
                        @if ($comment->replies->isNotEmpty())
                            <div class="pl-11 border-l-2 border-slate-100 mt-4 space-y-4">
                                @foreach ($comment->replies as $reply)
                                    @php
                                        $replyAuthor = $reply->user;
                                        $replyProfile = $replyAuthor->profile;
                                        $replyLikes = $reply->likes->count();
                                        $isReplyLiked = $reply->likes->where('user_id', $currentUser->id)->isNotEmpty();
                                        $isReplyOwner = $reply->user_id === $currentUser->id;
                                        $isReplyActive = in_array($reply->status, [\App\Enums\CommentStatus::PUBLISHED, \App\Enums\CommentStatus::EDITED]);
                                    @endphp

                                    @if ($isReplyActive)
                                        <div class="flex items-start justify-between" wire:key="reply-{{ $reply->id }}">
                                            <div class="flex items-start gap-2.5">
                                                <div class="w-6.5 h-6.5 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center font-bold text-slate-500 text-xxs shadow-sm select-none">
                                                    {{ mb_substr($replyAuthor->name, 0, 2) }}
                                                </div>

                                                <div>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs font-bold text-slate-800">{{ $replyAuthor->name }}</span>
                                                        <x-ui.icon name="check-circle" size="xs" class="text-ue-brand" />
                                                    </div>
                                                    @if ($replyProfile)
                                                        <div class="text-xxs text-slate-400 font-medium -mt-1">
                                                            {{ Str::ucfirst($replyProfile->role_type) }}
                                                            @if ($replyProfile->faculty)
                                                                · {{ $replyProfile->faculty }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                    <div class="text-slate-700 text-sm mt-1 leading-relaxed">
                                                        {{ $reply->body }}
                                                    </div>

                                                    <div class="flex items-center gap-4 mt-1.5 text-slate-400 text-xxs">
                                                        <span>{{ $reply->created_at->diffForHumans() }}</span>

                                                        <button
                                                            type="button"
                                                            wire:click="toggleCommentLike({{ $reply->id }})"
                                                            class="font-semibold hover:text-rose-600 transition-colors flex items-center gap-0.5 {{ $isReplyLiked ? 'text-rose-600' : '' }}"
                                                        >
                                                            <span>{{ $replyLikes }}</span> Thích
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Reply actions --}}
                                            <div class="flex items-center gap-1">
                                                @if ($isReplyOwner)
                                                    <button
                                                        type="button"
                                                        wire:click="deleteComment({{ $reply->id }})"
                                                        class="text-slate-400 hover:text-red-600 transition-colors p-1 rounded hover:bg-red-50"
                                                        title="Xóa phản hồi"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa phản hồi này?') || event.stopImmediatePropagation()"
                                                    >
                                                        <x-ui.icon name="trash" size="xs" />
                                                    </button>
                                                @else
                                                    <button
                                                        type="button"
                                                        wire:click="openCommentReport({{ $reply->id }})"
                                                        class="text-slate-400 hover:text-yellow-600 transition-colors p-1 rounded hover:bg-yellow-50"
                                                        title="Báo cáo phản hồi"
                                                    >
                                                        <x-ui.icon name="flag" size="xs" />
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
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

    {{-- 5. REPORT COMMENT MODAL --}}
    @if ($showReportModal && $reportingComment)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fadeIn" role="dialog" aria-modal="true" aria-labelledby="comment-report-title">
            <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden animate-scaleIn">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 id="comment-report-title" class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <x-ui.icon name="alert-triangle" size="xs" class="text-yellow-600" />
                        Báo cáo bình luận vi phạm
                    </h3>
                    <button type="button" wire:click="closeReport" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <x-ui.icon name="x" size="xs" />
                    </button>
                </div>

                <form wire:submit.prevent="submitCommentReport" class="p-6 space-y-4">
                    @error('comment_report')
                        <div class="p-3 bg-red-50 border border-red-200 text-red-800 text-xs rounded-xl font-medium">
                            {{ $message }}
                        </div>
                    @enderror

                    <div>
                        <label for="report-reason" class="block text-xs font-semibold text-slate-500 mb-1.5">Lý do báo cáo</label>
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
                        <label for="report-desc" class="block text-xs font-semibold text-slate-500 mb-1.5">Chi tiết bổ sung (không bắt buộc)</label>
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

</div>
