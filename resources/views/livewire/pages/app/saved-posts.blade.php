<?php

use App\Actions\Posts\DeletePost;
use App\Actions\Posts\UpdatePost;
use App\Actions\Posts\TogglePostLike;
use App\Actions\Posts\TogglePostSave;
use App\Actions\Posts\HidePostFromFeed;
use App\Actions\Reports\CreateReport;
use App\Actions\Messaging\SendSharedPostMessage;
use App\Actions\Messaging\FindOrCreateDirectConversation;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Enums\ReportReason;
use App\Enums\ConnectionStatus;
use App\Models\Post;
use App\Models\PostSave;
use App\Models\User;
use App\Models\Connection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

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
        'editingBody' => 'required|string|max:3000',
    ];

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
     * Toggle post save (unsave) using policy action.
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
     * Render the component view.
     */
    public function with(): array
    {
        $user = Auth::user();

        // Get saved posts (latest saved first, filtering out hidden/deleted and user-hidden posts, except those hidden in current session)
        $saves = PostSave::with([
            'post' => function ($query) use ($user): void {
                $query->with(['user.profile', 'media.variants'])
                    ->visibleTo($user)
                    ->withCount([
                        'likes',
                        'comments as published_comments_count' => function ($query): void {
                            $query->where('status', CommentStatus::PUBLISHED->value);
                        },
                        'likes as liked_by_current_user_count' => function ($query) use ($user): void {
                            $query->where('user_id', $user->id);
                        },
                    ]);
            },
        ])
            ->where('user_id', $user->id)
            ->whereHas('post', function ($query) use ($user) {
                $query->whereIn('status', [PostStatus::PUBLISHED, PostStatus::EDITED])
                    ->visibleTo($user)
                    ->where(function ($q) use ($user) {
                        $q->whereDoesntHave('hides', function ($h) use ($user) {
                            $h->where('user_id', $user->id);
                        })
                        ->orWhereIn('id', $this->locallyHiddenPostIds);
                    });
            })
            ->latest('id')
            ->paginate(10);

        return [
            'saves' => $saves,
            'currentUser' => $user,
        ];
    }
};

?>

<div class="ue-feed-layout">
    <div class="ue-feed-column">

        {{-- Header title --}}
        <div class="flex items-start gap-3 border-b border-slate-150 pb-4 mb-2">
            <x-ui.icon name="bookmark" size="lg" class="text-ue-brand" />
            <div>
                <h1 class="text-xl font-bold text-slate-800">Bài viết đã lưu</h1>
                <p class="mt-1 text-xs font-medium text-slate-400">Chỉ bạn mới xem được danh sách lưu trữ cá nhân này.</p>
            </div>
        </div>

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

        {{-- SAVED POSTS LIST --}}
        <section class="ue-feed-surface">
            <div class="ue-feed-list">
                @forelse ($saves as $save)
                    @php
                        $post = $save->post;
                        $isLiked = (int) $post->liked_by_current_user_count > 0;
                        $isSaved = true; // since it is in saves
                        $likeCount = (int) $post->likes_count;
                        $commentCount = (int) $post->published_comments_count;
                    @endphp

                    @if (in_array($post->id, $locallyHiddenPostIds))
                        {{-- Hidden Saved Post Placeholder with Hoàn tác button --}}
                        <article class="ue-feed-item p-4 sm:p-5 bg-slate-50/50 flex items-center justify-between gap-4 ue-animate-fade-in" wire:key="hidden-saved-placeholder-{{ $post->id }}">
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
                        <article class="ue-feed-item" wire:key="post-item-{{ $post->id }}">
                            <x-ui.post-card
                                :post="$post"
                                :currentUser="$currentUser"
                                :isSaved="$isSaved"
                                :isLiked="$isLiked"
                                :likeCount="$likeCount"
                                :commentCount="$commentCount"
                                :editingPostId="$editingPostId"
                                :editingBody="$editingBody"
                            />
                        </article>
                    @endif
                @empty
                    <div class="p-8">
                        <x-ui.empty-state
                            icon="bookmark"
                            title="Chưa có bài viết đã lưu"
                            description="Khi bạn lưu bài viết hữu ích, chúng sẽ xuất hiện tại đây."
                        >
                            <x-ui.button
                                href="{{ route('dashboard') }}"
                                variant="outline"
                                size="md"
                                icon="arrow-left"
                            >
                                Quay lại bảng tin
                            </x-ui.button>
                        </x-ui.empty-state>
                    </div>
                @endforelse
            </div>

            {{-- End state / Pagination Sentinel inside feed surface --}}
            <div class="ue-feed-end-state">
                <div class="w-full flex flex-col items-center justify-center gap-2">
                    <span class="text-xxs text-slate-400 font-semibold mb-1">Bạn đã xem hết danh sách lưu trữ.</span>
                    {{ $saves->links() }}
                </div>
            </div>
        </section>

    </div>

    {{-- Mobile bottom nav padding buffer --}}
    <div class="ue-mobile-bottom-spacer"></div>

    {{-- REPORT MODAL --}}
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
