<?php

use App\Actions\Connections\SendGreeting;
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
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = 'all'; // all, student, teacher, alumni

    // Greeting modal state
    public bool $showGreetingModal = false;

    public ?User $targetUser = null;

    public string $greetingMessage = 'Xin chào! Mình muốn kết nối học tập/cộng đồng với bạn.';

    // Feedback/toast
    public ?string $feedbackMessage = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

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
     * Get connection status for display.
     */
    public function getConnectionStatus(int $userId): string
    {
        $currentUserId = Auth::id();

        // 1. Check if same user
        if ($userId === $currentUserId) {
            return 'self';
        }

        // 2. Check blocks
        $isBlocked = BlockedUser::where(function ($q) use ($userId, $currentUserId) {
            $q->where('blocker_id', $currentUserId)->where('blocked_id', $userId);
        })->orWhere(function ($q) use ($userId, $currentUserId) {
            $q->where('blocker_id', $userId)->where('blocked_id', $currentUserId);
        })->exists();

        if ($isBlocked) {
            return 'blocked';
        }

        // 3. Check connection
        $userOneId = min($currentUserId, $userId);
        $userTwoId = max($currentUserId, $userId);
        $isConnected = Connection::where('user_one_id', $userOneId)
            ->where('user_two_id', $userTwoId)
            ->where('status', ConnectionStatus::ACTIVE)
            ->exists();

        if ($isConnected) {
            return 'connected';
        }

        // 4. Check sent request
        $hasSent = Greeting::where('sender_id', $currentUserId)
            ->where('receiver_id', $userId)
            ->where('status', GreetingStatus::PENDING)
            ->exists();

        if ($hasSent) {
            return 'pending_sent';
        }

        // 5. Check received request
        $hasReceived = Greeting::where('sender_id', $userId)
            ->where('receiver_id', $currentUserId)
            ->where('status', GreetingStatus::PENDING)
            ->exists();

        if ($hasReceived) {
            return 'pending_received';
        }

        return 'none';
    }

    /**
     * Resolve the shared context between current user and target user.
     *
     * @return array<string>
     */
    public function resolveSharedContext(Profile $targetProfile): array
    {
        $currentUser = Auth::user();
        if (! $currentUser || ! $currentUser->profile) {
            return [];
        }

        $myProfile = $currentUser->profile;
        $shared = [];

        // 1. Same Faculty
        $myFacultyId = null;
        $targetFacultyId = null;

        if ($myProfile->role_type === 'student' && $myProfile->studentProfile) {
            $myFacultyId = $myProfile->studentProfile->faculty_id;
        } elseif ($myProfile->role_type === 'alumni' && $myProfile->alumniProfile) {
            $myFacultyId = $myProfile->alumniProfile->faculty_id;
        } elseif (in_array($myProfile->role_type, ['teacher', 'advisor'], true) && $myProfile->advisorProfile) {
            $myFacultyId = $myProfile->advisorProfile->faculty_id;
        }

        if ($targetProfile->role_type === 'student' && $targetProfile->studentProfile) {
            $targetFacultyId = $targetProfile->studentProfile->faculty_id;
        } elseif ($targetProfile->role_type === 'alumni' && $targetProfile->alumniProfile) {
            $targetFacultyId = $targetProfile->alumniProfile->faculty_id;
        } elseif (in_array($targetProfile->role_type, ['teacher', 'advisor'], true) && $targetProfile->advisorProfile) {
            $targetFacultyId = $targetProfile->advisorProfile->faculty_id;
        }

        if ($myFacultyId && $targetFacultyId && $myFacultyId === $targetFacultyId) {
            if (in_array($targetProfile->role_type, ['teacher', 'advisor'], true)) {
                $shared[] = 'Giảng viên cùng khoa';
            } elseif ($targetProfile->role_type === 'alumni') {
                $shared[] = 'Cựu sinh viên cùng khoa';
            } else {
                $shared[] = 'Cùng khoa '.($targetProfile->faculty ?: '');
            }
        }

        // 2. Same Major/Program
        $myProgramId = null;
        $targetProgramId = null;

        if ($myProfile->role_type === 'student' && $myProfile->studentProfile) {
            $myProgramId = $myProfile->studentProfile->academic_program_id;
        } elseif ($myProfile->role_type === 'alumni' && $myProfile->alumniProfile) {
            $myProgramId = $myProfile->alumniProfile->academic_program_id;
        }

        if ($targetProfile->role_type === 'student' && $targetProfile->studentProfile) {
            $targetProgramId = $targetProfile->studentProfile->academic_program_id;
        } elseif ($targetProfile->role_type === 'alumni' && $targetProfile->alumniProfile) {
            $targetProgramId = $targetProfile->alumniProfile->academic_program_id;
        }

        if ($myProgramId && $targetProgramId && $myProgramId === $targetProgramId) {
            $programName = null;
            if ($targetProfile->studentProfile && $targetProfile->studentProfile->academicProgram) {
                $programName = $targetProfile->studentProfile->academicProgram->name;
            } elseif ($targetProfile->alumniProfile && $targetProfile->alumniProfile->academicProgram) {
                $programName = $targetProfile->alumniProfile->academicProgram->name;
            }
            if ($programName) {
                $shared[] = 'Cùng ngành '.$programName;
            }
        }

        // 3. Same Cohort
        $myCohort = null;
        $targetCohort = null;

        if ($myProfile->role_type === 'student' && $myProfile->studentProfile) {
            $myCohort = $myProfile->studentProfile->cohort;
        } elseif ($myProfile->role_type === 'alumni' && $myProfile->alumniProfile) {
            $myCohort = $myProfile->alumniProfile->cohort;
        }

        if ($targetProfile->role_type === 'student' && $targetProfile->studentProfile) {
            $targetCohort = $targetProfile->studentProfile->cohort;
        } elseif ($targetProfile->role_type === 'alumni' && $targetProfile->alumniProfile) {
            $targetCohort = $targetProfile->alumniProfile->cohort;
        }

        if ($myCohort && $targetCohort && $myCohort === $targetCohort) {
            $shared[] = 'Cùng khóa '.$targetCohort;
        }

        return $shared;
    }

    public function with(): array
    {
        $userId = Auth::id();

        // Count stats for sidebar badges
        $receivedCount = Greeting::where('receiver_id', $userId)->where('status', GreetingStatus::PENDING)->count();
        $connectionsCount = Connection::where(function ($q) use ($userId) {
            $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
        })->where('status', ConnectionStatus::ACTIVE)->count();
        $sentCount = Greeting::where('sender_id', $userId)->where('status', GreetingStatus::PENDING)->count();
        $blockedCount = BlockedUser::where('blocker_id', $userId)->count();

        $blockedUserIds = BlockedUser::where('blocker_id', $userId)->pluck('blocked_id')
            ->concat(BlockedUser::where('blocked_id', $userId)->pluck('blocker_id'))
            ->unique()
            ->toArray();

        $query = Profile::where('user_id', '!=', $userId)
            ->where('discoverable', true)
            ->whereNotIn('user_id', $blockedUserIds)
            ->whereHas('user', function ($q) {
                $q->where('account_status', AccountStatus::ACTIVE)
                    ->where(function ($sub) {
                        $sub->whereDoesntHave('profilePrivacySetting')
                            ->orWhereHas('profilePrivacySetting', function ($pq) {
                                $pq->where('discovery_visibility', 'enabled');
                            });
                    });
            })
            ->with(['user', 'studentProfile.faculty', 'advisorProfile.faculty', 'alumniProfile.faculty', 'studentProfile.academicProgram', 'alumniProfile.academicProgram']);

        if ($this->roleFilter !== 'all') {
            $query->where('role_type', $this->roleFilter);
        }

        if (! empty($this->search)) {
            $searchTerm = '%'.$this->search.'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('display_name', 'like', $searchTerm)
                    ->orWhere('bio', 'like', $searchTerm)
                    ->orWhereHas('user', function ($uq) use ($searchTerm) {
                        $uq->where('name', 'like', $searchTerm)
                            ->orWhere('email', 'like', $searchTerm);
                    })
                    ->orWhereHas('studentProfile.faculty', function ($fq) use ($searchTerm) {
                        $fq->where('name', 'like', $searchTerm);
                    })
                    ->orWhereHas('studentProfile.academicProgram', function ($apq) use ($searchTerm) {
                        $apq->where('name', 'like', $searchTerm);
                    })
                    ->orWhereHas('alumniProfile.faculty', function ($fq) use ($searchTerm) {
                        $fq->where('name', 'like', $searchTerm);
                    })
                    ->orWhereHas('alumniProfile.academicProgram', function ($apq) use ($searchTerm) {
                        $apq->where('name', 'like', $searchTerm);
                    })
                    ->orWhereHas('advisorProfile.faculty', function ($fq) use ($searchTerm) {
                        $fq->where('name', 'like', $searchTerm);
                    });
            });
        }

        return [
            'profiles' => $query->paginate(12),
            'receivedCount' => $receivedCount,
            'connectionsCount' => $connectionsCount,
            'sentCount' => $sentCount,
            'blockedCount' => $blockedCount,
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
            <a href="{{ route('connections.index', ['activeTab' => 'home']) }}" wire:navigate
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="home" size="xs" class="text-slate-500" />
                <span class="flex-1 text-left">Trang chủ</span>
            </a>

            {{-- Lời mời kết bạn --}}
            <a href="{{ route('connections.index', ['activeTab' => 'received']) }}" wire:navigate
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all text-slate-700 hover:bg-slate-50">
                <div class="relative">
                    <x-ui.icon name="user-check" size="xs" class="text-slate-500" />
                    @if($receivedCount > 0)
                        <span class="absolute -top-1 -right-1 flex h-2 w-2 items-center justify-center rounded-full bg-red-500 ring-2 ring-white"></span>
                    @endif
                </div>
                <span class="flex-1 text-left">Lời mời kết bạn</span>
                @if($receivedCount > 0)
                    <span class="text-xs font-semibold text-slate-400 mr-1">{{ $receivedCount }} mới</span>
                @endif
            </a>

            {{-- Gợi ý (Active here) --}}
            <button wire:click="reset()"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all bg-ue-brand-soft text-ue-brand">
                <x-ui.icon name="user-plus" size="xs" class="text-ue-brand" />
                <span class="flex-1 text-left">Gợi ý</span>
                <x-ui.icon name="chevron-right" size="xs" class="text-ue-brand" />
            </button>

            {{-- Tất cả bạn bè --}}
            <a href="{{ route('connections.index', ['activeTab' => 'connections']) }}" wire:navigate
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="users" size="xs" class="text-slate-500" />
                <span class="flex-1 text-left">Tất cả bạn bè</span>
                <span class="text-xs text-slate-400 mr-1">{{ $connectionsCount }}</span>
            </a>

            {{-- Lời mời đã gửi --}}
            <a href="{{ route('connections.index', ['activeTab' => 'sent']) }}" wire:navigate
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="send" size="xs" class="text-slate-500" />
                <span class="flex-1 text-left">Lời mời đã gửi</span>
                <span class="text-xs text-slate-400 mr-1">{{ $sentCount }}</span>
            </a>

            {{-- Sinh nhật --}}
            <a href="{{ route('connections.index', ['activeTab' => 'birthday']) }}" wire:navigate
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="calendar" size="xs" class="text-slate-500" />
                <span class="flex-1 text-left">Sinh nhật</span>
            </a>

            {{-- Danh sách chặn --}}
            <a href="{{ route('connections.index', ['activeTab' => 'blocked']) }}" wire:navigate
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="slash" size="xs" class="text-slate-500" />
                <span class="flex-1 text-left">Đã chặn</span>
                <span class="text-xs text-slate-400 mr-1">{{ $blockedCount }}</span>
            </a>
        </nav>
    </aside>

    {{-- 2. Main Content Area --}}
    <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
        
        {{-- Mobile Header: horizontal tabs and buttons --}}
        <div class="lg:hidden bg-white p-3 rounded-2xl border border-slate-200 mb-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h1 class="text-lg font-extrabold text-slate-800">Gợi ý kết bạn</h1>
                <a href="{{ route('connections.index') }}" wire:navigate
                    class="p-2 bg-slate-100 hover:bg-slate-250 text-slate-600 rounded-full transition shadow-3xs" title="Quản lý bạn bè">
                    <x-ui.icon name="users" size="xs" />
                </a>
            </div>

            {{-- Mobile Nav Chips --}}
            <div class="flex gap-1.5 overflow-x-auto pb-1 select-none scrollbar-none">
                <a href="{{ route('connections.index', ['activeTab' => 'home']) }}" wire:navigate
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap bg-slate-100 text-slate-600 hover:bg-slate-200">
                    Trang chủ
                </a>
                <a href="{{ route('connections.index', ['activeTab' => 'received']) }}" wire:navigate
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap relative bg-slate-100 text-slate-600 hover:bg-slate-200">
                    Lời mời
                    @if($receivedCount > 0)
                        <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full bg-red-500 text-[9px] text-white font-bold leading-none">
                            {{ $receivedCount }}
                        </span>
                    @endif
                </a>
                <button wire:click="reset()"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap bg-ue-brand text-white">
                    Gợi ý
                </button>
                <a href="{{ route('connections.index', ['activeTab' => 'connections']) }}" wire:navigate
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap bg-slate-100 text-slate-600 hover:bg-slate-200">
                    Bạn bè ({{ $connectionsCount }})
                </a>
                <a href="{{ route('connections.index', ['activeTab' => 'sent']) }}" wire:navigate
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap bg-slate-100 text-slate-600 hover:bg-slate-200">
                    Đã gửi ({{ $sentCount }})
                </a>
                <a href="{{ route('connections.index', ['activeTab' => 'birthday']) }}" wire:navigate
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap bg-slate-100 text-slate-600 hover:bg-slate-200">
                    Sinh nhật
                </a>
                <a href="{{ route('connections.index', ['activeTab' => 'blocked']) }}" wire:navigate
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap bg-slate-100 text-slate-600 hover:bg-slate-200">
                    Đã chặn ({{ $blockedCount }})
                </a>
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

        {{-- Main Content Section --}}
        <div class="space-y-6">
            {{-- Title Header and Controls --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-800">Những người bạn có thể biết</h2>
                    <p class="text-xxs text-slate-400 font-medium mt-0.5">Tìm kiếm bạn bè, mentor, cựu sinh viên cùng trường Đại học Sư phạm TP.HCM để cùng nhau học tập và chia sẻ.</p>
                </div>

                <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
                    {{-- Search Input --}}
                    <div class="relative flex-1 max-w-md">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-ui.icon name="search" size="xs" class="text-slate-400" />
                        </span>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Tìm tên, khoa, ngành học, tiểu sử..."
                            class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 focus:border-ue-brand/40 bg-slate-50 placeholder-slate-400 text-slate-700"
                        />
                    </div>

                    {{-- Filters --}}
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 select-none scrollbar-none">
                        <button
                            type="button"
                            wire:click="$set('roleFilter', 'all')"
                            class="px-3 py-1.5 rounded-lg text-xxs font-bold transition-all shrink-0 whitespace-nowrap {{ $roleFilter === 'all' ? 'bg-ue-brand-soft text-ue-brand border border-ue-brand-border shadow-3xs' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border border-transparent' }}"
                        >
                            Tất cả
                        </button>
                        <button
                            type="button"
                            wire:click="$set('roleFilter', 'student')"
                            class="px-3 py-1.5 rounded-lg text-xxs font-bold transition-all shrink-0 whitespace-nowrap {{ $roleFilter === 'student' ? 'bg-ue-brand-soft text-ue-brand border border-ue-brand-border shadow-3xs' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border border-transparent' }}"
                        >
                            Sinh viên
                        </button>
                        <button
                            type="button"
                            wire:click="$set('roleFilter', 'teacher')"
                            class="px-3 py-1.5 rounded-lg text-xxs font-bold transition-all shrink-0 whitespace-nowrap {{ $roleFilter === 'teacher' ? 'bg-ue-brand-soft text-ue-brand border border-ue-brand-border shadow-3xs' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border border-transparent' }}"
                        >
                            Giảng viên
                        </button>
                        <button
                            type="button"
                            wire:click="$set('roleFilter', 'alumni')"
                            class="px-3 py-1.5 rounded-lg text-xxs font-bold transition-all shrink-0 whitespace-nowrap {{ $roleFilter === 'alumni' ? 'bg-ue-brand-soft text-ue-brand border border-ue-brand-border shadow-3xs' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border border-transparent' }}"
                        >
                            Cựu sinh viên
                        </button>
                    </div>
                </div>
            </div>

            {{-- Grid List --}}
            <div>
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                    wire:loading.delay.class="ue-content-loading"
                    wire:target="search,roleFilter,nextPage,previousPage,gotoPage"
                    aria-busy="false"
                >
                    @forelse ($profiles as $profile)
                        @php
                            $status = $this->getConnectionStatus($profile->user_id);
                            $profileUrl = route('profile.show', $profile->user);
                        @endphp
                        <div class="ue-loadable-card bg-white border border-slate-200 rounded-2xl p-4 flex flex-col justify-between hover:shadow-sm hover:border-slate-350 transition-all duration-200 group">
                            <div>
                                {{-- Profile identity info --}}
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ $profileUrl }}" wire:navigate class="block rounded-full focus:outline-none focus:ring-2 focus:ring-ue-brand/30" aria-label="Xem trang cá nhân của {{ $profile->display_name }}">
                                            <x-ui.avatar :user="$profile->user" size="md" class="border border-slate-100 group-hover:scale-105 transition-transform duration-200" />
                                        </a>
                                        <div>
                                            <a href="{{ $profileUrl }}" wire:navigate class="text-xs font-bold text-slate-800 flex items-center gap-1 leading-snug hover:text-ue-brand hover:underline">
                                                {{ $profile->display_name }}
                                                <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                                            </a>
                                            <p class="text-[10px] text-slate-400 font-bold tracking-wide uppercase mt-0.5">
                                                @if ($profile->role_type === 'student') Sinh viên
                                                @elseif (in_array($profile->role_type, ['teacher', 'advisor'], true)) Giảng viên
                                                @elseif ($profile->role_type === 'alumni') Cựu sinh viên
                                                @else Thành viên
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Faculty tag --}}
                                    @if ($profile->faculty)
                                        <span class="bg-slate-50 text-[9px] font-bold text-slate-500 px-2 py-0.5 rounded-md border border-slate-150 leading-none flex-shrink-0">
                                            {{ \Illuminate\Support\Str::limit($profile->faculty, 15) }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Bio --}}
                                @if ($profile->bio)
                                    <p class="text-xxs text-slate-500 font-semibold leading-relaxed mt-3.5 line-clamp-2">
                                        {{ $profile->bio }}
                                    </p>
                                @else
                                    <p class="text-xxs text-slate-350 italic font-semibold leading-relaxed mt-3.5">
                                        Chưa cập nhật giới thiệu bản thân.
                                    </p>
                                @endif

                                {{-- Shared Context Commonalities --}}
                                @php
                                    $sharedContext = $this->resolveSharedContext($profile);
                                @endphp
                                @if (!empty($sharedContext))
                                    <div class="mt-3.5 flex flex-wrap gap-1.5">
                                        @foreach ($sharedContext as $contextText)
                                            <span class="inline-flex items-center gap-1 bg-ue-brand-soft text-[9px] font-extrabold text-ue-brand px-2 py-0.5 rounded-md border border-ue-brand-border leading-none">
                                                <x-ui.icon name="sparkles" size="xxs" />
                                                {{ $contextText }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Action row --}}
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                <span class="text-[10px] text-slate-400 font-bold">
                                    @if ($status === 'connected')
                                        <span class="flex items-center gap-1 text-emerald-650">
                                            <x-ui.icon name="check" size="xs" /> Đã kết nối
                                        </span>
                                    @elseif ($status === 'pending_sent')
                                        <span class="text-amber-650">Đã gửi lời chào</span>
                                    @elseif ($status === 'pending_received')
                                        <span class="text-indigo-650">Chờ bạn đồng ý</span>
                                    @elseif ($status === 'blocked')
                                        <span class="text-red-500">Đã chặn</span>
                                    @else
                                        <span class="text-slate-400">Chưa kết nối</span>
                                    @endif
                                </span>

                                {{-- Action buttons --}}
                                @if ($status === 'none')
                                    <button
                                        type="button"
                                        wire:click="startGreeting({{ $profile->user_id }})"
                                        class="bg-ue-brand hover:bg-ue-brand-dark text-white text-xxs font-bold px-3 py-1.5 rounded-lg shadow-3xs hover:shadow-2xs transition-all flex items-center gap-1.5"
                                    >
                                        <x-ui.icon name="user-plus" size="xs" /> Gửi lời chào
                                    </button>
                                @elseif ($status === 'connected')
                                    <a
                                        href="{{ route('messages.index', ['conversation' => \App\Models\Conversation::where('conversation_type', \App\Enums\ConversationType::DIRECT)->whereHas('participants', function($q) use ($profile) { $q->where('user_id', $profile->user_id); })->first()?->id]) }}"
                                        wire:navigate
                                        class="bg-slate-50 hover:bg-slate-100 text-slate-700 text-xxs font-bold px-3 py-1.5 rounded-lg border border-slate-250 transition-colors flex items-center gap-1.5"
                                    >
                                        <x-ui.icon name="message-square" size="xs" /> Nhắn tin
                                    </a>
                                @elseif ($status === 'pending_received')
                                    <a
                                        href="{{ route('connections.index', ['activeTab' => 'received']) }}"
                                        wire:navigate
                                        class="bg-indigo-50 hover:bg-indigo-100 text-indigo-755 text-xxs font-bold px-3 py-1.5 rounded-lg transition-colors border border-indigo-150"
                                    >
                                        Xem lời mời
                                    </a>
                                @else
                                    <button
                                        type="button"
                                        disabled
                                        class="bg-slate-100 text-slate-350 text-xxs font-bold px-3 py-1.5 rounded-lg cursor-not-allowed border border-slate-200"
                                    >
                                        @if ($status === 'pending_sent') Chờ phản hồi
                                        @else Không khả dụng
                                        @endif
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 flex flex-col items-center justify-center text-center space-y-3 bg-white rounded-2xl border border-slate-200">
                            <x-ui.icon name="users" size="lg" class="text-slate-300" />
                            <h3 class="text-sm font-bold text-slate-700">Chưa tìm thấy thành viên phù hợp</h3>
                            <p class="text-xxs text-slate-450 max-w-sm">Hãy thử thay đổi từ khóa tìm kiếm hoặc lọc theo các đối tượng khác để kết nối nhé.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Pagination --}}
            @if ($profiles->hasPages())
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    {{ $profiles->links() }}
                </div>
            @endif
        </div>
    </main>

    {{-- Standard greeting request modal (Non-dating, study/community contexts) --}}
    @if ($showGreetingModal && $targetUser)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-sm" id="greeting-modal" role="dialog" aria-modal="true">
            <div class="bg-white w-full max-w-md rounded-2xl border border-slate-150 shadow-2xl p-5 transform transition-all duration-sm flex flex-col gap-4">
                {{-- Head --}}
                <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Gửi lời chào kết nối</h2>
                        <p class="text-xxs text-slate-400 font-medium mt-0.5">Gửi lời chào để bắt đầu kết nối học tập/cộng đồng.</p>
                    </div>
                    <button type="button" @click="$wire.set('showGreetingModal', false)" class="text-slate-400 hover:text-slate-655 transition-colors">
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
