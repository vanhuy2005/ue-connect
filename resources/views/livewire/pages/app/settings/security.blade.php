<?php

use Livewire\Volt\Component;

new class extends Component
{
    // UI only for now
}; ?>

<div class="space-y-6">
    <div>
        <h2 class="text-sm font-bold text-slate-800">Bảo mật tài khoản</h2>
        <p class="text-xxs text-slate-400 font-medium mt-0.5">Tăng cường lớp bảo vệ cho tài khoản học đường của bạn.</p>
    </div>

    <div class="bg-white border border-slate-150 rounded-2xl p-5 shadow-2xs space-y-6">
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div class="flex-1 space-y-0.5">
                    <span class="text-xxs font-bold text-slate-800 block">Xác thực hai yếu tố (2FA)</span>
                    <span class="text-[10px] text-slate-400 block">Sử dụng ứng dụng Authenticator (Google/Microsoft) để lấy mã đăng nhập.</span>
                </div>
                <button type="button" disabled class="bg-slate-50 border border-slate-200 text-slate-400 text-[10px] font-bold px-3 py-1.5 rounded-lg cursor-not-allowed">Sắp ra mắt</button>
            </div>
        </div>
        
        <hr class="border-slate-100">

        <div class="space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div class="flex-1 space-y-0.5">
                    <span class="text-xxs font-bold text-slate-800 block">Đổi mật khẩu</span>
                    <span class="text-[10px] text-slate-400 block">Mật khẩu của bạn được quản lý bởi hệ thống định danh HCMUE (SSO). Vui lòng truy cập trang quản lý của trường để thay đổi.</span>
                </div>
                <a href="#" target="_blank" class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-[10px] font-bold px-3 py-1.5 rounded-lg transition-colors shadow-xs">Cổng thông tin HCMUE</a>
            </div>
        </div>
    </div>
</div>
