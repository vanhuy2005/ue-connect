<?php

use App\Actions\Posts\CreatePost;
use App\Actions\Posts\DeletePost;
use App\Actions\Reports\CreateReport;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Enums\ReportReason;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostSave;
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

    // Report properties
    public ?Post $reportingPost = null;
    public string $reportReason = 'spam';
    public string $reportDescription = '';
    public bool $showReportModal = false;

    // Feedback message
    public ?string $feedbackMessage = null;

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
     * Toggle post like.
     */
    public function toggleLike(int $postId): void
    {
        $user = Auth::user();
        if (! $user->isActive()) {
            return;
        }

        $post = Post::findOrFail($postId);

        $existingLike = PostLike::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            PostLike::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Toggle post save.
     */
    public function toggleSave(int $postId): void
    {
        $user = Auth::user();
        if (! $user->isActive()) {
            return;
        }

        $post = Post::findOrFail($postId);

        $existingSave = PostSave::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingSave) {
            $existingSave->delete();
        } else {
            PostSave::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Delete post.
     */
    public function deletePost(int $postId, DeletePost $deletePost): void
    {
        $post = Post::findOrFail($postId);
        $deletePost->execute(Auth::user(), $post);
        $this->feedbackMessage = 'Đã xóa bài viết thành công.';
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
     * Render the component view.
     */
    public function with(): array
    {
        $user = Auth::user();

        // Get latest verified active posts
        $posts = Post::with(['user.profile', 'comments', 'likes', 'saves'])
            ->whereIn('status', [PostStatus::PUBLISHED, PostStatus::EDITED])
            ->latest('published_at')
            ->paginate(10);

        return [
            'posts' => $posts,
            'currentUser' => $user,
        ];
    }
};

?>

<div class="max-w-2xl mx-auto px-4 py-8">

    {{-- System feedback alerts --}}
    @if ($feedbackMessage)
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start gap-2 shadow-sm animate-fadeIn" role="alert">
            <x-ui.icon name="check-circle" size="sm" class="text-emerald-600 mt-0.5 flex-shrink-0" />
            <div class="flex-1 font-medium">{{ $feedbackMessage }}</div>
            <button type="button" wire:click="$set('feedbackMessage', null)" class="text-emerald-400 hover:text-emerald-600 transition-colors">
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>
    @endif

    {{-- 1. COMPOSER CARD --}}
    @if ($currentUser->isActive())
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm mb-8">
            <h2 class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2">
                <x-ui.icon name="edit" size="xs" class="text-ue-brand" />
                Chia sẻ với cộng đồng HCMUE
            </h2>

            <form wire:submit.prevent="submitPost">
                <div class="mb-4">
                    <label for="post-body" class="sr-only">Nội dung bài viết</label>
                    <textarea
                        id="post-body"
                        wire:model="body"
                        placeholder="Có điều gì mới muốn chia sẻ hôm nay? Hỏi đáp, tài liệu học tập, kinh nghiệm..."
                        rows="3"
                        class="w-full border-0 focus:ring-0 p-0 text-slate-700 placeholder-slate-400 text-sm resize-none bg-transparent"
                        maxlength="3000"
                    ></textarea>
                    @error('body')
                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-3">
                        {{-- Character counter --}}
                        <span class="text-xs text-slate-400 font-medium">
                            {{ mb_strlen($body) }}/3000
                        </span>

                        {{-- Visibility (Disabled for Phase 2 as community/connection models aren't ready yet) --}}
                        <div class="relative">
                            <label for="post-visibility" class="sr-only">Quyền xem</label>
                            <select
                                id="post-visibility"
                                wire:model="visibility"
                                class="text-xs font-semibold text-slate-500 bg-slate-50 border-0 rounded-lg py-1 pl-2 pr-8 focus:ring-0 focus:outline-none cursor-pointer"
                            >
                                <option value="verified_users">Chỉ sinh viên xác thực</option>
                                <option value="connections_only" disabled>Bạn bè (Sắp ra mắt)</option>
                                <option value="community" disabled>Cộng đồng (Sắp ra mắt)</option>
                            </select>
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
    @endif

    {{-- 2. FEED LIST --}}
    <div class="space-y-6">
        @forelse ($posts as $post)
            @php
                $author = $post->user;
                $profile = $author->profile;
                $isLiked = $post->likes->where('user_id', $currentUser->id)->isNotEmpty();
                $isSaved = $post->saves()->where('user_id', $currentUser->id)->exists();
                $likeCount = $post->likes->count();
                $commentCount = $post->comments->where('status', \App\Enums\CommentStatus::PUBLISHED->value)->count();
                $isOwner = $post->user_id === $currentUser->id;
            @endphp

            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:border-slate-300 transition-colors animate-fadeIn" wire:key="post-card-{{ $post->id }}">
                {{-- Post header --}}
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        {{-- Avatar indicator --}}
                        <div class="w-10 h-10 rounded-full bg-ue-brand-soft border border-slate-100 flex items-center justify-center font-bold text-ue-brand text-sm shadow-sm select-none">
                            {{ mb_substr($author->name, 0, 2) }}
                        </div>

                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm font-bold text-slate-800">{{ $author->name }}</span>
                                <x-ui.icon name="check-circle" size="xs" class="text-ue-brand" aria-label="Đã xác thực" />
                            </div>
                            
                            {{-- Faculty and major metadata --}}
                            @if ($profile)
                                <div class="text-xs text-slate-400 font-medium">
                                    {{ Str::ucfirst($profile->role_type) }}
                                    @if ($profile->faculty)
                                        · {{ $profile->faculty }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- More menu & report options --}}
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400 font-medium" title="{{ $post->published_at->format('H:i d/m/Y') }}">
                            {{ $post->published_at->diffForHumans() }}
                        </span>

                        @if ($isOwner)
                            {{-- Delete action for owners --}}
                            <button
                                type="button"
                                wire:click="deletePost({{ $post->id }})"
                                class="text-slate-400 hover:text-red-600 transition-colors p-1.5 rounded-lg hover:bg-red-50"
                                title="Xóa bài viết"
                                onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này?') || event.stopImmediatePropagation()"
                            >
                                <x-ui.icon name="trash" size="xs" />
                            </button>
                        @else
                            {{-- Report action for others --}}
                            <button
                                type="button"
                                wire:click="openReport({{ $post->id }})"
                                class="text-slate-400 hover:text-yellow-600 transition-colors p-1.5 rounded-lg hover:bg-yellow-50"
                                title="Báo cáo bài viết"
                            >
                                <x-ui.icon name="flag" size="xs" />
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Post body --}}
                <div class="mb-4 text-slate-700 text-sm whitespace-pre-wrap leading-relaxed">
                    {{ $post->body }}
                </div>

                {{-- Action Bar --}}
                <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-slate-500">
                    <div class="flex items-center gap-6">
                        {{-- Like toggle --}}
                        <button
                            type="button"
                            wire:click="toggleLike({{ $post->id }})"
                            class="flex items-center gap-1.5 text-xs font-semibold hover:text-rose-600 transition-colors py-1 px-2 rounded-lg hover:bg-rose-50 {{ $isLiked ? 'text-rose-600' : '' }}"
                        >
                            <x-ui.icon name="heart" size="xs" class="{{ $isLiked ? 'fill-rose-600' : '' }}" />
                            <span>{{ $likeCount }} Thích</span>
                        </button>

                        {{-- Comments link --}}
                        <a
                            href="{{ route('posts.show', $post) }}"
                            class="flex items-center gap-1.5 text-xs font-semibold hover:text-ue-brand transition-colors py-1 px-2 rounded-lg hover:bg-blue-50"
                        >
                            <x-ui.icon name="message-square" size="xs" />
                            <span>{{ $commentCount }} Bình luận</span>
                        </a>
                    </div>

                    {{-- Save toggle --}}
                    <button
                        type="button"
                        wire:click="toggleSave({{ $post->id }})"
                        class="flex items-center gap-1.5 text-xs font-semibold hover:text-amber-600 transition-colors py-1 px-2 rounded-lg hover:bg-amber-50 {{ $isSaved ? 'text-amber-600' : '' }}"
                        title="{{ $isSaved ? 'Hủy lưu bài viết' : 'Lưu bài viết' }}"
                    >
                        <x-ui.icon name="bookmark" size="xs" class="{{ $isSaved ? 'fill-amber-600' : '' }}" />
                        <span>Lưu</span>
                    </button>
                </div>
            </div>

        @empty
            {{-- 3. EMPTY STATE --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center shadow-sm">
                <div class="w-16 h-16 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
                    <x-ui.icon name="message-square" size="lg" class="text-slate-400" />
                </div>
                <h3 class="text-base font-bold text-slate-800 mb-2">Bảng tin chưa có bài viết</h3>
                <p class="text-sm text-slate-500 max-w-sm mx-auto mb-6">
                    Hãy là người đầu tiên chia sẻ điều hữu ích với cộng đồng HCMUE.
                </p>
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
            </div>
        @endforelse

        {{-- Pagination --}}
        <div class="pt-4">
            {{ $posts->links() }}
        </div>
    </div>

    {{-- 4. REPORT POST MODAL --}}
    @if ($showReportModal && $reportingPost)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fadeIn" role="dialog" aria-modal="true" aria-labelledby="report-modal-title">
            <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden animate-scaleIn">
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

</div>
