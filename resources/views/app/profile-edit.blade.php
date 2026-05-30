<x-app-layout>
    <x-slot name="title">Chỉnh sửa hồ sơ</x-slot>

    <div class="py-12 px-4 max-w-4xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
            <a href="{{ route('profile') }}" class="text-slate-500 hover:text-slate-700 transition-colors p-1" aria-label="Quay lại hồ sơ">
                <x-ui.icon name="arrow-left" size="sm" />
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-800 tracking-tight">Cài đặt tài khoản</h1>
                <p class="text-xs text-slate-400 font-medium">Quản lý thông tin cá nhân, cài đặt bảo mật và thông tin xác thực.</p>
            </div>
        </div>

        {{-- Identity verification notice --}}
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex gap-3 text-xxs font-semibold text-slate-500">
            <x-ui.icon name="shield-check" size="sm" class="text-ue-brand flex-shrink-0" />
            <div class="space-y-1">
                <p class="text-slate-850 font-bold">Tài khoản đã được xác thực danh tính học đường</p>
                <p class="leading-relaxed">Các thông tin định danh như Họ và tên, Mã số SV/CB, Khoa và Ngành học được đồng bộ từ dữ liệu gốc và chỉ có thể thay đổi thông qua quy trình xét duyệt xác thực.</p>
            </div>
        </div>

        {{-- Personal Info Form --}}
        <div class="p-5 bg-white border border-slate-150 rounded-2xl shadow-2xs">
            <h2 class="text-xs font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4">Thông tin cá nhân</h2>
            <livewire:profile.update-profile-information-form />
        </div>

        {{-- Password Form --}}
        <div class="p-5 bg-white border border-slate-150 rounded-2xl shadow-2xs">
            <h2 class="text-xs font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4">Thay đổi mật khẩu</h2>
            <livewire:profile.update-password-form />
        </div>

        {{-- Delete Account Form --}}
        <div class="p-5 bg-white border border-slate-150 rounded-2xl shadow-2xs border-red-100">
            <h2 class="text-xs font-bold text-red-600 border-b border-red-50 pb-2 mb-4">Vùng nguy hiểm</h2>
            <livewire:profile.delete-user-form />
        </div>
    </div>
</x-app-layout>
