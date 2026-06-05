<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div class="space-y-5">
    <div class="text-center">
        <h1 class="text-xl font-extrabold text-ue-text tracking-snug">Quên mật khẩu</h1>
        <p class="text-xs text-ue-text-muted mt-2 leading-relaxed">
            Nhập email đã đăng ký, UEConnect sẽ gửi liên kết để bạn đặt lại mật khẩu.
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
            <span wire:loading.remove wire:target="sendPasswordResetLink">Gửi liên kết đặt lại mật khẩu</span>
            <span wire:loading wire:target="sendPasswordResetLink" class="flex items-center gap-2">
                <span class="ue-spinner" aria-hidden="true"></span>
                Đang gửi...
            </span>
        </x-ui.button>
    </form>
</div>
