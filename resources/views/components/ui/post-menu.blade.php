@props([
    'post',
    'currentUser',
    'isOwner' => false,
    'isAdmin' => false,
    'isSaved' => false,
])

@php
    $menuId = "post-menu-" . $post->id;
    $sheetId = "post-sheet-" . $post->id;
    $authorProfileUrl = route('profile.show', $post->user);
@endphp

<div class="relative flex items-center">
    {{-- Trigger for Desktop Popover --}}
    <button
        type="button"
        data-ue-menu-trigger="{{ $menuId }}"
        aria-haspopup="true"
        aria-expanded="false"
        aria-label="Tùy chọn bài viết"
        class="hidden sm:inline-flex items-center justify-center w-8 h-8 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors focus:outline-none ue-focus-ring"
    >
        <x-ui.icon name="more-horizontal" size="sm" />
    </button>

    {{-- Trigger for Mobile Bottom Sheet --}}
    <button
        type="button"
        data-ue-sheet-trigger="{{ $sheetId }}"
        aria-label="Tùy chọn bài viết di động"
        class="inline-flex sm:hidden items-center justify-center w-8 h-8 text-slate-400 hover:text-slate-600 rounded-full focus:outline-none"
    >
        <x-ui.icon name="more-horizontal" size="sm" />
    </button>

    {{-- Desktop Popover Menu --}}
    <div
        data-ue-menu="{{ $menuId }}"
        class="ue-popover right-0 mt-8 w-60"
        role="menu"
    >
        <div class="flex flex-col gap-0.5">
            <a
                href="{{ $authorProfileUrl }}"
                class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-ue-brand transition-colors text-left rounded-lg"
                role="menuitem"
            >
                <x-ui.icon name="user" size="xs" class="text-slate-400" />
                <span>Xem trang cá nhân</span>
            </a>

            {{-- Save / Unsave --}}
            <button
                type="button"
                wire:click="toggleSave({{ $post->id }})"
                class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-left rounded-lg"
                role="menuitem"
            >
                <x-ui.icon name="bookmark" size="xs" class="{{ $isSaved ? 'text-amber-600 fill-amber-600' : 'text-slate-400' }}" />
                <span class="{{ $isSaved ? 'text-amber-600 font-bold' : '' }}">
                    {{ $isSaved ? 'Bỏ lưu bài viết' : 'Lưu bài viết' }}
                </span>
            </button>

            {{-- Copy Link --}}
            <button
                type="button"
                onclick="navigator.clipboard.writeText('{{ route('posts.show', $post) }}'); if (window.UEToast) window.UEToast.show({ type: 'success', message: 'Đã sao chép liên kết vào bộ nhớ tạm.' });"
                class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-left rounded-lg border-t border-slate-100"
                role="menuitem"
            >
                <x-ui.icon name="link" size="xs" class="text-slate-400" />
                <span>Sao chép liên kết</span>
            </button>

            @if($isOwner)
                {{-- Edit --}}
                <button
                    type="button"
                    wire:click="startEdit({{ $post->id }})"
                    class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-left rounded-lg"
                    role="menuitem"
                >
                    <x-ui.icon name="edit" size="xs" class="text-slate-400" />
                    <span>Chỉnh sửa bài viết</span>
                </button>

                {{-- Delete --}}
                <button
                    type="button"
                    wire:click="openDeleteModal({{ $post->id }})"
                    class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors text-left rounded-lg"
                    role="menuitem"
                >
                    <x-ui.icon name="trash" size="xs" class="text-red-500" />
                    <span>Xóa bài viết</span>
                </button>
            @else
                {{-- Hide --}}
                <button
                    type="button"
                    wire:click="hidePost({{ $post->id }})"
                    class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-left rounded-lg"
                    role="menuitem"
                >
                    <x-ui.icon name="eye-off" size="xs" class="text-slate-400" />
                    <span>Ẩn bài viết này</span>
                </button>

                {{-- Report --}}
                <button
                    type="button"
                    wire:click="openReport({{ $post->id }})"
                    class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-yellow-50 hover:text-yellow-700 transition-colors text-left rounded-lg"
                    role="menuitem"
                >
                    <x-ui.icon name="flag" size="xs" class="text-yellow-600" />
                    <span>Báo cáo bài viết</span>
                </button>
            @endif

            {{-- Moderator options --}}
            @if($isAdmin)
                <div class="border-t border-slate-100 mt-1.5 pt-1.5">
                    <p class="px-3 py-0.5 text-[9px] font-bold text-slate-400 uppercase tracking-wider">Ban kiểm duyệt</p>
                    
                    <button
                        type="button"
                        wire:click="hidePostGlobally({{ $post->id }})"
                        class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-750 hover:bg-slate-50 transition-colors text-left rounded-lg"
                        role="menuitem"
                    >
                        <x-ui.icon name="shield" size="xs" class="text-slate-500" />
                        <span>Ẩn khỏi cộng đồng</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Mobile Bottom Sheet Menu Drawer --}}
    <div
        data-ue-sheet="{{ $sheetId }}"
        class="ue-bottom-sheet"
        aria-hidden="true"
        role="dialog"
    >
        <div class="ue-bottom-sheet__handle"></div>
        <div class="ue-bottom-sheet__header">
            <h4 class="text-xs font-bold text-slate-800 text-center">Tùy chọn bài viết</h4>
        </div>
        <div class="ue-bottom-sheet__body">
            <a
                href="{{ $authorProfileUrl }}"
                class="ue-bottom-sheet__item"
            >
                <x-ui.icon name="user" size="sm" class="text-slate-400" />
                <span>Xem trang cá nhân</span>
            </a>

            {{-- Save --}}
            <button
                type="button"
                wire:click="toggleSave({{ $post->id }})"
                onclick="if (window.closeActiveBottomSheet) window.closeActiveBottomSheet();"
                class="ue-bottom-sheet__item"
            >
                <x-ui.icon name="bookmark" size="sm" class="{{ $isSaved ? 'text-amber-600 fill-amber-600' : 'text-slate-400' }}" />
                <span class="{{ $isSaved ? 'text-amber-600 font-bold' : '' }}">
                    {{ $isSaved ? 'Bỏ lưu bài viết' : 'Lưu bài viết' }}
                </span>
            </button>

            {{-- Copy Link --}}
            <button
                type="button"
                onclick="navigator.clipboard.writeText('{{ route('posts.show', $post) }}'); if (window.UEToast) window.UEToast.show({ type: 'success', message: 'Đã sao chép liên kết vào bộ nhớ tạm.' }); if (window.closeActiveBottomSheet) window.closeActiveBottomSheet();"
                class="ue-bottom-sheet__item"
            >
                <x-ui.icon name="link" size="sm" class="text-slate-400" />
                <span>Sao chép liên kết bài viết</span>
            </button>

            @if($isOwner)
                {{-- Edit --}}
                <button
                    type="button"
                    wire:click="startEdit({{ $post->id }})"
                    onclick="if (window.closeActiveBottomSheet) window.closeActiveBottomSheet();"
                    class="ue-bottom-sheet__item"
                >
                    <x-ui.icon name="edit" size="sm" class="text-slate-400" />
                    <span>Chỉnh sửa bài viết</span>
                </button>

                {{-- Delete --}}
                <button
                    type="button"
                    wire:click="openDeleteModal({{ $post->id }})"
                    onclick="if (window.closeActiveBottomSheet) window.closeActiveBottomSheet();"
                    class="ue-bottom-sheet__item text-red-650"
                >
                    <x-ui.icon name="trash" size="sm" class="text-red-500" />
                    <span>Xóa bài viết</span>
                </button>
            @else
                {{-- Hide --}}
                <button
                    type="button"
                    wire:click="hidePost({{ $post->id }})"
                    onclick="if (window.closeActiveBottomSheet) window.closeActiveBottomSheet();"
                    class="ue-bottom-sheet__item"
                >
                    <x-ui.icon name="eye-off" size="sm" class="text-slate-400" />
                    <span>Ẩn bài viết này</span>
                </button>

                {{-- Report --}}
                <button
                    type="button"
                    wire:click="openReport({{ $post->id }})"
                    onclick="if (window.closeActiveBottomSheet) window.closeActiveBottomSheet();"
                    class="ue-bottom-sheet__item text-yellow-700 hover:bg-yellow-50/50"
                >
                    <x-ui.icon name="flag" size="sm" class="text-yellow-650" />
                    <span>Báo cáo bài viết</span>
                </button>
            @endif

            @if($isAdmin)
                <div class="border-t border-slate-100 my-2 pt-2">
                    <p class="px-3 py-1 text-[9px] font-bold text-slate-400 uppercase tracking-wider">Ban kiểm duyệt</p>
                    <button
                        type="button"
                        wire:click="hidePostGlobally({{ $post->id }})"
                        onclick="if (window.closeActiveBottomSheet) window.closeActiveBottomSheet();"
                        class="ue-bottom-sheet__item"
                    >
                        <x-ui.icon name="shield" size="sm" class="text-slate-500" />
                        <span>Ẩn khỏi cộng đồng</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
