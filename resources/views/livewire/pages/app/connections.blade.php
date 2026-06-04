<?php

use App\Actions\Connections\AcceptGreeting;
use App\Actions\Connections\BlockUser;
use App\Actions\Connections\CancelGreeting;
use App\Actions\Connections\DeclineGreeting;
use App\Actions\Connections\RemoveConnection;
use App\Actions\Connections\SendGreeting;
use App\Actions\Connections\UnblockUser;
use App\Enums\AccountStatus;
use App\Enums\ConnectionStatus;
use App\Enums\GreetingStatus;
use App\Models\BlockedUser;
use App\Models\Connection;
use App\Models\Greeting;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $activeTab = 'home'; // home, connections, received, sent, blocked, birthday

    public string $connectionSearch = '';

    // Greeting modal state (for quick suggestions connecting flow)
    public bool $showGreetingModal = false;

    public ?User $targetUser = null;

    public string $greetingMessage = 'Xin chào! Mình muốn kết nối học tập/cộng đồng với bạn.';

    public ?string $feedbackMessage = null;

    protected $queryString = [
        'activeTab' => ['except' => 'home'],
    ];

    /**
     * Start the greeting connection flow by opening the modal.
     */
    public function startGreeting(int $userId): void
    {
        $user = User::findOrFail($userId);

        if ($user->id === Auth::id()) {
            $this->feedbackMessage = 'Bạn không thể kết nối với chính mình.';

            return;
        }

        // Check block state
        $isBlocked = BlockedUser::where(function ($q) use ($user) {
            $q->where('blocker_id', Auth::id())->where('blocked_id', $user->id);
        })->orWhere(function ($q) use ($user) {
            $q->where('blocker_id', $user->id)->where('blocked_id', Auth::id());
        })->exists();

        if ($isBlocked) {
            $this->feedbackMessage = 'Không thể gửi lời chào do trạng thái chặn giữa hai tài khoản.';

            return;
        }

        // Check pending requests
        $hasPending = Greeting::where(function ($q) use ($user) {
            $q->where('sender_id', Auth::id())->where('receiver_id', $user->id);
        })->where('status', GreetingStatus::PENDING)->exists();

        if ($hasPending) {
            $this->feedbackMessage = 'Bạn đã gửi một lời chào đang chờ phản hồi.';

            return;
        }

        // Check if already connected
        $userOneId = min(Auth::id(), $user->id);
        $userTwoId = max(Auth::id(), $user->id);
        $isConnected = Connection::where('user_one_id', $userOneId)
            ->where('user_two_id', $userTwoId)
            ->where('status', ConnectionStatus::ACTIVE)
            ->exists();

        if ($isConnected) {
            $this->feedbackMessage = 'Hai bạn đã kết nối với nhau.';

            return;
        }

        $this->targetUser = $user;
        $this->greetingMessage = 'Xin chào! Mình muốn kết nối học tập/cộng đồng với bạn.';
        $this->showGreetingModal = true;
    }

    /**
     * Submit the greeting request.
     */
    public function submitGreeting(SendGreeting $sendGreeting): void
    {
        if (! $this->targetUser) {
            return;
        }

        try {
            $sendGreeting->execute(Auth::user(), $this->targetUser, [
                'message' => $this->greetingMessage,
            ]);

            $this->showGreetingModal = false;
            $this->feedbackMessage = 'Đã gửi lời chào kết nối thành công.';
            $this->targetUser = null;
        } catch (Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Accept a received greeting request.
     */
    public function acceptGreeting(int $greetingId, AcceptGreeting $acceptGreeting): void
    {
        try {
            $greeting = Greeting::findOrFail($greetingId);
            $connection = $acceptGreeting->execute(Auth::user(), $greeting);

            $this->feedbackMessage = 'Đã chấp nhận lời mời kết nối thành công.';
        } catch (Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Decline a received greeting request.
     */
    public function declineGreeting(int $greetingId, DeclineGreeting $declineGreeting): void
    {
        try {
            $greeting = Greeting::findOrFail($greetingId);
            $declineGreeting->execute(Auth::user(), $greeting);

            $this->feedbackMessage = 'Đã từ chối lời mời kết nối.';
        } catch (Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Cancel a sent greeting request.
     */
    public function cancelGreeting(int $greetingId, CancelGreeting $cancelGreeting): void
    {
        try {
            $greeting = Greeting::findOrFail($greetingId);
            $cancelGreeting->execute(Auth::user(), $greeting);

            $this->feedbackMessage = 'Đã hủy lời mời kết nối.';
        } catch (Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Remove an existing connection.
     */
    public function removeConnection(int $connectionId, RemoveConnection $removeConnection): void
    {
        try {
            $connection = Connection::findOrFail($connectionId);
            $removeConnection->execute(Auth::user(), $connection);

            $this->feedbackMessage = 'Đã hủy kết nối thành công.';
        } catch (Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Block a user.
     */
    public function blockUser(int $userId, BlockUser $blockUser): void
    {
        try {
            $user = User::findOrFail($userId);
            $blockUser->execute(Auth::user(), $user, [
                'reason' => 'Blocked via Connections Hub.',
            ]);

            $this->feedbackMessage = 'Đã chặn người dùng này thành công.';
        } catch (Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Unblock a user.
     */
    public function unblockUser(int $userId, UnblockUser $unblockUser): void
    {
        try {
            $user = User::findOrFail($userId);
            $unblockUser->execute(Auth::user(), $user);

            $this->feedbackMessage = 'Đã bỏ chặn người dùng này.';
        } catch (Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    public function with(): array
    {
        $userId = Auth::id();

        // 1. Blocked users lookup (reusable for exclusion)
        $blockedUserIds = BlockedUser::where('blocker_id', $userId)->pluck('blocked_id')
            ->concat(BlockedUser::where('blocked_id', $userId)->pluck('blocker_id'))
            ->unique()
            ->toArray();

        // 2. Fetch active connections
        $connectionsQuery = Connection::where(function ($q) use ($userId) {
            $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
        })
            ->where('status', ConnectionStatus::ACTIVE)
            ->with([
            'userOne.profile.studentProfile.faculty',
            'userOne.profile.alumniProfile.faculty',
            'userOne.profile.advisorProfile.faculty',
            'userOne.profile.studentProfile.academicProgram',
            'userOne.profile.alumniProfile.academicProgram',
            'userTwo.profile.studentProfile.faculty',
            'userTwo.profile.alumniProfile.faculty',
            'userTwo.profile.advisorProfile.faculty',
            'userTwo.profile.studentProfile.academicProgram',
            'userTwo.profile.alumniProfile.academicProgram',
        ]);

        $connections = $connectionsQuery->get()
            ->map(function ($connection) use ($userId) {
                $otherUser = $connection->user_one_id === $userId ? $connection->userTwo : $connection->userOne;

                return [
                    'id' => $connection->id,
                    'user' => $otherUser,
                    'connected_at' => $connection->connected_at,
                ];
            });

        if (! empty($this->connectionSearch)) {
            $search = mb_strtolower($this->connectionSearch);
            $connections = $connections->filter(function ($item) use ($search) {
                $user = $item['user'];
                $profile = $user->profile;
                if (! $profile) {
                    return false;
                }

                $nameMatch = str_contains(mb_strtolower($user->name), $search) || str_contains(mb_strtolower($profile->display_name), $search);
                $facultyMatch = $profile->faculty ? str_contains(mb_strtolower($profile->faculty), $search) : false;

                $program = null;
                if ($profile->studentProfile && $profile->studentProfile->academicProgram) {
                    $program = $profile->studentProfile->academicProgram->name;
                } elseif ($profile->alumniProfile && $profile->alumniProfile->academicProgram) {
                    $program = $profile->alumniProfile->academicProgram->name;
                }
                $programMatch = $program ? str_contains(mb_strtolower($program), $search) : false;

                return $nameMatch || $facultyMatch || $programMatch;
            })->values();
        }

        // 3. Fetch received pending greetings
        $received = Greeting::where('receiver_id', $userId)
            ->where('status', GreetingStatus::PENDING)
            ->with(['sender.profile.studentProfile.faculty'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 4. Fetch sent pending greetings
        $sent = Greeting::where('sender_id', $userId)
            ->where('status', GreetingStatus::PENDING)
            ->with(['receiver.profile.studentProfile.faculty'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 5. Fetch blocked users
        $blocked = BlockedUser::where('blocker_id', $userId)
            ->with(['blocked.profile.studentProfile.faculty'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 6. Suggestions query (excluding self, blocked, connected, pending)
        $excludeUserIds = Connection::where(function ($q) use ($userId) {
            $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
        })
            ->get()
            ->flatMap(fn ($c) => [$c->user_one_id, $c->user_two_id])
            ->concat(Greeting::where('sender_id', $userId)->pluck('receiver_id'))
            ->concat(Greeting::where('receiver_id', $userId)->pluck('sender_id'))
            ->concat([$userId])
            ->concat($blockedUserIds)
            ->unique()
            ->toArray();

        $suggestions = Profile::whereNotIn('user_id', $excludeUserIds)
            ->where('discoverable', true)
            ->whereHas('user', function ($q) {
                $q->where('account_status', AccountStatus::ACTIVE)
                    ->where(function ($sub) {
                        $sub->whereDoesntHave('profilePrivacySetting')
                            ->orWhereHas('profilePrivacySetting', function ($pq) {
                                $pq->where('discovery_visibility', 'enabled');
                            });
                    });
            })
            ->with(['user', 'studentProfile.faculty', 'advisorProfile.faculty', 'alumniProfile.faculty'])
            ->limit(4)
            ->get();

        return [
            'connections' => $connections,
            'received' => $received,
            'sent' => $sent,
            'blocked' => $blocked,
            'suggestions' => $suggestions,
        ];
    }
}; ?>

<div class="flex flex-col lg:flex-row min-h-screen bg-[#f0f2f5] w-full">
    
    {{-- 1. Desktop Left Sidebar --}}
    <aside class="hidden lg:flex flex-col w-80 bg-white border-r border-slate-200 flex-shrink-0 p-4 sticky top-0 h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Bạn bè</h1>
            <a href="#" class="p-2 text-slate-500 hover:bg-slate-100 rounded-full transition" title="Cài đặt kết nối">
                <x-ui.icon name="settings" size="sm" />
            </a>
        </div>

        {{-- Navigation Menu --}}
        <nav class="space-y-1 mb-6">
            {{-- Trang chủ --}}
            <button wire:click="$set('activeTab', 'home')"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all {{ $activeTab === 'home' ? 'bg-ue-brand-soft text-ue-brand' : 'text-slate-700 hover:bg-slate-50' }}">
                <x-ui.icon name="home" size="xs" class="{{ $activeTab === 'home' ? 'text-ue-brand' : 'text-slate-500' }}" />
                <span class="flex-1 text-left">Trang chủ</span>
            </button>

            {{-- Lời mời kết bạn --}}
            <button wire:click="$set('activeTab', 'received')"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all {{ $activeTab === 'received' ? 'bg-ue-brand-soft text-ue-brand' : 'text-slate-700 hover:bg-slate-50' }}">
                <div class="relative">
                    <x-ui.icon name="user-check" size="xs" class="{{ $activeTab === 'received' ? 'text-ue-brand' : 'text-slate-500' }}" />
                    @if(count($received) > 0)
                        <span class="absolute -top-1 -right-1 flex h-2 w-2 items-center justify-center rounded-full bg-red-500 ring-2 ring-white"></span>
                    @endif
                </div>
                <span class="flex-1 text-left">Lời mời đã nhận</span>
                @if(count($received) > 0)
                    <span class="text-xs font-semibold text-slate-400 mr-1">{{ count($received) }} mới</span>
                @endif
            </button>

            {{-- Gợi ý (Links to Discovery page) --}}
            <a href="{{ route('discovery.index') }}" wire:navigate
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="user-plus" size="xs" class="text-slate-500" />
                <span class="flex-1 text-left">Gợi ý</span>
                <x-ui.icon name="chevron-right" size="xs" class="text-slate-400" />
            </a>

            {{-- Tất cả bạn bè --}}
            <button wire:click="$set('activeTab', 'connections')"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all {{ $activeTab === 'connections' ? 'bg-ue-brand-soft text-ue-brand' : 'text-slate-700 hover:bg-slate-50' }}">
                <x-ui.icon name="users" size="xs" class="{{ $activeTab === 'connections' ? 'text-ue-brand' : 'text-slate-500' }}" />
                <span class="flex-1 text-left">Bạn bè/kết nối</span>
                <span class="text-xs text-slate-400 mr-1">{{ count($connections) }}</span>
            </button>

            {{-- Lời mời đã gửi --}}
            <button wire:click="$set('activeTab', 'sent')"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all {{ $activeTab === 'sent' ? 'bg-ue-brand-soft text-ue-brand' : 'text-slate-700 hover:bg-slate-50' }}">
                <x-ui.icon name="send" size="xs" class="{{ $activeTab === 'sent' ? 'text-ue-brand' : 'text-slate-500' }}" />
                <span class="flex-1 text-left">Lời mời đã gửi</span>
                <span class="text-xs text-slate-400 mr-1">{{ count($sent) }}</span>
            </button>

            {{-- Sinh nhật --}}
            <button wire:click="$set('activeTab', 'birthday')"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all {{ $activeTab === 'birthday' ? 'bg-ue-brand-soft text-ue-brand' : 'text-slate-700 hover:bg-slate-50' }}">
                <x-ui.icon name="calendar" size="xs" class="{{ $activeTab === 'birthday' ? 'text-ue-brand' : 'text-slate-500' }}" />
                <span class="flex-1 text-left">Sinh nhật</span>
            </button>

            {{-- Danh sách tùy chỉnh (Đã chặn) --}}
            <button wire:click="$set('activeTab', 'blocked')"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all {{ $activeTab === 'blocked' ? 'bg-ue-brand-soft text-ue-brand' : 'text-slate-700 hover:bg-slate-50' }}">
                <x-ui.icon name="slash" size="xs" class="{{ $activeTab === 'blocked' ? 'text-ue-brand' : 'text-slate-500' }}" />
                <span class="flex-1 text-left">Đã chặn</span>
                <span class="text-xs text-slate-400 mr-1">{{ count($blocked) }}</span>
            </button>
        </nav>
    </aside>

    {{-- 2. Main Content Area --}}
    <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
        
        {{-- Mobile Header: horizontal tabs and buttons --}}
        <div class="lg:hidden bg-white p-3 rounded-2xl border border-slate-200 mb-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h1 class="text-lg font-extrabold text-slate-800">Bạn bè</h1>
                <a href="{{ route('discovery.index') }}" wire:navigate
                    class="p-2 bg-ue-brand text-white rounded-full transition shadow-sm" title="Khám phá UEers">
                    <x-ui.icon name="user-plus" size="xs" />
                </a>
            </div>

            {{-- Mobile Nav Chips --}}
            <div class="flex gap-1.5 overflow-x-auto pb-1 select-none scrollbar-none">
                <button wire:click="$set('activeTab', 'home')"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'home' ? 'bg-ue-brand text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Trang chủ
                </button>
                <button wire:click="$set('activeTab', 'received')"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap relative {{ $activeTab === 'received' ? 'bg-ue-brand text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Lời mời
                    @if(count($received) > 0)
                        <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full bg-red-500 text-[9px] text-white font-bold leading-none">
                            {{ count($received) }}
                        </span>
                    @endif
                </button>
                <a href="{{ route('discovery.index') }}" wire:navigate
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap bg-slate-100 text-slate-600 hover:bg-slate-200">
                    Gợi ý
                </a>
                <button wire:click="$set('activeTab', 'connections')"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'connections' ? 'bg-ue-brand text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Bạn bè ({{ count($connections) }})
                </button>
                <button wire:click="$set('activeTab', 'sent')"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'sent' ? 'bg-ue-brand text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Đã gửi ({{ count($sent) }})
                </button>
                <button wire:click="$set('activeTab', 'birthday')"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'birthday' ? 'bg-ue-brand text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Sinh nhật
                </button>
                <button wire:click="$set('activeTab', 'blocked')"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'blocked' ? 'bg-ue-brand text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Đã chặn ({{ count($blocked) }})
                </button>
            </div>
        </div>

        {{-- Toast Feedback Message --}}
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

        {{-- TAB: Home / Trang chủ --}}
        @if ($activeTab === 'home')
            <div class="space-y-6">
                {{-- Statistics Grid --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs hover:shadow-xs transition flex items-center gap-4 group">
                        <x-ui.icon name="users" size="lg" class="text-slate-500 group-hover:text-ue-brand transition duration-200 flex-shrink-0" />
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Bạn bè</p>
                            <h3 class="text-xl font-extrabold text-slate-800">{{ count($connections) }}</h3>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs hover:shadow-xs transition flex items-center gap-4 group">
                        <x-ui.icon name="user-check" size="lg" class="text-slate-500 group-hover:text-ue-brand transition duration-200 flex-shrink-0" />
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Lời mời mới</p>
                            <h3 class="text-xl font-extrabold text-slate-800">{{ count($received) }}</h3>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs hover:shadow-xs transition flex items-center gap-4 group">
                        <x-ui.icon name="send" size="lg" class="text-slate-500 group-hover:text-ue-brand transition duration-200 flex-shrink-0" />
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Đang chờ</p>
                            <h3 class="text-xl font-extrabold text-slate-800">{{ count($sent) }}</h3>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs hover:shadow-xs transition flex items-center gap-4 group">
                        <x-ui.icon name="slash" size="lg" class="text-slate-500 group-hover:text-ue-brand transition duration-200 flex-shrink-0" />
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Đã chặn</p>
                            <h3 class="text-xl font-extrabold text-slate-800">{{ count($blocked) }}</h3>
                        </div>
                    </div>
                </div>

                {{-- Two-column section --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-6">
                        {{-- Recent Received Invitations --}}
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                                <h2 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                                    <x-ui.icon name="user-check" size="xs" class="text-indigo-500" />
                                    Lời mời kết bạn gần đây
                                </h2>
                                @if (count($received) > 0)
                                    <button wire:click="$set('activeTab', 'received')" class="text-xs font-bold text-ue-brand hover:underline">
                                        Xem tất cả
                                    </button>
                                @endif
                            </div>

                            <div class="space-y-3">
                                @forelse ($received->take(3) as $greeting)
                                    @php $senderProfileUrl = route('profile.show', $greeting->sender); @endphp
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-3.5 hover:bg-slate-50 rounded-xl transition duration-150 border border-slate-100 hover:border-slate-200">
                                        <div class="flex items-start gap-3">
                                            <a href="{{ $senderProfileUrl }}" wire:navigate class="flex-shrink-0">
                                                <x-ui.avatar :user="$greeting->sender" size="md" class="border border-slate-100" />
                                            </a>
                                            <div class="min-w-0 flex-1">
                                                <a href="{{ $senderProfileUrl }}" wire:navigate class="text-xs font-bold text-slate-850 hover:text-ue-brand hover:underline flex items-center gap-1 leading-snug">
                                                    {{ $greeting->sender->name }}
                                                    <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                                                </a>
                                                @if ($greeting->sender->profile && $greeting->sender->profile->faculty)
                                                    <p class="text-[10px] text-slate-400 font-bold mt-0.5">{{ $greeting->sender->profile->faculty }}</p>
                                                @endif
                                                <div class="mt-2 text-xxs text-slate-650 bg-slate-100 border border-slate-150 px-3 py-2 rounded-xl italic relative inline-block max-w-full leading-normal">
                                                    "{{ $greeting->message }}"
                                                </div>
                                                <p class="text-[9px] text-slate-350 font-bold mt-1.5">{{ $greeting->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 self-end sm:self-center">
                                            <button type="button" wire:click="declineGreeting({{ $greeting->id }})"
                                                class="bg-slate-50 hover:bg-slate-100 text-slate-600 text-xxs font-bold px-3 py-1.5 rounded-lg border border-slate-200 transition-colors">
                                                Từ chối
                                            </button>
                                            <button type="button" wire:click="acceptGreeting({{ $greeting->id }})"
                                                class="bg-ue-brand hover:bg-ue-brand-dark text-white text-xxs font-bold px-3 py-1.5 rounded-lg shadow-2xs hover:shadow-3xs transition-all">
                                                Chấp nhận
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-8 flex flex-col items-center justify-center text-center space-y-2 bg-slate-50 rounded-xl border border-dashed border-slate-250">
                                        <x-ui.icon name="mail" size="sm" class="text-slate-300" />
                                        <h4 class="text-xxs font-bold text-slate-550">Hộp thư trống</h4>
                                        <p class="text-[10px] text-slate-400 max-w-xs">Không có lời mời kết bạn mới nào.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Quick Suggestions --}}
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                                <h2 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                                    <x-ui.icon name="user-plus" size="xs" class="text-emerald-500" />
                                    Gợi ý kết bạn
                                </h2>
                                <a href="{{ route('discovery.index') }}" wire:navigate class="text-xs font-bold text-ue-brand hover:underline">
                                    Xem tất cả
                                </a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @forelse ($suggestions as $profile)
                                    @php $profileUrl = route('profile.show', $profile->user); @endphp
                                    <div class="bg-slate-50 border border-slate-150 hover:border-slate-250 hover:shadow-2xs rounded-xl p-3 flex flex-col justify-between transition duration-200 group">
                                        <div class="flex items-start gap-2.5">
                                            <a href="{{ $profileUrl }}" wire:navigate class="flex-shrink-0">
                                                <x-ui.avatar :user="$profile->user" size="sm" class="border border-slate-150 group-hover:scale-105 transition duration-150" />
                                            </a>
                                            <div class="min-w-0 flex-1">
                                                <a href="{{ $profileUrl }}" wire:navigate class="text-xxs font-extrabold text-slate-850 hover:text-ue-brand hover:underline truncate block leading-normal">
                                                    {{ $profile->display_name }}
                                                </a>
                                                <p class="text-[9px] text-slate-400 font-bold uppercase mt-0.5 tracking-wider">
                                                    @if ($profile->role_type === 'student') Sinh viên
                                                    @elseif ($profile->role_type === 'advisor') Mentor/Giảng viên
                                                    @elseif ($profile->role_type === 'alumni') Cựu sinh viên
                                                    @else Thành viên
                                                    @endif
                                                </p>
                                                @if ($profile->faculty)
                                                    <p class="text-[9px] text-slate-500 mt-1 truncate">Khoa {{ $profile->faculty }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex justify-end">
                                            <button type="button" wire:click="startGreeting({{ $profile->user_id }})"
                                                class="w-full bg-white hover:bg-ue-brand-soft text-ue-brand border border-slate-200 hover:border-ue-brand-border text-[10px] font-bold py-1.5 px-2.5 rounded-lg shadow-3xs transition flex items-center justify-center gap-1.5">
                                                <x-ui.icon name="user-plus" size="xs" /> Gửi lời chào
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full py-8 flex flex-col items-center justify-center text-center space-y-2 bg-slate-50 rounded-xl border border-dashed border-slate-250">
                                        <x-ui.icon name="users" size="sm" class="text-slate-300" />
                                        <h4 class="text-xxs font-bold text-slate-550">Không có gợi ý mới</h4>
                                        <p class="text-[10px] text-slate-450">Tất cả thành viên đã được kết nối.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Right column --}}
                    <div class="space-y-6">
                        {{-- Birthdays widget --}}
                        <div class="bg-gradient-to-br from-rose-50 to-pink-50 rounded-2xl border border-rose-100 p-5 shadow-2xs">
                            <div class="flex items-center gap-2 mb-3">
                                <x-ui.icon name="calendar" size="sm" class="text-rose-500 flex-shrink-0" />
                                <h3 class="text-xs font-extrabold text-slate-800">Sinh nhật</h3>
                            </div>
                            <div class="space-y-2">
                                <p class="text-xxs font-medium text-slate-600 leading-normal">
                                    Hôm nay không có bạn bè nào có sinh nhật. Hãy thường xuyên theo dõi để chuẩn bị lời chúc đến những người bạn của mình nhé!
                                </p>
                            </div>
                        </div>

                        {{-- Tips --}}
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
                            <h3 class="text-xs font-extrabold text-slate-800 mb-2">Lời khuyên kết nối</h3>
                            <ul class="space-y-2 text-[10px] font-semibold text-slate-500">
                                <li class="flex items-start gap-1.5">
                                    <span class="text-ue-brand mt-0.5">•</span>
                                    <span>Nêu rõ mục tiêu hoặc giới thiệu bản thân một cách thân thiện để bắt đầu kết nối.</span>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <span class="text-ue-brand mt-0.5">•</span>
                                    <span>Đảm bảo thông tin hồ sơ của bạn rõ ràng, chính xác.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- TAB: Connections / Bạn bè --}}
        @if ($activeTab === 'connections')
            <div class="space-y-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="relative flex-1 max-w-md">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-ui.icon name="search" size="xs" class="text-slate-400" />
                        </span>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="connectionSearch"
                            placeholder="Tìm bạn bè theo tên, khoa, ngành học..."
                            class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 focus:border-ue-brand/40 bg-white placeholder-slate-400 text-slate-700"
                        />
                    </div>
                    <span class="text-xxs font-bold text-slate-400">Tổng số: {{ count($connections) }} bạn bè</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse ($connections as $item)
                        @php
                            $connectedUserProfileUrl = route('profile.show', $item['user']);
                        @endphp
                        <div class="bg-white border border-slate-200 rounded-2xl p-4 flex items-center justify-between hover:border-slate-350 hover:shadow-2xs transition duration-200 group">
                            <div class="flex items-center gap-3 min-w-0">
                                <a href="{{ $connectedUserProfileUrl }}" class="block rounded-full flex-shrink-0 group-hover:scale-105 transition-transform duration-200">
                                    <x-ui.avatar :user="$item['user']" size="md" />
                                </a>
                                <div class="min-w-0">
                                    <a href="{{ $connectedUserProfileUrl }}" class="text-xs font-bold text-slate-800 flex items-center gap-1 hover:text-ue-brand hover:underline truncate">
                                        {{ $item['user']->name }}
                                        <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand flex-shrink-0" />
                                    </a>
                                    @if ($item['user']->profile && $item['user']->profile->faculty)
                                        <p class="text-[10px] text-slate-400 font-bold mt-0.5 truncate">{{ $item['user']->profile->faculty }}</p>
                                    @endif
                                    <p class="text-[9px] text-slate-350 font-semibold mt-1">Kết nối: {{ $item['connected_at']->format('d/m/Y') }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 flex-shrink-0" x-data="{ openOptions: false }" @click.away="openOptions = false">
                                <a
                                    href="{{ route('messages.index', ['conversation' => \App\Models\Conversation::where('conversation_type', \App\Enums\ConversationType::DIRECT)->whereHas('participants', function($q) use ($item) { $q->where('user_id', $item['user']->id); })->first()?->id]) }}"
                                    class="bg-ue-brand hover:bg-ue-brand-dark text-white text-xxs font-bold px-3 py-1.5 rounded-lg shadow-2xs hover:shadow-3xs transition-all flex items-center gap-1"
                                >
                                    <x-ui.icon name="message-square" size="xs" /> Nhắn tin
                                </a>

                                <div class="relative">
                                    <x-ui.icon-button
                                        icon="more-vertical"
                                        label="Tùy chọn kết nối"
                                        variant="ghost"
                                        size="sm"
                                        @click="openOptions = !openOptions"
                                        class="text-slate-400 hover:text-slate-650 focus:ring-1 focus:ring-slate-100"
                                    />

                                    <div
                                        x-show="openOptions"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute right-0 mt-1 rounded-xl bg-white border border-slate-150 shadow-lg py-1.5 z-30 w-44"
                                        style="display: none;"
                                    >
                                        <a
                                            href="{{ $connectedUserProfileUrl }}"
                                            class="w-full text-left px-3 py-2 text-xxs font-semibold text-slate-700 hover:bg-slate-50 hover:text-ue-brand flex items-center gap-2 transition-colors"
                                        >
                                            <x-ui.icon name="user" size="xs" class="text-slate-400" />
                                            Xem trang cá nhân
                                        </a>
                                        <button
                                            type="button"
                                            wire:click="removeConnection({{ $item['id'] }})"
                                            @click="openOptions = false"
                                            class="w-full text-left px-3 py-2 text-xxs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors"
                                        >
                                            <x-ui.icon name="user-minus" size="xs" class="text-slate-400" />
                                            Hủy kết nối
                                        </button>
                                        <button
                                            type="button"
                                            @click="openOptions = false; alert('Báo cáo hồ sơ thành công. Tin báo cáo đã được chuyển cho Ban kiểm duyệt.')"
                                            class="w-full text-left px-3 py-2 text-xxs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors border-t border-slate-100"
                                        >
                                            <x-ui.icon name="flag" size="xs" class="text-slate-400" />
                                            Báo cáo hồ sơ
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="blockUser({{ $item['user']->id }})"
                                            @click="openOptions = false"
                                            class="w-full text-left px-3 py-2 text-xxs font-semibold text-red-650 hover:bg-red-50 flex items-center gap-2 transition-colors border-t border-slate-100"
                                        >
                                            <x-ui.icon name="slash" size="xs" class="text-red-400" />
                                            Chặn thành viên
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 flex flex-col items-center justify-center text-center space-y-3 bg-white rounded-2xl border border-slate-200">
                            <x-ui.icon name="users" size="lg" class="text-slate-300" />
                            <h3 class="text-sm font-bold text-slate-700">
                                {{ !empty($connectionSearch) ? 'Không tìm thấy bạn bè phù hợp' : 'Chưa có kết nối nào' }}
                            </h3>
                            <p class="text-xxs text-slate-450 max-w-sm">
                                {{ !empty($connectionSearch) ? 'Hãy thử thay đổi từ khóa tìm kiếm.' : 'Hãy chuyển sang mục Gợi ý để bắt đầu kết nối với những người bạn HCMUE mới nhé.' }}
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- TAB: Received greetings --}}
        @if ($activeTab === 'received')
            <div class="space-y-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h2 class="text-sm font-extrabold text-slate-800">Lời mời kết bạn đã nhận</h2>
                    <p class="text-xxs text-slate-400 font-medium mt-0.5">Danh sách các lời mời kết bạn từ người dùng khác gửi đến bạn.</p>
                </div>

                <div class="space-y-3">
                    @forelse ($received as $greeting)
                        @php
                            $senderProfileUrl = route('profile.show', $greeting->sender);
                        @endphp
                        <div class="bg-white border border-slate-200 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-slate-350 hover:shadow-2xs transition duration-200">
                            <div class="flex items-start gap-3 min-w-0">
                                <a href="{{ $senderProfileUrl }}" class="block rounded-full flex-shrink-0">
                                    <x-ui.avatar :user="$greeting->sender" size="md" />
                                </a>
                                <div class="min-w-0">
                                    <a href="{{ $senderProfileUrl }}" class="text-xs font-bold text-slate-800 flex items-center gap-1 hover:text-ue-brand hover:underline">
                                        {{ $greeting->sender->name }}
                                        <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                                    </a>
                                    @if ($greeting->sender->profile && $greeting->sender->profile->faculty)
                                        <p class="text-[10px] text-slate-400 font-bold mt-0.5">{{ $greeting->sender->profile->faculty }}</p>
                                    @endif
                                    <div class="bg-slate-50 border border-slate-150 px-3.5 py-2.5 rounded-xl text-xxs font-medium text-slate-650 max-w-lg mt-2 italic leading-normal relative inline-block">
                                        "{{ $greeting->message }}"
                                    </div>
                                    <p class="text-[9px] text-slate-300 font-semibold mt-1.5">{{ $greeting->created_at->diffForHumans() }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-end md:self-center flex-shrink-0">
                                <button
                                    type="button"
                                    wire:click="declineGreeting({{ $greeting->id }})"
                                    class="bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-slate-700 text-xxs font-bold px-3 py-2 rounded-xl border border-slate-250 transition-colors"
                                >
                                    Từ chối
                                </button>
                                <button
                                    type="button"
                                    wire:click="acceptGreeting({{ $greeting->id }})"
                                    class="bg-ue-brand hover:bg-ue-brand-dark text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-2xs hover:shadow-3xs transition-all"
                                >
                                    Chấp nhận
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-white rounded-2xl border border-slate-200">
                            <x-ui.icon name="mail" size="lg" class="text-slate-300" />
                            <h3 class="text-sm font-bold text-slate-700">Không có lời mời kết bạn nào</h3>
                            <p class="text-xxs text-slate-450 max-w-sm">Hộp thư trống. Khi người khác gửi lời chào kết nối với bạn, chúng sẽ xuất hiện ở đây.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- TAB: Sent greetings --}}
        @if ($activeTab === 'sent')
            <div class="space-y-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h2 class="text-sm font-extrabold text-slate-800">Lời mời kết bạn đã gửi</h2>
                    <p class="text-xxs text-slate-400 font-medium mt-0.5">Danh sách các lời mời kết bạn đang chờ phản hồi từ phía người nhận.</p>
                </div>

                <div class="space-y-3">
                    @forelse ($sent as $greeting)
                        @php
                            $receiverProfileUrl = route('profile.show', $greeting->receiver);
                        @endphp
                        <div class="bg-white border border-slate-200 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-slate-350 hover:shadow-2xs transition duration-200">
                            <div class="flex items-start gap-3 min-w-0">
                                <a href="{{ $receiverProfileUrl }}" class="block rounded-full flex-shrink-0">
                                    <x-ui.avatar :user="$greeting->receiver" size="md" />
                                </a>
                                <div class="min-w-0">
                                    <a href="{{ $receiverProfileUrl }}" class="text-xs font-bold text-slate-850 hover:text-ue-brand hover:underline">
                                        {{ $greeting->receiver->name }}
                                    </a>
                                    @if ($greeting->receiver->profile && $greeting->receiver->profile->faculty)
                                        <p class="text-[10px] text-slate-400 font-bold mt-0.5 truncate">{{ $greeting->receiver->profile->faculty }}</p>
                                    @endif
                                    <p class="text-[9.5px] text-slate-500 font-semibold mt-1">Tin nhắn: "{{ \Illuminate\Support\Str::limit($greeting->message, 80) }}"</p>
                                    <p class="text-[9px] text-slate-300 font-semibold mt-1">Đã gửi: {{ $greeting->created_at->diffForHumans() }}</p>
                                </div>
                            </div>

                            <button
                                type="button"
                                wire:click="cancelGreeting({{ $greeting->id }})"
                                class="bg-slate-50 hover:bg-red-50 text-slate-500 hover:text-red-700 text-xxs font-bold px-3 py-1.5 rounded-lg border border-slate-200 hover:border-red-200 transition-colors self-end md:self-center flex-shrink-0"
                            >
                                Hủy yêu cầu
                            </button>
                        </div>
                    @empty
                        <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-white rounded-2xl border border-slate-200">
                            <x-ui.icon name="send" size="lg" class="text-slate-300" />
                            <h3 class="text-sm font-bold text-slate-700">Chưa gửi lời chào nào</h3>
                            <p class="text-xxs text-slate-455 max-w-sm">Tất cả lời mời kết nối bạn đã gửi đang chờ người nhận phản hồi sẽ được liệt kê ở đây.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- TAB: Birthday / Sinh nhật --}}
        @if ($activeTab === 'birthday')
            <div class="space-y-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h2 class="text-sm font-extrabold text-slate-800">Sinh nhật của bạn bè</h2>
                    <p class="text-xxs text-slate-400 font-medium mt-0.5">Theo dõi ngày sinh nhật của những người bạn đã kết nối để gửi lời chúc.</p>
                </div>

                <div class="bg-gradient-to-br from-rose-50 to-pink-50 border border-rose-100 rounded-2xl py-12 px-6 flex flex-col items-center justify-center text-center space-y-4 shadow-sm">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-rose-500 to-pink-500 text-white flex items-center justify-center shadow-md animate-pulse">
                        <x-ui.icon name="calendar" size="lg" />
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-800">Hôm nay không có sinh nhật nào</h3>
                    <p class="text-xxs text-slate-500 max-w-md leading-relaxed font-medium">
                        Không có bạn bè nào của bạn sinh nhật vào hôm nay. Hãy tiếp tục kết nối với nhiều thành viên HCMUE hơn và chuẩn bị những lời chúc ý nghĩa gửi đến họ nhé!
                    </p>
                </div>
            </div>
        @endif

        {{-- TAB: Blocked / Đã chặn --}}
        @if ($activeTab === 'blocked')
            <div class="space-y-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h2 class="text-sm font-extrabold text-slate-800">Danh sách chặn</h2>
                    <p class="text-xxs text-slate-400 font-medium mt-0.5">Những tài khoản này sẽ không thể gửi tin nhắn, lời mời kết nối hay tìm thấy bạn trong kết quả tìm kiếm.</p>
                </div>

                <div class="space-y-3">
                    @forelse ($blocked as $item)
                        @php
                            $blockedUserProfileUrl = route('profile.show', $item->blocked);
                        @endphp
                        <div class="bg-white border border-slate-200 rounded-2xl p-4 flex items-center justify-between hover:border-slate-350 hover:shadow-2xs transition duration-200">
                            <div class="flex items-center gap-3">
                                <a href="{{ $blockedUserProfileUrl }}" class="block rounded-full flex-shrink-0">
                                    <x-ui.avatar :user="$item->blocked" size="md" />
                                </a>
                                <div>
                                    <a href="{{ $blockedUserProfileUrl }}" class="text-xs font-bold text-slate-850 hover:text-ue-brand hover:underline">
                                        {{ $item->blocked->name }}
                                    </a>
                                    @if ($item->blocked->profile && $item->blocked->profile->faculty)
                                        <p class="text-[10px] text-slate-400 font-bold mt-0.5">{{ $item->blocked->profile->faculty }}</p>
                                    @endif
                                    <p class="text-[9px] text-slate-300 font-semibold mt-1">Đã chặn: {{ $item->created_at->diffForHumans() }}</p>
                                </div>
                            </div>

                            <button
                                type="button"
                                wire:click="unblockUser({{ $item->blocked_id }})"
                                class="bg-slate-50 hover:bg-slate-100 text-slate-600 text-xxs font-bold px-3 py-1.5 rounded-lg border border-slate-250 transition-colors"
                            >
                                Bỏ chặn
                            </button>
                        </div>
                    @empty
                        <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-white rounded-2xl border border-slate-200">
                            <x-ui.icon name="slash" size="lg" class="text-slate-300" />
                            <h3 class="text-sm font-bold text-slate-700">Danh sách chặn trống</h3>
                            <p class="text-xxs text-slate-450 max-w-sm">Tài khoản bạn đã chặn sẽ xuất hiện tại đây. Họ sẽ không thể gửi tin nhắn hoặc lời mời kết nối với bạn.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </main>

    {{-- Standard greeting request modal (for quick suggestions) --}}
    @if ($showGreetingModal && $targetUser)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-sm" id="greeting-modal" role="dialog" aria-modal="true">
            <div class="bg-white w-full max-w-md rounded-2xl border border-slate-150 shadow-2xl p-5 transform transition-all duration-sm flex flex-col gap-4">
                {{-- Head --}}
                <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Gửi lời chào kết nối</h2>
                        <p class="text-xxs text-slate-400 font-medium mt-0.5">Gửi lời chào để bắt đầu kết nối học tập/cộng đồng.</p>
                    </div>
                    <button type="button" @click="$wire.set('showGreetingModal', false)" class="text-slate-400 hover:text-slate-650 transition-colors">
                        <x-ui.icon name="x" size="sm" />
                    </button>
                </div>

                {{-- Recipient info summary --}}
                <div class="bg-slate-50 border border-slate-100 p-3 rounded-xl flex items-center gap-3">
                    <a href="{{ route('profile.show', $targetUser) }}" class="block rounded-full focus:outline-none focus:ring-2 focus:ring-ue-brand/30" aria-label="Xem trang cá nhân của {{ $targetUser->name }}">
                        <x-ui.avatar :user="$targetUser" size="sm" />
                    </a>
                    <div>
                        <a href="{{ route('profile.show', $targetUser) }}" class="text-xxs font-bold text-slate-800 leading-tight hover:text-ue-brand hover:underline">{{ $targetUser->name }}</a>
                        @if ($targetUser->profile && $targetUser->profile->faculty)
                            <p class="text-[10px] font-semibold text-slate-400 mt-0.5">{{ $targetUser->profile->faculty }}</p>
                        @endif
                    </div>
                </div>

                {{-- Message Input --}}
                <div class="space-y-1.5">
                    <label for="greeting-msg" class="text-xxs font-bold text-slate-600">Lời giới thiệu bản thân / Lý do kết nối</label>
                    <textarea
                        id="greeting-msg"
                        wire:model="greetingMessage"
                        rows="3"
                        class="w-full text-xxs font-medium p-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 focus:border-ue-brand/40 resize-none bg-slate-50 placeholder-slate-400 text-slate-700"
                        placeholder="Hãy gửi lời giới thiệu thân thiện, lịch sự..."
                        maxlength="200"
                    ></textarea>
                    <div class="flex justify-between items-center text-[10px] font-semibold text-slate-400">
                        <span>Hãy giữ nội dung lịch sự và văn minh</span>
                        <span>{{ mb_strlen($greetingMessage) }}/200</span>
                    </div>
                </div>

                {{-- Footer actions --}}
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        @click="$wire.set('showGreetingModal', false)"
                        class="px-4 py-2 text-xxs font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition-colors border border-slate-250"
                    >
                        Hủy bỏ
                    </button>
                    <button
                        type="button"
                        wire:click="submitGreeting"
                        class="px-4 py-2 text-xxs font-bold text-white bg-ue-brand hover:bg-ue-brand-dark rounded-xl shadow-2xs hover:shadow-sm transition-all"
                    >
                        Xác nhận gửi
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
