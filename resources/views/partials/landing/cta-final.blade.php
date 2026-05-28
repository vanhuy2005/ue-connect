{{-- ============================================================ --}}
{{-- FINAL CALL TO ACTION --}}
{{-- ============================================================ --}}
<section class="py-16 sm:py-24 bg-ue-neutral-900 text-white relative overflow-hidden" aria-labelledby="cta-heading">
    {{-- Decorative background graphics --}}
    <div class="absolute inset-0 bg-gradient-to-br from-ue-blue-900/40 to-transparent pointer-events-none"></div>
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-ue-brand/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8 relative z-10">
        <h2 id="cta-heading" class="text-3xl sm:text-4xl font-extrabold tracking-tight">
            Sẵn sàng kết nối cùng Cộng đồng Sư phạm?
        </h2>
        <p class="mt-4 text-lg text-ue-neutral-300 max-w-2xl mx-auto leading-relaxed">
            Gia nhập UEConnect ngay hôm nay để không bỏ lỡ các cơ hội học tập, định hướng nghề nghiệp và kết nối bền vững cùng các thế hệ HCMUEer.
        </p>
        
        <div class="mt-10 flex flex-col sm:flex-row justify-center items-center gap-4">
            @if(auth()->check())
                <x-ui.button
                    href="{{ route('dashboard') }}"
                    variant="inverse"
                    size="lg"
                    icon="arrow-right"
                    icon-position="right"
                >
                    Vào Bảng điều khiển
                </x-ui.button>
            @else
                <x-ui.button
                    href="{{ route('login') }}"
                    variant="inverse"
                    size="lg"
                    icon="microsoft"
                    class="w-full sm:w-auto"
                >
                    Đăng nhập bằng Entra ID (Office 365)
                </x-ui.button>
                <x-ui.button
                    href="#verification-trust"
                    variant="ghost"
                    size="lg"
                    class="border border-ue-neutral-700 text-white hover:bg-ue-neutral-800 hover:text-white active:bg-ue-neutral-700 active:text-white w-full sm:w-auto"
                >
                    Tìm hiểu quy trình xác thực
                </x-ui.button>
            @endif
        </div>

        <p class="mt-6 text-xs text-ue-neutral-400">
            Dành riêng cho sinh viên, giảng viên và cựu sinh viên Đại học Sư phạm TP.HCM.
        </p>
    </div>
</section>
