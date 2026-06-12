<?php

use App\Models\User;
use App\Models\BlockedUser;
use App\Actions\Connections\UnblockUser;
use App\Actions\Settings\UpdateProfilePrivacySettingsAction;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public ?string $subSection = null;

    // Feedback Toast
    public ?string $feedbackMessage = null;
    public ?string $errorMessage = null;

    // Privacy Form State
    public string $profile_visibility = 'public_to_verified';
    public string $discovery_visibility = 'enabled';
    public bool $is_profile_private = false;
    public bool $is_discoverable = true;
    public bool $show_faculty = true;
    public bool $show_major = true;
    public bool $show_cohort = true;
    public bool $show_class_code = false;
    public bool $show_bio = true;
    public bool $show_interests = true;
    public bool $show_connection_goals = true;
    public bool $show_communities = false;
    public bool $show_career_info = false;
    public bool $show_mentor_topics = true;

    // Mentions & Tags (Future-ready, persisted)
    public string $mentions_preference = 'everyone';
    public string $tags_preference = 'everyone';

    // Online Status (Persisted)
    public string $online_status_visibility = 'connections';

    // Block/Unblock confirmation state
    public ?int $unblockingUserId = null;
    public ?string $unblockingUserName = null;

    public function mount(?string $subSection = null): void
    {
        $this->subSection = $subSection;
        $user = Auth::user();
        if (!$user) return;

        $privacy = $user->profilePrivacySetting;
        if ($privacy) {
            $this->profile_visibility = $privacy->profile_visibility;
            $this->discovery_visibility = $privacy->discovery_visibility;
            $this->is_profile_private = ($this->profile_visibility === 'connections_only' || $this->profile_visibility === 'private');
            $this->is_discoverable = ($this->discovery_visibility === 'enabled');
            $this->show_faculty = (bool) ($privacy->show_faculty ?? true);
            $this->show_major = (bool) ($privacy->show_major ?? true);
            $this->show_cohort = (bool) ($privacy->show_cohort ?? true);
            $this->show_class_code = (bool) ($privacy->show_class_code ?? false);
            $this->show_bio = (bool) ($privacy->show_bio ?? true);
            $this->show_interests = (bool) ($privacy->show_interests ?? true);
            $this->show_connection_goals = (bool) ($privacy->show_connection_goals ?? true);
            $this->show_communities = (bool) ($privacy->show_communities ?? false);
            $this->show_career_info = (bool) ($privacy->show_career_info ?? false);
            $this->show_mentor_topics = (bool) ($privacy->show_mentor_topics ?? true);
            $this->mentions_preference = $privacy->mentions_preference ?? 'everyone';
            $this->tags_preference = $privacy->tags_preference ?? 'everyone';
            $this->online_status_visibility = $privacy->online_status_visibility ?? 'connections';
        }
    }

    public function updatedIsDiscoverable($value): void
    {
        $this->discovery_visibility = $value ? 'enabled' : 'disabled';
        $this->savePrivacy(app(UpdateProfilePrivacySettingsAction::class));
    }

    public function updatedIsProfilePrivate($value): void
    {
        $this->profile_visibility = $value ? 'connections_only' : 'public_to_verified';
    }

    public function savePrivacy(UpdateProfilePrivacySettingsAction $action): void
    {
        // Normalize boolean/numeric string values
        if ($this->profile_visibility === true || $this->profile_visibility === '1' || $this->profile_visibility === 1) {
            $this->profile_visibility = 'connections_only';
        } elseif ($this->profile_visibility === false || $this->profile_visibility === '0' || $this->profile_visibility === 0) {
            $this->profile_visibility = 'public_to_verified';
        }

        if ($this->discovery_visibility === true || $this->discovery_visibility === '1' || $this->discovery_visibility === 1) {
            $this->discovery_visibility = 'enabled';
        } elseif ($this->discovery_visibility === false || $this->discovery_visibility === '0' || $this->discovery_visibility === 0) {
            $this->discovery_visibility = 'disabled';
        }

        try {
            $user = Auth::user();
            $action->execute($user, [
                'profile_visibility' => $this->profile_visibility,
                'discovery_visibility' => $this->discovery_visibility,
                'show_faculty' => $this->show_faculty,
                'show_major' => $this->show_major,
                'show_cohort' => $this->show_cohort,
                'show_class_code' => $this->show_class_code,
                'show_bio' => $this->show_bio,
                'show_interests' => $this->show_interests,
                'show_connection_goals' => $this->show_connection_goals,
                'show_communities' => $this->show_communities,
                'show_career_info' => $this->show_career_info,
                'show_mentor_topics' => $this->show_mentor_topics,
                'mentions_preference' => $this->mentions_preference,
                'tags_preference' => $this->tags_preference,
                'online_status_visibility' => $this->online_status_visibility,
            ]);

            $this->feedbackMessage = 'Đã cập nhật thiết lập quyền riêng tư.';
        } catch (\Exception $e) {
            $this->errorMessage = 'Không thể lưu thay đổi. Vui lòng thử lại.';
        }
    }

    public function confirmUnblock(int $userId, string $name): void
    {
        $this->unblockingUserId = $userId;
        $this->unblockingUserName = $name;
    }

    public function executeUnblock(UnblockUser $action): void
    {
        if (!$this->unblockingUserId) return;

        try {
            $user = Auth::user();
            $target = User::findOrFail($this->unblockingUserId);
            $action->execute($user, $target);

            $this->feedbackMessage = 'Đã bỏ chặn thành công.';
            $this->unblockingUserId = null;
            $this->unblockingUserName = null;
        } catch (\Exception $e) {
            $this->errorMessage = 'Thao tác thất bại. Vui lòng thử lại.';
        }
    }

    public function getBlockedUsers(): \Illuminate\Support\Collection
    {
        return BlockedUser::where('blocker_id', Auth::id())
            ->with('blocked.profile')
            ->get();
    }
}; ?>

