<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'lowercase', 
                'email', 
                'max:255', 
                'unique:'.User::class,
                'regex:/^[a-zA-Z0-9._%+-]+@hcmue\.edu\.vn$/i'
            ],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.regex' => 'Địa chỉ email phải thuộc tên miền @hcmue.edu.vn của Trường Đại học Sư phạm TP.HCM.',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['account_status'] = \App\Enums\AccountStatus::REGISTERED;

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('verification.start', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6 flex flex-col items-center justify-center gap-4">
        <div class="relative w-full flex items-center justify-center">
            <div class="border-t border-ue-border w-full"></div>
            <div class="absolute bg-ue-surface px-4 text-xs font-semibold uppercase tracking-wider text-ue-text-muted">
                Hoặc đăng ký bằng
            </div>
        </div>
        
        <a href="{{ route('auth.microsoft.redirect') }}" class="w-full flex items-center justify-center gap-2 h-10 px-4 rounded-lg font-semibold text-sm border border-ue-border bg-ue-surface hover:bg-ue-surface-hover active:bg-ue-surface-pressed text-ue-text ue-focus-ring select-none whitespace-nowrap transition-colors duration-sm ease-out shadow-sm">
            <x-ui.icon name="microsoft" size="md" />
            <span>Đăng ký nhanh bằng Outlook HCMUE</span>
        </a>
    </div>
</div>
