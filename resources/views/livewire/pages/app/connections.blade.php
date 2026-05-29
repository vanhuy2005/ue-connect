<?php

use App\Models\User;
use App\Models\Greeting;
use App\Models\Connection;
use App\Models\BlockedUser;
use App\Enums\GreetingStatus;
use App\Enums\ConnectionStatus;
use App\Actions\Connections\AcceptGreeting;
use App\Actions\Connections\DeclineGreeting;
use App\Actions\Connections\CancelGreeting;
use App\Actions\Connections\RemoveConnection;
use App\Actions\Connections\BlockUser;
use App\Actions\Connections\UnblockUser;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $activeTab = 'connections'; // connections, received, sent, blocked
    public string $connectionSearch = '';

    public ?string $feedbackMessage = null;

    /**
     * Accept a received greeting request.
     */
    public function acceptGreeting(int $greetingId, AcceptGreeting $acceptGreeting): void
    {
        try {
            $greeting = Greeting::findOrFail($greetingId);
            $connection = $acceptGreeting->execute(Auth::user(), $greeting);

            $this->feedbackMessage = 'Đã chấp nhận lời mời kết nối thành công.';
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    public function with(): array
    {
        $userId = Auth::id();

        // 1. Fetch active connections
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
                'userTwo.profile.alumniProfile.academicProgram'
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

        if (!empty($this->connectionSearch)) {
            $search = mb_strtolower($this->connectionSearch);
            $connections = $connections->filter(function ($item) use ($search) {
                $user = $item['user'];
                $profile = $user->profile;
                if (!$profile) {
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

        // 2. Fetch received pending greetings
        $received = Greeting::where('receiver_id', $userId)
            ->where('status', GreetingStatus::PENDING)
            ->with(['sender.profile.studentProfile.faculty'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Fetch sent pending greetings
        $sent = Greeting::where('sender_id', $userId)
            ->where('status', GreetingStatus::PENDING)
            ->with(['receiver.profile.studentProfile.faculty'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 4. Fetch blocked users
        $blocked = BlockedUser::where('blocker_id', $userId)
            ->with(['blocked.profile.studentProfile.faculty'])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'connections' => $connections,
            'received' => $received,
            'sent' => $sent,
            'blocked' => $blocked,
        ];
    }
}; ?>

<div class="py-6 px-4 max-w-6xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-1.5 border-b border-slate-100 pb-4">
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Quản lý kết nối</h1>
        <p class="text-xs text-slate-400 font-medium">Kết nối học tập, quản lý danh sách bạn bè, các lời mời đã nhận, đã gửi và người dùng đã chặn.</p>
    </div>

    {{-- Tabs system --}}
    <div class="flex border-b border-slate-150 gap-1 overflow-x-auto">
        <button
            type="button"
            wire:click="$set('activeTab', 'connections')"
            class="px-4 py-2 text-xxs font-bold border-b-2 transition-all whitespace-nowrap {{ $activeTab === 'connections' ? 'border-ue-brand text-ue-brand' : 'border-transparent text-slate-500 hover:text-slate-700' }}"
        >
            Bạn bè/kết nối ({{ count($connections) }})
        </button>
        <button
            type="button"
            wire:click="$set('activeTab', 'received')"
            class="px-4 py-2 text-xxs font-bold border-b-2 transition-all whitespace-nowrap {{ $activeTab === 'received' ? 'border-ue-brand text-ue-brand' : 'border-transparent text-slate-500 hover:text-slate-700' }}"
        >
            Lời mời đã nhận ({{ count($received) }})
        </button>
        <button
            type="button"
            wire:click="$set('activeTab', 'sent')"
            class="px-4 py-2 text-xxs font-bold border-b-2 transition-all whitespace-nowrap {{ $activeTab === 'sent' ? 'border-ue-brand text-ue-brand' : 'border-transparent text-slate-500 hover:text-slate-700' }}"
        >
            Lời mời đã gửi ({{ count($sent) }})
        </button>
        <button
            type="button"
            wire:click="$set('activeTab', 'blocked')"
            class="px-4 py-2 text-xxs font-bold border-b-2 transition-all whitespace-nowrap {{ $activeTab === 'blocked' ? 'border-ue-brand text-ue-brand' : 'border-transparent text-slate-500 hover:text-slate-700' }}"
        >
            Đã chặn ({{ count($blocked) }})
        </button>
    </div>

    {{-- Feedback Toast --}}
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

    {{-- Tab content grids --}}
    <div>
        {{-- TAB 1: Connections --}}
        @if ($activeTab === 'connections')
            <div class="space-y-4">
                {{-- Connections Search Input --}}
                <div class="relative max-w-md">
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse ($connections as $item)
                        <div class="bg-white border border-slate-150 rounded-2xl p-4 flex items-center justify-between hover:border-slate-350 hover:shadow-2xs transition-all duration-sm">
                            <div class="flex items-center gap-3">
                                <x-ui.avatar :user="$item['user']" size="md" />
                                <div>
                                    <h3 class="text-xs font-bold text-slate-800 flex items-center gap-1">
                                        {{ $item['user']->name }}
                                        <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                                    </h3>
                                    @if ($item['user']->profile && $item['user']->profile->faculty)
                                        <p class="text-[10px] text-slate-400 font-semibold mt-0.5">{{ $item['user']->profile->faculty }}</p>
                                    @endif
                                    <p class="text-[9px] text-slate-300 font-medium mt-1">Kết nối ngày: {{ $item['connected_at']->format('d/m/Y') }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5" x-data="{ openOptions: false }" @click.away="openOptions = false">
                                <a
                                    href="{{ route('messages.index', ['conversation' => \App\Models\Conversation::where('conversation_type', \App\Enums\ConversationType::DIRECT)->whereHas('participants', function($q) use ($item) { $q->where('user_id', $item['user']->id); })->first()?->id]) }}"
                                    class="bg-ue-brand hover:bg-ue-brand-dark text-white text-xxs font-bold px-3 py-1.5 rounded-lg shadow-2xs hover:shadow-sm transition-all flex items-center gap-1"
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
                                        class="text-slate-400 hover:text-slate-600 focus:ring-1 focus:ring-slate-100"
                                    />

                                    <div
                                        x-show="openOptions"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute right-0 mt-1 rounded-xl bg-white border border-slate-150 shadow-lg py-1 z-30 w-40"
                                        style="display: none;"
                                    >
                                        <button
                                            type="button"
                                            wire:click="removeConnection({{ $item['id'] }})"
                                            @click="openOptions = false"
                                            class="w-full text-left px-3 py-2 text-xxs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors"
                                        >
                                            <x-ui.icon name="user-minus" size="xs" class="text-slate-400" />
                                            Hủy kết nối
                                        </button>
                                        <button
                                            type="button"
                                            @click="openOptions = false; alert('Báo cáo hồ sơ thành công. Tin báo cáo đã được chuyển cho Ban kiểm duyệt.')"
                                            class="w-full text-left px-3 py-2 text-xxs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors border-t border-slate-100"
                                        >
                                            <x-ui.icon name="flag" size="xs" class="text-slate-400" />
                                            Báo cáo hồ sơ
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="blockUser({{ $item['user']->id }})"
                                            @click="openOptions = false"
                                            class="w-full text-left px-3 py-2 text-xxs font-semibold text-red-600 hover:bg-red-50 flex items-center gap-1.5 transition-colors border-t border-slate-100"
                                        >
                                            <x-ui.icon name="slash" size="xs" class="text-red-400" />
                                            Chặn thành viên
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 rounded-2xl border border-dashed border-slate-250">
                            <x-ui.icon name="users" size="lg" class="text-slate-300" />
                            <h3 class="text-sm font-bold text-slate-700">
                                {{ !empty($connectionSearch) ? 'Không tìm thấy bạn bè phù hợp' : 'Chưa có kết nối nào' }}
                            </h3>
                            <p class="text-xxs text-slate-400 max-w-sm">
                                {{ !empty($connectionSearch) ? 'Hãy thử thay đổi từ khóa tìm kiếm.' : 'Hãy chuyển sang tab Khám phá để bắt đầu kết nối với những người bạn HCMUE mới nhé.' }}
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- TAB 2: Received greetings --}}
        @if ($activeTab === 'received')
            <div class="space-y-3">
                @forelse ($received as $greeting)
                    <div class="bg-white border border-slate-150 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-slate-350 hover:shadow-2xs transition-all duration-sm">
                        <div class="flex items-start gap-3">
                            <x-ui.avatar :user="$greeting->sender" size="md" />
                            <div class="space-y-1.5">
                                <h3 class="text-xs font-bold text-slate-800 flex items-center gap-1">
                                    {{ $greeting->sender->name }}
                                    <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                                </h3>
                                @if ($greeting->sender->profile && $greeting->sender->profile->faculty)
                                    <p class="text-[10px] text-slate-400 font-semibold leading-none">{{ $greeting->sender->profile->faculty }}</p>
                                @endif
                                <div class="bg-slate-50 border border-slate-100 px-3 py-2 rounded-xl text-xxs font-medium text-slate-600 max-w-lg mt-1 italic leading-normal">
                                    "{{ $greeting->message }}"
                                </div>
                                <p class="text-[9px] text-slate-300 font-semibold">{{ $greeting->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end md:self-center">
                            <button
                                type="button"
                                wire:click="declineGreeting({{ $greeting->id }})"
                                class="bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-slate-700 text-xxs font-bold px-3 py-1.5 rounded-lg border border-slate-200 transition-colors"
                            >
                                Từ chối
                            </button>
                            <button
                                type="button"
                                wire:click="acceptGreeting({{ $greeting->id }})"
                                class="bg-ue-brand hover:bg-ue-brand-dark text-white text-xxs font-bold px-3 py-1.5 rounded-lg shadow-2xs hover:shadow-sm transition-all"
                            >
                                Chấp nhận
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 rounded-2xl border border-dashed border-slate-250">
                        <x-ui.icon name="mail" size="lg" class="text-slate-300" />
                        <h3 class="text-sm font-bold text-slate-700">Không có lời mời kết nối nào</h3>
                        <p class="text-xxs text-slate-400 max-w-sm">Hộp thư trống. Khi người khác gửi lời chào kết nối với bạn, chúng sẽ xuất hiện ở đây.</p>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- TAB 3: Sent greetings --}}
        @if ($activeTab === 'sent')
            <div class="space-y-3">
                @forelse ($sent as $greeting)
                    <div class="bg-white border border-slate-150 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-slate-350 hover:shadow-2xs transition-all duration-sm">
                        <div class="flex items-center gap-3">
                            <x-ui.avatar :user="$greeting->receiver" size="md" />
                            <div>
                                <h3 class="text-xs font-bold text-slate-800 flex items-center gap-1">
                                    {{ $greeting->receiver->name }}
                                    <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                                </h3>
                                @if ($greeting->receiver->profile && $greeting->receiver->profile->faculty)
                                    <p class="text-[10px] text-slate-400 font-semibold mt-0.5">{{ $greeting->receiver->profile->faculty }}</p>
                                @endif
                                <p class="text-[9px] text-slate-350 font-medium mt-1">Nội dung đã gửi: "{{ \Illuminate\Support\Str::limit($greeting->message, 80) }}"</p>
                                <p class="text-[9px] text-slate-300 font-semibold mt-0.5">Đã gửi: {{ $greeting->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="cancelGreeting({{ $greeting->id }})"
                            class="bg-slate-50 hover:bg-red-50 text-slate-500 hover:text-red-700 text-xxs font-bold px-3 py-1.5 rounded-lg border border-slate-200 hover:border-red-200 transition-colors self-end md:self-center"
                        >
                            Hủy yêu cầu
                        </button>
                    </div>
                @empty
                    <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 rounded-2xl border border-dashed border-slate-250">
                        <x-ui.icon name="send" size="lg" class="text-slate-300" />
                        <h3 class="text-sm font-bold text-slate-700">Chưa gửi lời chào nào</h3>
                        <p class="text-xxs text-slate-400 max-w-sm">Tất cả lời mời kết nối bạn đã gửi đang chờ người nhận phản hồi sẽ được liệt kê ở đây.</p>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- TAB 4: Blocked --}}
        @if ($activeTab === 'blocked')
            <div class="space-y-3">
                @forelse ($blocked as $item)
                    <div class="bg-white border border-slate-150 rounded-2xl p-4 flex items-center justify-between hover:border-slate-350 hover:shadow-2xs transition-all duration-sm">
                        <div class="flex items-center gap-3">
                            <x-ui.avatar :user="$item->blocked" size="md" />
                            <div>
                                <h3 class="text-xs font-bold text-slate-800">
                                    {{ $item->blocked->name }}
                                </h3>
                                @if ($item->blocked->profile && $item->blocked->profile->faculty)
                                    <p class="text-[10px] text-slate-400 font-semibold mt-0.5">{{ $item->blocked->profile->faculty }}</p>
                                @endif
                                <p class="text-[9px] text-slate-300 font-medium mt-1">Đã chặn: {{ $item->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="unblockUser({{ $item->blocked_id }})"
                            class="bg-slate-50 hover:bg-slate-100 text-slate-600 text-xxs font-bold px-3 py-1.5 rounded-lg border border-slate-200 transition-colors"
                        >
                            Bỏ chặn
                        </button>
                    </div>
                @empty
                    <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 rounded-2xl border border-dashed border-slate-250">
                        <x-ui.icon name="slash" size="lg" class="text-slate-300" />
                        <h3 class="text-sm font-bold text-slate-700">Danh sách chặn trống</h3>
                        <p class="text-xxs text-slate-400 max-w-sm">Tài khoản bạn đã chặn sẽ xuất hiện tại đây. Họ sẽ không thể gửi tin nhắn hoặc lời mời kết nối với bạn.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>
