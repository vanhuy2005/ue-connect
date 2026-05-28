<x-app-layout>
    <x-slot name="title">Chi tiết bài viết</x-slot>
    <div class="max-w-2xl mx-auto px-4 py-8">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-sm text-ue-text-secondary hover:text-ue-brand mb-6 transition-colors">
            <x-ui.icon name="arrow-left" size="xs" />
            Quay lại bảng tin
        </a>
        <livewire:pages.app.post-detail :post="$post" />
    </div>
</x-app-layout>
