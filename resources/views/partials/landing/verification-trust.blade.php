{{-- ============================================================ --}}
{{-- VERIFICATION TRUST SECTION --}}
{{-- ============================================================ --}}
<section id="verification-trust" class="py-16 sm:py-24 bg-white" aria-labelledby="verification-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center">

            {{-- Left: Copy --}}
            <div class="mb-12 lg:mb-0">
                <span class="inline-block px-3 py-1 rounded-full bg-ue-brand-soft text-ue-brand text-xs font-semibold mb-4 border border-[rgba(18,72,116,0.14)]">
                    Xác thực danh tính
                </span>
                <h2 id="verification-heading" class="text-2xl sm:text-3xl font-bold text-ue-text mb-4">
                    Xác thực để tạo niềm tin
                </h2>
                <p class="text-ue-text-secondary mb-6 leading-relaxed">
                    UEConnect ưu tiên xác thực danh tính bằng email HCMUE và minh chứng phù hợp để giữ cộng đồng an toàn, nghiêm túc và đáng tin cậy.
                </p>

                {{-- SSO disclaimer --}}
                <div class="flex gap-3 p-4 rounded-xl bg-ue-brand-soft border border-[rgba(18,72,116,0.14)] mb-6">
                    <x-ui.icon name="info-circle" size="md" class="text-ue-brand flex-shrink-0 mt-0.5" />
                    <p class="text-sm text-ue-brand leading-relaxed">
                        <strong>Lưu ý:</strong> Đăng nhập Microsoft HCMUE chỉ xác minh email và tổ chức. Một số vai trò vẫn cần gửi hồ sơ xác thực để admin duyệt trước khi truy cập đầy đủ.
                    </p>
                </div>

                {{-- Steps --}}
                <ol class="space-y-4" aria-label="Các bước xác thực danh tính">
                    @foreach([
                        ['user',           'Chọn vai trò',          'Sinh viên, Cựu sinh viên, Cố vấn/Giảng viên'],
                        ['mail',           'Xác minh email',         'Email @hcmue.edu.vn hoặc email đã đăng ký'],
                        ['upload',         'Gửi minh chứng',        'Thẻ sinh viên, bằng tốt nghiệp, quyết định công tác...'],
                        ['shield-check',   'Admin xét duyệt',       'Đội ngũ quản trị kiểm tra và phê duyệt trong 1-2 ngày'],
                        ['check-circle',   'Tham gia cộng đồng',    'Truy cập đầy đủ UEConnect sau khi được duyệt'],
                    ] as $i => [$icon, $title, $body])
                        <li class="flex gap-4">
                            <div class="flex-shrink-0 flex flex-col items-center">
                                <div class="w-9 h-9 rounded-full bg-ue-brand flex items-center justify-center text-white text-sm font-bold">
                                    {{ $i + 1 }}
                                </div>
                                @if($i < 4)
                                    <div class="w-px flex-1 bg-ue-border mt-2 mb-0 min-h-[20px]" aria-hidden="true"></div>
                                @endif
                            </div>
                            <div class="pb-4">
                                <p class="text-sm font-semibold text-ue-text">{{ $title }}</p>
                                <p class="text-xs text-ue-text-muted mt-0.5">{{ $body }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            {{-- Right: Role cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach([
                    [
                        'role'  => 'Sinh viên hiện tại',
                        'icon'  => 'graduation-cap',
                        'color' => 'text-ue-brand',
                        'bg'    => 'bg-ue-brand-soft',
                        'border'=> 'border-[rgba(18,72,116,0.14)]',
                        'email' => '@student.hcmue.edu.vn',
                        'badge' => 'Sinh viên',
                        'badgeCls' => 'bg-ue-brand-soft text-ue-brand',
                    ],
                    [
                        'role'  => 'Cựu sinh viên',
                        'icon'  => 'users',
                        'color' => 'text-mentor-text',
                        'bg'    => 'bg-mentor/10',
                        'border'=> 'border-[rgba(124,92,255,0.14)]',
                        'email' => 'Email cá nhân + minh chứng',
                        'badge' => 'Alumni',
                        'badgeCls' => 'bg-mentor/10 text-mentor-text',
                    ],
                    [
                        'role'  => 'Giảng viên / Cố vấn',
                        'icon'  => 'briefcase',
                        'color' => 'text-success-text',
                        'bg'    => 'bg-success/10',
                        'border'=> 'border-[rgba(32,201,151,0.14)]',
                        'email' => '@hcmue.edu.vn / @teacher.hcmue.edu.vn',
                        'badge' => 'Cố vấn',
                        'badgeCls' => 'bg-success/10 text-success-text',
                    ],
                    [
                        'role'  => 'Mentor bên ngoài',
                        'icon'  => 'star',
                        'color' => 'text-warning-text',
                        'bg'    => 'bg-warning/10',
                        'border'=> 'border-[rgba(245,158,11,0.14)]',
                        'email' => 'Theo lời mời từ admin',
                        'badge' => 'Mentor',
                        'badgeCls' => 'bg-warning/10 text-warning-text',
                    ],
                ] as $card)
                    <article class="bg-ue-surface border {{ $card['border'] }} rounded-2xl p-5">
                        <div class="inline-flex w-10 h-10 rounded-xl {{ $card['bg'] }} items-center justify-center mb-3">
                            <x-ui.icon :name="$card['icon']" size="md" :class="$card['color']" />
                        </div>
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm font-semibold text-ue-text">{{ $card['role'] }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $card['badgeCls'] }} font-medium">{{ $card['badge'] }}</span>
                        </div>
                        <p class="text-xs text-ue-text-muted">{{ $card['email'] }}</p>
                    </article>
                @endforeach
            </div>

        </div>
    </div>
</section>
