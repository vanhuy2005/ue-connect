<?php

use Livewire\Volt\Component;

new class extends Component
{
    // Informational only for now
}; ?>

<div class="space-y-6">
    <div>
        <h2 class="text-sm font-bold text-slate-800">Dữ liệu & Bảo mật</h2>
        <p class="text-xxs text-slate-400 font-medium mt-0.5">Quản lý cách chúng tôi lưu trữ và sử dụng dữ liệu của bạn.</p>
    </div>

    <div class="bg-white border border-slate-150 rounded-2xl p-5 shadow-2xs space-y-6">
        <div class="space-y-4">
            <h3 class="text-xxs font-bold text-slate-850 flex items-center gap-1.5 uppercase tracking-wider text-[9px]">Tải xuống dữ liệu (Export)</h3>
            <div class="flex items-center justify-between gap-4">
                <div class="flex-1 space-y-0.5">
                    <span class="text-xxs font-bold text-slate-800 block">Yêu cầu bản sao dữ liệu</span>
                    <span class="text-[10px] text-slate-400 block">Bạn có quyền yêu cầu trích xuất toàn bộ dữ liệu cá nhân, bài viết và tương tác của mình trên UEConnect. Quá trình có thể mất tới 48 giờ.</span>
                </div>
                <button type="button" disabled class="bg-slate-50 border border-slate-200 text-slate-400 text-[10px] font-bold px-3 py-1.5 rounded-lg cursor-not-allowed">Đang phát triển</button>
            </div>
        </div>
        
        <hr class="border-slate-100">

        <div class="space-y-4">
            <h3 class="text-xxs font-bold text-slate-850 flex items-center gap-1.5 uppercase tracking-wider text-[9px]">Lịch sử hoạt động</h3>
            <div class="flex items-center justify-between gap-4">
                <div class="flex-1 space-y-0.5">
                    <span class="text-xxs font-bold text-slate-800 block">Nhật ký truy cập (Audit Log)</span>
                    <span class="text-[10px] text-slate-400 block">Xem lại lịch sử đăng nhập và các thay đổi quan trọng trên tài khoản.</span>
                </div>
                <button type="button" disabled class="bg-slate-50 border border-slate-200 text-slate-400 text-[10px] font-bold px-3 py-1.5 rounded-lg cursor-not-allowed">Đang phát triển</button>
            </div>
        </div>

        <hr class="border-slate-100">

        <div class="space-y-4">
            <h3 class="text-xxs font-bold text-red-600 flex items-center gap-1.5 uppercase tracking-wider text-[9px]">Vùng nguy hiểm</h3>
            <div class="flex items-center justify-between gap-4 bg-red-50/50 p-4 rounded-xl border border-red-100">
                <div class="flex-1 space-y-0.5">
                    <span class="text-xxs font-bold text-red-700 block">Xóa tài khoản vĩnh viễn</span>
                    <span class="text-[10px] text-red-500 block">Do tính chất liên kết học đường, việc xóa tài khoản cần được phê duyệt bởi BQT để tránh mất mát dữ liệu học tập. Vui lòng gửi yêu cầu hỗ trợ.</span>
                </div>
                <a wire:navigate href="{{ route('settings', ['section' => 'support']) }}" class="bg-white border border-red-200 text-red-600 hover:bg-red-50 text-[10px] font-bold px-3 py-1.5 rounded-lg transition-colors shadow-xs">Liên hệ BQT</a>
            </div>
        </div>
    </div>
</div>
