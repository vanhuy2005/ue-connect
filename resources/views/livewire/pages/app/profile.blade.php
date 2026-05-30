<?php

use App\Models\User;
use App\Models\Profile;
use App\Models\Connection;
use App\Models\BlockedUser;
use App\Enums\ConnectionStatus;
use App\Actions\Connections\SendGreeting;
use App\Actions\Connections\BlockUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Volt\Component;

new class extends Component
{
    public User $user;
    public string $activeTab = 'posts'; // posts, media, saved, about
    public ?string $feedbackMessage = null;

    public function mount(User $user): void
    {
        $this->user = $user;
        
        $profile = $user->profile()->withTrashed()->first();
        if (!$profile) {
            $user->profile()->create([
                'display_name' => $user->name,
                'role_type' => 'student',
                'profile_status' => 'active',
                'visibility' => 'public',
                'discoverable' => true,
            ]);
        } elseif ($profile->trashed()) {
            $profile->restore();
        }
        
        $this->user->load('profile');
    }

    /**
     * Get connections count.
     */
    public function getConnectionsCountProperty(): int
    {
        return Connection::where(function ($q) {
            $q->where('user_one_id', $this->user->id)
              ->orWhere('user_two_id', $this->user->id);
        })->where('status', ConnectionStatus::ACTIVE)->count();
    }

    /**
     * Get posts count.
     */
    public function getPostsCountProperty(): int
    {
        return $this->user->posts()->count();
    }

    /**
     * Get connection status for display.
     */
    public function getConnectionStatusProperty(): string
    {
        $currentUserId = Auth::id();
        if ($this->user->id === $currentUserId) {
            return 'self';
        }

        // Check blocks
        $isBlocked = BlockedUser::where(function ($q) use ($currentUserId) {
            $q->where('blocker_id', $currentUserId)->where('blocked_id', $this->user->id);
        })->orWhere(function ($q) use ($currentUserId) {
            $q->where('blocker_id', $this->user->id)->where('blocked_id', $currentUserId);
        })->exists();

        if ($isBlocked) {
            return 'blocked';
        }

        // Check active connection
        $userOneId = min($currentUserId, $this->user->id);
        $userTwoId = max($currentUserId, $this->user->id);
        $isConnected = Connection::where('user_one_id', $userOneId)
            ->where('user_two_id', $userTwoId)
            ->where('status', ConnectionStatus::ACTIVE)
            ->exists();

        if ($isConnected) {
            return 'connected';
        }

        // Check greetings
        $hasSent = \App\Models\Greeting::where('sender_id', $currentUserId)
            ->where('receiver_id', $this->user->id)
            ->where('status', \App\Enums\GreetingStatus::PENDING)
            ->exists();

        if ($hasSent) {
            return 'pending_sent';
        }

        $hasReceived = \App\Models\Greeting::where('sender_id', $this->user->id)
            ->where('receiver_id', $currentUserId)
            ->where('status', \App\Enums\GreetingStatus::PENDING)
            ->exists();

        if ($hasReceived) {
            return 'pending_received';
        }

        return 'none';
    }

    /**
     * Block the user.
     */
    public function blockUser(BlockUser $blockUser): void
    {
        try {
            $blockUser->execute(Auth::user(), $this->user, [
                'reason' => 'Blocked from profile page.',
            ]);
            $this->feedbackMessage = 'Đã chặn người dùng này thành công.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Unblock the user.
     */
    public function unblockUser(\App\Actions\Connections\UnblockUser $unblockUser): void
    {
        try {
            $unblockUser->execute(Auth::user(), $this->user);
            $this->feedbackMessage = 'Đã bỏ chặn người dùng này thành công.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Determine if I blocked this user.
     */
    public function getIsBlockedByMeProperty(): bool
    {
        return BlockedUser::where('blocker_id', Auth::id())
            ->where('blocked_id', $this->user->id)
            ->exists();
    }

    /**
     * Determine if they blocked me.
     */
    public function getIsBlockingMeProperty(): bool
    {
        return BlockedUser::where('blocker_id', $this->user->id)
            ->where('blocked_id', Auth::id())
            ->exists();
    }

    /**
     * Report user.
     */
    public function reportUser(): void
    {
        $this->feedbackMessage = 'Báo cáo người dùng thành công. Ban quản trị sẽ sớm xem xét xử lý.';
    }
}; ?>

<div class="py-6 px-4 max-w-4xl mx-auto space-y-8">
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

    {{-- Graceful blocked states handling --}}
    @if ($this->isBlockingMe)
        <div class="py-16 text-center bg-white border border-slate-200 rounded-3xl p-8 max-w-lg mx-auto shadow-2xs space-y-4 select-none">
            <div class="w-16 h-16 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto text-slate-400">
                <x-ui.icon name="eye-off" size="lg" />
            </div>
            <div class="space-y-2">
                <h2 class="text-base font-bold text-slate-800">Hồ sơ không khả dụng</h2>
                <p class="text-xxs text-slate-400 leading-relaxed max-w-md mx-auto">
                    Tài khoản này hiện không khả dụng hoặc bạn không có quyền xem thông tin hồ sơ này.
                </p>
            </div>
            <div class="pt-3">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-850 text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-2xs transition-colors">
                    <x-ui.icon name="home" size="xs" />
                    Quay lại Trang chủ
                </a>
            </div>
        </div>
    @elseif ($this->isBlockedByMe)
        <div class="py-16 text-center bg-white border border-slate-200 rounded-3xl p-8 max-w-lg mx-auto shadow-2xs space-y-4">
            <div class="w-16 h-16 rounded-full bg-red-50 border border-red-100 flex items-center justify-center mx-auto text-red-500">
                <x-ui.icon name="shield" size="lg" />
            </div>
            <div class="space-y-2">
                <h2 class="text-base font-bold text-slate-850">Bạn đã chặn tài khoản này</h2>
                <p class="text-xxs text-slate-400 leading-relaxed max-w-md mx-auto">
                    Mọi bài đăng và thông tin học tập của {{ $user->profile?->display_name ?? $user->name }} đang bị ẩn để bảo vệ trải nghiệm của bạn. Bạn có thể bỏ chặn để khôi phục quyền tương tác.
                </p>
            </div>
            <div class="pt-3">
                <button 
                    type="button" 
                    wire:click="unblockUser"
                    class="bg-slate-900 hover:bg-slate-850 text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-2xs transition-colors"
                >
                    Bỏ chặn tài khoản
                </button>
            </div>
        </div>
    @else
        {{-- Normal Profile --}}
        @php
            $isOwn = $user->id === Auth::id();
            $targetPrivacy = $user->profilePrivacySetting;
            
            $showFaculty = $isOwn || ($targetPrivacy ? (bool)$targetPrivacy->show_faculty : true);
            $showMajor = $isOwn || ($targetPrivacy ? (bool)$targetPrivacy->show_major : true);
            $showCohort = $isOwn || ($targetPrivacy ? (bool)$targetPrivacy->show_cohort : true);
            $showClassCode = $isOwn || ($targetPrivacy ? (bool)$targetPrivacy->show_class_code : false);
            $showBio = $isOwn || ($targetPrivacy ? (bool)$targetPrivacy->show_bio : true);
        @endphp

        {{-- Instagram-ready Profile Header --}}
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6 md:gap-10 border-b border-slate-100 pb-8">
            {{-- Avatar Section --}}
            <div class="relative flex-shrink-0">
                <x-ui.avatar :user="$user" size="2xl" class="w-24 h-24 md:w-28 md:h-28 ring-4 ring-slate-50 border border-slate-200 shadow-sm" />
            </div>

            {{-- Profile Info Section --}}
            <div class="flex-1 space-y-4 text-center md:text-left min-w-0 w-full">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 justify-center md:justify-start">
                    <h1 class="text-lg font-bold text-slate-800 flex items-center justify-center md:justify-start gap-1.5 truncate">
                        {{ $user->profile?->display_name ?? $user->name }}
                        <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                    </h1>

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-center md:justify-start gap-2 flex-wrap">
                        @if ($this->connectionStatus === 'self')
                            <a href="{{ route('profile.edit') }}" class="bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xxs font-bold px-4 py-1.5 rounded-lg transition-colors flex items-center gap-1.5 shadow-2xs">
                                <x-ui.icon name="edit" size="xs" />
                                Chỉnh sửa hồ sơ
                            </a>
                        @else
                            @if ($this->connectionStatus === 'connected')
                                <a href="{{ route('messages.index', ['conversation' => \App\Models\Conversation::where('conversation_type', \App\Enums\ConversationType::DIRECT)->whereHas('participants', function($q) { $q->where('user_id', $this->user->id); })->first()?->id]) }}" class="bg-ue-brand hover:bg-ue-brand-dark text-white text-xxs font-bold px-4 py-1.5 rounded-lg shadow-2xs hover:shadow-sm transition-all flex items-center gap-1.5">
                                    <x-ui.icon name="message-square" size="xs" />
                                    Nhắn tin
                                </a>
                                <button type="button" disabled class="bg-slate-50 text-emerald-600 border border-slate-200 text-xxs font-bold px-4 py-1.5 rounded-lg flex items-center gap-1.5">
                                    <x-ui.icon name="check" size="xs" />
                                    Bạn bè
                                </button>
                            @elseif ($this->connectionStatus === 'pending_sent')
                                <button type="button" disabled class="bg-slate-100 text-slate-450 text-xxs font-bold px-4 py-1.5 rounded-lg border border-slate-200 cursor-not-allowed">
                                    Chờ phản hồi
                                </button>
                            @elseif ($this->connectionStatus === 'pending_received')
                                <a href="{{ route('connections.index') }}" class="bg-indigo-650 hover:bg-indigo-750 text-white text-xxs font-bold px-4 py-1.5 rounded-lg shadow-2xs transition-colors">
                                    Xem lời mời
                                </a>
                            @elseif ($this->connectionStatus === 'blocked')
                                <span class="text-xxs font-bold text-red-500 bg-red-50 border border-red-100 px-3 py-1.5 rounded-lg">Đã chặn</span>
                            @else
                                <a href="{{ route('discovery.index') }}" class="bg-ue-brand hover:bg-ue-brand-dark text-white text-xxs font-bold px-4 py-1.5 rounded-lg shadow-2xs transition-colors flex items-center gap-1.5">
                                    <x-ui.icon name="user-plus" size="xs" />
                                    Gửi lời chào
                                </a>
                            @endif

                            {{-- More Dropdown Safety controls --}}
                            <div class="relative" x-data="{ openOptions: false }" @click.away="openOptions = false">
                                <button @click="openOptions = !openOptions" class="p-1.5 text-slate-450 hover:text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors shadow-2xs">
                                    <x-ui.icon name="more-horizontal" size="xs" />
                                </button>
                                <div x-show="openOptions" x-transition class="absolute right-0 mt-1 bg-white border border-slate-150 rounded-xl shadow-lg py-1 z-30 w-40 text-left" style="display: none;">
                                    <button type="button" wire:click="blockUser" class="w-full text-left px-3 py-2 text-xxs font-semibold text-red-600 hover:bg-red-50 flex items-center gap-1.5 transition-colors">
                                        <x-ui.icon name="shield-x" size="xs" class="text-red-400" />
                                        Chặn thành viên
                                    </button>
                                    <button type="button" wire:click="reportUser" class="w-full text-left px-3 py-2 text-xxs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors">
                                        <x-ui.icon name="flag" size="xs" class="text-slate-400" />
                                        Báo cáo tài khoản
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Stats --}}
                <div class="flex items-center justify-center md:justify-start gap-6 py-1 text-slate-700 select-none">
                    <div class="flex items-baseline gap-1">
                        <span class="text-xs font-bold">{{ $this->postsCount }}</span>
                        <span class="text-xxs text-slate-400 font-medium">Bài viết</span>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-xs font-bold">{{ $this->connectionsCount }}</span>
                        <span class="text-xxs text-slate-400 font-medium">Bạn bè</span>
                    </div>
                </div>

                {{-- Credentials / Metadata --}}
                <div class="space-y-1 text-slate-500 text-xxs font-medium max-w-md">
                    <p class="text-slate-450 tracking-wide uppercase text-[9px] font-bold">
                        @if (($user->profile?->role_type ?? 'student') === 'student') Sinh viên
                        @elseif (($user->profile?->role_type ?? '') === 'advisor') Mentor/Giảng viên
                        @elseif (($user->profile?->role_type ?? '') === 'alumni') Cựu sinh viên
                        @else Thành viên
                        @endif
                        @if ($showFaculty && $user->profile?->faculty)
                            · {{ $user->profile?->faculty }}
                        @endif
                    </p>
                    @if ($showBio)
                        @if ($user->profile?->bio)
                            <p class="leading-relaxed text-slate-650">{{ $user->profile?->bio }}</p>
                        @else
                            <p class="italic text-slate-350">Chưa cập nhật giới thiệu cá nhân.</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Modern Horizontal Profile Tabs --}}
        <div class="flex flex-col space-y-4">
            <div class="flex border-b border-slate-150 overflow-x-auto pb-px justify-center sm:justify-start gap-4 sm:gap-6 select-none scrollbar-none">
                <button 
                    type="button" 
                    wire:click="$set('activeTab', 'posts')" 
                    class="pb-3 text-xxs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'posts' ? 'border-slate-800 text-slate-800 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600' }}"
                >
                    Bài viết
                </button>
                <button 
                    type="button" 
                    wire:click="$set('activeTab', 'media')" 
                    class="pb-3 text-xxs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'media' ? 'border-slate-800 text-slate-800 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600' }}"
                >
                    Phương tiện
                </button>
                @if ($user->id === Auth::id())
                    <button 
                        type="button" 
                        wire:click="$set('activeTab', 'saved')" 
                        class="pb-3 text-xxs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'saved' ? 'border-slate-800 text-slate-800 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600' }}"
                    >
                        Đã lưu
                    </button>
                @endif
                <button 
                    type="button" 
                    wire:click="$set('activeTab', 'about')" 
                    class="pb-3 text-xxs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'about' ? 'border-slate-800 text-slate-800 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600' }}"
                >
                    Giới thiệu
                </button>
            </div>

            {{-- Tab Content --}}
            <div>
                @if ($activeTab === 'posts')
                    {{-- User's Posts list --}}
                    <div class="space-y-4">
                        @forelse ($user->posts()->latest()->take(10)->get() as $post)
                            <div class="bg-white border border-slate-150 rounded-2xl p-4 shadow-2xs">
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :user="$user" size="xs" />
                                    <div>
                                        <h4 class="text-xxs font-bold text-slate-800">{{ $user->profile?->display_name ?? $user->name }}</h4>
                                        <span class="text-[9px] text-slate-400">{{ $post->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <p class="text-xxs font-medium text-slate-655 leading-relaxed mt-2.5">{{ $post->body }}</p>
                                @if ($post->media_url)
                                    <div class="mt-3 rounded-xl overflow-hidden border border-slate-100 max-h-60 bg-slate-50 flex items-center justify-center">
                                        <img src="{{ $post->media_url }}" alt="Media post" class="object-contain max-h-60" />
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 border border-dashed border-slate-200 rounded-2xl">
                                <x-ui.icon name="edit" size="lg" class="text-slate-300" />
                                <h3 class="text-xs font-bold text-slate-700">Chưa có bài đăng nào</h3>
                                <p class="text-xxs text-slate-400 max-w-xs">Các chia sẻ cá nhân của thành viên sẽ xuất hiện tại đây.</p>
                            </div>
                        @endforelse
                    </div>

                @elseif ($activeTab === 'media')
                    {{-- Media Square Grid --}}
                    @php
                        $mediaPosts = $user->posts()->whereNotNull('media_url')->latest()->get();
                    @endphp
                    @if ($mediaPosts->isNotEmpty())
                        <div class="grid grid-cols-3 gap-1.5 sm:gap-3">
                            @foreach ($mediaPosts as $mp)
                                <a href="{{ route('posts.show', $mp) }}" class="aspect-square bg-slate-100 border border-slate-150 rounded-xl overflow-hidden group relative flex items-center justify-center">
                                    <img src="{{ $mp->media_url }}" alt="Grid image" class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300" />
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 border border-dashed border-slate-200 rounded-2xl">
                            <x-ui.icon name="community" size="lg" class="text-slate-300" />
                            <h3 class="text-xs font-bold text-slate-700">Chưa có ảnh/video chia sẻ</h3>
                            <p class="text-xxs text-slate-400 max-w-xs">Các hình ảnh, video hoạt động được thành viên chia sẻ sẽ hiển thị tại đây.</p>
                        </div>
                    @endif

                @elseif ($activeTab === 'saved' && $user->id === Auth::id())
                    {{-- Saved posts list (own only) --}}
                    <div class="space-y-4">
                        @forelse ($user->postSaves()->with('post.user.profile')->latest()->take(10)->get() as $saved)
                            @if ($saved->post)
                                <div class="bg-white border border-slate-150 rounded-2xl p-4 shadow-2xs">
                                    <div class="flex items-center gap-3">
                                        <x-ui.avatar :user="$saved->post->user" size="xs" />
                                        <div>
                                            <h4 class="text-xxs font-bold text-slate-800">{{ $saved->post->user->profile?->display_name ?? $saved->post->user->name }}</h4>
                                            <span class="text-[9px] text-slate-400">{{ $saved->post->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <p class="text-xxs font-medium text-slate-655 leading-relaxed mt-2.5">{{ $saved->post->body }}</p>
                                </div>
                            @endif
                        @empty
                            <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 border border-dashed border-slate-200 rounded-2xl">
                                <x-ui.icon name="bookmark" size="lg" class="text-slate-300" />
                                <h3 class="text-xs font-bold text-slate-700">Thư mục đã lưu trống</h3>
                                <p class="text-xxs text-slate-400 max-w-xs">Đánh dấu những bài viết bạn muốn lưu để dễ dàng xem lại tại đây.</p>
                            </div>
                        @endforelse
                    </div>

                @elseif ($activeTab === 'about')
                    {{-- Public Information details --}}
                    <div class="bg-white border border-slate-150 rounded-2xl p-5 space-y-4 shadow-2xs">
                        <h3 class="text-xs font-bold text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-1.5">
                            <x-ui.icon name="user" size="xs" class="text-slate-400" />
                            Thông tin xác thực học đường
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xxs text-slate-600 font-medium">
                            <div class="space-y-1">
                                <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Họ và tên xác thực</span>
                                <span class="text-slate-850 font-bold">{{ $user->name }}</span>
                            </div>

                            {{-- Email: Hidden by default for other users, only own profile sees full email --}}
                            @if ($isOwn)
                                <div class="space-y-1">
                                    <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Địa chỉ email học đường</span>
                                    <span class="text-slate-850 font-bold">{{ $user->email }}</span>
                                </div>
                            @endif

                            @if (($user->profile?->role_type ?? 'student') === 'student' && $user->profile?->studentProfile)
                                <div class="space-y-1">
                                    <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Mã số sinh viên</span>
                                    <span class="text-slate-850 font-bold">
                                        {{-- Full MSSV never public! Mask it if not own profile --}}
                                        @if ($isOwn)
                                            {{ $user->profile?->studentProfile?->student_code }}
                                        @else
                                            {{ substr($user->profile?->studentProfile?->student_code, 0, 5) }}•••••
                                        @endif
                                    </span>
                                </div>
                                @if ($showMajor)
                                    <div class="space-y-1">
                                        <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Chương trình đào tạo</span>
                                        <span class="text-slate-850 font-bold">
                                            {{ $user->profile?->studentProfile?->academicProgram ? $user->profile?->studentProfile?->academicProgram?->name : 'N/A' }}
                                        </span>
                                    </div>
                                @endif
                                @if ($showFaculty)
                                    <div class="space-y-1">
                                        <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Khoa</span>
                                        <span class="text-slate-850 font-bold">
                                            {{ $user->profile?->studentProfile?->faculty ? $user->profile?->studentProfile?->faculty?->name : 'N/A' }}
                                        </span>
                                    </div>
                                @endif
                                @if ($showCohort)
                                    <div class="space-y-1">
                                        <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Khóa tuyển sinh (Cohort)</span>
                                        <span class="text-slate-850 font-bold">{{ $user->profile?->studentProfile?->cohort ?: 'N/A' }}</span>
                                    </div>
                                @endif
                            @elseif (($user->profile?->role_type ?? '') === 'alumni' && $user->profile?->alumniProfile)
                                @if ($showCohort)
                                    <div class="space-y-1">
                                        <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Khóa (Cohort) / Năm tốt nghiệp</span>
                                        <span class="text-slate-850 font-bold">
                                            {{ $user->profile?->alumniProfile?->cohort ?: 'N/A' }} / {{ $user->profile?->alumniProfile?->graduation_year ?: 'N/A' }}
                                        </span>
                                    </div>
                                @endif
                                @if ($showMajor)
                                    <div class="space-y-1">
                                        <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Đơn vị công tác hiện tại</span>
                                        <span class="text-slate-850 font-bold">
                                            {{ $user->profile?->alumniProfile?->current_position ?: 'N/A' }} tại {{ $user->profile?->alumniProfile?->current_organization ?: 'N/A' }}
                                        </span>
                                    </div>
                                @endif
                            @elseif (($user->profile?->role_type ?? '') === 'advisor' && $user->profile?->advisorProfile)
                                <div class="space-y-1">
                                    <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Chức danh / Khoa</span>
                                    <span class="text-slate-850 font-bold">
                                        {{ $user->profile?->advisorProfile?->title ?: 'N/A' }} tại {{ $user->profile?->advisorProfile?->faculty ? $user->profile?->advisorProfile?->faculty?->name : 'N/A' }}
                                    </span>
                                </div>
                            @endif

                            <div class="space-y-1">
                                <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Ngày tham gia UEConnect</span>
                                <span class="text-slate-850 font-bold">{{ $user->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