<div>
    {{-- Toast feedback --}}
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
            <x-ui.icon name="shield-check" size="sm" class="text-emerald-500 flex-shrink-0" />
            <span class="text-xxs font-semibold flex-1 leading-normal">{{ $feedbackMessage }}</span>
            <button @click="show = false" class="text-slate-400 hover:text-white transition-colors">
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>
    @endif

    @if ($errorMessage)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => { show = false; $wire.set('errorMessage', null); }, 3000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-20 left-4 right-4 md:left-auto md:right-8 md:w-96 z-50 bg-red-900 text-white rounded-xl shadow-xl px-4 py-3 border border-red-800 flex items-center gap-3"
        >
            <x-ui.icon name="alert-triangle" size="sm" class="text-red-400 flex-shrink-0" />
            <span class="text-xxs font-semibold flex-1 leading-normal">{{ $errorMessage }}</span>
            <button @click="show = false" class="text-slate-400 hover:text-white transition-colors">
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>
    @endif

    @if ($subSection === 'profile')
        <div class="space-y-6">
            <div>
                <a wire:navigate href="{{ route('settings', ['section' => 'privacy']) }}" class="flex items-center gap-1 text-[10px] font-bold text-slate-400 hover:text-slate-600 mb-2">
                    <x-ui.icon name="chevron-left" size="2xs" /> Quyền riêng tư
                </a>
                <h2 class="text-sm font-bold text-slate-800">Quyền riêng tư hồ sơ</h2>
                <p class="text-xxs text-slate-400 font-medium mt-0.5">Kiểm soát thông tin nào hiển thị công khai với thành viên khác.</p>
            </div>

            <form wire:submit.prevent="savePrivacy" class="space-y-4">
                <div class="bg-white border border-slate-150 rounded-2xl p-5 divide-y divide-slate-100 shadow-2xs">
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div class="flex-1 space-y-0.5">
                            <label for="private-profile" class="text-xxs font-bold text-slate-800 block">Trang cá nhân riêng tư</label>
                            <span class="text-[10px] text-slate-400 leading-normal block">Khi bật, chỉ những người bạn phê duyệt kết nối mới có thể xem đầy đủ thông tin hồ sơ và bài đăng của bạn.</span>
                        </div>
                        <input type="checkbox" id="private-profile" wire:model="is_profile_private" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                    </div>
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div class="flex-1 space-y-0.5">
                            <label for="show-faculty" class="text-xxs font-bold text-slate-800 block">Hiển thị Khoa học đường</label>
                        </div>
                        <input type="checkbox" id="show-faculty" wire:model="show_faculty" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                    </div>
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div class="flex-1 space-y-0.5">
                            <label for="show-major" class="text-xxs font-bold text-slate-800 block">Hiển thị Ngành đào tạo</label>
                        </div>
                        <input type="checkbox" id="show-major" wire:model="show_major" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                    </div>
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div class="flex-1 space-y-0.5">
                            <label for="show-cohort" class="text-xxs font-bold text-slate-800 block">Hiển thị Khóa học (Cohort)</label>
                        </div>
                        <input type="checkbox" id="show-cohort" wire:model="show_cohort" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                    </div>
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div class="flex-1 space-y-0.5">
                            <label for="show-class" class="text-xxs font-bold text-slate-800 block">Hiển thị lớp học sinh hoạt</label>
                        </div>
                        <input type="checkbox" id="show-class" wire:model="show_class_code" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                    </div>
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div class="flex-1 space-y-0.5">
                            <label for="show-bio" class="text-xxs font-bold text-slate-800 block">Hiển thị tiểu sử cá nhân (Bio)</label>
                        </div>
                        <input type="checkbox" id="show-bio" wire:model="show_bio" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="submit" wire:loading.attr="disabled" class="bg-ue-brand hover:bg-ue-brand-hover disabled:opacity-50 text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-xs transition-all flex items-center gap-2">
                        <span wire:loading.remove wire:target="savePrivacy">Lưu thay đổi</span>
                        <span wire:loading wire:target="savePrivacy">Đang lưu...</span>
                    </button>
                </div>
            </form>
        </div>
    @elseif ($subSection === 'blocked')
        <div class="space-y-6">
            <div>
                <a wire:navigate href="{{ route('settings', ['section' => 'privacy']) }}" class="flex items-center gap-1 text-[10px] font-bold text-slate-400 hover:text-slate-600 mb-2">
                    <x-ui.icon name="chevron-left" size="2xs" /> Quyền riêng tư
                </a>
                <h2 class="text-sm font-bold text-slate-800">Trang cá nhân đã chặn</h2>
                <p class="text-xxs text-slate-400 font-medium mt-0.5">Danh sách các tài khoản bạn đã chặn trong hệ thống.</p>
            </div>

            <div class="bg-white border border-slate-150 rounded-2xl overflow-hidden shadow-2xs divide-y divide-slate-100">
                @forelse ($this->getBlockedUsers() as $block)
                    @if ($block->blocked)
                        <div class="p-4 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <x-ui.avatar :user="$block->blocked" size="xs" />
                                <div>
                                    <p class="text-xxs font-bold text-slate-850 leading-tight">{{ $block->blocked->profile?->display_name ?? $block->blocked->name }}</p>
                                </div>
                            </div>
                            <button type="button" wire:click="confirmUnblock({{ $block->blocked->id }}, '{{ addslashes($block->blocked->name) }}')" class="bg-slate-50 border border-slate-200 text-slate-700 text-[10px] font-bold px-3 py-1.5 rounded-lg hover:bg-slate-100 transition-colors shadow-2xs">
                                Bỏ chặn
                            </button>
                        </div>
                    @endif
                @empty
                    <div class="p-8 text-center space-y-2">
                        <x-ui.icon name="shield-check" size="md" class="text-slate-300 mx-auto" />
                        <p class="text-xxs text-slate-400 italic">Bạn chưa chặn tài khoản nào.</p>
                    </div>
                @endforelse
            </div>

            @if ($unblockingUserId)
                <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs select-none" role="dialog" aria-modal="true">
                    <div class="bg-white rounded-2xl max-w-sm w-full border border-slate-200 shadow-2xl p-6 text-center space-y-4">
                        <div class="w-12 h-12 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto text-slate-600">
                            <x-ui.icon name="shield-check" size="md" />
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-base font-bold text-slate-800">Bỏ chặn {{ $unblockingUserName }}?</h3>
                        </div>
                        <div class="flex items-center justify-center gap-3 pt-2">
                            <button type="button" wire:click="$set('unblockingUserId', null)" class="px-4 py-2 text-xxs font-bold text-slate-500 hover:text-slate-700 transition-colors">Hủy</button>
                            <button type="button" wire:click="executeUnblock" class="bg-ue-brand hover:bg-ue-brand-hover text-white text-xxs font-bold px-4 py-2 rounded-xl transition-all shadow-2xs">Xác nhận bỏ chặn</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @elseif ($subSection === 'mentions')
        <div class="space-y-6">
            <div>
                <a wire:navigate href="{{ route('settings', ['section' => 'privacy']) }}" class="flex items-center gap-1 text-[10px] font-bold text-slate-400 hover:text-slate-600 mb-2">
                    <x-ui.icon name="chevron-left" size="2xs" /> Quyền riêng tư
                </a>
                <h2 class="text-sm font-bold text-slate-800">Gắn thẻ và nhắc đến</h2>
                <p class="text-xxs text-slate-400 font-medium mt-0.5">Tùy chọn cho phép gắn thẻ @lượt nhắc trong các bài viết và bình luận.</p>
            </div>

            <form wire:submit.prevent="savePrivacy" class="space-y-6">
                <div class="bg-white border border-slate-150 rounded-2xl p-5 shadow-2xs space-y-6">
                    <div class="space-y-2.5">
                        <h3 class="text-xxs font-bold text-slate-800 uppercase tracking-wider text-[9px]">Ai có thể nhắc đến (@mention) bạn</h3>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" wire:model="mentions_preference" value="everyone" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                <div class="text-xxs font-semibold text-slate-700">Mọi người</div>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" wire:model="mentions_preference" value="connections" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                <div class="text-xxs font-semibold text-slate-700">Chỉ bạn bè kết nối</div>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" wire:model="mentions_preference" value="nobody" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                <div class="text-xxs font-semibold text-slate-700">Không một ai</div>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        <h3 class="text-xxs font-bold text-slate-800 uppercase tracking-wider text-[9px]">Ai có thể gắn thẻ (tag) bạn</h3>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" wire:model="tags_preference" value="everyone" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                <div class="text-xxs font-semibold text-slate-700">Mọi người</div>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" wire:model="tags_preference" value="connections" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                <div class="text-xxs font-semibold text-slate-700">Chỉ bạn bè kết nối</div>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" wire:model="tags_preference" value="nobody" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                <div class="text-xxs font-semibold text-slate-700">Không một ai</div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" class="bg-ue-brand hover:bg-ue-brand-hover disabled:opacity-50 text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-xs transition-all">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    @elseif ($subSection === 'online_status')
        <div class="space-y-6">
            <div>
                <a wire:navigate href="{{ route('settings', ['section' => 'privacy']) }}" class="flex items-center gap-1 text-[10px] font-bold text-slate-400 hover:text-slate-600 mb-2">
                    <x-ui.icon name="chevron-left" size="2xs" /> Quyền riêng tư
                </a>
                <h2 class="text-sm font-bold text-slate-800">Trạng thái trực tuyến</h2>
            </div>
            <form wire:submit.prevent="savePrivacy" class="space-y-6">
                <div class="bg-white border border-slate-150 rounded-2xl p-5 shadow-2xs space-y-6">
                    <div class="space-y-2.5">
                        <h3 class="text-xxs font-bold text-slate-800 uppercase tracking-wider text-[9px]">Ai có thể nhìn thấy trạng thái trực tuyến của bạn</h3>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" wire:model="online_status_visibility" value="connections" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                <div class="text-xxs font-semibold text-slate-700">Bạn bè kết nối</div>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" wire:model="online_status_visibility" value="mutual_connections" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                <div class="text-xxs font-semibold text-slate-700">Bạn học kết nối chéo (Mutual)</div>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" wire:model="online_status_visibility" value="nobody" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                <div class="text-xxs font-semibold text-slate-700">Không ai cả</div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" class="bg-ue-brand hover:bg-ue-brand-hover disabled:opacity-50 text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-xs transition-all">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    @else
        <div class="space-y-6">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Quyền riêng tư</h2>
                <p class="text-xxs text-slate-400 font-medium mt-0.5">Quản lý khả năng hiển thị thông tin và tương tác của bạn.</p>
            </div>

            <div class="bg-white border border-slate-150 rounded-2xl overflow-hidden shadow-2xs divide-y divide-slate-100">
                <a wire:navigate href="{{ route('settings', ['section' => 'privacy', 'subSection' => 'profile']) }}" class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                    <div class="space-y-0.5">
                        <span class="text-xxs font-bold text-slate-800 block">Quyền riêng tư hồ sơ</span>
                        <span class="text-[10px] text-slate-400 block">Ẩn/hiện thông tin Khoa, Ngành học, Lớp, Tiểu sử...</span>
                    </div>
                    <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                </a>

                <a wire:navigate href="{{ route('settings', ['section' => 'privacy', 'subSection' => 'mentions']) }}" class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                    <div class="space-y-0.5">
                        <span class="text-xxs font-bold text-slate-800 block">Gắn thẻ và nhắc đến</span>
                        <span class="text-[10px] text-slate-400 block">Tùy chọn cho phép gắn thẻ @lượt nhắc.</span>
                    </div>
                    <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                </a>

                <a wire:navigate href="{{ route('settings', ['section' => 'privacy', 'subSection' => 'online_status']) }}" class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                    <div class="space-y-0.5">
                        <span class="text-xxs font-bold text-slate-800 block">Trạng thái trực tuyến</span>
                        <span class="text-[10px] text-slate-400 block">Quản lý hiển thị trạng thái đang online.</span>
                    </div>
                    <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                </a>

                <a wire:navigate href="{{ route('settings', ['section' => 'privacy', 'subSection' => 'blocked']) }}" class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                    <div class="space-y-0.5">
                        <span class="text-xxs font-bold text-slate-800 block">Trang cá nhân đã chặn</span>
                        <span class="text-[10px] text-slate-400 block">Quản lý và bỏ chặn các tài khoản đã chặn.</span>
                    </div>
                    <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                </a>
            </div>

            <div class="bg-white border border-slate-150 rounded-2xl p-5 shadow-2xs space-y-4">
                <h3 class="text-xxs font-bold text-slate-850 flex items-center gap-1.5 uppercase tracking-wider text-[9px]">Đề xuất Khám phá (Discovery)</h3>
                <div class="flex items-center justify-between gap-4">
                    <div class="flex-1 space-y-0.5">
                        <label for="discoverable-toggle" class="text-xxs font-bold text-slate-800 block">Cho phép xuất hiện trong Khám phá</label>
                        <span class="text-[10px] text-slate-400 block">Khi bật, tài khoản của bạn sẽ xuất hiện trên danh sách Khám phá đề xuất kết nối của các thành viên HCMUE khác.</span>
                    </div>
                    <input type="checkbox" id="discoverable-toggle" wire:model="is_discoverable" wire:change="savePrivacy" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                </div>
            </div>
        </div>
    @endif
</div>
