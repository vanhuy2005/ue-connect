<x-app-layout shell="admin">
    <x-slot name="title">Phân tích</x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-ue-text">Phân tích</h1>
            <p class="mt-2 text-sm text-ue-text-muted">Tổng quan nhanh về quy mô và lưu lượng hệ thống.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            <x-ui.card variant="admin">
                <p class="text-xs font-semibold text-ue-text-muted uppercase">Người dùng</p>
                <p class="mt-2 text-3xl font-bold text-ue-text">{{ $totalUsers }}</p>
            </x-ui.card>

            <x-ui.card variant="admin">
                <p class="text-xs font-semibold text-ue-text-muted uppercase">Cộng đồng</p>
                <p class="mt-2 text-3xl font-bold text-ue-text">{{ $totalCommunities }}</p>
            </x-ui.card>

            <x-ui.card variant="admin">
                <p class="text-xs font-semibold text-ue-text-muted uppercase">Bài viết</p>
                <p class="mt-2 text-3xl font-bold text-ue-text">{{ $totalPosts }}</p>
            </x-ui.card>

            <x-ui.card variant="admin">
                <p class="text-xs font-semibold text-ue-text-muted uppercase">Báo cáo</p>
                <p class="mt-2 text-3xl font-bold text-ue-text">{{ $totalReports }}</p>
            </x-ui.card>

            <x-ui.card variant="admin">
                <p class="text-xs font-semibold text-ue-text-muted uppercase">Xác thực</p>
                <p class="mt-2 text-3xl font-bold text-ue-text">{{ $totalVerifications }}</p>
            </x-ui.card>
        </div>

        <x-ui.card class="mt-6" variant="admin">
            <x-slot name="header">Gợi ý hành động</x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <a href="{{ route('admin.verifications.queue') }}" class="group block rounded-lg border border-ue-border p-5 bg-ue-surface hover:bg-ue-surface-hover hover:border-ue-border-strong transition-all duration-sm">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-ue-text group-hover:text-ue-brand-active">Mở xác thực</p>
                        <x-ui.icon name="arrow-right" size="sm" class="text-ue-text-muted group-hover:text-ue-brand-active transform group-hover:translate-x-1 transition-transform" />
                    </div>
                    <p class="mt-1.5 text-xs text-ue-text-muted">Xử lý hồ sơ chờ duyệt.</p>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="group block rounded-lg border border-ue-border p-5 bg-ue-surface hover:bg-ue-surface-hover hover:border-ue-border-strong transition-all duration-sm">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-ue-text group-hover:text-ue-brand-active">Xem báo cáo</p>
                        <x-ui.icon name="arrow-right" size="sm" class="text-ue-text-muted group-hover:text-ue-brand-active transform group-hover:translate-x-1 transition-transform" />
                    </div>
                    <p class="mt-1.5 text-xs text-ue-text-muted">Kiểm tra nội dung bị gắn cờ.</p>
                </a>
                <a href="{{ route('admin.audit-logs.index') }}" class="group block rounded-lg border border-ue-border p-5 bg-ue-surface hover:bg-ue-surface-hover hover:border-ue-border-strong transition-all duration-sm">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-ue-text group-hover:text-ue-brand-active">Nhật ký thao tác</p>
                        <x-ui.icon name="arrow-right" size="sm" class="text-ue-text-muted group-hover:text-ue-brand-active transform group-hover:translate-x-1 transition-transform" />
                    </div>
                    <p class="mt-1.5 text-xs text-ue-text-muted">Theo dõi hoạt động quản trị gần đây.</p>
                </a>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>