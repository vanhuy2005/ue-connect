<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';
    public string $otp = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $otpVerified = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->email = Str::lower(trim((string) request()->string('email')));
    }

    /**
     * Verify the OTP before allowing password reset.
     */
    public function verifyOtp(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $this->email = Str::lower(trim($this->email));
        $cachedOtp = Cache::get('password_reset_otp_' . $this->email);

        if (!$cachedOtp || $cachedOtp !== $this->otp) {
            $this->addError('otp', 'Mã xác nhận không hợp lệ hoặc đã hết hạn.');
            return;
        }

        $this->otpVerified = true;
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        if (!$this->otpVerified) {
            return;
        }

        $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $this->email = Str::lower(trim($this->email));
        $user = User::where('email', $this->email)->first();

        if ($user) {
            $user->forceFill([
                'password' => Hash::make($this->password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
            
            Cache::forget('password_reset_otp_' . $this->email);
        }

        Session::flash('status', __('passwords.reset'));
        Session::flash('reset_email', $this->email);

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div class="space-y-5">
    <div class="text-center">
        <h1 class="text-xl font-extrabold text-ue-text tracking-snug">Đặt lại mật khẩu</h1>
        <p class="text-xs text-ue-text-muted mt-2 leading-relaxed">
            @if(!$otpVerified)
                Vui lòng nhập mã xác nhận (OTP) 6 số được gửi đến email của bạn.
            @else
                Tạo mật khẩu mới cho tài khoản UEConnect của bạn.
            @endif
        </p>
    </div>

    @if(!$otpVerified)
    <form wire:submit="verifyOtp" class="space-y-4">
        <div class="space-y-1 hidden">
            <x-ui.input
                wire:model="email"
                id="email"
                type="email"
                name="email"
                required
                readonly
            />
        </div>

        <div class="space-y-1">
            <div x-data="{
                digits: ['', '', '', '', '', ''],
                updateOtp() {
                    $wire.set('otp', this.digits.join(''));
                },
                handleInput(e, index) {
                    if (e.target.value.length > 1) {
                        e.target.value = e.target.value.slice(0, 1);
                    }
                    this.digits[index] = e.target.value;
                    this.updateOtp();
                    if (e.target.value !== '' && index < 5) {
                        this.$refs['digit' + (index + 1)].focus();
                    }
                },
                handleBackspace(e, index) {
                    if (e.target.value === '' && index > 0) {
                        this.digits[index - 1] = '';
                        this.updateOtp();
                        this.$refs['digit' + (index - 1)].focus();
                    } else {
                        this.digits[index] = '';
                        this.updateOtp();
                    }
                },
                handlePaste(e) {
                    e.preventDefault();
                    const pastedData = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6).split('');
                    for (let i = 0; i < pastedData.length; i++) {
                        if (i < 6) {
                            this.digits[i] = pastedData[i];
                        }
                    }
                    this.updateOtp();
                    const focusIndex = Math.min(pastedData.length, 5);
                    this.$refs['digit' + focusIndex]?.focus();
                }
            }" class="flex gap-2 justify-center mt-2">
                <template x-for="(digit, index) in digits" :key="index">
                    <input 
                        type="text" 
                        maxlength="1"
                        inputmode="numeric"
                        x-model="digits[index]"
                        x-bind:ref="'digit' + index"
                        @input="handleInput($event, index)"
                        @keydown.backspace="handleBackspace($event, index)"
                        @paste="handlePaste($event)"
                        class="w-12 h-14 text-center text-2xl font-bold rounded-lg border border-ue-border shadow-sm focus:ring-ue-primary focus:border-ue-primary text-ue-text transition-colors"
                        :class="{ 'border-ue-primary ring-1 ring-ue-primary': digits[index] !== '' }"
                    />
                </template>
            </div>
            <div class="text-center mt-2">
                <x-ui.field-error name="otp" />
            </div>
        </div>

        <div class="pt-2">
            <x-ui.button type="submit" variant="primary" class="w-full justify-center font-bold" wire:loading.attr="disabled" wire:target="verifyOtp">
                <span wire:loading.remove wire:target="verifyOtp">Xác nhận OTP</span>
                <span wire:loading wire:target="verifyOtp" class="flex items-center gap-2">
                    <span class="ue-spinner" aria-hidden="true"></span>
                    Đang xử lý...
                </span>
            </x-ui.button>
        </div>
    </form>
    @else
    <form wire:submit="resetPassword" class="space-y-4">
        <div class="space-y-1">
            <x-ui.label for="password" :required="true">Mật khẩu mới</x-ui.label>
            <x-ui.input
                wire:model="password"
                id="password"
                type="password"
                name="password"
                required
                autofocus
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

        <div class="pt-2">
            <x-ui.button type="submit" variant="primary" class="w-full justify-center font-bold" wire:loading.attr="disabled" wire:target="resetPassword">
                <span wire:loading.remove wire:target="resetPassword">Đổi mật khẩu</span>
                <span wire:loading wire:target="resetPassword" class="flex items-center gap-2">
                    <span class="ue-spinner" aria-hidden="true"></span>
                    Đang xử lý...
                </span>
            </x-ui.button>
        </div>
    </form>
    @endif
</div>
