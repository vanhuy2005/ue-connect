{{-- ============================================================ --}}
{{-- FEATURES SECTION --}}
{{-- ============================================================ --}}
<section id="features" class="py-16 sm:py-24 bg-ue-bg" aria-labelledby="features-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Section header --}}
        <div class="text-center mb-12 lg:mb-16">
            <span class="inline-block px-3 py-1 rounded-full bg-ue-brand-soft text-ue-brand text-xs font-semibold mb-4 border border-[rgba(18,72,116,0.14)]">
                Tính năng
            </span>
            <h2 id="features-heading" class="text-2xl sm:text-3xl lg:text-4xl font-bold text-ue-text mb-4">
                Không chỉ là mạng xã hội sinh viên
            </h2>
            <p class="text-ue-text-secondary max-w-2xl mx-auto text-base lg:text-lg">
                UEConnect được xây dựng cho cộng đồng đại học thực sự — nơi mỗi kết nối đều có ý nghĩa học tập và phát triển.
            </p>
        </div>

        {{-- Feature grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            @foreach([
                [
                    'icon'  => 'file-text',
                    'title' => 'Feed & chia sẻ',
                    'body'  => 'Chia sẻ bài viết, ảnh, tài liệu học tập và khoảnh khắc đời thường với cộng đồng HCMUE.',
                    'color' => 'text-ue-brand',
                    'bg'    => 'bg-ue-brand-soft',
                ],
                [
                    'icon'  => 'users',
                    'title' => 'Khám phá UEers',
                    'body'  => 'Tìm bạn học, bạn cùng khóa, cùng ngành hoặc chung sở thích trong môi trường được xác thực.',
                    'color' => 'text-mentor',
                    'bg'    => 'bg-mentor/10',
                ],
                [
                    'icon'  => 'community',
                    'title' => 'Cộng đồng / CLB',
                    'body'  => 'Tham gia cộng đồng học tập, CLB để học hỏi, chia sẻ và phát triển kỹ năng cùng nhau.',
                    'color' => 'text-success-text',
                    'bg'    => 'bg-success/10',
                ],
                [
                    'icon'  => 'message',
                    'title' => 'Tin nhắn an toàn',
                    'body'  => 'Nhắn tin trực tiếp với bạn bè và thành viên cộng đồng trong không gian riêng tư, được kiểm soát.',
                    'color' => 'text-info-text',
                    'bg'    => 'bg-info/10',
                ],
                [
                    'icon'  => 'graduation-cap',
                    'title' => 'Mentor & Alumni',
                    'body'  => 'Kết nối với cựu sinh viên và mentor để được hướng dẫn nghề nghiệp và học thuật.',
                    'color' => 'text-warning-text',
                    'bg'    => 'bg-warning/10',
                ],
                [
                    'icon'  => 'shield-check',
                    'title' => 'Xác thực & An toàn',
                    'body'  => 'Mọi thành viên đều được xác thực qua email HCMUE và quy trình duyệt bởi admin.',
                    'color' => 'text-ue-brand',
                    'bg'    => 'bg-ue-brand-soft',
                ],
            ] as $feature)
                <article class="bg-ue-surface border border-ue-border rounded-2xl p-6 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="inline-flex items-center justify-center w-11 h-11 rounded-xl {{ $feature['bg'] }} mb-4">
                        <x-ui.icon :name="$feature['icon']" size="lg" :class="$feature['color']" />
                    </div>
                    <h3 class="text-base font-semibold text-ue-text mb-2">{{ $feature['title'] }}</h3>
                    <p class="text-sm text-ue-text-secondary leading-relaxed">{{ $feature['body'] }}</p>
                </article>
            @endforeach

        </div>
    </div>
</section>
