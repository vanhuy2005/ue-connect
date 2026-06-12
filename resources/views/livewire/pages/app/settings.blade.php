<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Volt\Component;

new class extends Component
{
    // Deep link parameters
    public string $section = 'account';
    public ?string $subSection = null;
    public bool $showMenuOnMobile = true;

    public function mount(string $section = 'account', ?string $subSection = null): void
    {
        $routeSection = request()->route('section');
        $this->showMenuOnMobile = (empty($routeSection) || $routeSection === 'index');

        $this->section = $section === 'index' ? 'account' : $section;
        if ($routeSection) {
            $this->section = $routeSection === 'index' ? 'account' : $routeSection;
        }
        $this->subSection = $subSection ?? request()->route('subSection');
    }

    public function switchSection(string $section, ?string $subSection = null): void
    {
        $this->section = $section;
        $this->subSection = $subSection;
        $this->showMenuOnMobile = false;
    }
}; ?>

<div class="py-6 px-4 w-full max-w-5xl mx-auto space-y-6 flex-1 flex flex-col min-h-0 h-full overflow-hidden">
    {{-- Title Header --}}
    <div class="flex flex-col gap-1 border-b border-slate-100 pb-4">
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Cài đặt</h1>
        <p class="text-xs text-slate-400 font-medium">Thiết lập tài khoản, quyền riêng tư, thông báo và bảo mật học đường.</p>
    </div>

    {{-- Layout shell --}}
    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-xs flex-1 flex flex-col min-h-0">
        {{-- Responsive Layout: Desktop 2-column split, Mobile 1-column dynamic --}}
        <div class="flex flex-col lg:flex-row h-full min-h-0 overflow-hidden">
            
            {{-- Left column: Category List Navigation --}}
            {{-- Mobile: only visible if requested route is settings main list, or always hidden on detail screens --}}
            <nav 
                class="w-full lg:w-72 border-r border-slate-150 flex-shrink-0 p-4 space-y-1.5 overflow-y-auto ue-scrollbar-none {{ $showMenuOnMobile ? 'block' : 'hidden lg:block' }}"
                aria-label="Danh mục cài đặt"
            >
                {{-- Account center overview item --}}
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 mb-3 flex items-center gap-3">
                    <x-ui.avatar :user="Auth::user()" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="text-xxs font-bold text-slate-800 truncate leading-tight">{{ Auth::user()->profile?->display_name ?? Auth::user()->name }}</p>
                        <p class="text-[9px] font-bold tracking-wider text-ue-brand uppercase leading-none mt-1">
                            @if ((Auth::user()->profile?->role_type ?? 'student') === 'student') Sinh viên
                            @elseif (in_array((Auth::user()->profile?->role_type ?? ''), ['teacher', 'advisor'], true)) Giảng viên
                            @elseif ((Auth::user()->profile?->role_type ?? '') === 'alumni') Cựu sinh viên
                            @else Thành viên
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Categories list --}}
                <ul class="space-y-1" role="list">
                    <li>
                        <a 
                            href="{{ route('settings', ['section' => 'account']) }}"
                            wire:click.prevent="switchSection('account')"
                            x-on:click="window.history.pushState(null, '', '{{ route('settings', ['section' => 'account']) }}')"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xxs font-bold transition-all {{ $section === 'account' ? 'bg-ue-brand-soft text-ue-brand-active hover:bg-ue-brand-soft-hover hover:!text-ue-brand-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span class="flex items-center gap-2.5">
                                <x-ui.icon name="user" size="xs" />
                                Trung tâm tài khoản
                            </span>
                            <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                        </a>
                    </li>
                    <li>
                        <a 
                            href="{{ route('settings', ['section' => 'privacy']) }}"
                            wire:click.prevent="switchSection('privacy')"
                            x-on:click="window.history.pushState(null, '', '{{ route('settings', ['section' => 'privacy']) }}')"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xxs font-bold transition-all {{ $section === 'privacy' ? 'bg-ue-brand-soft text-ue-brand-active hover:bg-ue-brand-soft-hover hover:!text-ue-brand-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span class="flex items-center gap-2.5">
                                <x-ui.icon name="shield" size="xs" />
                                Quyền riêng tư
                            </span>
                            <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                        </a>
                    </li>
                    <li>
                        <a 
                            href="{{ route('settings', ['section' => 'notifications']) }}"
                            wire:click.prevent="switchSection('notifications')"
                            x-on:click="window.history.pushState(null, '', '{{ route('settings', ['section' => 'notifications']) }}')"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xxs font-bold transition-all {{ $section === 'notifications' ? 'bg-ue-brand-soft text-ue-brand-active hover:bg-ue-brand-soft-hover hover:!text-ue-brand-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span class="flex items-center gap-2.5">
                                <x-ui.icon name="heart" size="xs" />
                                Thông báo
                            </span>
                            <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                        </a>
                    </li>
                    <li>
                        <a 
                            href="{{ route('settings', ['section' => 'content']) }}"
                            wire:click.prevent="switchSection('content')"
                            x-on:click="window.history.pushState(null, '', '{{ route('settings', ['section' => 'content']) }}')"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xxs font-bold transition-all {{ $section === 'content' ? 'bg-ue-brand-soft text-ue-brand-active hover:bg-ue-brand-soft-hover hover:!text-ue-brand-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span class="flex items-center gap-2.5">
                                <x-ui.icon name="menu" size="xs" />
                                Tùy chọn nội dung
                            </span>
                            <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                        </a>
                    </li>
                    <li>
                        <a 
                            href="{{ route('settings', ['section' => 'data-privacy']) }}"
                            wire:click.prevent="switchSection('data-privacy')"
                            x-on:click="window.history.pushState(null, '', '{{ route('settings', ['section' => 'data-privacy']) }}')"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xxs font-bold transition-all {{ $section === 'data-privacy' ? 'bg-ue-brand-soft text-ue-brand-active hover:bg-ue-brand-soft-hover hover:!text-ue-brand-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span class="flex items-center gap-2.5">
                                <x-ui.icon name="shield-check" size="xs" />
                                Dữ liệu & Bảo mật
                            </span>
                            <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                        </a>
                    </li>
                    <li>
                        <a 
                            href="{{ route('settings', ['section' => 'support']) }}"
                            wire:click.prevent="switchSection('support')"
                            x-on:click="window.history.pushState(null, '', '{{ route('settings', ['section' => 'support']) }}')"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xxs font-bold transition-all {{ $section === 'support' ? 'bg-ue-brand-soft text-ue-brand-active hover:bg-ue-brand-soft-hover hover:!text-ue-brand-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span class="flex items-center gap-2.5">
                                <x-ui.icon name="help-circle" size="xs" />
                                Hỗ trợ & Trợ giúp
                            </span>
                            <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                        </a>
                    </li>
                    <li>
                        <a 
                            href="{{ route('settings', ['section' => 'security']) }}"
                            wire:click.prevent="switchSection('security')"
                            x-on:click="window.history.pushState(null, '', '{{ route('settings', ['section' => 'security']) }}')"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xxs font-bold transition-all {{ $section === 'security' ? 'bg-ue-brand-soft text-ue-brand-active hover:bg-ue-brand-soft-hover hover:!text-ue-brand-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span class="flex items-center gap-2.5">
                                <x-ui.icon name="log-in" size="xs" />
                                Bảo mật tài khoản
                            </span>
                            <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                        </a>
                    </li>
                    <li>
                        <a 
                            href="{{ route('settings', ['section' => 'language']) }}"
                            wire:click.prevent="switchSection('language')"
                            x-on:click="window.history.pushState(null, '', '{{ route('settings', ['section' => 'language']) }}')"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xxs font-bold transition-all {{ $section === 'language' ? 'bg-ue-brand-soft text-ue-brand-active hover:bg-ue-brand-soft-hover hover:!text-ue-brand-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span class="flex items-center gap-2.5">
                                <x-ui.icon name="eye" size="xs" />
                                Ngôn ngữ hiển thị
                            </span>
                            <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                        </a>
                    </li>

                    {{-- Admin system moderator shortcuts --}}
                    @if (Gate::any(['review_verification', 'manage_reports']))
                        <div class="border-t border-slate-100 my-2 pt-2">
                            <span class="px-3 text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Quản lý hệ thống</span>
                            <li>
                                <a 
                                    href="{{ route('admin.dashboard') }}"
                                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xxs font-bold text-slate-600 hover:bg-slate-50 transition-all"
                                >
                                    <x-ui.icon name="shield" size="xs" />
                                    Tổng quan quản trị
                                </a>
                            </li>
                        </div>
                    @endif
                </ul>
            </nav>

            {{-- Right column: Detail Content panel --}}
            {{-- Mobile: only visible if requested route is a specific settings section --}}
            <section 
                class="flex-1 p-6 lg:p-8 bg-slate-50/20 overflow-y-auto ue-scrollbar-none {{ $showMenuOnMobile ? 'hidden lg:block' : 'block' }}"
                aria-label="Nội dung cài đặt chi tiết"
            >
                {{-- Mobile Navigation Header with Back button --}}
                <div class="flex lg:hidden items-center justify-between border-b border-slate-100 pb-3 mb-5 flex-shrink-0">
                    <a wire:navigate href="{{ route('settings') }}" class="flex items-center gap-1.5 text-xxs font-bold text-slate-600 hover:text-slate-800 transition-colors">
                        <x-ui.icon name="chevron-left" size="xs" />
                        Quay lại Cài đặt
                    </a>
                </div>

                {{-- Dynamic Component Loading with wire:key to trigger proper transitions --}}
                <div wire:key="settings-panel-{{ $section }}-{{ $subSection ?? 'default' }}">
                    @switch($section)
                        @case('account')
                            <livewire:pages.app.settings.account />
                            @break
                        @case('privacy')
                            <livewire:pages.app.settings.privacy :subSection="$subSection" />
                            @break
                        @case('notifications')
                            <livewire:pages.app.settings.notifications />
                            @break
                        @case('content')
                            <livewire:pages.app.settings.content />
                            @break
                        @case('data-privacy')
                            <livewire:pages.app.settings.data-privacy />
                            @break
                        @case('support')
                            <livewire:pages.app.settings.support />
                            @break
                        @case('security')
                            <livewire:pages.app.settings.security />
                            @break
                        @case('language')
                            <livewire:pages.app.settings.language />
                            @break
                        @default
                            <livewire:pages.app.settings.account />
                    @endswitch
                </div>
            </section>
        </div>
    </div>
</div>
