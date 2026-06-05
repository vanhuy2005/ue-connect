<x-app-layout shell="admin">
    <x-slot name="title">Kiểm duyệt</x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-ue-text">Kiểm duyệt</h1>
            <p class="mt-2 text-sm text-ue-text-muted">Điểm vào nhanh cho các tác vụ kiểm duyệt nội dung và an toàn cộng đồng.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-ui.card variant="admin">
                <p class="text-xs font-semibold text-ue-text-muted uppercase">Báo cáo chờ xử lý</p>
                <p class="mt-2 text-3xl font-bold text-ue-text">{{ $pendingReports }}</p>
                <a href="{{ route('admin.reports.index') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-ue-brand-active hover:underline">
                    Mở hàng đợi
                    <x-ui.icon name="arrow-right" size="xs" />
                </a>
            </x-ui.card>

            <x-ui.card variant="admin">
                <p class="text-xs font-semibold text-ue-text-muted uppercase">Xác thực chờ duyệt</p>
                <p class="mt-2 text-3xl font-bold text-ue-text">{{ $pendingVerifications }}</p>
                <a href="{{ route('admin.verifications.queue') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-ue-brand-active hover:underline">
                    Mở hàng đợi
                    <x-ui.icon name="arrow-right" size="xs" />
                </a>
            </x-ui.card>

            <x-ui.card variant="admin">
                <p class="text-xs font-semibold text-ue-text-muted uppercase">Tài khoản bị khóa</p>
                <p class="mt-2 text-3xl font-bold text-ue-text">{{ $suspendedUsers }}</p>
                <a href="{{ route('admin.users.index') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-ue-brand-active hover:underline">
                    Xem người dùng
                    <x-ui.icon name="arrow-right" size="xs" />
                </a>
            </x-ui.card>
        </div>

        <x-ui.card class="mt-6" variant="admin">
            <x-slot name="header">Hoạt động gần đây</x-slot>

            <div class="space-y-3 text-sm">
                @forelse($recentActions as $action)
                    <div class="flex items-center justify-between gap-4 border-b border-ue-border pb-3 last:border-b-0 last:pb-0">
                        <div>
                            <p class="font-semibold text-ue-text">{{ $action->action_key }}</p>
                            <p class="text-ue-text-muted">{{ $action->actor?->name ?? 'System' }}</p>
                        </div>
                        <p class="text-xs text-ue-text-muted">{{ $action->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-ue-text-muted">Chưa có hoạt động kiểm duyệt gần đây.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-app-layout>