<?php

use App\Models\User;
use App\Models\Profile;
use App\Models\Greeting;
use App\Models\Connection;
use App\Models\BlockedUser;
use App\Enums\GreetingStatus;
use App\Enums\ConnectionStatus;
use App\Actions\Connections\SendGreeting;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = 'all'; // all, student, advisor, alumni

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
        } catch (\Exception $e) {
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
        if (!$currentUser || !$currentUser->profile) {
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
        } elseif ($myProfile->role_type === 'advisor' && $myProfile->advisorProfile) {
            $myFacultyId = $myProfile->advisorProfile->faculty_id;
        }

        if ($targetProfile->role_type === 'student' && $targetProfile->studentProfile) {
            $targetFacultyId = $targetProfile->studentProfile->faculty_id;
        } elseif ($targetProfile->role_type === 'alumni' && $targetProfile->alumniProfile) {
            $targetFacultyId = $targetProfile->alumniProfile->faculty_id;
        } elseif ($targetProfile->role_type === 'advisor' && $targetProfile->advisorProfile) {
            $targetFacultyId = $targetProfile->advisorProfile->faculty_id;
        }

        if ($myFacultyId && $targetFacultyId && $myFacultyId === $targetFacultyId) {
            if ($targetProfile->role_type === 'advisor') {
                $shared[] = 'Cố vấn / giảng viên cùng khoa';
            } elseif ($targetProfile->role_type === 'alumni') {
                $shared[] = 'Cựu sinh viên cùng khoa';
            } else {
                $shared[] = 'Cùng khoa ' . ($targetProfile->faculty ?: '');
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
                $shared[] = 'Cùng ngành ' . $programName;
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
            $shared[] = 'Cùng khóa ' . $targetCohort;
        }

        return $shared;
    }

    public function with(): array
    {
        $blockedUserIds = BlockedUser::where('blocker_id', Auth::id())->pluck('blocked_id')
            ->concat(BlockedUser::where('blocked_id', Auth::id())->pluck('blocker_id'))
            ->unique()
            ->toArray();

        $query = Profile::where('user_id', '!=', Auth::id())
            ->where('discoverable', true)
            ->whereNotIn('user_id', $blockedUserIds)
            ->whereHas('user', function ($q) {
                $q->where('account_status', \App\Enums\AccountStatus::ACTIVE)
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
            $searchTerm = '%' . $this->search . '%';
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
        ];
    }
}; ?>

<div class="py-6 px-4 max-w-6xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-1.5 border-b border-slate-100 pb-4">
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Khám phá UEers</h1>
        <p class="text-xs text-slate-400 font-medium">Tìm kiếm và kết nối với các bạn sinh viên, mentor, câu lạc bộ trong hệ thống xác thực HCMUE.</p>
    </div>

    {{-- Controls --}}
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
                class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 focus:border-ue-brand/40 bg-white placeholder-slate-400 text-slate-700"
            />
        </div>

        {{-- Filters --}}
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
            <button
                type="button"
                wire:click="$set('roleFilter', 'all')"
                class="px-3 py-1.5 rounded-lg text-xxs font-bold transition-all {{ $roleFilter === 'all' ? 'bg-ue-brand-soft text-ue-brand-active border border-ue-brand-border shadow-xs' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border border-transparent' }}"
            >
                Tất cả
            </button>
            <button
                type="button"
                wire:click="$set('roleFilter', 'student')"
                class="px-3 py-1.5 rounded-lg text-xxs font-bold transition-all {{ $roleFilter === 'student' ? 'bg-ue-brand-soft text-ue-brand-active border border-ue-brand-border shadow-xs' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border border-transparent' }}"
            >
                Sinh viên
            </button>
            <button
                type="button"
                wire:click="$set('roleFilter', 'advisor')"
                class="px-3 py-1.5 rounded-lg text-xxs font-bold transition-all {{ $roleFilter === 'advisor' ? 'bg-ue-brand-soft text-ue-brand-active border border-ue-brand-border shadow-xs' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border border-transparent' }}"
            >
                Mentor / Giảng viên
            </button>
            <button
                type="button"
                wire:click="$set('roleFilter', 'alumni')"
                class="px-3 py-1.5 rounded-lg text-xxs font-bold transition-all {{ $roleFilter === 'alumni' ? 'bg-ue-brand-soft text-ue-brand-active border border-ue-brand-border shadow-xs' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border border-transparent' }}"
            >
                Cựu sinh viên
            </button>
        </div>
    </div>

    {{-- Feedback Message Toast --}}
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

    {{-- Grid List --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($profiles as $profile)
            @php
                $status = $this->getConnectionStatus($profile->user_id);
            @endphp
            <div class="bg-white border border-slate-150 rounded-2xl p-4 flex flex-col justify-between hover:shadow-sm hover:border-slate-300 transition-all duration-sm">
                <div>
                    {{-- Profile identity info --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <x-ui.avatar :user="$profile->user" size="md" class="border border-slate-100" />
                            <div>
                                <h3 class="text-xs font-bold text-slate-800 flex items-center gap-1 leading-snug">
                                    {{ $profile->display_name }}
                                    <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                                </h3>
                                <p class="text-[10px] text-slate-400 font-semibold tracking-wide uppercase mt-0.5">
                                    @if ($profile->role_type === 'student') Sinh viên
                                    @elseif ($profile->role_type === 'advisor') Mentor/Giảng viên
                                    @elseif ($profile->role_type === 'alumni') Cựu sinh viên
                                    @else Thành viên
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Faculty tag --}}
                        @if ($profile->faculty)
                            <span class="bg-slate-50 text-[9px] font-bold text-slate-500 px-2 py-0.5 rounded-md border border-slate-100 leading-none">
                                {{ \Illuminate\Support\Str::limit($profile->faculty, 15) }}
                            </span>
                        @endif
                    </div>

                    {{-- Bio --}}
                    @if ($profile->bio)
                        <p class="text-xxs text-slate-500 font-medium leading-relaxed mt-3.5 line-clamp-2">
                            {{ $profile->bio }}
                        </p>
                    @else
                        <p class="text-xxs text-slate-300 italic font-medium leading-relaxed mt-3.5">
                            Chưa cập nhật giới thiệu bản thân.
                        </p>
                    @endif

                    {{-- Shared Context Commonalities --}}
                    @php
                        $sharedContext = $this->resolveSharedContext($profile);
                    @endphp
                    @if (!empty($sharedContext))
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($sharedContext as $contextText)
                                <span class="inline-flex items-center gap-1 bg-ue-brand-soft/40 text-[10px] font-bold text-ue-brand px-2 py-0.5 rounded-md border border-ue-brand-soft leading-none">
                                    <x-ui.icon name="sparkles" size="2xs" />
                                    {{ $contextText }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Action row --}}
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <span class="text-[10px] text-slate-400 font-medium">
                        @if ($status === 'connected')
                            <span class="flex items-center gap-1 text-emerald-600 font-semibold">
                                <x-ui.icon name="check" size="xs" /> Đã kết nối
                            </span>
                        @elseif ($status === 'pending_sent')
                            <span class="text-amber-600 font-semibold">Đã gửi lời chào</span>
                        @elseif ($status === 'pending_received')
                            <span class="text-indigo-600 font-semibold">Chờ bạn đồng ý</span>
                        @elseif ($status === 'blocked')
                            <span class="text-red-500 font-semibold">Đã chặn</span>
                        @else
                            <span class="text-slate-400 font-medium">Chưa kết nối</span>
                        @endif
                    </span>

                    {{-- Action buttons --}}
                    @if ($status === 'none')
                        <button
                            type="button"
                            wire:click="startGreeting({{ $profile->user_id }})"
                            class="bg-ue-brand hover:bg-ue-brand-dark text-white text-xxs font-bold px-3 py-1.5 rounded-lg shadow-2xs hover:shadow-sm transition-all flex items-center gap-1.5"
                        >
                            <x-ui.icon name="user-plus" size="xs" /> Gửi lời chào
                        </button>
                    @elseif ($status === 'connected')
                        <a
                            href="{{ route('messages.index', ['conversation' => \App\Models\Conversation::where('conversation_type', \App\Enums\ConversationType::DIRECT)->whereHas('participants', function($q) use ($profile) { $q->where('user_id', $profile->user_id); })->first()?->id]) }}"
                            class="bg-slate-50 hover:bg-slate-100 text-slate-700 text-xxs font-bold px-3 py-1.5 rounded-lg border border-slate-200 transition-colors flex items-center gap-1.5"
                        >
                            <x-ui.icon name="message-square" size="xs" /> Nhắn tin
                        </a>
                    @elseif ($status === 'pending_received')
                        <a
                            href="{{ route('connections.index') }}"
                            class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xxs font-bold px-3 py-1.5 rounded-lg transition-colors"
                        >
                            Xem lời mời
                        </a>
                    @else
                        <button
                            type="button"
                            disabled
                            class="bg-slate-100 text-slate-350 text-xxs font-semibold px-3 py-1.5 rounded-lg cursor-not-allowed"
                        >
                            @if ($status === 'pending_sent') Chờ phản hồi
                            @else Không khả dụng
                            @endif
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 rounded-2xl border border-dashed border-slate-250">
                <x-ui.icon name="users" size="lg" class="text-slate-300" />
                <h3 class="text-sm font-bold text-slate-700">Chưa tìm thấy UEers phù hợp</h3>
                <p class="text-xxs text-slate-400 max-w-sm">Hãy thử thay đổi từ khóa tìm kiếm hoặc lọc theo các đối tượng khác để kết nối nhé.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $profiles->links() }}
    </div>

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
                    <button type="button" @click="$wire.set('showGreetingModal', false)" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <x-ui.icon name="x" size="sm" />
                    </button>
                </div>

                {{-- Recipient info summary --}}
                <div class="bg-slate-50 border border-slate-100 p-3 rounded-xl flex items-center gap-3">
                    <x-ui.avatar :user="$targetUser" size="sm" />
                    <div>
                        <p class="text-xxs font-bold text-slate-800 leading-tight">{{ $targetUser->name }}</p>
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
                        class="px-4 py-2 text-xxs font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition-colors border border-slate-200"
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
