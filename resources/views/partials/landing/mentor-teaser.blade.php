{{-- ============================================================ --}}
{{-- MENTOR & ALUMNI TEASER SECTION --}}
{{-- ============================================================ --}}
<section id="mentor-teaser" class="py-16 sm:py-24 bg-white" aria-labelledby="mentor-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="text-center mb-12">
            <span class="inline-block px-3 py-1 rounded-full bg-mentor/10 text-mentor-text text-xs font-semibold mb-4 border border-[rgba(124,92,255,0.14)]">
                Mentor & Alumni
            </span>
            <h2 id="mentor-heading" class="text-2xl sm:text-3xl font-bold text-ue-text mb-4">
                Mentor, alumni và cơ hội phát triển
            </h2>
            <p class="text-ue-text-secondary max-w-2xl mx-auto">
                Kết nối với cựu sinh viên HCMUE và mentor chuyên nghiệp để được hướng dẫn học thuật, định hướng nghề nghiệp và phát triển bản thân.
            </p>
        </div>

        <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-start">

            {{-- Left: Mentor cards --}}
            <div class="mb-10 lg:mb-0 space-y-4">
                <p class="text-xs font-semibold text-ue-text-muted uppercase tracking-wide mb-4">Mentor & Cựu sinh viên nổi bật</p>
                @foreach([
                    [
                        'initials'   => 'LN',
                        'name'       => 'ThS. Lê Hoàng Nam',
                        'role'       => 'Mentor · Khoa Toán học',
                        'detail'     => 'Hướng dẫn nghiên cứu khoa học và định hướng học thuật',
                        'badgeColor' => 'bg-mentor/10 text-mentor-text',
                        'badge'      => 'Mentor',
                        'avatarBg'   => 'bg-mentor/20 text-mentor-text',
                    ],
                    [
                        'initials'   => 'DH',
                        'name'       => 'Anh Nguyễn Đức Huy',
                        'role'       => 'Alumni · Khoa Công nghệ Thông tin',
                        'detail'     => 'Software Engineer tại FPT Software, K44',
                        'badgeColor' => 'bg-ue-brand-soft text-ue-brand',
                        'badge'      => 'Alumni',
                        'avatarBg'   => 'bg-ue-brand-soft text-ue-brand',
                    ],
                    [
                        'initials'   => 'NA',
                        'name'       => 'Chị Trần Ngọc Ánh',
                        'role'       => 'Alumni · Khoa Ngữ văn',
                        'detail'     => 'Biên tập viên tại NXB Giáo dục, K42',
                        'badgeColor' => 'bg-ue-brand-soft text-ue-brand',
                        'badge'      => 'Alumni',
                        'avatarBg'   => 'bg-success/20 text-success-text',
                    ],
                ] as $mentor)
                    <article class="flex items-center gap-4 p-4 bg-ue-surface border border-ue-border rounded-2xl hover:shadow-sm hover:border-ue-border-strong transition-all">
                        <div class="w-12 h-12 rounded-full {{ $mentor['avatarBg'] }} flex items-center justify-center text-sm font-bold flex-shrink-0">
                            {{ $mentor['initials'] }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <p class="text-sm font-semibold text-ue-text truncate">{{ $mentor['name'] }}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $mentor['badgeColor'] }} font-medium flex-shrink-0">{{ $mentor['badge'] }}</span>
                            </div>
                            <p class="text-xs text-ue-text-muted">{{ $mentor['role'] }}</p>
                            <p class="text-xs text-ue-text-secondary mt-0.5">{{ $mentor['detail'] }}</p>
                        </div>
                        <x-ui.icon name="arrow-right" size="sm" class="text-ue-text-muted flex-shrink-0" />
                    </article>
                @endforeach
            </div>

            {{-- Right: Opportunities --}}
            <div>
                <p class="text-xs font-semibold text-ue-text-muted uppercase tracking-wide mb-4">Cơ hội dành cho bạn</p>
                <div class="space-y-3">
                    @foreach([
                        ['briefcase',      'Thực tập Data Analyst',             'Công ty TNHH ABC · TP. HCM',                  'Mới đăng'],
                        ['book-open',      'Học bổng khuyến khích học tập',     'Phòng Công tác Sinh viên · HCMUE',            'Còn 5 ngày'],
                        ['megaphone',      'Tuyển CTV truyền thông',            'CLB Truyền thông HCMUE · Tình nguyện',        'Đang tuyển'],
                        ['globe',          'Hội thảo Kỹ năng mềm',             'Khoa Tâm lý Giáo dục · Miễn phí',            'Tuần tới'],
                    ] as [$icon, $title, $detail, $tag])
                        <div class="flex items-start gap-3 p-4 bg-ue-surface-subtle border border-ue-border rounded-xl hover:border-ue-border-strong hover:bg-ue-surface transition-all cursor-pointer">
                            <div class="w-8 h-8 rounded-lg bg-ue-brand-soft flex items-center justify-center flex-shrink-0">
                                <x-ui.icon :name="$icon" size="sm" class="text-ue-brand" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-ue-text">{{ $title }}</p>
                                <p class="text-xs text-ue-text-muted mt-0.5">{{ $detail }}</p>
                            </div>
                            <span class="text-xs bg-ue-brand-soft text-ue-brand px-2 py-0.5 rounded-full font-medium flex-shrink-0">{{ $tag }}</span>
                        </div>
                    @endforeach
                </div>

                <p class="text-xs text-ue-text-muted mt-4 italic">
                    * Các cơ hội trên chỉ là ví dụ minh hoạ. Cơ hội thực sẽ được đăng bởi cộng đồng sau khi ra mắt.
                </p>
            </div>

        </div>
    </div>
</section>
