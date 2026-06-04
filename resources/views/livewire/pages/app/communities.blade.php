<?php

use App\Actions\Community\CreateCommunitySuggestionAction;
use App\Actions\Community\RequestJoinCommunityAction;
use App\Actions\Messaging\FindOrCreateDirectConversation;
use App\Actions\Messaging\SendSharedPostMessage;
use App\Actions\Posts\HidePostFromFeed;
use App\Actions\Posts\TogglePostLike;
use App\Actions\Posts\TogglePostSave;
use App\Enums\CommunityJoinPolicy;
use App\Enums\CommunityMemberRole;
use App\Enums\CommunityType;
use App\Enums\CommunityVisibility;
use App\Enums\ConnectionStatus;
use App\Enums\PostStatus;
use App\Models\Community;
use App\Models\CommunityJoinRequest;
use App\Models\CommunityMember;
use App\Models\Connection;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $type = '';

    public string $subTab = 'discover'; // feed, discover, mine

    // Suggestion modal
    public bool $showSuggestModal = false;

    public string $suggestName = '';

    public string $suggestType = 'club';

    public string $suggestJoinPolicy = 'approval_required';

    public string $suggestVisibility = 'public';

    public string $suggestRelatedFaculty = '';

    public string $suggestPurpose = '';

    public string $suggestTargetMembers = '';

    public string $suggestRules = '';

    // Post sharing inside feed
    public bool $showShareModal = false;

    public ?int $sharingPostId = null;

    public string $shareSearch = '';

    public ?int $selectedShareUserId = null;

    public string $shareOptionalMessage = '';

    // Hidden posts in session
    public array $locallyHiddenPostIds = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'type' => ['except' => ''],
        'subTab' => ['except' => 'discover'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function setSubTab(string $tab): void
    {
        $this->subTab = $tab;
        $this->resetPage();
    }

    public function getCommunitiesProperty()
    {
        $query = Community::discoverable()
            ->latest('members_count');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('short_description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->type && $this->type !== 'all') {
            $query->byType($this->type);
        }

        return $query->paginate(12, pageName: 'discoverPage');
    }

    public function getJoinedCommunitiesProperty()
    {
        $userId = auth()->id();
        $memberIds = CommunityMember::where('user_id', $userId)
            ->where('status', 'active')
            ->pluck('community_id');

        return Community::query()
            ->where(function ($query) use ($memberIds, $userId) {
                $query->where('owner_id', $userId)
                    ->orWhereIn('id', $memberIds);
            })
            ->latest('updated_at')
            ->get();
    }

    public function getManagedCommunitiesProperty()
    {
        $managedMemberIds = CommunityMember::where('user_id', auth()->id())
            ->where('status', 'active')
            ->whereIn('role', [
                CommunityMemberRole::Owner->value,
                CommunityMemberRole::Manager->value,
            ])
            ->pluck('community_id');

        return Community::query()
            ->where(function ($query) use ($managedMemberIds) {
                $query->where('owner_id', auth()->id())
                    ->orWhereIn('id', $managedMemberIds);
            })
            ->latest()
            ->get();
    }

    public function getPendingRequestsProperty()
    {
        return CommunityJoinRequest::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->with('community')
            ->latest()
            ->get();
    }

    public function getUserMembershipIdsProperty(): array
    {
        $memberIds = CommunityMember::where('user_id', auth()->id())
            ->where('status', 'active')
            ->pluck('community_id')
            ->toArray();

        $ownedIds = Community::where('owner_id', auth()->id())
            ->pluck('id')
            ->toArray();

        return array_unique(array_merge($memberIds, $ownedIds));
    }

    public function getFeedPostsProperty()
    {
        $joinedIds = $this->userMembershipIds;

        if (empty($joinedIds)) {
            return collect();
        }

        return Post::where('scope_type', 'community')
            ->whereIn('scope_id', $joinedIds)
            ->whereIn('status', [PostStatus::PUBLISHED->value, PostStatus::EDITED->value])
            ->with(['author.profile', 'mediaFiles', 'reactions', 'comments'])
            ->latest('published_at')
            ->paginate(10, pageName: 'feedPage');
    }

    public function getCommunityTypesProperty(): array
    {
        return [['value' => '', 'label' => 'Tất cả'], ...collect(CommunityType::cases())
            ->map(fn ($t) => ['value' => $t->value, 'label' => $t->label()])
            ->toArray(),
        ];
    }

    public function getSuggestibleTypesProperty(): array
    {
        return collect(CommunityType::cases())
            ->filter(fn ($t) => $t !== CommunityType::OfficialAnnouncementGroup)
            ->values()
            ->all();
    }

    public function joinCommunity(int $communityId, RequestJoinCommunityAction $action): void
    {
        $community = Community::findOrFail($communityId);
        $this->authorize('join', $community);

        $action->execute(auth()->user(), $community, []);

        $this->dispatch('notify', type: 'success', message: $community->requiresApproval()
            ? 'Yêu cầu tham gia đã được gửi, chờ xét duyệt.'
            : 'Bạn đã tham gia cộng đồng!');
    }

    public function cancelRequest(int $requestId): void
    {
        $request = CommunityJoinRequest::where('id', $requestId)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();

        $request->delete();
        $this->dispatch('notify', type: 'success', message: 'Đã hủy yêu cầu tham gia.');
    }

    public function submitSuggestion(CreateCommunitySuggestionAction $action): void
    {
        $this->validate([
            'suggestName' => ['required', 'string', 'min:3', 'max:160'],
            'suggestType' => ['required', 'string', 'in:'.implode(',', array_column(CommunityType::cases(), 'value'))],
            'suggestJoinPolicy' => ['required', 'string', 'in:'.implode(',', array_column(CommunityJoinPolicy::cases(), 'value'))],
            'suggestVisibility' => ['required', 'string', 'in:'.implode(',', array_column(CommunityVisibility::cases(), 'value'))],
            'suggestRelatedFaculty' => ['nullable', 'string', 'max:160'],
            'suggestPurpose' => ['required', 'string', 'min:20', 'max:2000'],
            'suggestTargetMembers' => ['required', 'string', 'min:5', 'max:500'],
            'suggestRules' => ['nullable', 'string', 'max:5000'],
        ]);

        $action->execute(auth()->user(), [
            'suggested_name' => $this->suggestName,
            'community_type' => $this->suggestType,
            'join_policy' => $this->suggestJoinPolicy,
            'visibility' => $this->suggestVisibility,
            'purpose' => $this->suggestPurpose,
            'target_members' => $this->suggestTargetMembers,
            'rules' => $this->suggestRules ?: null,
            'related_faculty' => $this->suggestRelatedFaculty ?: null,
        ]);

        $this->showSuggestModal = false;
        $this->reset([
            'suggestName', 'suggestType', 'suggestJoinPolicy', 'suggestVisibility',
            'suggestRelatedFaculty', 'suggestPurpose', 'suggestTargetMembers', 'suggestRules',
        ]);
        $this->dispatch('notify', type: 'success', message: 'Đề xuất cộng đồng đã được gửi!');
    }

    // ─── Post Actions (for feed tab) ──────────────────────────────────────────

    public function toggleLike(int $postId, TogglePostLike $togglePostLike): void
    {
        $post = Post::findOrFail($postId);
        $togglePostLike->execute(auth()->user(), $post);
    }

    public function toggleSave(int $postId, TogglePostSave $togglePostSave): void
    {
        $post = Post::findOrFail($postId);
        $togglePostSave->execute(auth()->user(), $post);
        $this->dispatch('notify', type: 'success', message: 'Đã cập nhật trạng thái lưu bài viết.');
    }

    public function hidePost(int $postId, HidePostFromFeed $hidePostFromFeed): void
    {
        $post = Post::findOrFail($postId);
        $hidePostFromFeed->execute(auth()->user(), $post);
        $this->locallyHiddenPostIds[] = $postId;
        $this->dispatch('notify', type: 'success', message: 'Đã ẩn bài viết khỏi bảng tin.');
    }

    public function startShare(int $postId): void
    {
        $this->sharingPostId = $postId;
        $this->shareSearch = '';
        $this->selectedShareUserId = null;
        $this->shareOptionalMessage = '';
        $this->showShareModal = true;
    }

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
            $conversation = $findOrCreateDirectConversation->execute(auth()->user(), $recipient);

            $sendSharedPostMessage->execute(auth()->user(), $conversation, $post, [
                'body' => $this->shareOptionalMessage ?: null,
            ]);

            $this->showShareModal = false;
            $this->sharingPostId = null;
            $this->selectedShareUserId = null;
            $this->shareOptionalMessage = '';

            $this->dispatch('notify', type: 'success', message: 'Đã chia sẻ bài viết qua tin nhắn thành công.');
        } catch (Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function getShareConnections(): Collection
    {
        $userId = auth()->id();
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
                return Str::contains(strtolower($user->name), strtolower($search)) ||
                       ($user->profile && Str::contains(strtolower($user->profile->display_name), strtolower($search)));
            });
        }

        return $connections->values();
    }
};
?>

