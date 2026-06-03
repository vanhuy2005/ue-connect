<x-app-layout shell="admin">
    <x-slot name="title">Hạn mức & Dung lượng Media</x-slot>

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        {{-- Back Link --}}
        <div class="mb-6">
            <a href="{{ route('admin.media.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-ue-brand hover:underline">
                <x-ui.icon name="arrow-left" size="sm" />
                <span>Quay lại quản lý media</span>
            </a>
        </div>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-ue-text">Dung lượng & Hạn mức lưu trữ</h1>
            <p class="mt-2 text-sm text-ue-text-secondary">Giám sát tổng thể dung lượng tệp tin tải lên và tình trạng đồng bộ đám mây.</p>
        </div>

        @php
            $global = $report['global'] ?? [];
            $limits = $global['limits'] ?? [];
            
            // Format bytes helper
            $formatBytes = function($bytes) {
                return round($bytes / 1024 / 1024, 2) . ' MB';
            };

            $dailyUploadMb = round($global['daily_upload_bytes'] / 1024 / 1024, 2);
            $limitDailyMb = isset($limits['global_daily_upload_bytes']) ? round($limits['global_daily_upload_bytes'] / 1024 / 1024, 2) : 0;
            $dailyPercent = $limitDailyMb > 0 ? min(round(($dailyUploadMb / $limitDailyMb) * 100), 100) : 0;

            $syncCount = $global['cloudinary_synced_today'] ?? 0;
            $limitSync = $limits['cloudinary_daily_sync_limit'] ?? 0;
            $syncPercent = $limitSync > 0 ? min(round(($syncCount / $limitSync) * 100), 100) : 0;
        @endphp

        {{-- Top Summary Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <x-ui.card class="p-6">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                        <x-ui.icon name="database" size="md" />
                    </div>
                    <div>
                        <span class="text-xs font-bold text-ue-text-muted uppercase tracking-wider block">Tổng dung lượng</span>
                        <span class="text-2xl font-bold text-ue-text mt-1 block">{{ $formatBytes($global['storage_bytes'] ?? 0) }}</span>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-green-50 text-green-600 rounded-xl">
                        <x-ui.icon name="upload" size="md" />
                    </div>
                    <div>
                        <span class="text-xs font-bold text-ue-text-muted uppercase tracking-wider block">Lượt upload hôm nay</span>
                        <span class="text-2xl font-bold text-ue-text mt-1 block">{{ number_format($global['daily_upload_count'] ?? 0) }}</span>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                        <x-ui.icon name="refresh-cw" size="md" />
                    </div>
                    <div>
                        <span class="text-xs font-bold text-ue-text-muted uppercase tracking-wider block">Cloudinary Synced</span>
                        <span class="text-2xl font-bold text-ue-text mt-1 block">{{ number_format($syncCount) }} / {{ number_format($limitSync) }}</span>
                    </div>
                </div>
            </x-ui.card>
        </div>

        {{-- Progress bars / limit details --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <x-ui.card class="p-6">
                <h2 class="text-base font-bold text-ue-text mb-4">Hạn mức Upload hệ thống (Hàng ngày)</h2>
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1.5 font-semibold">
                        <span class="text-ue-text">Dung lượng tải lên hôm nay</span>
                        <span class="text-ue-brand">{{ $dailyUploadMb }} MB / {{ $limitDailyMb }} MB</span>
                    </div>
                    <div class="w-full bg-ue-border rounded-full h-3 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-300 {{ $dailyPercent > 80 ? 'bg-red-500' : 'bg-ue-brand' }}" style="width: {{ $dailyPercent }}%"></div>
                    </div>
                    <div class="flex justify-between text-2xs text-ue-text-muted mt-1.5 font-medium">
                        <span>Đã sử dụng {{ $dailyPercent }}%</span>
                        <span>Giới hạn: {{ $limitDailyMb }} MB</span>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6">
                <h2 class="text-base font-bold text-ue-text mb-4">Đồng bộ đám mây (Cloudinary Daily Sync)</h2>
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1.5 font-semibold">
                        <span class="text-ue-text">Số tệp đồng bộ hôm nay</span>
                        <span class="text-indigo-600">{{ $syncCount }} / {{ $limitSync }}</span>
                    </div>
                    <div class="w-full bg-ue-border rounded-full h-3 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-300 {{ $syncPercent > 80 ? 'bg-red-500' : 'bg-indigo-600' }}" style="width: {{ $syncPercent }}%"></div>
                    </div>
                    <div class="flex justify-between text-2xs text-ue-text-muted mt-1.5 font-medium">
                        <span>Đã đồng bộ {{ $syncPercent }}%</span>
                        <span>Giới hạn: {{ $limitSync }} tệp</span>
                    </div>
                </div>
            </x-ui.card>
        </div>

        {{-- Top Users Table --}}
        <x-ui.card class="p-6">
            <h2 class="text-base font-bold text-ue-text mb-4 border-b border-ue-border pb-3">Người dùng tải lên nhiều nhất hôm nay (Top Uploaders)</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="text-xs font-bold text-ue-text-muted uppercase">
                        <tr>
                            <th scope="col" class="py-3">User ID</th>
                            <th scope="col" class="py-3">Số lượng tệp tải</th>
                            <th scope="col" class="py-3">Tổng dung lượng tải lên</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ue-border">
                        @forelse ($report['top_users_today'] ?? [] as $row)
                            <tr class="hover:bg-ue-surface-hover transition-colors">
                                <td class="py-3.5 font-semibold text-ue-brand">
                                    <a href="{{ route('admin.users.show', ['user' => $row->user_id]) }}" class="hover:underline">
                                        User #{{ $row->user_id }}
                                    </a>
                                </td>
                                <td class="py-3.5 text-ue-text">{{ number_format($row->upload_count) }} tệp</td>
                                <td class="py-3.5 text-ue-text font-medium">{{ $formatBytes($row->upload_bytes) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-ue-text-muted text-xs">
                                    Không có hoạt động upload nào trong hôm nay.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
