<?php

use App\Actions\Posts\CreatePost;
use App\Actions\Posts\DeletePost;
use App\Actions\Posts\UpdatePost;
use App\Actions\Posts\TogglePostLike;
use App\Actions\Posts\TogglePostSave;
use App\Actions\Posts\HidePostFromFeed;
use App\Actions\Reports\CreateReport;
use App\Actions\Messaging\SendSharedPostMessage;
use App\Actions\Messaging\FindOrCreateDirectConversation;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Enums\ReportReason;
use App\Enums\ConnectionStatus;
use App\Models\Post;
use App\Models\User;
use App\Models\Connection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    // Composer properties
    public string $body = '';
    public string $visibility = 'verified_users';

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
    public ?int $selectedShareUserId = null;
    public string $shareOptionalMessage = '';

    /**
     * Rules for validation.
     */
    protected array $rules = [
        'body' => 'required|string|max:3000',
        'visibility' => 'required|string|in:verified_users,connections_only,community,private',
    ];

    /**
     * Submit a new post.
     */
    public function submitPost(CreatePost $createPost): void
    {
        $this->validate();

        $createPost->execute(Auth::user(), [
            'body' => $this->body,
            'visibility' => $this->visibility,
        ]);

        $this->body = '';
        $this->feedbackMessage = 'Đăng bài viết thành công.';
        $this->dispatch('post-created');
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
        
        if (! Auth::user()->can('update', $post)) {
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
     * Start post sharing flow.
     */
    public function startShare(int $postId): void
    {
        $post = Post::findOrFail($postId);
        
        if (! Auth::user()->can('share', $post)) {
            $this->feedbackMessage = 'Bạn không có quyền chia sẻ bài viết này.';
            return;
        }

        $this->sharingPostId = $postId;
        $this->shareSearch = '';
        $this->selectedShareUserId = null;
        $this->shareOptionalMessage = '';
        $this->showShareModal = true;
    }

    /**
     * Execute post sharing to conversation.
     */
    public function executeShare(
        SendSharedPostMessage $sendSharedPostMessage,
        FindOrCreateDirectConversation $findOrCreateDirectConversation
    ): void {
        if (! $this->sharingPostId || ! $this->selectedShareUserId) {
            return;
        }

        try {
            $post = Post::findOrFail($this->sharingPostId);
            $recipient = User::findOrFail($this->selectedShareUserId);

            // Find or create conversation
            $conversation = $findOrCreateDirectConversation->execute(Auth::user(), $recipient);

            // Send share post message
            $sendSharedPostMessage->execute(Auth::user(), $conversation, $post, [
                'body' => $this->shareOptionalMessage ?: null,
            ]);

            $this->showShareModal = false;
            $this->sharingPostId = null;
            $this->selectedShareUserId = null;
            $this->shareOptionalMessage = '';
            
            $this->feedbackMessage = 'Đã chia sẻ bài viết qua tin nhắn thành công.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
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

        // Get latest verified active posts (Strictly PUBLISHED and EDITED only, excluding hidden posts, except those hidden in current session)
        $posts = Post::with(['user.profile', 'comments', 'likes', 'saves'])
            ->whereIn('status', [PostStatus::PUBLISHED, PostStatus::EDITED])
            ->where(function ($query) use ($user) {
                $query->whereDoesntHave('hides', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->orWhereIn('id', $this->locallyHiddenPostIds);
            })
            ->latest('published_at')
            ->paginate(10);

        return [
            'posts' => $posts,
            'currentUser' => $user,
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
                    <button type="button" class="px-3 py-1.5 rounded-full text-xxs font-bold bg-ue-brand-soft text-ue-brand">
                        Dành cho bạn
                    </button>
                    <button type="button" class="px-3 py-1.5 rounded-full text-xxs font-bold text-slate-400 hover:bg-slate-50 transition-colors" disabled>
                        Theo dõi
                    </button>
                </div>
            </div>

            {{-- Mobile: Threads-style centered tab strip only --}}
            <div class="flex sm:hidden items-center justify-center border-b border-slate-100 pb-1">
                <button type="button" class="flex-1 py-2 text-xs font-bold text-slate-800 border-b-2 border-slate-800 text-center">
                    Dành cho bạn
                </button>
                <button type="button" class="flex-1 py-2 text-xs font-medium text-slate-400 text-center" disabled>
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
                                </div>

                                <div class="ue-composer__toolbar">
                                    <div class="ue-composer__actions">
                                        <span class="ue-composer__counter">
                                            {{ mb_strlen($body) }}/3000
                                        </span>
                                        <div class="relative">
                                            <label for="post-visibility" class="sr-only">Quyền xem</label>
                                            <select
                                                id="post-visibility"
                                                wire:model="visibility"
                                                class="absolute inset-0 w-full h-full opacity-0 z-10 cursor-pointer"
                                            >
                                                <option value="verified_users">Chỉ sinh viên xác thực</option>
                                                <option value="connections_only" disabled>Bạn bè (Sắp ra mắt)</option>
                                                <option value="community" disabled>Cộng đồng (Sắp ra mắt)</option>
                                            </select>
                                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 text-slate-500 rounded-lg select-none pointer-events-none">
                                                <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand/10" />
                                                <span class="hidden sm:inline text-xxs font-bold">Chỉ sinh viên xác thực</span>
                                                <span class="sm:hidden text-[10px] font-bold">Xác thực</span>
                                                <x-ui.icon name="chevron-down" size="xs" class="text-slate-400" />
                                            </div>
                                        </div>
                                    </div>
                                    <x-ui.button
                                        type="submit"
                                        variant="primary"
                                        size="sm"
                                        icon="send"
                                    >
                                        Đăng bài
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
                        $isLiked = $post->likes->where('user_id', $currentUser->id)->isNotEmpty();
                        $isSaved = $post->saves->where('user_id', $currentUser->id)->isNotEmpty();
                        $likeCount = $post->likes->count();
                        $commentCount = $post->comments->where('status', \App\Enums\CommentStatus::PUBLISHED->value)->count();
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

            {{-- End state / Pagination Sentinel inside feed surface --}}
            <div class="ue-feed-end-state">
                <div class="w-full flex flex-col items-center justify-center gap-2">
                    <span class="text-xxs text-slate-400 font-semibold mb-1">Bạn đã xem hết bài viết hiện có.</span>
                    {{ $posts->links() }}
                </div>
            </div>
        </section>
    </div>

    {{-- Mobile bottom nav padding buffer --}}
    <div class="ue-mobile-bottom-spacer"></div>

    {{-- Modals & Sheets --}}
    <x-ui.create-post-modal :body="$body" :visibility="$visibility" />
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
                        >
                            Gửi báo cáo
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
                    >
                        Xóa bài viết
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
                            <button
                                type="button"
                                wire:click="$set('selectedShareUserId', {{ $connUser->id }})"
                                class="w-full text-left p-3 hover:bg-slate-50 flex items-center justify-between transition-colors {{ $selectedShareUserId === $connUser->id ? 'bg-slate-50' : '' }}"
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
                                @if ($selectedShareUserId === $connUser->id)
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
                    <button type="button" wire:click="$set('showShareModal', false)" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 transition-colors">
                        Hủy bỏ
                    </button>
                    <button
                        type="button"
                        wire:click="executeShare"
                        @if (! $selectedShareUserId) disabled @endif
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
