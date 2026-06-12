<footer class="bg-ue-neutral-950 text-ue-neutral-400 py-12 border-t border-ue-neutral-900" role="contentinfo">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 lg:gap-12 mb-12">
            {{-- Column 1: Brand Info --}}
            <div class="md:col-span-2 font-medium">
                <div class="flex items-center gap-2 mb-4">
                    <x-brand.logo variant="mark" size="md" />
                    <span class="text-lg font-bold text-white tracking-tight">UEConnect</span>
                </div>
                <p class="text-sm leading-relaxed mb-6 max-w-sm">
                    Mạng kết nối và hỗ trợ cộng đồng sinh viên, giảng viên và cựu sinh viên trường Đại học Sư phạm Thành phố Hồ Chí Minh (HCMUE). Môi trường giao lưu học thuật và định hướng nghề nghiệp xác thực, an toàn.
                </p>
                <div class="text-xs text-ue-neutral-500">
                    <p class="mb-1">© {{ date('Y') }} UEConnect. Bảo lưu mọi quyền.</p>
                </div>
            </div>

            {{-- Column 2: Quick Links --}}
            <div>
                <h3 class="text-xs font-semibold text-white tracking-wider uppercase mb-4">Liên kết nhanh</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="#hero" class="hover:text-white transition-colors">Giới thiệu</a></li>
                    <li><a href="#features" class="hover:text-white transition-colors">Tính năng chính</a></li>
                    <li><a href="#verification-trust" class="hover:text-white transition-colors">Quy trình xác thực</a></li>
                    <li><a href="#safety" class="hover:text-white transition-colors">Bảo mật & Quyền riêng tư</a></li>
                </ul>
            </div>

            {{-- Column 3: Contact & Support --}}
            <div>
                <h3 class="text-xs font-semibold text-white tracking-wider uppercase mb-4">Hỗ trợ & Liên hệ</h3>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start gap-2">
                        <x-ui.icon name="map-pin" size="xs" class="mt-1 flex-shrink-0 text-ue-neutral-500" />
                        <span>280 An Dương Vương, Phường 4, Quận 5, TP. Hồ Chí Minh</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-ui.icon name="mail" size="xs" class="flex-shrink-0 text-ue-neutral-500" />
                        <a href="mailto:support@hcmue.edu.vn" class="hover:text-white transition-colors">support@hcmue.edu.vn</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-ui.icon name="phone" size="xs" class="flex-shrink-0 text-ue-neutral-500" />
                        <span>(028) 3835 2020</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="pt-8 border-t border-ue-neutral-900 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs">
            <div class="flex flex-wrap gap-x-6 gap-y-2">
                <a href="#" class="hover:text-white transition-colors">Điều khoản dịch vụ</a>
                <a href="#" class="hover:text-white transition-colors">Chính sách bảo mật</a>
                <a href="#" class="hover:text-white transition-colors">Tiêu chuẩn cộng đồng</a>
            </div>

        </div>
    </div>
</footer>
