<x-app-layout>
    <x-slot name="title">Trang chủ</x-slot>

    <div class="max-w-2xl mx-auto px-4 py-12 text-center">
        <div class="mb-6">
            <x-ui.icon name="home" size="3xl" class="text-ue-brand mx-auto mb-4" />
        </div>
        <h1 class="text-2xl font-bold text-ue-text mb-3">Chào mừng đến UEConnect!</h1>
        <p class="text-ue-text-secondary mb-8 max-w-md mx-auto">
            Bảng tin đang trong quá trình phát triển. Tính năng chia sẻ bài viết và kết nối sẽ sớm ra mắt.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-left max-w-xl mx-auto">
            <div class="bg-ue-surface border border-ue-border rounded-xl p-4">
                <x-ui.icon name="users" size="lg" class="text-ue-brand mb-2" />
                <p class="text-sm font-semibold text-ue-text">Khám phá</p>
                <p class="text-xs text-ue-text-muted mt-1">Tìm bạn học, bạn cùng ngành</p>
            </div>
            <div class="bg-ue-surface border border-ue-border rounded-xl p-4">
                <x-ui.icon name="message" size="lg" class="text-ue-brand mb-2" />
                <p class="text-sm font-semibold text-ue-text">Tin nhắn</p>
                <p class="text-xs text-ue-text-muted mt-1">Kết nối và trò chuyện</p>
            </div>
            <div class="bg-ue-surface border border-ue-border rounded-xl p-4">
                <x-ui.icon name="community" size="lg" class="text-ue-brand mb-2" />
                <p class="text-sm font-semibold text-ue-text">Cộng đồng</p>
                <p class="text-xs text-ue-text-muted mt-1">Tham gia CLB và nhóm học tập</p>
            </div>
        </div>
    </div>
</x-app-layout>
