<?php

use App\Mail\Auth\ResetPasswordOtpMail;
use App\Models\User;
use App\Support\Mail\MailDeliveryConfiguration;
use App\Support\Mail\SmartMailer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset OTP to the provided email address.
     *
     * Routing:
     * - @hcmue.edu.vn / @student.hcmue.edu.vn / @teacher.hcmue.edu.vn
     *   → sent via Outlook SMTP to avoid Microsoft spam filters.
     * - All other domains (gmail, outlook personal, etc.)
     *   → sent via Resend (DKIM-signed).
     */
    public function sendPasswordResetLink(): void
    {
        $this->email = Str::lower(trim($this->email));

        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $requestId = (string) Str::uuid();
        $user = User::where('email', $this->email)->first();
        $mailer = SmartMailer::resolveMailer($this->email);

        Log::info('Password reset OTP requested', [
            'request_id' => $requestId,
            'email_domain' => Str::after($this->email, '@'),
            'mailer' => $mailer,
            'user_exists' => (bool) $user,
        ]);

        if ($user) {
            $configurationStatus = MailDeliveryConfiguration::status($mailer);

            if (! $configurationStatus['configured']) {
                Log::warning('Password reset OTP mailer is not configured', [
                    'request_id' => $requestId,
                    'email_domain' => Str::after($this->email, '@'),
                    'mailer' => $mailer,
                    'reason' => $configurationStatus['reason'],
                    'context' => $configurationStatus['context'],
                ]);

                $this->addError('email', 'Hệ thống gửi OTP đang chưa được cấu hình đầy đủ. Vui lòng thử lại sau hoặc liên hệ quản trị viên.');

                return;
            }

            $otp = (string) random_int(100000, 999999);

            Cache::put('password_reset_otp_' . $this->email, $otp, now()->addMinutes(15));

            try {
                SmartMailer::to($this->email, new ResetPasswordOtpMail($otp));

                Log::info('Password reset OTP dispatched', [
                    'request_id' => $requestId,
                    'email_domain' => Str::after($this->email, '@'),
                    'mailer' => $mailer,
                    'user_id' => $user->id,
                ]);
            } catch (\Throwable $e) {
                Cache::forget('password_reset_otp_' . $this->email);

                Log::error('Password reset OTP send failed', [
                    'request_id' => $requestId,
                    'email' => $this->email,
                    'email_domain' => Str::after($this->email, '@'),
                    'mailer' => $mailer,
                    'error' => $e->getMessage(),
                ]);

                $this->addError('email', 'Không thể gửi mã OTP đến email này. Vui lòng thử lại sau hoặc liên hệ quản trị viên.');

                return;
            }
        } else {
            Log::info('Password reset OTP skipped for unknown email', [
                'request_id' => $requestId,
                'email_domain' => Str::after($this->email, '@'),
                'mailer' => $mailer,
            ]);
        }

        // Always redirect to avoid email enumeration
        $this->redirectRoute('password.reset', ['email' => $this->email], navigate: true);
    }
}; ?>

<div class="space-y-5">
    <div class="text-center">
        <h1 class="text-xl font-extrabold text-ue-text tracking-snug">Quên mật khẩu</h1>
        <p class="text-xs text-ue-text-muted mt-2 leading-relaxed">
            Nhập email đã đăng ký, UEConnect sẽ gửi mã xác nhận (OTP) để bạn đặt lại mật khẩu.
        </p>
    </div>

    @if (session('status'))
        <x-ui.alert variant="success" :dismissible="true">
            {{ session('status') }}
        </x-ui.alert>
    @endif

    <form wire:submit="sendPasswordResetLink" class="space-y-4">
        <div class="space-y-1">
            <x-ui.label for="email" :required="true">Email đăng ký</x-ui.label>
            <x-ui.input
                wire:model="email"
                id="email"
                type="email"
                name="email"
                required
                autofocus
                placeholder="mssv@student.hcmue.edu.vn hoặc email cá nhân"
                autocomplete="username"
                :hasError="$errors->has('email')"
                size="sm"
            />
            <x-ui.field-error name="email" />
        </div>

        <x-ui.button type="submit" variant="primary" class="w-full justify-center font-bold" wire:loading.attr="disabled" wire:target="sendPasswordResetLink">
            <span wire:loading.remove wire:target="sendPasswordResetLink">Nhận mã OTP</span>
            <span wire:loading wire:target="sendPasswordResetLink" class="flex items-center gap-2">
                <span class="ue-spinner" aria-hidden="true"></span>
                Đang gửi...
            </span>
        </x-ui.button>
    </form>
</div>
