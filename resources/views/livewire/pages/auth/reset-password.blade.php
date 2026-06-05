<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div class="space-y-5">
    <div class="text-center">
        <h1 class="text-xl font-extrabold text-ue-text tracking-snug">Đặt lại mật khẩu</h1>
        <p class="text-xs text-ue-text-muted mt-2 leading-relaxed">
            Tạo mật khẩu mới cho tài khoản UEConnect của bạn.
        </p>
    </div>

    <form wire:submit="resetPassword" class="space-y-4">
        <div class="space-y-1">
            <x-ui.label for="email" :required="true">Email đăng ký</x-ui.label>
            <x-ui.input
                wire:model="email"
                id="email"
                type="email"
                name="email"
                required
                autofocus
                autocomplete="username"
                :hasError="$errors->has('email')"
                size="sm"
            />
            <x-ui.field-error name="email" />
        </div>

        <div class="space-y-1">
            <x-ui.label for="password" :required="true">Mật khẩu mới</x-ui.label>
            <x-ui.input
                wire:model="password"
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Tối thiểu 8 ký tự"
                :hasError="$errors->has('password')"
                size="sm"
            />
            <x-ui.field-error name="password" />
        </div>

        <div class="space-y-1">
            <x-ui.label for="password_confirmation" :required="true">Xác nhận mật khẩu mới</x-ui.label>
            <x-ui.input
                wire:model="password_confirmation"
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Nhập lại mật khẩu"
                :hasError="$errors->has('password_confirmation')"
                size="sm"
            />
            <x-ui.field-error name="password_confirmation" />
        </div>

        <x-ui.button type="submit" variant="primary" class="w-full justify-center font-bold" wire:loading.attr="disabled" wire:target="resetPassword">
            <span wire:loading.remove wire:target="resetPassword">Đặt lại mật khẩu</span>
            <span wire:loading wire:target="resetPassword" class="flex items-center gap-2">
                <span class="ue-spinner" aria-hidden="true"></span>
                Đang xử lý...
            </span>
        </x-ui.button>
    </form>
</div>
