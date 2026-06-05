<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public ?string $mailDeliveryStatus = null;

    public function isOutlookMailConfigured(): bool
    {
        return config('mail.default') === 'smtp'
            && config('mail.mailers.smtp.host') === 'smtp.office365.com'
            && filled(config('mail.mailers.smtp.username'))
            && filled(config('mail.mailers.smtp.password'))
            && filled(config('mail.from.address'));
    }

    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        if (! $this->isOutlookMailConfigured()) {
            if (in_array(config('mail.default'), ['log', 'array'], true)) {
                Auth::user()->sendEmailVerificationNotification();
                $this->flashMailDeliveryStatus('verification-link-logged');

                return;
            }

            $this->flashMailDeliveryStatus('mail-not-configured');

            return;
        }

        try {
            Auth::user()->sendEmailVerificationNotification();
            $this->flashMailDeliveryStatus('verification-link-sent');
        } catch (Throwable $exception) {
            Log::warning('Email verification delivery failed.', [
                'user_id' => Auth::id(),
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'message' => $exception->getMessage(),
            ]);

            $this->flashMailDeliveryStatus('verification-link-failed');
        }
    }

    private function flashMailDeliveryStatus(string $status): void
    {
        $this->mailDeliveryStatus = $status;
        Session::flash('status', $status);
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="space-y-5">
    <div class="text-center">
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-ue-brand-soft text-ue-brand">
            <x-ui.icon name="mail" size="lg" />
        </div>
        <h1 class="text-xl font-extrabold text-ue-text tracking-snug">Xác minh email đăng ký</h1>
        <p class="text-xs text-ue-text-muted mt-2 leading-relaxed">
            UEConnect đã gửi liên kết xác minh đến email của bạn. Bạn cần xác minh hộp thư trước khi gửi hồ sơ định danh.
        </p>
    </div>

    @php($deliveryStatus = session('status', $mailDeliveryStatus))

    @if ($deliveryStatus == 'verification-link-sent')
        <x-ui.alert variant="success" :dismissible="true">
            Liên kết xác minh mới đã được gửi. Vui lòng kiểm tra hộp thư đến hoặc thư rác.
        </x-ui.alert>
    @endif

    @if ($deliveryStatus == 'verification-link-logged')
        <x-ui.alert variant="warning" :dismissible="true">
            Môi trường hiện tại đang dùng mailer {{ config('mail.default') }}, nên email chỉ được ghi vào log dev và chưa gửi ra Outlook thật.
        </x-ui.alert>
    @endif

    @if ($deliveryStatus == 'mail-not-configured')
        <x-ui.alert variant="danger" :dismissible="true">
            SMTP Outlook chưa được cấu hình đủ. Cần MAIL_MAILER=smtp, MAIL_HOST=smtp.office365.com, MAIL_USERNAME, MAIL_PASSWORD và MAIL_FROM_ADDRESS hợp lệ.
        </x-ui.alert>
    @endif

    @if ($deliveryStatus == 'verification-link-failed')
        <x-ui.alert variant="danger" :dismissible="true">
            UEConnect chưa gửi được email qua Outlook SMTP. Vui lòng kiểm tra tài khoản gửi, mật khẩu/app password và quyền SMTP AUTH.
        </x-ui.alert>
    @endif

    @unless ($this->isOutlookMailConfigured())
        <x-ui.alert variant="warning">
            Dev mail hiện chưa cấu hình Outlook SMTP đầy đủ. Nếu cần nhận trong inbox thật, hãy cấu hình Office 365 SMTP trong .env trước khi test.
        </x-ui.alert>
    @endunless

    <div class="rounded-xl border border-ue-border bg-ue-surface-subtle p-4">
        <div class="flex items-start gap-3">
            <x-ui.icon name="shield-check" size="sm" class="text-ue-brand mt-0.5 flex-shrink-0" />
            <p class="text-xs leading-relaxed text-ue-text-secondary">
                Email trường là định danh duy nhất. Nếu email sinh viên/giảng viên của bạn đã bị người khác đăng ký, hãy dùng quên mật khẩu hoặc liên hệ admin để được hỗ trợ thu hồi.
            </p>
        </div>
    </div>

    <div class="space-y-3">
        <x-ui.button wire:click="sendVerification" variant="primary" class="w-full justify-center font-bold" wire:loading.attr="disabled" wire:target="sendVerification">
            <span wire:loading.remove wire:target="sendVerification">Gửi lại email xác minh</span>
            <span wire:loading wire:target="sendVerification" class="flex items-center gap-2">
                <span class="ue-spinner" aria-hidden="true"></span>
                Đang gửi...
            </span>
        </x-ui.button>

        <x-ui.button wire:click="logout" variant="ghost" class="w-full justify-center">
            Đăng xuất
        </x-ui.button>
    </div>
</div>
