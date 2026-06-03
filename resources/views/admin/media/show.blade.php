<x-app-layout shell="admin">
    <x-slot name="title">Chi tiết Media: {{ $media->original_filename }}</x-slot>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        {{-- Back Link --}}
        <div class="mb-6">
            <a href="{{ route('admin.media.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-ue-brand hover:underline">
                <x-ui.icon name="arrow-left" size="sm" />
                <span>Quay lại danh sách</span>
            </a>
        </div>

        {{-- Main Details Card --}}
        <x-ui.card class="mb-8 p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 border-b border-ue-border pb-6 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-ue-text">{{ $media->original_filename }}</h1>
                    <p class="text-xs font-mono text-ue-text-muted mt-1">UUID: {{ $media->uuid }}</p>
                </div>
                <div class="flex gap-2">
                    @if ($media->status !== 'quarantined')
                        <form action="{{ route('admin.media.quarantine', $media) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn cách ly file này?');">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 font-semibold text-sm transition-colors flex items-center gap-2">
                                <x-ui.icon name="shield-alert" size="sm" />
                                Cách ly file
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('admin.media.delete', $media) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa file này?');">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-lg border border-ue-border hover:bg-red-50 hover:text-red-600 font-semibold text-sm transition-colors flex items-center gap-2">
                            <x-ui.icon name="trash" size="sm" />
                            Xóa file
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 text-sm">
                <div>
                    <span class="text-xs font-bold text-ue-text-muted uppercase tracking-wider block">Người tải lên</span>
                    @if ($media->user)
                        <div class="mt-1 font-semibold text-ue-text">{{ $media->user->name }}</div>
                        <div class="text-xs text-ue-text-muted">Email: {{ $media->user->email }} | ID: {{ $media->user_id }}</div>
                    @else
                        <span class="text-ue-text-disabled block mt-1">Unknown</span>
                    @endif
                </div>

                <div>
                    <span class="text-xs font-bold text-ue-text-muted uppercase tracking-wider block">Quan hệ liên kết</span>
                    <div class="mt-1 font-semibold text-ue-text">
                        @if ($media->mediable_type)
                            <span class="font-mono text-xs">{{ $media->mediable_type }}</span>
                            <span class="text-xs text-ue-text-muted">(ID: {{ $media->mediable_id }})</span>
                        @else
                            <span class="text-ue-text-muted text-xs">Không có (Tệp tạm thời)</span>
                        @endif
                    </div>
                </div>

                <div>
                    <span class="text-xs font-bold text-ue-text-muted uppercase tracking-wider block">Trạng thái</span>
                    <div class="mt-1 flex items-center gap-2">
                        <x-ui.badge variant="{{ $media->status === 'ready' ? 'success' : ($media->status === 'quarantined' ? 'danger' : 'neutral') }}">
                            {{ $media->status === 'ready' ? 'Sẵn sàng (Ready)' : ($media->status === 'quarantined' ? 'Đã cách ly' : $media->status) }}
                        </x-ui.badge>
                        <x-ui.badge variant="{{ $media->visibility === 'public' ? 'success' : 'warning' }}">
                            {{ $media->visibility === 'public' ? 'Công khai (Public)' : 'Riêng tư (Private)' }}
                        </x-ui.badge>
                    </div>
                </div>

                <div>
                    <span class="text-xs font-bold text-ue-text-muted uppercase tracking-wider block">Định dạng & Dung lượng</span>
                    <div class="mt-1 text-ue-text font-semibold">
                        {{ round($media->size_bytes / 1024 / 1024, 2) }} MB ({{ number_format($media->size_bytes) }} bytes)
                    </div>
                    <div class="text-xs text-ue-text-muted">MIME: {{ $media->mime_type }} | Đuôi: {{ $media->extension }}</div>
                </div>

                <div>
                    <span class="text-xs font-bold text-ue-text-muted uppercase tracking-wider block">Chi tiết lưu trữ</span>
                    <div class="mt-1 text-ue-text">
                        Disk chính: <span class="font-semibold">{{ $media->primary_disk }}</span><br>
                        Chiến lược: <span class="font-semibold">{{ $media->storage_strategy }}</span><br>
                        Nhà cung cấp: <span class="font-semibold">{{ $media->primary_provider }}</span>
                    </div>
                </div>

                <div>
                    <span class="text-xs font-bold text-ue-text-muted uppercase tracking-wider block">Đường dẫn tệp</span>
                    <div class="mt-1 font-mono text-xs text-ue-text break-all bg-ue-surface-subtle p-2 rounded-lg border border-ue-border">
                        {{ $media->primary_path }}
                    </div>
                </div>
            </div>

            @if ($media->isPrivate())
                <div class="mt-6 p-4 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-800 text-xs flex items-center gap-2">
                    <x-ui.icon name="shield" size="sm" class="text-yellow-600 flex-shrink-0" />
                    <div>
                        <strong>Bảo vệ tệp riêng tư:</strong> Tệp tin này được đánh dấu là riêng tư.
                        Đường dẫn URL raw của tệp sẽ không được hiển thị trực tiếp. Mọi yêu cầu xem hoặc tải về đều phải đi qua hệ thống kiểm tra phân quyền.
                    </div>
                </div>
            @endif
        </x-ui.card>

        {{-- Variants Block --}}
        <x-ui.card class="p-6">
            <h2 class="text-lg font-bold text-ue-text mb-4 border-b border-ue-border pb-3">Các phiên bản phái sinh (Media Variants)</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="text-xs font-bold text-ue-text-muted uppercase">
                        <tr>
                            <th scope="col" class="py-2">Tên phiên bản</th>
                            <th scope="col" class="py-2">Định dạng</th>
                            <th scope="col" class="py-2">Kích thước</th>
                            <th scope="col" class="py-2">Disk</th>
                            <th scope="col" class="py-2">Cloudinary Sync</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ue-border">
                        @forelse ($media->variants as $variant)
                            <tr>
                                <td class="py-3 font-semibold text-ue-text">{{ $variant->variant_name }}</td>
                                <td class="py-3 text-ue-text-muted">{{ $variant->mime_type }}</td>
                                <td class="py-3 text-ue-text">{{ round($variant->size_bytes / 1024, 2) }} KB</td>
                                <td class="py-3 font-mono text-xs text-ue-text-muted">{{ $variant->disk }}</td>
                                <td class="py-3">
                                    <div class="flex items-center gap-1.5">
                                        <x-ui.badge variant="{{ $variant->cloudinary_sync_status === 'synced' ? 'success' : ($variant->cloudinary_sync_status === 'failed' ? 'danger' : 'neutral') }}">
                                            {{ $variant->cloudinary_sync_status }}
                                        </x-ui.badge>
                                        @if ($variant->cloudinary_error_message)
                                            <span class="text-2xs text-red-500" title="{{ $variant->cloudinary_error_message }}">
                                                ({{ Str::limit($variant->cloudinary_error_message, 20) }})
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-ue-text-muted text-xs">
                                    Không có phiên bản phái sinh nào cho tệp tin này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
