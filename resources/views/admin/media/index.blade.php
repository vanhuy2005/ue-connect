<x-app-layout shell="admin">
    <x-slot name="title">Quản lý Media</x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        {{-- Header Section --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-ue-text">Quản lý Media</h1>
                <p class="mt-2 text-sm text-ue-text-secondary">Quản lý tất cả tệp tin đa phương tiện tải lên bởi người dùng.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.media.usage') }}" class="px-4 py-2 rounded-lg bg-white border border-ue-border hover:bg-ue-surface-hover text-ue-text font-semibold text-sm transition-colors flex items-center gap-2">
                    <x-ui.icon name="bar-chart-3" size="sm" />
                    <span>Dung lượng sử dụng</span>
                </a>
            </div>
        </div>

        {{-- Status Notification --}}
        @if (session('status'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium flex items-center gap-2">
                <x-ui.icon name="check-circle" size="sm" class="text-green-600" />
                <div>{!! session('status') !!}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium">
                @foreach ($errors->all() as $error)
                    <div class="flex items-center gap-2">
                        <x-ui.icon name="alert-triangle" size="sm" class="text-red-600" />
                        <div>{{ $error }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Quick Operations Card --}}
        <x-ui.card class="mb-8 p-6">
            <h2 class="text-sm font-bold uppercase tracking-wider text-ue-text-muted mb-4">Thao tác hệ thống</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                <form action="{{ route('admin.media.health') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 rounded-lg border border-ue-border bg-white text-ue-text hover:bg-ue-surface-hover text-xs font-bold transition-all duration-150 flex items-center justify-center gap-2">
                        <x-ui.icon name="heart" size="sm" class="text-red-500" />
                        Kiểm tra lưu trữ
                    </button>
                </form>

                <form action="{{ route('admin.media.quota') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 rounded-lg border border-ue-border bg-white text-ue-text hover:bg-ue-surface-hover text-xs font-bold transition-all duration-150 flex items-center justify-center gap-2">
                        <x-ui.icon name="bar-chart" size="sm" class="text-blue-500" />
                        Cập nhật hạn mức
                    </button>
                </form>

                <form action="{{ route('admin.media.cloudinary-sync') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 rounded-lg border border-ue-border bg-white text-ue-text hover:bg-ue-surface-hover text-xs font-bold transition-all duration-150 flex items-center justify-center gap-2">
                        <x-ui.icon name="refresh-cw" size="sm" class="text-indigo-500" />
                        Đồng bộ Cloudinary
                    </button>
                </form>

                <form action="{{ route('admin.media.cleanup-temporary') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 rounded-lg border border-ue-border bg-white text-ue-text hover:bg-ue-surface-hover text-xs font-bold transition-all duration-150 flex items-center justify-center gap-2">
                        <x-ui.icon name="trash" size="sm" class="text-orange-500" />
                        Dọn tệp tạm
                    </button>
                </form>

                <form action="{{ route('admin.media.cleanup-orphaned') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 rounded-lg border border-ue-border bg-white text-ue-text hover:bg-ue-surface-hover text-xs font-bold transition-all duration-150 flex items-center justify-center gap-2">
                        <x-ui.icon name="shield-alert" size="sm" class="text-yellow-600" />
                        Dọn tệp mồ côi
                    </button>
                </form>
            </div>
        </x-ui.card>

        {{-- Filter Bar --}}
        <x-ui.card class="mb-6 p-6">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                <div>
                    <x-ui.label for="search" class="text-xs">Tìm kiếm</x-ui.label>
                    <x-ui.input id="search" name="search" value="{{ request('search') }}" placeholder="Tên tệp, UUID..." class="mt-1 h-9 text-xs" />
                </div>
                <div>
                    <x-ui.label for="collection" class="text-xs">Danh mục</x-ui.label>
                    <x-ui.select id="collection" name="collection" class="mt-1 h-9 text-xs py-1">
                        <option value="">-- Tất cả --</option>
                        <option value="avatar" {{ request('collection') === 'avatar' ? 'selected' : '' }}>Avatar</option>
                        <option value="profile_cover" {{ request('collection') === 'profile_cover' ? 'selected' : '' }}>Profile Cover</option>
                        <option value="post_image" {{ request('collection') === 'post_image' ? 'selected' : '' }}>Post Image</option>
                        <option value="message_attachment" {{ request('collection') === 'message_attachment' ? 'selected' : '' }}>Message Attachment</option>
                        <option value="verification_evidence" {{ request('collection') === 'verification_evidence' ? 'selected' : '' }}>Verification Evidence</option>
                    </x-ui.select>
                </div>
                <div>
                    <x-ui.label for="visibility" class="text-xs">Chế độ xem</x-ui.label>
                    <x-ui.select id="visibility" name="visibility" class="mt-1 h-9 text-xs py-1">
                        <option value="">-- Tất cả --</option>
                        <option value="public" {{ request('visibility') === 'public' ? 'selected' : '' }}>Công khai (Public)</option>
                        <option value="private" {{ request('visibility') === 'private' ? 'selected' : '' }}>Riêng tư (Private)</option>
                    </x-ui.select>
                </div>
                <div>
                    <x-ui.label for="status" class="text-xs">Trạng thái</x-ui.label>
                    <x-ui.select id="status" name="status" class="mt-1 h-9 text-xs py-1">
                        <option value="">-- Tất cả --</option>
                        <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Sẵn sàng (Ready)</option>
                        <option value="temporary" {{ request('status') === 'temporary' ? 'selected' : '' }}>Tạm thời (Temporary)</option>
                        <option value="quarantined" {{ request('status') === 'quarantined' ? 'selected' : '' }}>Cách ly (Quarantined)</option>
                    </x-ui.select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-ue-brand text-white font-semibold text-xs h-9 flex-1">Lọc</button>
                    <a href="{{ route('admin.media.index') }}" class="px-4 py-2 rounded-lg border border-ue-border text-ue-text text-xs h-9 flex items-center justify-center">Xóa</a>
                </div>
            </form>
        </x-ui.card>

        {{-- Table Card --}}
        <x-ui.card padding="none" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ue-border text-sm text-left">
                    <thead class="bg-ue-surface-subtle text-xs font-bold text-ue-text-muted uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-4">Tên tệp tin</th>
                            <th scope="col" class="px-6 py-4">Người tải</th>
                            <th scope="col" class="px-6 py-4">Kích thước / Định dạng</th>
                            <th scope="col" class="px-6 py-4">Danh mục</th>
                            <th scope="col" class="px-6 py-4">Trạng thái</th>
                            <th scope="col" class="px-6 py-4">Ngày tải</th>
                            <th scope="col" class="px-6 py-4 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-ue-surface divide-y divide-ue-border">
                        @forelse ($media as $item)
                            <tr class="hover:bg-ue-surface-hover transition-colors">
                                <td class="px-6 py-4 max-w-xs">
                                    <div class="font-semibold text-ue-text truncate" title="{{ $item->original_filename }}">{{ $item->original_filename }}</div>
                                    <div class="text-2xs font-mono text-ue-text-muted mt-0.5 truncate">{{ $item->uuid }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($item->user)
                                        <div class="font-medium text-ue-text">{{ $item->user->name }}</div>
                                        <div class="text-xs text-ue-text-muted">ID: {{ $item->user_id }}</div>
                                    @else
                                        <span class="text-xs text-ue-text-disabled">Unknown</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-ue-text">{{ round($item->size_bytes / 1024 / 1024, 2) }} MB</div>
                                    <div class="text-xs text-ue-text-muted uppercase">{{ $item->extension }} ({{ $item->mime_type }})</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-ue-text capitalize">{{ str_replace('_', ' ', $item->collection) }}</div>
                                    <x-ui.badge variant="{{ $item->visibility === 'public' ? 'success' : 'warning' }}" class="text-[10px] mt-1 py-0.5">
                                        {{ $item->visibility === 'public' ? 'Công khai' : 'Riêng tư' }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-ui.badge variant="{{ $item->status === 'ready' ? 'success' : ($item->status === 'quarantined' ? 'danger' : 'neutral') }}">
                                        {{ $item->status === 'ready' ? 'Sẵn sàng' : ($item->status === 'quarantined' ? 'Đã cách ly' : $item->status) }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-ue-text-muted">
                                    {{ $item->created_at->format('H:i d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.media.show', $item) }}" class="px-2.5 py-1.5 rounded-lg border border-ue-border hover:bg-ue-surface-hover text-ue-text transition-colors">
                                            Chi tiết
                                        </a>
                                        @if ($item->status !== 'quarantined')
                                            <form action="{{ route('admin.media.quarantine', $item) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn chuyển file này vào khu vực cách ly?');">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                                    Cách ly
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.media.delete', $item) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa file này?');">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-ue-border hover:bg-red-50 hover:text-red-600 transition-colors">
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-ue-text-muted">
                                    <div class="flex flex-col items-center justify-center">
                                        <x-ui.icon name="image" size="lg" class="text-ue-text-disabled mb-2" />
                                        <div class="font-bold">Không tìm thấy tệp media nào</div>
                                        <div class="text-xs mt-1">Không có kết quả nào phù hợp với bộ lọc tìm kiếm của bạn.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-ue-surface border-t border-ue-border px-6 py-4">
                {{ $media->links() }}
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
