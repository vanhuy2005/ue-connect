<?php

use App\Models\User;
use App\Support\Auth\AllowedEmailDomain;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
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
                if (! AllowedEmailDomain::check($value, $allowedDomains)) {
                    $fail('Sinh viên hiện tại cần sử dụng email sinh viên HCMUE, ví dụ: mssv@student.hcmue.edu.vn.');
                }
            };
        } elseif ($this->identity_type === 'teacher_advisor') {
            $allowedDomains = config('ueconnect.identity.staff_email_domains', ['teacher.hcmue.edu.vn']);
            $emailRules[] = function ($attribute, $value, $fail) use ($allowedDomains) {
                if (! AllowedEmailDomain::check($value, $allowedDomains)) {
                    $fail('Giảng viên cần sử dụng email công vụ HCMUE, ví dụ: ten@teacher.hcmue.edu.vn.');
                }
            };
        } else {
            // Alumni: personal emails are allowed, evidence is required in verification.
        }

        $validated = $this->validate([
            'identity_type' => ['required', 'string', 'in:current_student,teacher_advisor,alumni'],
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.unique' => 'Email này đã được đăng ký. Nếu đây là email trường của bạn, hãy dùng đăng nhập/quên mật khẩu hoặc liên hệ admin để được hỗ trợ.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'account_status' => \App\Enums\AccountStatus::REGISTERED,
            'intended_identity_type' => $validated['identity_type'],
        ]);

        try {
            event(new Registered($user));
        } catch (Throwable $exception) {
            Log::warning('Initial email verification delivery failed.', [
                'user_id' => $user->id,
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'message' => $exception->getMessage(),
            ]);

            Session::flash('status', 'verification-link-failed');
        }

        Auth::login($user);

        $this->redirect(route('verification.notice', absolute: false), navigate: true);
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
        <h1 class="text-xl font-extrabold text-ue-text tracking-snug">Đăng ký tài khoản</h1>
        <p class="text-xs text-ue-text-muted mt-1 leading-relaxed">
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
    <form wire:submit="register" class="space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            {{-- Vai trò / Identity Type Selector --}}
            <div class="space-y-1">
                <x-ui.label for="identity_type" :required="true">Bạn là ai?</x-ui.label>
                <x-ui.select 
                    id="identity_type" 
                    name="identity_type" 
                    wire:model.live="identity_type"
                    :hasError="$errors->has('identity_type')"
                    size="sm"
                >
                    <option value="current_student">Sinh viên hiện tại</option>
                    <option value="teacher_advisor">Giảng viên</option>
                    <option value="alumni">Cựu sinh viên</option>
                </x-ui.select>
                <x-ui.field-error name="identity_type" />
            </div>

            {{-- Họ và tên --}}
            <div class="space-y-1">
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
                    size="sm"
                />
                <x-ui.field-error name="name" />
            </div>
        </div>

        {{-- Email Address --}}
        <div class="space-y-1">
            @php
                $emailPlaceholder = match ($identity_type) {
                    'current_student' => '4901104055@student.hcmue.edu.vn',
                    'teacher_advisor' => 'ntt239@teacher.hcmue.edu.vn',
                    'alumni' => 'nguyenvana@gmail.com',
                    default => 'email@example.com',
                };
            @endphp
            <x-ui.label for="email" :required="true">Email</x-ui.label>
            <x-ui.input 
                id="email" 
                name="email" 
                type="email" 
                wire:model="email" 
                :placeholder="$emailPlaceholder" 
                required 
                autocomplete="username"
                :hasError="$errors->has('email')"
                size="sm"
            />
            <x-ui.field-error name="email" />
            <p class="text-[10px] text-ue-text-muted mt-0.5 leading-normal">
                @if ($identity_type === 'current_student')
                    Chỉ chấp nhận email sinh viên HCMUE (mssv@student.hcmue.edu.vn).
                @elseif ($identity_type === 'teacher_advisor')
                    Chỉ chấp nhận email công vụ HCMUE dạng ten@teacher.hcmue.edu.vn.
                @elseif ($identity_type === 'alumni')
                    Cho phép sử dụng email cá nhân. Cần cung cấp minh chứng sau.
                @endif
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            {{-- Mật khẩu --}}
            <div class="space-y-1">
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
                    size="sm"
                />
                <x-ui.field-error name="password" />
            </div>

            {{-- Xác nhận mật khẩu --}}
            <div class="space-y-1">
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
                    size="sm"
                />
                <x-ui.field-error name="password_confirmation" />
            </div>
        </div>

        <div class="pt-1.5">
            <x-ui.button 
                type="submit" 
                variant="primary" 
                class="w-full justify-center shadow-sm font-bold" 
                size="md" 
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
                <span wire:loading.remove wire:target="redirectToMicrosoft">Đăng ký bằng Outlook HCMUE</span>
                <span wire:loading wire:target="redirectToMicrosoft" class="flex items-center gap-2">
                    <span class="ue-spinner" aria-hidden="true"></span>
                    Đang chuyển hướng...
                </span>
            </x-ui.button>
        @else
            <div class="p-2.5 bg-ue-surface-subtle border border-ue-border rounded-xl flex items-start gap-2.5 opacity-60">
                <x-ui.icon name="microsoft" size="sm" class="text-ue-text-disabled mt-0.5 flex-shrink-0" />
                <div class="space-y-0.5">
                    <p class="text-xs font-semibold text-ue-text-secondary">Đăng ký nhanh chưa sẵn sàng</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Footer Login Link --}}
    <div class="text-center pt-1">
        <p class="text-xs text-ue-text-secondary">
            Đã có tài khoản? 
            <a class="font-bold text-ue-brand hover:text-ue-brand-hover hover:underline transition-colors ml-1" href="{{ route('login') }}" wire:navigate>
                Đăng nhập ngay
            </a>
        </p>
    </div>
</div>
