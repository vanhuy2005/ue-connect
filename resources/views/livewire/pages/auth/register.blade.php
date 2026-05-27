<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $identity_type = 'current_student';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        // P0-2: Check if external mentor is disabled
        if ($this->identity_type === 'external_mentor' && ! config('ueconnect.identity.external_mentor_personal_email_allowed')) {
            throw ValidationException::withMessages([
                'identity_type' => ['Tính năng đăng ký mentor khách mời hiện chỉ mở theo lời mời.'],
            ]);
        }

        $emailRules = [
            'required',
            'string',
            'lowercase',
            'email',
            'max:255',
            'unique:'.User::class,
        ];

        // Specific email checks based on identity type
        if ($this->identity_type === 'current_student') {
            $allowedDomains = config('ueconnect.identity.student_email_domains', ['student.hcmue.edu.vn']);
            $emailRules[] = function ($attribute, $value, $fail) use ($allowedDomains) {
                if (! \App\Support\Auth\AllowedEmailDomain::check($value, $allowedDomains)) {
                    $fail('Sinh viên hiện tại cần sử dụng email sinh viên HCMUE, ví dụ: mssv@student.hcmue.edu.vn.');
                }
            };
        } elseif ($this->identity_type === 'teacher_advisor') {
            $allowedDomains = config('ueconnect.identity.staff_email_domains', ['teacher.hcmue.edu.vn', 'hcmue.edu.vn']);
            $emailRules[] = function ($attribute, $value, $fail) use ($allowedDomains) {
                if (! \App\Support\Auth\AllowedEmailDomain::check($value, $allowedDomains)) {
                    $fail('Giảng viên hoặc cố vấn học tập cần sử dụng email thuộc hệ thống HCMUE.');
                }
            };
        } else {
            // Alumni or External Mentor: personal emails allowed
        }

        $validated = $this->validate([
            'identity_type' => ['required', 'string', 'in:current_student,teacher_advisor,alumni,external_mentor'],
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'account_status' => \App\Enums\AccountStatus::REGISTERED,
            'intended_identity_type' => $validated['identity_type'],
        ]);

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('verification.start', absolute: false), navigate: true);
    }

    /**
     * Redirect to Microsoft Outlook SSO.
     */
    public function redirectToMicrosoft()
    {
        return redirect()->route('auth.microsoft.redirect');
    }
}; ?>

