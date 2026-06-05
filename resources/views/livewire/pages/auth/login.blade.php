<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Redirect to Microsoft Outlook SSO.
     */
    public function redirectToMicrosoft()
    {
        return redirect()->route('auth.microsoft.redirect');
    }
}; ?>

<div class="space-y-4">
    {{-- Header / Welcome --}}
    <div class="text-center mb-1">
        <h1 class="text-xl font-extrabold text-ue-text tracking-snug">Đăng nhập</h1>
        <p class="text-xs text-ue-text-muted mt-1.5 leading-relaxed">
            Kết nối cộng đồng Sư phạm HCMUE
        </p>
    </div>

    {{-- Session Status --}}
    @if (session('status'))
        <x-ui.alert variant="success" :dismissible="true">
            {{ session('status') }}
        </x-ui.alert>
    @endif

    @foreach (['sso', 'microsoft', 'email'] as $key)
        @php
            $msg = $errors->first($key) ?: (session('errors') ? session('errors')->first($key) : null);
        @endphp
        @if ($msg)
            <x-ui.alert variant="danger" :dismissible="true" class="mb-4">
                {{ $msg }}
            </x-ui.alert>
        @endif
    @endforeach

    {{-- Credentials Form --}}
    <form wire:submit="login" class="space-y-3.5">
        {{-- Email Address --}}
        <div class="space-y-1">
            <x-ui.label for="email" :required="true">Email đăng nhập</x-ui.label>
            <x-ui.input 
                id="email" 
                name="form.email" 
                type="email" 
                wire:model="form.email" 
                placeholder="Nhập email đã đăng ký" 
                required 
                autofocus 
                autocomplete="username"
                :hasError="$errors->has('form.email')"
                size="sm"
            />
            <x-ui.field-error name="form.email" />
            <p class="text-[10px] text-ue-text-muted mt-0.5 leading-normal">
                Sinh viên dùng email <strong>@student.hcmue.edu.vn</strong>, giảng viên dùng <strong>@teacher.hcmue.edu.vn</strong>. Cựu sinh viên có thể đăng nhập bằng email cá nhân đã đăng ký.
            </p>
        </div>

        {{-- Password --}}
        <div class="space-y-1">
            <div class="flex items-center justify-between">
                <x-ui.label for="password" :required="true">Mật khẩu</x-ui.label>
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-ue-brand hover:text-ue-brand-hover hover:underline transition-colors" href="{{ route('password.request') }}" wire:navigate>
                        Quên mật khẩu?
                    </a>
                @endif
            </div>
            <x-ui.input 
                id="password" 
                name="form.password" 
                type="password" 
                wire:model="form.password" 
                placeholder="••••••••" 
                required 
                autocomplete="current-password"
                :hasError="$errors->has('form.password')"
                size="sm"
            />
            <x-ui.field-error name="form.password" />
        </div>

        {{-- Remember Me & Submit --}}
        <div class="flex items-center justify-between pt-0.5">
            <label for="remember" class="inline-flex items-center cursor-pointer select-none">
                <input 
                    wire:model="form.remember" 
                    id="remember" 
                    type="checkbox" 
                    class="rounded border-ue-border text-ue-brand focus:ring-ue-brand/20 transition duration-sm cursor-pointer" 
                    name="remember"
                >
                <span class="ml-2 text-xs font-medium text-ue-text-secondary">Duy trì đăng nhập</span>
            </label>
        </div>

        <div class="pt-1">
            <x-ui.button 
                type="submit" 
                variant="primary" 
                class="w-full justify-center shadow-sm font-bold" 
                size="md" 
                wire:loading.attr="disabled"
                wire:target="login"
            >
                <span wire:loading.remove wire:target="login">Đăng nhập</span>
                <span wire:loading wire:target="login" class="flex items-center gap-2">
                    <span class="ue-spinner" aria-hidden="true"></span>
                    Đang xử lý...
                </span>
            </x-ui.button>
        </div>
    </form>

    {{-- Microsoft SSO Divider --}}
    @php
        $microsoftEnabled = config('services.microsoft.enabled')
            && !empty(config('services.microsoft.client_id'))
            && !empty(config('services.microsoft.client_secret'))
            && !empty(config('services.microsoft.redirect'))
            && !empty(config('services.microsoft.tenant'));
    @endphp

    <div class="space-y-3.5">
        <div class="relative w-full flex items-center justify-center py-1">
            <div class="border-t border-ue-border w-full"></div>
            <div class="absolute bg-white px-4 text-[10px] font-bold uppercase tracking-widest text-ue-text-muted">
                Hoặc
            </div>
        </div>

        @if ($microsoftEnabled)
            <x-ui.button 
                wire:click="redirectToMicrosoft" 
                variant="outline" 
                class="w-full justify-center shadow-sm hover:border-ue-border-strong font-bold" 
                size="md" 
                icon="microsoft"
                wire:loading.attr="disabled"
                wire:target="redirectToMicrosoft"
            >
                <span wire:loading.remove wire:target="redirectToMicrosoft">Tiếp tục với Outlook HCMUE</span>
                <span wire:loading wire:target="redirectToMicrosoft" class="flex items-center gap-2">
                    <span class="ue-spinner" aria-hidden="true"></span>
                    Đang chuyển hướng...
                </span>
            </x-ui.button>
        @else
            <div class="p-2.5 bg-ue-surface-subtle border border-ue-border rounded-xl flex items-start gap-2.5 opacity-60">
                <x-ui.icon name="microsoft" size="sm" class="text-ue-text-disabled mt-0.5 flex-shrink-0" />
                <div class="space-y-0.5">
                    <p class="text-xs font-semibold text-ue-text-secondary">Đăng nhập Microsoft SSO chưa sẵn sàng</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Footer Registration Link --}}
    <div class="text-center pt-1">
        <p class="text-xs text-ue-text-secondary">
            Bạn là thành viên mới? 
            <a class="font-bold text-ue-brand hover:text-ue-brand-hover hover:underline transition-colors ml-1" href="{{ route('register') }}" wire:navigate>
                Đăng ký tài khoản
            </a>
        </p>
    </div>
</div>
