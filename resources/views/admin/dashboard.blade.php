<x-app-layout shell="admin">
    <x-slot name="title">Tổng quan quản trị</x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <h1 class="text-2xl font-bold text-ue-text">Tổng quan quản trị</h1>
            <p class="mt-1 text-sm text-ue-text-muted">Trung tâm điều hành và kiểm duyệt thông tin của UEConnect.</p>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Verification card -->
                <div class="bg-ue-surface border border-ue-border rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="p-3 bg-ue-brand-soft rounded-xl text-ue-brand">
                                <x-ui.icon name="shield" size="lg" />
                            </span>
                            <h2 class="text-lg font-bold text-ue-text">Duyệt xác thực</h2>
                        </div>
                        <p class="mt-4 text-sm text-ue-text-muted">Quản lý và duyệt danh tính của sinh viên, cựu sinh viên và cố vấn.</p>
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('admin.verifications.queue') }}" class="w-full flex items-center justify-center gap-2 h-10 px-4 rounded-lg font-semibold text-sm bg-ue-brand hover:bg-ue-brand-hover text-white transition-colors duration-sm shadow-sm">
                            <span>Đi tới danh sách duyệt</span>
                            <x-ui.icon name="arrow-right" size="sm" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