<div class="space-y-6">
    {{-- Header / Welcome --}}
    <div class="text-center">
        <h1 class="text-2xl font-bold text-ue-text tracking-snug">Đăng ký tài khoản</h1>
        <p class="text-xs text-ue-text-muted mt-1.5 leading-relaxed">
            Tham gia cộng đồng Sư phạm HCMUE
        </p>
    </div>

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

    {{-- Registration Form --}}
    <form wire:submit="register" class="space-y-4">
        {{-- Vai trò / Identity Type Selector --}}
        <div class="space-y-2">
            <x-ui.label :required="true">Bạn là ai trong cộng đồng HCMUE?</x-ui.label>
            <div class="grid grid-cols-2 gap-3.5">
                <label class="relative flex flex-col p-4 bg-ue-surface border rounded-2xl cursor-pointer hover:bg-ue-surface-hover focus-within:ring-2 focus-within:ring-ue-brand transition-all duration-sm {{ $identity_type === 'current_student' ? 'border-ue-brand ring-1 ring-ue-brand bg-ue-brand-soft/20' : 'border-ue-border' }}">
                    <input type="radio" wire:model.live="identity_type" value="current_student" class="sr-only" />
                    <span class="text-xs font-bold text-ue-text mb-0.5">Sinh viên hiện tại</span>
                    <span class="text-[10px] text-ue-text-muted leading-tight">Dành cho sinh viên đang theo học</span>
                </label>
                
                <label class="relative flex flex-col p-4 bg-ue-surface border rounded-2xl cursor-pointer hover:bg-ue-surface-hover focus-within:ring-2 focus-within:ring-ue-brand transition-all duration-sm {{ $identity_type === 'teacher_advisor' ? 'border-ue-brand ring-1 ring-ue-brand bg-ue-brand-soft/20' : 'border-ue-border' }}">
                    <input type="radio" wire:model.live="identity_type" value="teacher_advisor" class="sr-only" />
                    <span class="text-xs font-bold text-ue-text mb-0.5">Giảng viên / Cố vấn</span>
                    <span class="text-[10px] text-ue-text-muted leading-tight">Dành cho giảng viên, cán bộ trường</span>
                </label>

                <label class="relative flex flex-col p-4 bg-ue-surface border rounded-2xl cursor-pointer hover:bg-ue-surface-hover focus-within:ring-2 focus-within:ring-ue-brand transition-all duration-sm {{ $identity_type === 'alumni' ? 'border-ue-brand ring-1 ring-ue-brand bg-ue-brand-soft/20' : 'border-ue-border' }}">
                    <input type="radio" wire:model.live="identity_type" value="alumni" class="sr-only" />
                    <span class="text-xs font-bold text-ue-text mb-0.5">Cựu sinh viên</span>
                    <span class="text-[10px] text-ue-text-muted leading-tight">Dùng email cá nhân & gửi minh chứng</span>
                </label>

                <label class="relative flex flex-col p-4 bg-ue-surface border rounded-2xl cursor-pointer hover:bg-ue-surface-hover focus-within:ring-2 focus-within:ring-ue-brand transition-all duration-sm {{ $identity_type === 'external_mentor' ? 'border-ue-brand ring-1 ring-ue-brand bg-ue-brand-soft/20' : 'border-ue-border' }}">
                    <input type="radio" wire:model.live="identity_type" value="external_mentor" class="sr-only" />
                    <span class="text-xs font-bold text-ue-text mb-0.5">Mentor khách mời</span>
                    <span class="text-[10px] text-ue-text-muted leading-tight">Tính năng mở theo lời mời đặc biệt</span>
                </label>
            </div>
            <x-ui.field-error name="identity_type" />
        </div>

        {{-- Họ và tên --}}
        <div class="space-y-1.5">
            <x-ui.label for="name" :required="true">Họ và tên</x-ui.label>
            <x-ui.input 
                id="name" 
                name="name" 
                type="text" 
                wire:model="name" 
                placeholder="Nguyễn Văn A" 
                required 
                autofocus 
                autocomplete="name"
                :hasError="$errors->has('name')"
            />
            <x-ui.field-error name="name" />
        </div>

        {{-- Email Address --}}
        <div class="space-y-1.5">
            <x-ui.label for="email" :required="true">Email</x-ui.label>
            <x-ui.input 
                id="email" 
                name="email" 
                type="email" 
                wire:model="email" 
                placeholder="tensinhvien@student.hcmue.edu.vn" 
                required 
                autocomplete="username"
                :hasError="$errors->has('email')"
            />
            <x-ui.field-error name="email" />
            <p class="text-2xs text-ue-text-muted mt-1 leading-normal">
                @if ($identity_type === 'current_student')
                    Chỉ chấp nhận email sinh viên HCMUE (ví dụ: mssv@student.hcmue.edu.vn).
                @elseif ($identity_type === 'teacher_advisor')
                    Chỉ chấp nhận email công vụ HCMUE (ví dụ: @teacher.hcmue.edu.vn hoặc @hcmue.edu.vn).
                @elseif ($identity_type === 'alumni')
                    Cho phép sử dụng email cá nhân (Gmail, Outlook...). Cần cung cấp minh chứng cựu sinh viên sau đó.
                @else
                    Đăng ký mentor khách mời hiện chỉ áp dụng theo lời mời đặc biệt.
                @endif
            </p>
        </div>

        {{-- Mật khẩu --}}
        <div class="space-y-1.5">
            <x-ui.label for="password" :required="true">Mật khẩu</x-ui.label>
            <x-ui.input 
                id="password" 
                name="password" 
                type="password" 
                wire:model="password" 
                placeholder="Tối thiểu 8 ký tự" 
                required 
                autocomplete="new-password"
                :hasError="$errors->has('password')"
            />
            <x-ui.field-error name="password" />
        </div>

        {{-- Xác nhận mật khẩu --}}
        <div class="space-y-1.5">
            <x-ui.label for="password_confirmation" :required="true">Xác nhận mật khẩu</x-ui.label>
            <x-ui.input 
                id="password_confirmation" 
                name="password_confirmation" 
                type="password" 
                wire:model="password_confirmation" 
                placeholder="Nhập lại mật khẩu" 
                required 
                autocomplete="new-password"
                :hasError="$errors->has('password_confirmation')"
            />
            <x-ui.field-error name="password_confirmation" />
        </div>

        <div class="pt-3">
            <x-ui.button 
                type="submit" 
                variant="primary" 
                class="w-full justify-center shadow-sm" 
                size="lg" 
                wire:loading.attr="disabled"
                wire:target="register"
            >
                <span wire:loading.remove wire:target="register">Đăng ký tài khoản</span>
                <span wire:loading wire:target="register" class="flex items-center gap-2">
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

    <div class="space-y-4">
        <div class="relative w-full flex items-center justify-center py-2">
            <div class="border-t border-ue-border w-full"></div>
            <div class="absolute bg-ue-surface px-4 text-2xs font-bold uppercase tracking-widest text-ue-text-muted">
                Hoặc
            </div>
        </div>

        @if ($microsoftEnabled)
            <x-ui.button 
                wire:click="redirectToMicrosoft" 
                variant="outline" 
                class="w-full justify-center shadow-sm hover:border-ue-border-strong" 
                size="lg" 
                icon="microsoft"
                wire:loading.attr="disabled"
                wire:target="redirectToMicrosoft"
            >
                <span wire:loading.remove wire:target="redirectToMicrosoft">Đăng ký bằng Outlook HCMUE</span>
                <span wire:loading wire:target="redirectToMicrosoft" class="flex items-center gap-2">
                    <span class="ue-spinner" aria-hidden="true"></span>
                    Đang chuyển hướng...
                </span>
            </x-ui.button>
        @else
            <div class="p-3.5 bg-ue-surface-subtle border border-ue-border rounded-xl flex items-start gap-3 opacity-60">
                <x-ui.icon name="microsoft" size="md" class="text-ue-text-disabled mt-0.5 flex-shrink-0" />
                <div class="space-y-0.5">
                    <p class="text-xs font-semibold text-ue-text-secondary">Đăng ký nhanh chưa sẵn sàng</p>
                    <p class="text-2xs text-ue-text-muted leading-relaxed">
                        Hệ thống xác thực nhanh bằng tài khoản trường hiện chưa được cấu hình.
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- Footer Login Link --}}
    <div class="text-center pt-2">
        <p class="text-sm text-ue-text-secondary">
            Đã có tài khoản? 
            <a class="font-semibold text-ue-brand hover:text-ue-brand-hover hover:underline transition-colors ml-1" href="{{ route('login') }}" wire:navigate>
                Đăng nhập ngay
            </a>
        </p>
    </div>
</div>
