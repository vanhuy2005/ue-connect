<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    // Account component has no state modifications right now, just view
}; ?>

<div class="space-y-6">
    <div>
        <h2 class="text-sm font-bold text-slate-800">Trung tâm tài khoản</h2>
        <p class="text-xxs text-slate-400 font-medium mt-0.5">Quản lý các thông tin định danh xác thực của bạn trên UEConnect.</p>
    </div>

    <div class="bg-white border border-slate-150 rounded-2xl p-5 space-y-4 shadow-2xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xxs text-slate-600 font-medium">
            <div class="space-y-1">
                <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Họ và tên xác thực</span>
                <span class="text-slate-850 font-bold">{{ Auth::user()->name }}</span>
            </div>
            <div class="space-y-1">
                <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Email học đường (HCMUE)</span>
                <span class="text-slate-850 font-bold">{{ Auth::user()->email }}</span>
            </div>
            <div class="space-y-1">
                <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Mã số tài khoản</span>
                <span class="text-slate-850 font-bold">#{{ Auth::user()->id }}</span>
            </div>
            <div class="space-y-1">
                <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Trạng thái tài khoản</span>
                <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 font-bold border border-emerald-100 px-2 py-0.5 rounded-md text-[10px]">
                    Active / Xác thực
                </span>
            </div>
        </div>

        <div class="border-t border-slate-100 pt-4 mt-2">
            <p class="text-[10px] text-slate-400 leading-normal">
                Các thông tin học đường cốt lõi (như Họ tên, MSSV, Khoa/Ngành) được lấy trực tiếp từ hệ thống xác thực tài khoản HCMUE. Nếu có sự sai lệch thông tin, vui lòng <a wire:navigate href="{{ route('settings', ['section' => 'support']) }}" class="text-ue-brand font-semibold underline">Gửi yêu cầu hỗ trợ</a>.
            </p>
        </div>
    </div>
</div>
