<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function mount(): void
    {
        // Redirect if profile is already complete
        if (auth()->user()?->profile) {
            $this->redirect(route('dashboard'), navigate: true);
        }
    }
}; ?>

<div class="max-w-xl mx-auto py-12 px-4">
    <div class="text-center mb-8">
        <x-ui.icon name="user-circle" size="2xl" class="text-ue-brand mx-auto mb-4" />
        <h1 class="text-2xl font-bold text-ue-text mb-2">Thiết lập hồ sơ của bạn</h1>
        <p class="text-ue-text-secondary">Hoàn thiện hồ sơ để bắt đầu kết nối với cộng đồng UEConnect.</p>
    </div>

    <div class="bg-ue-surface border border-ue-border rounded-2xl p-6 shadow-sm">
        <p class="text-sm text-ue-text-secondary text-center">
            Tính năng thiết lập hồ sơ đang trong quá trình phát triển.
            Bạn có thể <a href="{{ route('dashboard') }}" class="text-ue-brand font-semibold hover:underline">về trang chủ</a> và quay lại sau.
        </p>
    </div>
</div>
