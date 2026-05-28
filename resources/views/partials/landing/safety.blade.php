{{-- ============================================================ --}}
{{-- SAFETY & PRIVACY SECTION --}}
{{-- ============================================================ --}}
<section id="safety" class="py-16 sm:py-24 bg-white" aria-labelledby="safety-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center mb-16">
            <span class="text-xs font-semibold tracking-widest text-ue-brand uppercase block mb-3">An toàn & Bảo mật</span>
            <h2 id="safety-heading" class="text-3xl sm:text-4xl font-bold tracking-tight text-ue-neutral-900">
                Cam kết môi trường an toàn và bảo mật thông tin
            </h2>
            <p class="mt-4 max-w-2xl text-base text-ue-text-secondary lg:mx-auto">
                Tại UEConnect, sự an toàn và quyền riêng tư của bạn là ưu tiên hàng đầu. Chúng tôi thiết lập các quy chuẩn bảo mật nghiêm ngặt nhất.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
            {{-- Column 1: Verification Privacy --}}
            <div class="bg-ue-neutral-25 p-8 rounded-2xl border border-ue-border flex flex-col justify-between">
                <div>
                    <div class="inline-flex items-center justify-center p-3 bg-ue-blue-100 text-ue-brand rounded-xl mb-6">
                        <x-ui.icon name="shield" size="lg" />
                    </div>
                    <h3 class="text-xl font-bold text-ue-neutral-900 mb-4">Bảo mật thông tin xác thực</h3>
                    <p class="text-sm text-ue-text-secondary leading-relaxed mb-6">
                        Mọi tài liệu minh chứng bạn cung cấp (thẻ sinh viên, bảng điểm, quyết định) đều được mã hóa lưu trữ ở mức độ cao nhất. Chúng tôi chỉ sử dụng cho mục đích đối chiếu và sẽ <strong>tự động xóa vĩnh viễn</strong> khỏi hệ thống ngay sau khi yêu cầu xác thực được phê duyệt hoặc từ chối.
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs font-semibold text-ue-brand">
                    <x-ui.icon name="check-circle" size="xs" />
                    <span>Tuân thủ đầy quy trình bảo vệ dữ liệu</span>
                </div>
            </div>

            {{-- Column 2: Identity Masking --}}
            <div class="bg-ue-neutral-25 p-8 rounded-2xl border border-ue-border flex flex-col justify-between">
                <div>
                    <div class="inline-flex items-center justify-center p-3 bg-ue-blue-100 text-ue-brand rounded-xl mb-6">
                        <x-ui.icon name="eye-off" size="lg" />
                    </div>
                    <h3 class="text-xl font-bold text-ue-neutral-900 mb-4">Quyền riêng tư danh tính</h3>
                    <p class="text-sm text-ue-text-secondary leading-relaxed mb-6">
                        Bạn có toàn quyền kiểm soát chế độ hiển thị danh tính. Bạn có thể chọn hoạt động dưới tên thật hoặc sử dụng một <strong>biệt danh ẩn danh</strong> trong các cuộc thảo luận mở, trong khi nhãn chứng nhận vai trò (ví dụ: K47 - Công nghệ thông tin) vẫn được đảm bảo độ tin cậy.
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs font-semibold text-ue-brand">
                    <x-ui.icon name="check-circle" size="xs" />
                    <span>Tùy chỉnh linh hoạt trong trang thiết lập cá nhân</span>
                </div>
            </div>

            {{-- Column 3: Zero Tolerance --}}
            <div class="bg-ue-neutral-25 p-8 rounded-2xl border border-ue-border flex flex-col justify-between">
                <div>
                    <div class="inline-flex items-center justify-center p-3 bg-ue-blue-100 text-ue-brand rounded-xl mb-6">
                        <x-ui.icon name="alert-triangle" size="lg" />
                    </div>
                    <h3 class="text-xl font-bold text-ue-neutral-900 mb-4">Không khoan nhượng với vi phạm</h3>
                    <p class="text-sm text-ue-text-secondary leading-relaxed mb-6">
                        UEConnect là cộng đồng văn minh, học thuật và chia sẻ. Các hành vi quấy rối, bắt nạt, lừa đảo, phát ngôn thù hận hoặc tài khoản giả mạo sẽ bị xử lý nghiêm khắc thông qua cơ chế báo cáo nhanh và kiểm duyệt tự động, bao gồm đình chỉ hoặc <strong>khóa tài khoản vĩnh viễn</strong>.
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs font-semibold text-ue-brand">
                    <x-ui.icon name="check-circle" size="xs" />
                    <span>Hệ thống báo cáo hoạt động 24/7</span>
                </div>
            </div>

            {{-- Column 4: SSO Entra ID --}}
            <div class="bg-ue-neutral-25 p-8 rounded-2xl border border-ue-border flex flex-col justify-between">
                <div>
                    <div class="inline-flex items-center justify-center p-3 bg-ue-blue-100 text-ue-brand rounded-xl mb-6">
                        <x-ui.icon name="key" size="lg" />
                    </div>
                    <h3 class="text-xl font-bold text-ue-neutral-900 mb-4">Cổng đăng nhập HCMUE Single Sign-On</h3>
                    <p class="text-sm text-ue-text-secondary leading-relaxed mb-6">
                        Đăng nhập an toàn và nhanh chóng bằng chính tài khoản Microsoft Office 365 do HCMUE cấp. Không cần nhớ thêm mật khẩu mới, giảm thiểu tối đa nguy cơ rò rỉ tài khoản qua các dịch vụ bên thứ ba.
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs font-semibold text-ue-brand">
                    <x-ui.icon name="check-circle" size="xs" />
                    <span>Xác thực an toàn qua hệ thống nhà trường</span>
                </div>
            </div>
        </div>
    </div>
</section>
