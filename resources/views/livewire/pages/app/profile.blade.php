<?php

use App\Models\User;
use App\Models\Profile;
use App\Models\Connection;
use App\Models\BlockedUser;
use App\Models\Media;
use App\Models\UserFollow;
use App\Enums\ConnectionStatus;
use App\Actions\Connections\SendGreeting;
use App\Actions\Connections\BlockUser;
use App\Actions\Follows\FollowUser;
use App\Actions\Follows\UnfollowUser;
use App\Actions\Media\StoreTemporaryMediaAction;
use App\Actions\Media\AttachMediaToModelAction;
use App\Actions\Media\DeleteMediaAction;
use App\Actions\Media\GenerateMediaUrlAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public User $user;
    public string $activeTab = 'posts'; // posts, replies, media, communities
    public ?string $feedbackMessage = null;
    public bool $isFollowing = false;
    public int $followersCount = 0;
    public int $followingCount = 0;

    // Files inputs
    public $avatarFile;
    public $coverFile;

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
        
        $this->user->load(['profile.media.variants', 'profile.studentProfile.faculty', 'profile.studentProfile.academicProgram', 'profile.alumniProfile', 'profile.advisorProfile']);
        $this->refreshFollowState();
    }

    /**
     * Refresh follow aggregate state for the displayed profile.
     */
    public function refreshFollowState(): void
    {
        $viewerId = Auth::id();

        $this->isFollowing = $viewerId !== null
            && $viewerId !== $this->user->id
            && UserFollow::where('follower_id', $viewerId)
                ->where('following_id', $this->user->id)
                ->exists();

        $this->followersCount = UserFollow::where('following_id', $this->user->id)->count();
        $this->followingCount = UserFollow::where('follower_id', $this->user->id)->count();
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
     * Handle Avatar photo uploads.
     */
    public function updatedAvatarFile(): void
    {
        $this->validate([
            'avatarFile' => 'image|max:5120', // 5MB limit
        ]);

        try {
            $storeAction = app(StoreTemporaryMediaAction::class);
            $attachAction = app(AttachMediaToModelAction::class);
            $deleteAction = app(DeleteMediaAction::class);

            // Delete old polymorphic avatar if exists
            if ($this->user->id !== Auth::id()) {
                $this->feedbackMessage = 'Bạn chỉ có thể cập nhật hồ sơ của chính mình.';

                return;
            }

            $oldAvatar = $this->user->profile->avatar()->first();
            if ($oldAvatar) {
                $deleteAction->execute($oldAvatar);
            }

            // Store new temporary media (public visibility)
            $media = $storeAction->execute(Auth::user(), $this->avatarFile, 'avatar', ['visibility' => 'public']);

            // Attach to the Profile
            $attachAction->execute(Auth::user(), $this->user->profile, [$media->id], 'avatar');

            $this->user->load('profile.media.variants');
            $this->feedbackMessage = 'Cập nhật ảnh đại diện thành công.';
        } catch (\Exception $e) {
            $this->feedbackMessage = 'Lỗi tải ảnh lên: ' . $e->getMessage();
        }
    }

    /**
     * Handle Cover photo uploads.
     */
    public function updatedCoverFile(): void
    {
        $this->validate([
            'coverFile' => 'image|max:8192', // 8MB limit
        ]);

        try {
            $storeAction = app(StoreTemporaryMediaAction::class);
            $attachAction = app(AttachMediaToModelAction::class);
            $deleteAction = app(DeleteMediaAction::class);

            // Delete old polymorphic cover if exists
            if ($this->user->id !== Auth::id()) {
                $this->feedbackMessage = 'Bạn chỉ có thể cập nhật hồ sơ của chính mình.';

                return;
            }

            $oldCover = $this->user->profile->cover()->first();
            if ($oldCover) {
                $deleteAction->execute($oldCover);
            }

            // Store new temporary media (public visibility)
            $media = $storeAction->execute(Auth::user(), $this->coverFile, 'profile_cover', ['visibility' => 'public']);

            // Attach to the Profile
            $attachAction->execute(Auth::user(), $this->user->profile, [$media->id], 'profile_cover');

            $this->user->load('profile.media.variants');
            $this->feedbackMessage = 'Cập nhật ảnh bìa thành công.';
        } catch (\Exception $e) {
            $this->feedbackMessage = 'Lỗi tải ảnh lên: ' . $e->getMessage();
        }
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
     * Follow the displayed user.
     */
    public function followUser(FollowUser $followUser): void
    {
        try {
            $followUser->execute(Auth::user(), $this->user);

            $this->isFollowing = true;
            $this->followersCount++;
            $this->feedbackMessage = 'Đã theo dõi người dùng này.';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->refreshFollowState();
            $this->feedbackMessage = collect($e->errors())->flatten()->first() ?: 'Không thể theo dõi người dùng này.';
        } catch (\Exception $e) {
            $this->refreshFollowState();
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Unfollow the displayed user.
     */
    public function unfollowUser(UnfollowUser $unfollowUser): void
    {
        try {
            $unfollowUser->execute(Auth::user(), $this->user);

            $this->isFollowing = false;
            $this->followersCount = max(0, $this->followersCount - 1);
            $this->feedbackMessage = 'Đã bỏ theo dõi người dùng này.';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->refreshFollowState();
            $this->feedbackMessage = collect($e->errors())->flatten()->first() ?: 'Không thể bỏ theo dõi người dùng này.';
        } catch (\Exception $e) {
            $this->refreshFollowState();
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

    /**
     * Get safe avatar URL.
     */
    public function getAvatarUrlProperty(): string
    {
        $media = $this->user->profile->relationLoaded('media')
            ? $this->user->profile->media->firstWhere('collection', 'avatar')
            : $this->user->profile->avatar()->with('variants')->first();

        if ($media) {
            return app(GenerateMediaUrlAction::class)->execute($media, 'display', Auth::user()) ?: asset('images/default-avatar.svg');
        }

        return asset('images/default-avatar.svg');
    }

    /**
     * Get safe cover URL.
     */
    public function getCoverUrlProperty(): ?string
    {
        $media = $this->user->profile->relationLoaded('media')
            ? $this->user->profile->media->firstWhere('collection', 'profile_cover')
            : $this->user->profile->cover()->with('variants')->first();

        if ($media) {
            return app(GenerateMediaUrlAction::class)->execute($media, 'desktop', Auth::user());
        }

        return null;
    }

    public function with(): array
    {
        $profilePosts = collect();
        $profileComments = collect();
        $profileMedia = collect();
        $savedPosts = collect();

        if ($this->activeTab === 'posts') {
            $profilePosts = $this->user->posts()
                ->with('media.variants')
                ->latest()
                ->take(10)
                ->get();
        } elseif ($this->activeTab === 'replies') {
            $profileComments = $this->user->comments()
                ->with('post.user')
                ->latest()
                ->take(10)
                ->get();
        } elseif ($this->activeTab === 'media') {
            $profileMedia = Media::query()
                ->with('variants')
                ->where('user_id', $this->user->id)
                ->where('collection', 'post_image')
                ->where('status', 'ready')
                ->latest()
                ->take(30)
                ->get();
        } elseif ($this->activeTab === 'saved' && $this->user->id === Auth::id()) {
            $savedPosts = $this->user->postSaves()
                ->with(['post.user.profile', 'post.media.variants'])
                ->latest()
                ->take(10)
                ->get();
        }

        return [
            'profilePosts' => $profilePosts,
            'profileComments' => $profileComments,
            'profileMedia' => $profileMedia,
            'savedPosts' => $savedPosts,
        ];
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
                    wire:loading.attr="disabled"
                    wire:target="unblockUser"
                    class="bg-slate-900 hover:bg-slate-850 text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-2xs transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="unblockUser">Bỏ chặn tài khoản</span>
                    <span wire:loading wire:target="unblockUser">Đang xử lý...</span>
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

        {{-- Interactive Profile Header with Cover Image --}}
        <div class="relative bg-white border border-slate-150 rounded-3xl overflow-hidden shadow-2xs">
            {{-- Cover Photo Section --}}
            <div class="relative h-44 sm:h-64 w-full bg-gradient-to-tr from-slate-200 to-slate-100 overflow-hidden">
                @if ($this->coverUrl)
                    <img src="{{ $this->coverUrl }}" alt="Cover picture" class="w-full h-full object-cover" />
                @else
                    <div class="w-full h-full bg-slate-900 relative overflow-hidden flex items-center justify-center select-none">
                        {{-- Futuristic grid overlay --}}
                        <div class="absolute inset-0 opacity-15 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px]"></div>
                        
                        {{-- Contained ambient color layer. Keep it inside the cover to avoid mobile overflow. --}}
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_24%_20%,rgba(59,130,246,0.28)_0%,transparent_34%),radial-gradient(circle_at_78%_88%,rgba(99,102,241,0.2)_0%,transparent_38%)]"></div>
                        
                        {{-- Sophisticated tech design overlay --}}
                        <svg class="absolute w-full h-full text-blue-500/10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 300" fill="none">
                            <circle cx="400" cy="150" r="120" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 8" />
                            <circle cx="400" cy="150" r="180" stroke="currentColor" stroke-width="0.8" />
                            <line x1="0" y1="150" x2="800" y2="150" stroke="currentColor" stroke-width="0.8" stroke-dasharray="10 10" />
                            <line x1="400" y1="0" x2="400" y2="300" stroke="currentColor" stroke-width="0.8" stroke-dasharray="10 10" />
                            <path d="M 250 80 L 300 150 L 350 150" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            <path d="M 550 220 L 500 150 L 450 150" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>

                        {{-- Branding Header --}}
                        <div class="relative z-10 flex flex-col items-center gap-1.5 text-center">
                            <span class="text-white/35 font-extrabold tracking-[0.25em] text-[10px] sm:text-xs uppercase">UEConnect</span>
                            <span class="text-white/15 font-semibold text-[8px] sm:text-[9px] tracking-wide max-w-xs sm:max-w-md px-4 leading-normal">Hệ thống mạng xã hội học thuật tích hợp & kết nối sinh viên</span>
                        </div>
                    </div>
                @endif

                {{-- Change Cover Button (Own Profile) --}}
                @if ($isOwn)
                    <label class="absolute bottom-3 right-3 bg-slate-900/60 hover:bg-slate-900/80 text-white p-2 rounded-xl cursor-pointer backdrop-blur-xs transition-colors flex items-center gap-1.5 text-[10px] font-bold">
                        <x-ui.icon name="camera" size="xs" />
                        Thay đổi ảnh bìa
                        <input type="file" wire:model="coverFile" class="hidden" accept="image/*" />
                    </label>
                @endif
            </div>

            {{-- Profile Metadata Area --}}
            <div class="relative px-4 sm:px-6 pb-6 pt-0 md:pt-6 text-center md:text-left">
                {{-- Round Avatar Photo --}}
                <div class="relative -mt-14 mx-auto md:absolute md:-top-16 md:left-6 md:mt-0 md:mx-0 w-28 h-28 sm:w-32 sm:h-32 rounded-full overflow-hidden border-4 border-white bg-slate-50 shadow-md group">
                    <x-ui.avatar :user="$user" size="2xl" class="w-full h-full border-none rounded-none shadow-none text-2xl font-bold bg-slate-100 flex items-center justify-center" />

                    @if ($isOwn)
                        <label class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 cursor-pointer flex flex-col items-center justify-center text-white text-[9px] font-bold transition-opacity">
                            <x-ui.icon name="camera" size="sm" />
                            Đổi ảnh
                            <input type="file" wire:model="avatarFile" class="hidden" accept="image/*" />
                        </label>
                    @endif
                </div>

                @if (! $isOwn && ! $this->isBlockingMe && ! $this->isBlockedByMe)
                    <div class="mt-2 flex justify-center md:absolute md:left-6 md:top-20 md:w-32 md:mt-0">
                        @if ($isFollowing)
                            <button
                                type="button"
                                wire:click="unfollowUser"
                                wire:loading.attr="disabled"
                                wire:target="unfollowUser"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-slate-250 bg-white px-3 py-1.5 text-[10px] font-bold text-slate-650 shadow-2xs transition-colors hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <span wire:loading.remove wire:target="unfollowUser">Đang theo dõi</span>
                                <span wire:loading wire:target="unfollowUser">Đang xử lý...</span>
                            </button>
                        @else
                            <button
                                type="button"
                                wire:click="followUser"
                                wire:loading.attr="disabled"
                                wire:target="followUser"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-ue-brand px-3 py-1.5 text-[10px] font-bold text-white shadow-2xs transition-colors hover:bg-ue-brand-dark disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <span wire:loading.remove wire:target="followUser">Theo dõi</span>
                                <span wire:loading wire:target="followUser">Đang xử lý...</span>
                            </button>
                        @endif
                    </div>
                @endif

                {{-- Profile Info Section --}}
                <div class="space-y-3.5 w-full mt-4 md:mt-0 md:pl-40 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 justify-center md:justify-start">
                        <h1 class="text-base sm:text-lg font-bold text-slate-800 flex items-center justify-center md:justify-start gap-1.5 min-w-0">
                            <span class="truncate">{{ $user->profile?->display_name ?? $user->name }}</span>
                            @if ($user->isActive())
                                <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                            @endif
                        </h1>

                        {{-- Role Badge --}}
                        <span class="inline-flex self-center px-2 py-0.5 rounded-full text-[9px] font-bold tracking-wide uppercase bg-slate-100 text-slate-500">
                            @if (($user->profile?->role_type ?? 'student') === 'student') Sinh viên
                            @elseif (in_array(($user->profile?->role_type ?? ''), ['teacher', 'advisor'], true)) Giảng viên
                            @elseif (($user->profile?->role_type ?? '') === 'alumni') Cựu sinh viên
                            @else Thành viên
                            @endif
                        </span>

                        {{-- Action Buttons --}}
                        <div class="flex w-full sm:w-auto items-center justify-center md:justify-start gap-2 flex-wrap sm:ml-auto">
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

                                {{-- More Safety controls --}}
                                <div class="relative" x-data="{ openOptions: false }" @click.away="openOptions = false">
                                    <button @click="openOptions = !openOptions" class="p-1.5 text-slate-450 hover:text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors shadow-2xs">
                                        <x-ui.icon name="more-horizontal" size="xs" />
                                    </button>
                                    <div x-show="openOptions" x-transition class="absolute right-0 mt-1 bg-white border border-slate-150 rounded-xl shadow-lg py-1 z-30 w-40 text-left" style="display: none;">
                                        <button type="button" wire:click="blockUser" wire:loading.attr="disabled" wire:target="blockUser" class="w-full text-left px-3 py-2 text-xxs font-semibold text-red-600 hover:bg-red-50 flex items-center gap-1.5 transition-colors disabled:opacity-60">
                                            <x-ui.icon name="shield-x" size="xs" class="text-red-400" />
                                            <span wire:loading.remove wire:target="blockUser">Chặn thành viên</span>
                                            <span wire:loading wire:target="blockUser">Đang chặn...</span>
                                        </button>
                                        <button type="button" wire:click="reportUser" wire:loading.attr="disabled" wire:target="reportUser" class="w-full text-left px-3 py-2 text-xxs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors disabled:opacity-60">
                                            <x-ui.icon name="flag" size="xs" class="text-slate-400" />
                                            <span wire:loading.remove wire:target="reportUser">Báo cáo tài khoản</span>
                                            <span wire:loading wire:target="reportUser">Đang gửi...</span>
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
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold">{{ $followersCount }}</span>
                            <span class="text-xxs text-slate-400 font-medium">Người theo dõi</span>
                        </div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-bold">{{ $followingCount }}</span>
                            <span class="text-xxs text-slate-400 font-medium">Đang theo dõi</span>
                        </div>
                    </div>

                    {{-- Credentials & Bio --}}
                    <div class="space-y-1 text-slate-500 text-xxs font-medium max-w-lg mx-auto md:mx-0">
                        <p class="text-slate-450 tracking-wide uppercase text-[9px] font-bold">
                            @if ($showFaculty && $user->profile?->faculty)
                                Khoa: {{ $user->profile?->faculty }}
                            @endif
                        </p>
                        @if ($showBio)
                            @if ($user->profile?->bio)
                                <p class="leading-relaxed text-slate-655">{{ $user->profile?->bio }}</p>
                            @else
                                <p class="italic text-slate-350">Chưa cập nhật giới thiệu cá nhân.</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Modern Profile Tabs --}}
        <div class="flex flex-col space-y-4">
            <div class="flex border-b border-slate-150 overflow-x-auto pb-px justify-start px-4 sm:px-0 gap-4 sm:gap-6 select-none scrollbar-none">
                <button 
                    type="button" 
                    wire:click="$set('activeTab', 'posts')" 
                    class="pb-3 text-xxs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'posts' ? 'border-slate-800 text-slate-800 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600' }}"
                >
                    Bài viết
                </button>
                <button 
                    type="button" 
                    wire:click="$set('activeTab', 'replies')" 
                    class="pb-3 text-xxs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'replies' ? 'border-slate-800 text-slate-800 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600' }}"
                >
                    Phản hồi
                </button>
                <button 
                    type="button" 
                    wire:click="$set('activeTab', 'media')" 
                    class="pb-3 text-xxs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'media' ? 'border-slate-800 text-slate-800 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600' }}"
                >
                    Phương tiện
                </button>
                <button 
                    type="button" 
                    wire:click="$set('activeTab', 'communities')" 
                    class="pb-3 text-xxs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'communities' ? 'border-slate-800 text-slate-800 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600' }}"
                >
                    Cộng đồng
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

            {{-- Tab Content rendering --}}
            <div>
                @if ($activeTab === 'posts')
                    <div class="space-y-4">
                        @forelse ($profilePosts as $post)
                            <div class="bg-white border border-slate-150 rounded-2xl p-4 shadow-2xs">
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :user="$user" size="sm" />
                                    <div>
                                        <h4 class="text-xxs font-bold text-slate-800">{{ $user->profile?->display_name ?? $user->name }}</h4>
                                        <span class="text-[9px] text-slate-400">{{ $post->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <p class="text-xxs font-medium text-slate-655 leading-relaxed mt-2.5">{{ $post->body }}</p>

                                {{-- Render post image grid polymorphically if active --}}
                                @php
                                    $postMedia = $post->relationLoaded('media')
                                        ? $post->media->where('status', 'ready')->values()
                                        : collect();
                                @endphp
                                @if ($postMedia->isNotEmpty())
                                    <div class="mt-3 grid grid-cols-2 gap-2 rounded-xl overflow-hidden border border-slate-100">
                                        @foreach ($postMedia as $mediaItem)
                                            <a href="{{ app(GenerateMediaUrlAction::class)->execute($mediaItem, 'detail', Auth::user()) ?? app(GenerateMediaUrlAction::class)->execute($mediaItem, 'original', Auth::user()) }}" target="_blank" rel="noopener noreferrer" class="aspect-square bg-slate-50 flex items-center justify-center overflow-hidden">
                                                <img src="{{ app(GenerateMediaUrlAction::class)->execute($mediaItem, 'feed', Auth::user()) }}" alt="Post image" class="object-cover w-full h-full cursor-zoom-in" />
                                            </a>
                                        @endforeach
                                    </div>
                                @elseif ($post->media_url)
                                    <div class="mt-3 rounded-xl overflow-hidden border border-slate-100 max-h-60 bg-slate-50 flex items-center justify-center">
                                        <a href="{{ $post->media_url }}" target="_blank" rel="noopener noreferrer" class="block w-full">
                                            <img src="{{ $post->media_url }}" alt="Media post" class="object-contain max-h-60 mx-auto cursor-zoom-in" />
                                        </a>
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

                @elseif ($activeTab === 'replies')
                    <div class="space-y-4">
                        @forelse ($profileComments as $comment)
                            <div class="bg-white border border-slate-150 rounded-2xl p-4 shadow-2xs space-y-2">
                                <div class="flex items-center gap-2 text-slate-400 text-[10px] font-medium">
                                    <x-ui.icon name="corner-down-right" size="xs" />
                                    <span>Đã phản hồi bài viết của {{ $comment->post?->user?->name }}</span>
                                </div>
                                <p class="text-xxs font-medium text-slate-655 leading-relaxed">{{ $comment->body }}</p>
                                <span class="text-[9px] text-slate-400 block">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                        @empty
                            <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 border border-dashed border-slate-200 rounded-2xl">
                                <x-ui.icon name="message-square" size="lg" class="text-slate-300" />
                                <h3 class="text-xs font-bold text-slate-700">Chưa có phản hồi nào</h3>
                                <p class="text-xxs text-slate-400 max-w-xs">Các câu trả lời, thảo luận của thành viên sẽ xuất hiện tại đây.</p>
                            </div>
                        @endforelse
                    </div>

                @elseif ($activeTab === 'media')
                    @if ($profileMedia->isNotEmpty())
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($profileMedia as $mediaItem)
                                <div class="aspect-square bg-slate-100 border border-slate-150 rounded-xl overflow-hidden group relative flex items-center justify-center">
                                    <img src="{{ app(GenerateMediaUrlAction::class)->execute($mediaItem, 'thumb', Auth::user()) }}" alt="Grid image" class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300" />
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 border border-dashed border-slate-200 rounded-2xl">
                            <x-ui.icon name="image" size="lg" class="text-slate-300" />
                            <h3 class="text-xs font-bold text-slate-700">Chưa có ảnh chia sẻ</h3>
                            <p class="text-xxs text-slate-400 max-w-xs">Các hình ảnh được thành viên chia sẻ sẽ hiển thị tại đây.</p>
                        </div>
                    @endif

                @elseif ($activeTab === 'communities')
                    <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 border border-dashed border-slate-200 rounded-2xl">
                        <x-ui.icon name="users" size="lg" class="text-slate-300" />
                        <h3 class="text-xs font-bold text-slate-700">Chưa tham gia cộng đồng nào</h3>
                        <p class="text-xxs text-slate-400 max-w-xs">Cộng đồng và câu lạc bộ học thuật sẽ hiển thị tại đây khi tham gia.</p>
                    </div>

                @elseif ($activeTab === 'saved' && $user->id === Auth::id())
                    <div class="space-y-4">
                        @forelse ($savedPosts as $saved)
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
                    <div class="bg-white border border-slate-150 rounded-2xl p-5 space-y-4 shadow-2xs">
                        <h3 class="text-xs font-bold text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-1.5">
                            <x-ui.icon name="user" size="xs" class="text-slate-400" />
                            Thông tin học đường xác thực
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xxs text-slate-600 font-medium">
                            <div class="space-y-1">
                                <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Họ và tên xác thực</span>
                                <span class="text-slate-850 font-bold">{{ $user->name }}</span>
                            </div>

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
                                        @if ($isOwn)
                                            {{ $user->profile?->studentProfile?->student_code }}
                                        @else
                                            {{ substr($user->profile?->studentProfile?->student_code, 0, 5) }}•••••
                                        @endif
                                    </span>
                                </div>
                                @if ($showMajor && $user->profile?->studentProfile?->academicProgram)
                                    <div class="space-y-1">
                                        <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Ngành học</span>
                                        <span class="text-slate-850 font-bold">{{ $user->profile?->studentProfile?->academicProgram->name }}</span>
                                    </div>
                                @endif
                                @if ($showFaculty && $user->profile?->studentProfile?->faculty)
                                    <div class="space-y-1">
                                        <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Khoa</span>
                                        <span class="text-slate-850 font-bold">{{ $user->profile?->studentProfile?->faculty->name }}</span>
                                    </div>
                                @endif
                                @if ($showCohort && $user->profile?->studentProfile?->cohort)
                                    <div class="space-y-1">
                                        <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Khóa tuyển sinh</span>
                                        <span class="text-slate-850 font-bold">{{ $user->profile?->studentProfile?->cohort }}</span>
                                    </div>
                                @endif
                            @elseif (($user->profile?->role_type ?? '') === 'alumni' && $user->profile?->alumniProfile)
                                @if ($showCohort && $user->profile?->alumniProfile?->cohort)
                                    <div class="space-y-1">
                                        <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Khóa tuyển sinh / Năm tốt nghiệp</span>
                                        <span class="text-slate-850 font-bold">
                                            {{ $user->profile?->alumniProfile?->cohort }} / {{ $user->profile?->alumniProfile?->graduation_year }}
                                        </span>
                                    </div>
                                @endif
                            @elseif (in_array(($user->profile?->role_type ?? ''), ['teacher', 'advisor'], true) && $user->profile?->advisorProfile)
                                <div class="space-y-1">
                                    <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Chức vụ / Học vị</span>
                                    <span class="text-slate-850 font-bold">{{ $user->profile?->advisorProfile?->title ?: 'Giảng viên' }}</span>
                                </div>
                            @endif

                            <div class="space-y-1">
                                <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Ngày tham gia</span>
                                <span class="text-slate-850 font-bold">{{ $user->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
