{{-- ============================================================ --}}
{{-- COMMUNITY PREVIEW SECTION --}}
{{-- ============================================================ --}}
<section id="community-preview" class="py-16 sm:py-24 bg-ue-bg" aria-labelledby="community-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center">

            {{-- Left: Chat mockup --}}
            <div class="mb-12 lg:mb-0 order-2 lg:order-1" aria-hidden="true">
                <div class="bg-ue-surface border border-ue-border rounded-2xl p-4 shadow-md max-w-sm mx-auto lg:max-w-none">

                    {{-- Group header --}}
                    <div class="flex items-center gap-3 pb-3 mb-4 border-b border-ue-border">
                        <div class="w-10 h-10 rounded-xl bg-ue-brand-soft flex items-center justify-center">
                            <x-ui.icon name="community" size="md" class="text-ue-brand" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ue-text">Nhóm Toán K46</p>
                            <p class="text-xs text-ue-text-muted">86 thành viên · Học thuật</p>
                        </div>
                        <div class="ml-auto">
                            <span class="text-xs bg-ue-brand-soft text-ue-brand px-2 py-0.5 rounded-full font-medium">Đã tham gia</span>
                        </div>
                    </div>

                    {{-- Messages --}}
                    <div class="space-y-4">

                        {{-- Message left --}}
                        <div class="flex gap-2">
                            <div class="w-8 h-8 rounded-full bg-ue-brand-soft flex items-center justify-center text-xs font-bold text-ue-brand flex-shrink-0">HT</div>
                            <div>
                                <p class="text-xs text-ue-text-muted mb-1">Hoàng Trung</p>
                                <div class="bg-ue-surface-subtle border border-ue-border rounded-2xl rounded-tl-sm px-4 py-2.5 max-w-xs">
                                    <p class="text-sm text-ue-text">Cảm ơn bạn đã góp ý nhé! Mình sẽ chỉnh lại bài tập nhóm.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Message right --}}
                        <div class="flex gap-2 justify-end">
                            <div>
                                <div class="bg-ue-brand rounded-2xl rounded-tr-sm px-4 py-2.5 max-w-xs">
                                    <p class="text-sm text-white">Mai mình gửi lại bản tóm tắt chương 3 cho cả nhóm nhé 📝</p>
                                </div>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-mentor/20 flex items-center justify-center text-xs font-bold text-mentor-text flex-shrink-0">NA</div>
                        </div>

                        {{-- Message left --}}
                        <div class="flex gap-2">
                            <div class="w-8 h-8 rounded-full bg-success/20 flex items-center justify-center text-xs font-bold text-success-text flex-shrink-0">LM</div>
                            <div>
                                <p class="text-xs text-ue-text-muted mb-1">Lan My</p>
                                <div class="bg-ue-surface-subtle border border-ue-border rounded-2xl rounded-tl-sm px-4 py-2.5 max-w-xs">
                                    <p class="text-sm text-ue-text">Tuyệt quá! Ai có tài liệu Giải tích 2 không cho nhóm xin với 🙏</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Input mock --}}
                    <div class="mt-4 flex items-center gap-2 pt-3 border-t border-ue-border">
                        <div class="flex-1 h-9 bg-ue-surface-subtle border border-ue-border rounded-xl px-3 flex items-center">
                            <span class="text-sm text-ue-text-disabled">Nhắn tin nhóm...</span>
                        </div>
                        <div class="w-9 h-9 rounded-xl bg-ue-brand flex items-center justify-center">
                            <x-ui.icon name="send" size="sm" class="text-white" />
                        </div>
                    </div>

                </div>
            </div>

            {{-- Right: Copy --}}
            <div class="order-1 lg:order-2">
                <span class="inline-block px-3 py-1 rounded-full bg-ue-brand-soft text-ue-brand text-xs font-semibold mb-4 border border-[rgba(18,72,116,0.14)]">
                    Cộng đồng
                </span>
                <h2 id="community-heading" class="text-2xl sm:text-3xl font-bold text-ue-text mb-4">
                    Kết nối và trò chuyện tự nhiên
                </h2>
                <p class="text-ue-text-secondary mb-6 leading-relaxed">
                    Tham gia nhóm học tập, CLB và cộng đồng cùng sở thích. Nhắn tin, chia sẻ tài liệu và hỗ trợ nhau trong môi trường an toàn, không rác thải thông tin.
                </p>
                <ul class="space-y-3 mb-8">
                    @foreach([
                        'Nhóm học thuật theo ngành và khoá',
                        'CLB câu lạc bộ và hoạt động sinh viên',
                        'Cộng đồng theo sở thích và nghề nghiệp',
                        'Tin nhắn riêng tư sau khi kết nối',
                    ] as $item)
                        <li class="flex items-center gap-3 text-sm text-ue-text-secondary">
                            <x-ui.icon name="check-circle" size="sm" class="text-ue-brand flex-shrink-0" />
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
                <x-ui.button
                    href="{{ route('register') }}"
                    size="md"
                    icon="arrow-right"
                    icon-position="right"
                >
                    Tham gia ngay
                </x-ui.button>
            </div>

        </div>
    </div>
</section>