<div class="flex flex-col lg:flex-row min-h-screen bg-[#f0f2f5] w-full">
    
    {{-- 1. Desktop Left Sidebar --}}
    <aside class="hidden lg:flex flex-col w-80 bg-white border-r border-slate-200 flex-shrink-0 p-4 sticky top-0 h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Cộng đồng & CLB</h1>
            <a href="#" class="p-2 text-slate-500 hover:bg-slate-100 rounded-full transition" title="Cài đặt cộng đồng">
                <x-ui.icon name="settings" size="sm" />
            </a>
        </div>

        {{-- Sidebar Search --}}
        <div class="relative mb-4">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <x-ui.icon name="search" size="xs" class="text-slate-400" />
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm kiếm cộng đồng..."
                class="w-full pl-9 pr-4 py-2 text-sm bg-slate-100 border-0 rounded-full focus:bg-white focus:outline-none focus:ring-2 focus:ring-ue-brand transition placeholder-slate-500 text-slate-700" />
        </div>

        {{-- Navigation Menu --}}
        <nav class="space-y-1 mb-6">
            <button wire:click="setSubTab('feed')"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all {{ $subTab === 'feed' ? 'bg-ue-brand-soft text-ue-brand' : 'text-slate-700 hover:bg-slate-50' }}">
                <x-ui.icon name="message-square" size="xs" class="{{ $subTab === 'feed' ? 'text-ue-brand' : 'text-slate-500' }}" />
                <span class="flex-1 text-left">Bảng feed của bạn</span>
            </button>
            <button wire:click="setSubTab('discover')"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all {{ $subTab === 'discover' ? 'bg-ue-brand-soft text-ue-brand' : 'text-slate-700 hover:bg-slate-50' }}">
                <x-ui.icon name="users" size="xs" class="{{ $subTab === 'discover' ? 'text-ue-brand' : 'text-slate-500' }}" />
                <span class="flex-1 text-left">Khám phá</span>
            </button>
            <button wire:click="setSubTab('mine')"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all {{ $subTab === 'mine' ? 'bg-ue-brand-soft text-ue-brand' : 'text-slate-700 hover:bg-slate-50' }}">
                <x-ui.icon name="user" size="xs" class="{{ $subTab === 'mine' ? 'text-ue-brand' : 'text-slate-500' }}" />
                <span class="flex-1 text-left">Nhóm của bạn</span>
            </button>
        </nav>

        {{-- Primary CTA --}}
        <button wire:click="$set('showSuggestModal', true)"
            class="w-full py-2.5 bg-ue-brand hover:bg-opacity-95 text-white rounded-xl font-bold text-sm transition flex items-center justify-center gap-2 shadow-sm mb-6">
            <x-ui.icon name="plus" size="xs" />
            Đề xuất cộng đồng mới
        </button>

        <hr class="border-slate-200 mb-4" />

        {{-- Joined Groups Section --}}
        <div class="flex-1 overflow-y-auto pr-1">
            <div class="flex items-center justify-between mb-3 px-2">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Cộng đồng đã tham gia</h2>
                <button wire:click="setSubTab('mine')" class="text-xs font-semibold text-ue-brand hover:underline">Xem tất cả</button>
            </div>
            
            <div class="space-y-1">
                @forelse ($this->joinedCommunities as $c)
                    <a href="{{ route('community.show', $c->id) }}" wire:navigate
                        class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-100 transition group">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-ue-brand/20 to-ue-brand/5 border border-slate-150 flex items-center justify-center text-ue-brand font-black text-sm flex-shrink-0 group-hover:scale-105 transition-transform duration-200">
                            {{ strtoupper(mb_substr($c->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-slate-800 truncate group-hover:text-ue-brand transition-colors">{{ $c->name }}</p>
                            <p class="text-[10px] text-slate-400 font-medium truncate mt-0.5">
                                {{ $c->type?->label() ?? 'Nhóm' }} · {{ number_format($c->members_count) }} thành viên
                            </p>
                        </div>
                    </a>
                @empty
                    <p class="text-xs text-slate-450 italic px-2">Bạn chưa tham gia cộng đồng nào.</p>
                @endforelse
            </div>
        </div>
    </aside>

    {{-- 2. Main Content Area --}}
    <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
        
        {{-- Mobile Header: horizontal tabs and buttons --}}
        <div class="lg:hidden bg-white p-3 rounded-2xl border border-slate-200 mb-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h1 class="text-lg font-extrabold text-slate-800">Cộng đồng & CLB</h1>
                <button wire:click="$set('showSuggestModal', true)"
                    class="p-2 bg-ue-brand text-white rounded-full transition shadow-sm" title="Đề xuất cộng đồng">
                    <x-ui.icon name="plus" size="xs" />
                </button>
            </div>

            {{-- Mobile Nav Chips --}}
            <div class="flex gap-1.5 overflow-x-auto pb-1">
                <button wire:click="setSubTab('feed')"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap {{ $subTab === 'feed' ? 'bg-ue-brand text-white' : 'bg-slate-100 text-slate-600' }}">
                    Bảng feed của bạn
                </button>
                <button wire:click="setSubTab('discover')"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap {{ $subTab === 'discover' ? 'bg-ue-brand text-white' : 'bg-slate-100 text-slate-600' }}">
                    Khám phá
                </button>
                <button wire:click="setSubTab('mine')"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap {{ $subTab === 'mine' ? 'bg-ue-brand text-white' : 'bg-slate-100 text-slate-600' }}">
                    Nhóm của bạn
                </button>
            </div>
        </div>

        {{-- SUBTAB 1: Feed --}}
        <div class="{{ $subTab === 'feed' ? '' : 'hidden' }}">
            <div class="max-w-2xl mx-auto space-y-4">
                <div class="mb-2">
                    <h2 class="text-base font-extrabold text-slate-850">Hoạt động mới đây</h2>
                    <p class="text-xs text-slate-500">Bài đăng từ tất cả các cộng đồng bạn đã tham gia.</p>
                </div>

                @forelse ($this->feedPosts as $post)
                    @php
                        $isLiked = $post->likes->where('user_id', auth()->id())->isNotEmpty();
                        $isSaved = $post->saves->where('user_id', auth()->id())->isNotEmpty();
                        $likeCount = $post->likes->count();
                        $commentCount = $post->comments->where('status', \App\Enums\CommentStatus::PUBLISHED->value)->count();
                    @endphp

                    @if (in_array($post->id, $locallyHiddenPostIds))
                        <div class="bg-white border border-slate-200 rounded-2xl p-4 flex items-center justify-between gap-4 shadow-sm" wire:key="hidden-post-{{ $post->id }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400">
                                    <x-ui.icon name="eye-off" size="xs" />
                                </div>
                                <div class="text-left">
                                    <h4 class="text-xs font-bold text-slate-800">Đã ẩn bài viết</h4>
                                    <p class="text-[10px] text-slate-500">Bài viết đã được ẩn khỏi bảng feed của bạn.</p>
                                </div>
                            </div>
                            <button type="button" wire:click="$set('locallyHiddenPostIds', {{ json_encode(array_diff($locallyHiddenPostIds, [$post->id])) }})"
                                class="px-3 py-1.5 text-xs font-bold text-ue-brand bg-ue-brand-soft rounded-xl hover:bg-ue-brand hover:text-white transition">
                                Hoàn tác
                            </button>
                        </div>
                    @else
                        <article class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" wire:key="post-feed-{{ $post->id }}">
                            {{-- Community Source Header --}}
                            @if ($post->scope_id && $post->scope_type === 'community')
                                @php $postCommunity = \App\Models\Community::find($post->scope_id); @endphp
                                @if ($postCommunity)
                                    <div class="px-4 py-2 bg-slate-50/70 border-b border-slate-150 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 rounded bg-ue-brand-soft text-ue-brand flex items-center justify-center text-[10px] font-black">
                                                {{ strtoupper(mb_substr($postCommunity->name, 0, 1)) }}
                                            </div>
                                            <a href="{{ route('community.show', $postCommunity->id) }}" wire:navigate
                                                class="text-xs font-bold text-slate-700 hover:text-ue-brand truncate max-w-[200px] sm:max-w-sm">
                                                {{ $postCommunity->name }}
                                            </a>
                                        </div>
                                        <span class="text-[10px] bg-slate-200/80 text-slate-655 font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                                            Cộng đồng
                                        </span>
                                    </div>
                                @endif
                            @endif

                            <x-ui.post-card
                                :post="$post"
                                :currentUser="auth()->user()"
                                :isSaved="$isSaved"
                                :isLiked="$isLiked"
                                :likeCount="$likeCount"
                                :commentCount="$commentCount"
                            />
                        </article>
                    @endif
                @empty
                    {{-- Onboarding Empty State --}}
                    <div class="bg-white border border-slate-200 rounded-3xl p-8 text-center space-y-4 shadow-sm max-w-md mx-auto mt-10">
                        <div class="w-16 h-16 rounded-full bg-ue-brand-soft text-ue-brand flex items-center justify-center mx-auto">
                            <x-ui.icon name="users" size="lg" />
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-lg font-bold text-slate-850 bg-transparent">Bảng feed của bạn đang trống</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Bạn chưa tham gia bất kỳ cộng đồng nào hoặc các cộng đồng bạn tham gia chưa có bài viết mới. Hãy cùng khám phá và giao lưu nhé!
                            </p>
                        </div>
                        <div class="flex flex-col gap-2 pt-2 sm:flex-row sm:justify-center">
                            <button wire:click="setSubTab('discover')"
                                class="px-5 py-2 bg-ue-brand text-white rounded-xl font-bold text-xs hover:bg-opacity-95 transition shadow-sm">
                                Khám phá cộng đồng
                            </button>
                            <button wire:click="$set('showSuggestModal', true)"
                                class="px-5 py-2 border border-slate-250 text-slate-700 rounded-xl font-bold text-xs hover:bg-slate-50 transition">
                                Đề xuất nhóm mới
                            </button>
                        </div>
                    </div>
                @endforelse

                @if ($this->feedPosts->isNotEmpty() && $this->feedPosts->hasPages())
                    <div class="pt-4">{{ $this->feedPosts->links() }}</div>
                @endif
            </div>
        </div>

        {{-- SUBTAB 2: Discover --}}
        <div class="{{ $subTab === 'discover' ? '' : 'hidden' }}">
            <div class="space-y-6 max-w-5xl mx-auto">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-850">Khám phá cộng đồng</h2>
                        <p class="text-xs text-slate-500 mt-1">Tìm kiếm và kết nối với các câu luận bộ, nhóm học tập, mentor tại HCMUE.</p>
                    </div>
                </div>

                {{-- Interactive Filters --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-ui.icon name="search" size="xs" class="text-slate-400" />
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm kiếm theo tên hoặc mô tả..."
                            class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-ue-brand transition placeholder-slate-400 text-slate-700" />
                    </div>
                    <select wire:model.live="type"
                        class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-ue-brand transition text-slate-700">
                        @foreach ($this->communityTypes as $ct)
                            <option value="{{ $ct['value'] }}">{{ $ct['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Discover Cards Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @forelse ($this->communities as $c)
                        @php $isMember = in_array($c->id, $this->userMembershipIds); @endphp
                        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-md transition duration-200 flex flex-col group relative">
                            {{-- Header Gradient Fallback --}}
                            <div class="h-20 bg-gradient-to-br from-ue-brand/35 to-ue-brand/10 relative flex items-center justify-center">
                                <span class="text-3xl font-black text-ue-brand/20 select-none">{{ strtoupper(mb_substr($c->name, 0, 2)) }}</span>
                            </div>

                            <div class="p-4 flex-1 flex flex-col pt-6 relative">
                                {{-- Overlapping Group Avatar --}}
                                <div class="w-12 h-12 rounded-xl bg-ue-brand text-white border-2 border-white shadow-sm flex items-center justify-center text-lg font-black absolute -top-6 left-4">
                                    {{ strtoupper(mb_substr($c->name, 0, 2)) }}
                                </div>

                                <div class="min-w-0 mb-2 mt-1">
                                    <a href="{{ route('community.show', $c->id) }}" wire:navigate
                                        class="block font-bold text-sm text-slate-800 hover:text-ue-brand truncate">
                                        {{ $c->name }}
                                    </a>
                                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-md text-[10px] font-bold">
                                            {{ $c->type?->label() ?? 'Nhóm' }}
                                        </span>
                                        @if ($c->related_faculty)
                                            <span class="px-2 py-0.5 bg-ue-brand-soft/50 text-ue-brand rounded-md text-[10px] font-bold max-w-[120px] truncate">
                                                {{ $c->related_faculty }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if ($c->short_description ?? $c->description)
                                    <p class="text-xs text-slate-500 line-clamp-2 mb-4 leading-normal">
                                        {{ $c->short_description ?? $c->description }}
                                    </p>
                                @else
                                    <p class="text-xs text-slate-350 italic line-clamp-2 mb-4">Chưa có thông tin giới thiệu.</p>
                                @endif

                                <div class="mt-auto pt-3 border-t border-slate-100 flex items-center justify-between gap-3 text-xs">
                                    <span class="text-slate-455 font-bold flex items-center gap-1 select-none">
                                        <x-ui.icon name="users" size="xs" />
                                        {{ number_format($c->members_count) }}
                                    </span>
                                    
                                    @if ($isMember)
                                        <a href="{{ route('community.show', $c->id) }}" wire:navigate
                                            class="px-3.5 py-1.5 border border-ue-brand text-ue-brand hover:bg-ue-brand-soft rounded-xl text-xs font-bold transition">
                                            Đã tham gia
                                        </a>
                                    @elseif ($c->join_policy?->value === 'open')
                                        <button wire:click="joinCommunity({{ $c->id }})"
                                            class="px-3.5 py-1.5 bg-ue-brand hover:bg-opacity-95 text-white rounded-xl text-xs font-bold transition shadow-2xs">
                                            Tham gia
                                        </button>
                                    @else
                                        <button wire:click="joinCommunity({{ $c->id }})"
                                            class="px-3.5 py-1.5 bg-ue-brand hover:bg-opacity-95 text-white rounded-xl text-xs font-bold transition shadow-2xs">
                                            Gửi yêu cầu
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-16 text-center">
                            <x-ui.icon name="search" size="lg" class="text-slate-300 mx-auto mb-2" />
                            <p class="text-slate-455 italic text-sm">Không tìm thấy cộng đồng nào phù hợp.</p>
                        </div>
                    @endforelse
                </div>

                @if ($this->communities->isNotEmpty() && $this->communities->hasPages())
                    <div class="pt-4">{{ $this->communities->links() }}</div>
                @endif
            </div>
        </div>

        {{-- SUBTAB 3: Your Groups --}}
        <div class="{{ $subTab === 'mine' ? '' : 'hidden' }}">
            <div class="space-y-6 max-w-5xl mx-auto">
                
                {{-- 1. Pending Join Requests --}}
                @if ($this->pendingRequests->isNotEmpty())
                    <section class="bg-amber-50/70 border border-amber-250/50 rounded-2xl p-4 sm:p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <x-ui.icon name="clock" class="text-amber-600" size="sm" />
                            <h3 class="text-sm font-bold text-amber-800">Yêu cầu tham gia nhóm đang chờ duyệt ({{ $this->pendingRequests->count() }})</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach ($this->pendingRequests as $req)
                                <div class="bg-white rounded-xl border border-amber-200/60 p-4 flex flex-col justify-between shadow-2xs">
                                    <div>
                                        <h4 class="font-bold text-xs text-slate-800 truncate">{{ $req->community?->name }}</h4>
                                        <p class="text-[10px] text-slate-450 mt-1">Yêu cầu gửi: {{ $req->created_at->diffForHumans() }}</p>
                                    </div>
                                    <div class="mt-3 flex gap-2 justify-end">
                                        <a href="{{ route('community.show', $req->community_id) }}" wire:navigate
                                            class="px-2.5 py-1.5 border border-slate-200 text-slate-655 hover:bg-slate-50 text-[10px] font-bold rounded-lg transition">
                                            Xem nhóm
                                        </a>
                                        <button wire:click="cancelRequest({{ $req->id }})"
                                            class="px-2.5 py-1.5 bg-red-50 text-red-700 hover:bg-red-100 text-[10px] font-bold rounded-lg transition border border-red-200">
                                            Hủy yêu cầu
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- 2. Managed Communities --}}
                <section>
                    <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-800">Cộng đồng bạn quản lý</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Bao gồm các cộng đồng nháp, ẩn hoặc đang hoàn thiện.</p>
                        </div>
                        <span class="text-xs font-bold text-ue-brand bg-ue-brand-soft px-3 py-1 rounded-full">{{ $this->managedCommunities->count() }} nhóm</span>
                    </div>

                    @forelse ($this->managedCommunities as $c)
                        <div class="bg-white border border-slate-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:shadow-2xs transition mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-ue-brand/20 to-ue-brand/5 flex items-center justify-center text-ue-brand font-black text-base flex-shrink-0">
                                    {{ strtoupper(mb_substr($c->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('community.show', $c->id) }}" wire:navigate
                                        class="font-bold text-sm text-slate-800 hover:text-ue-brand truncate block">
                                        {{ $c->name }}
                                    </a>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] bg-slate-100 text-slate-500 font-bold px-2 py-0.5 rounded">
                                            {{ $c->type?->label() }}
                                        </span>
                                        <span class="text-[10px] bg-ue-surface-subtle text-ue-text-secondary font-bold px-2 py-0.5 rounded">
                                            {{ $c->visibility?->label() }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-2 items-center self-end sm:self-center">
                                @if ($c->owner_id === auth()->id())
                                    <span class="text-[10px] bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-lg font-bold">Chủ sở hữu</span>
                                @else
                                    <span class="text-[10px] bg-indigo-50 text-indigo-700 border border-indigo-200 px-2.5 py-1 rounded-lg font-bold">Ban quản lý</span>
                                @endif
                                <a href="{{ route('community.show', $c->id) }}?tab=settings" wire:navigate
                                    class="p-2 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-lg text-xs font-bold transition" title="Cài đặt">
                                    <x-ui.icon name="settings" size="xs" />
                                </a>
                                <a href="{{ route('community.show', $c->id) }}" wire:navigate
                                    class="px-3.5 py-2 bg-ue-brand hover:bg-opacity-95 text-white rounded-lg text-xs font-bold transition shadow-2xs">
                                    Truy cập
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white border border-slate-200 rounded-2xl p-6 text-center text-slate-455 italic text-xs shadow-2xs">
                            Bạn chưa sở hữu hoặc quản lý cộng đồng nào.
                        </div>
                    @endforelse
                </section>

                {{-- 3. Joined Communities --}}
                <section>
                    <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-800">Tất cả nhóm đã tham gia</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Danh sách các cộng đồng bạn đang hoạt động bình thường.</p>
                        </div>
                        <span class="text-xs font-bold text-slate-655 bg-slate-100 px-3 py-1 rounded-full">{{ count($this->joinedCommunities) }} nhóm</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse ($this->joinedCommunities as $c)
                            <div class="bg-white border border-slate-200 rounded-2xl p-4 flex items-center justify-between hover:shadow-2xs transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-ue-brand/20 to-ue-brand/5 flex items-center justify-center text-ue-brand font-black text-base flex-shrink-0">
                                        {{ strtoupper(mb_substr($c->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('community.show', $c->id) }}" wire:navigate
                                            class="font-bold text-xs text-slate-800 hover:text-ue-brand truncate block">
                                            {{ $c->name }}
                                        </a>
                                        <p class="text-[10px] text-slate-400 mt-0.5 truncate">{{ $c->type?->label() }} · {{ number_format($c->members_count) }} thành viên</p>
                                    </div>
                                </div>
                                <a href="{{ route('community.show', $c->id) }}" wire:navigate
                                    class="px-3.5 py-1.5 border border-ue-brand text-ue-brand hover:bg-ue-brand-soft rounded-lg text-xxs font-bold transition flex-shrink-0 ml-2">
                                    Truy cập
                                </a>
                            </div>
                        @empty
                            <div class="col-span-full bg-white border border-slate-200 rounded-2xl p-6 text-center text-slate-455 italic text-xs shadow-2xs">
                                Bạn chưa tham gia bất kỳ cộng đồng nào.
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </main>

    {{-- MODALS --}}

    {{-- 1. Create/Suggest Community Modal with Live Preview --}}
    @if ($showSuggestModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl p-6 md:p-8 flex flex-col md:flex-row gap-6 my-8 max-h-[90vh] overflow-y-auto" x-data="{ step: 1 }">
                
                {{-- Form Columns --}}
                <div class="flex-1 space-y-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-850">Đề xuất cộng đồng mới</h3>
                        <p class="text-xs text-slate-500">Mẫu đề xuất sẽ được chuyển trực tiếp cho Quản trị viên xét duyệt thành lập.</p>
                    </div>

                    <div class="space-y-3">
                        {{-- Name --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Tên cộng đồng <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.live="suggestName" maxlength="160"
                                class="w-full px-3 py-2 border rounded-xl text-xs transition focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200" placeholder="VD: CLB Lập trình UEConnect">
                            @error('suggestName') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            {{-- Type --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-650 uppercase tracking-wide mb-1">Phân loại <span class="text-red-500">*</span></label>
                                <select wire:model.live="suggestType" class="w-full px-3 py-2 border rounded-xl text-xs transition focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                                    @foreach ($this->suggestibleTypes as $type)
                                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Join Policy --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-655 uppercase tracking-wide mb-1">Chính sách <span class="text-red-500">*</span></label>
                                <select wire:model.live="suggestJoinPolicy" class="w-full px-3 py-2 border rounded-xl text-xs transition focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                                    @foreach (CommunityJoinPolicy::cases() as $jPolicy)
                                        <option value="{{ $jPolicy->value }}">{{ $jPolicy->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            {{-- Visibility --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-650 uppercase tracking-wide mb-1">Hiển thị <span class="text-red-500">*</span></label>
                                <select wire:model.live="suggestVisibility" class="w-full px-3 py-2 border rounded-xl text-xs transition focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                                    @foreach (CommunityVisibility::cases() as $vis)
                                        <option value="{{ $vis->value }}">{{ $vis->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Faculty --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-650 uppercase tracking-wide mb-1">Khoa quản lý/phụ trách</label>
                                <input type="text" wire:model.live="suggestRelatedFaculty" maxlength="160"
                                    class="w-full px-3 py-2 border rounded-xl text-xs transition focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200" placeholder="VD: Khoa Công nghệ thông tin">
                            </div>
                        </div>

                        {{-- Target Members --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-650 uppercase tracking-wide mb-1">Đối tượng hướng tới <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.live="suggestTargetMembers" maxlength="500"
                                class="w-full px-3 py-2 border rounded-xl text-xs transition focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200" placeholder="VD: Sinh viên CNTT, K45-K48...">
                            @error('suggestTargetMembers') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        {{-- Purpose / Description --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-650 uppercase tracking-wide mb-1">Mục đích hoạt động <span class="text-red-500">*</span></label>
                            <textarea wire:model.live="suggestPurpose" rows="3" maxlength="2000"
                                class="w-full px-3 py-2 border rounded-xl text-xs transition focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200 resize-none" placeholder="Mô tả lý do lập nhóm và nội dung hoạt động cốt lõi..."></textarea>
                            <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                                <span>Cung cấp tối thiểu 20 ký tự</span>
                                <span>{{ mb_strlen($suggestPurpose) }}/20 ký tự</span>
                            </div>
                            @error('suggestPurpose') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        {{-- Rules --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-650 uppercase tracking-wide mb-1">Nội quy sơ bộ</label>
                            <textarea wire:model.live="suggestRules" rows="2" maxlength="5000"
                                class="w-full px-3 py-2 border rounded-xl text-xs transition focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200 resize-none" placeholder="Các quy tắc tôn trọng và xây dựng cộng đồng học thuật..."></textarea>
                        </div>
                    </div>

                    <div class="flex gap-2 justify-end pt-3 border-t border-slate-100">
                        <button wire:click="$set('showSuggestModal', false)"
                            class="px-4 py-2 border border-slate-200 text-slate-650 hover:bg-slate-50 text-xs font-bold rounded-xl transition">
                            Hủy bỏ
                        </button>
                        <button wire:click="submitSuggestion"
                            class="px-5 py-2 bg-ue-brand hover:bg-opacity-95 text-white text-xs font-bold rounded-xl transition shadow-sm">
                            Gửi đề xuất
                        </button>
                    </div>
                </div>

                {{-- Desktop live preview panel --}}
                <div class="hidden md:flex flex-col w-80 bg-slate-50 border border-slate-200 rounded-3xl p-5 justify-between">
                    <div>
                        <span class="text-[10px] bg-slate-200 text-slate-600 font-bold px-2 py-0.5 rounded-full uppercase tracking-wider select-none">
                            Xem trước hiển thị
                        </span>
                        
                        <div class="bg-white border border-slate-150 rounded-2xl overflow-hidden shadow-2xs mt-4">
                            {{-- cover mockup --}}
                            <div class="h-16 bg-gradient-to-br from-ue-brand/30 to-ue-brand/10 flex items-center justify-center">
                                <span class="text-2xl font-black text-ue-brand/20 select-none">
                                    {{ strtoupper(mb_substr($suggestName ?: 'AB', 0, 2)) }}
                                </span>
                            </div>
                            
                            <div class="p-3 pt-5 relative">
                                {{-- Overlapping logo mockup --}}
                                <div class="w-9 h-9 rounded-lg bg-ue-brand text-white border-2 border-white shadow-sm flex items-center justify-center text-sm font-black absolute -top-[18px] left-3">
                                    {{ strtoupper(mb_substr($suggestName ?: 'AB', 0, 2)) }}
                                </div>

                                <h4 class="font-bold text-xs text-slate-800 truncate mt-1">
                                    {{ $suggestName ?: 'Tên nhóm của bạn' }}
                                </h4>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="text-[9px] bg-slate-100 text-slate-500 font-bold px-1.5 py-0.5 rounded">
                                        {{ collect(CommunityType::cases())->firstWhere('value', $suggestType)?->label() ?? 'Nhóm' }}
                                    </span>
                                    <span class="text-[9px] bg-ue-brand-soft text-ue-brand font-bold px-1.5 py-0.5 rounded truncate max-w-[120px]">
                                        {{ $suggestRelatedFaculty ?: 'Tất cả khoa' }}
                                    </span>
                                </div>

                                <p class="text-[10px] text-slate-450 line-clamp-2 mt-2 leading-relaxed">
                                    {{ $suggestPurpose ?: 'Phần mô tả mục đích hoạt động của nhóm sẽ hiển thị tại đây khi bạn nhập vào form.' }}
                                </p>
                            </div>
                        </div>

                        {{-- Metadata preview --}}
                        <div class="space-y-2 mt-4 text-[11px] text-slate-555 bg-white border border-slate-150 p-3.5 rounded-2xl">
                            <div class="flex justify-between">
                                <span>Chính sách tham gia:</span>
                                <strong class="text-slate-700">{{ collect(CommunityJoinPolicy::cases())->firstWhere('value', $suggestJoinPolicy)?->label() }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span>Trạng thái hiển thị:</span>
                                <strong class="text-slate-700">{{ collect(CommunityVisibility::cases())->firstWhere('value', $suggestVisibility)?->label() }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="text-[10px] text-slate-400 text-center leading-normal pt-4">
                        Thiết kế được tối ưu hóa cho hiển thị trên phiên bản mobile & máy tính.
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- 2. Unified Share Post Modal --}}
    @if ($showShareModal && $sharingPostId)
        @php $sharingPost = \App\Models\Post::find($sharingPostId); @endphp
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                        <x-ui.icon name="send" size="xs" class="text-ue-brand" />
                        Chia sẻ bài viết
                    </h3>
                    <button type="button" wire:click="$set('showShareModal', false)" class="text-slate-400 hover:text-slate-655 transition">
                        <x-ui.icon name="x" size="xs" />
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    {{-- Copy Public Link helper --}}
                    @if ($sharingPost && $sharingPost->scope_type !== 'community' && $sharingPost->visibility !== 'private')
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-150 flex items-center justify-between gap-3">
                            <span class="text-[11px] font-semibold text-slate-550 select-none">Đường dẫn bài viết công khai</span>
                            <button type="button" @click="navigator.clipboard.writeText('{{ route('posts.show', $sharingPostId) }}'); alert('Đã sao chép liên kết vào bộ nhớ tạm!');"
                                class="px-3 py-1.5 bg-ue-brand text-white text-[10px] font-bold rounded-lg hover:bg-opacity-95 transition">
                                Sao chép
                            </button>
                        </div>
                    @else
                        <div class="p-3 bg-amber-50 text-amber-800 text-[11px] font-semibold rounded-2xl border border-amber-200 flex items-start gap-2">
                            <x-ui.icon name="alert-triangle" size="xs" class="mt-0.5 text-amber-600 flex-shrink-0" />
                            <span>Bài viết trong nhóm nội bộ/chế độ riêng tư. Không thể tạo đường dẫn chia sẻ ra ngoài hệ thống.</span>
                        </div>
                    @endif

                    {{-- Search receiver --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600">Gửi tin nhắn trực tiếp cho Bạn bè</label>
                        <input type="text" wire:model.live.debounce.300ms="shareSearch" placeholder="Tìm kiếm bạn bè..."
                            class="w-full px-3 py-2 border rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200" />
                    </div>

                    {{-- Receiver list --}}
                    <div class="max-h-40 overflow-y-auto space-y-1.5 pr-1 border border-slate-100 p-2 rounded-2xl bg-slate-50/50">
                        @forelse ($this->shareConnections as $conn)
                            <label class="flex items-center justify-between p-2 hover:bg-white rounded-xl cursor-pointer border border-transparent hover:border-slate-150 transition">
                                <div class="flex items-center gap-2.5">
                                    <x-ui.avatar :user="$conn" size="xs" />
                                    <span class="text-xs font-bold text-slate-800">{{ $conn->name }}</span>
                                </div>
                                <input type="radio" wire:model="selectedShareUserId" value="{{ $conn->id }}" class="text-ue-brand focus:ring-ue-brand" />
                            </label>
                        @empty
                            <p class="text-[10px] text-slate-400 italic text-center py-4">Chưa kết nối với bạn bè nào phù hợp.</p>
                        @endforelse
                    </div>

                    {{-- Message input --}}
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-600">Lời nhắn đi kèm (tùy chọn)</label>
                        <textarea wire:model="shareOptionalMessage" rows="2" maxlength="255"
                            class="w-full px-3 py-2 border rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200 resize-none"
                            placeholder="Nhập nội dung tin nhắn đính kèm..."></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-5 py-3.5 bg-slate-50 border-t border-slate-100">
                    <button type="button" wire:click="$set('showShareModal', false)" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 transition">
                        Hủy
                    </button>
                    <button type="button" wire:click="executeShare" @disabled(!$selectedShareUserId)
                        class="px-4 py-2 bg-ue-brand text-white text-xs font-bold rounded-xl transition hover:bg-opacity-95 disabled:opacity-50 disabled:cursor-not-allowed shadow-2xs">
                        Gửi tin nhắn
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
