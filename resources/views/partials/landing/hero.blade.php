{{-- ============================================================ --}}
{{-- HERO SECTION --}}
{{-- ============================================================ --}}
<section id="hero" aria-labelledby="hero-heading" class="relative overflow-hidden bg-gradient-to-br from-ue-brand to-[#0A2B49]">

    {{-- Background texture --}}
    <div class="absolute inset-0 opacity-[0.06] pointer-events-none"
         style="background-image: radial-gradient(circle at 1px 1px, #fff 1px, transparent 0); background-size: 28px 28px;"
         aria-hidden="true">
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
        <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center">

            {{-- Left: Copy --}}
            <div class="mb-12 lg:mb-0">
                {{-- Trust chips --}}
                <div class="flex flex-wrap gap-2 mb-6" aria-label="Điểm nổi bật">
                    @foreach(['Chỉ dành cho HCMUE', 'Xác thực sinh viên', 'An toàn & đáng tin cậy'] as $chip)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 text-white/90 text-xs font-medium border border-white/15">
                            <x-ui.icon name="check-circle" size="xs" class="text-white/70" />
                            {{ $chip }}
                        </span>
                    @endforeach
                </div>

                {{-- Headline --}}
                <h1 id="hero-heading" class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight mb-6">
                    Kết nối đúng người.<br>
                    Đúng cộng đồng.<br>
                    <span class="text-white/80">Đúng hành trình đại học.</span>
                </h1>

                {{-- Subcopy --}}
                <p class="text-base lg:text-lg text-white/75 mb-8 max-w-lg leading-relaxed">
                    UEConnect là nền tảng mạng xã hội xác thực dành cho cộng đồng HCMUE, giúp sinh viên kết nối bạn bè, tham gia cộng đồng, tìm mentor và khám phá cơ hội trong môi trường an toàn, đáng tin cậy.
                </p>

                {{-- CTA pair --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <x-ui.button
                        href="{{ route('register') }}"
                        variant="inverse"
                        size="lg"
                        icon="user-plus"
                    >
                        Tham gia UEConnect
                    </x-ui.button>
                    <x-ui.button
                        href="#features"
                        variant="ghost"
                        size="lg"
                        icon="arrow-down"
                        icon-position="right"
                        class="border border-white/30 text-white hover:bg-white/10 hover:text-white active:bg-white/20 active:text-white"
                    >
                        Xem tính năng
                    </x-ui.button>
                </div>
            </div>

            {{-- Right: Visual mockup --}}
            <div class="relative flex justify-center lg:justify-end" aria-hidden="true">
                <img src="{{ asset('images/brand/hero-img-ue-connect.png') }}"
                     alt="UEConnect Platform Showcase"
                     class="w-full max-w-[540px] lg:max-w-[620px] xl:max-w-[680px] h-auto object-contain rounded-2xl shadow-2xl transition-transform duration-500 hover:scale-[1.02]"
                     loading="eager"
                     decoding="async">
            </div>

        </div>
    </div>

    {{-- Wave divider --}}
    <div class="absolute bottom-0 left-0 right-0 h-8 bg-white" style="clip-path: ellipse(55% 100% at 50% 100%);" aria-hidden="true"></div>

</section>

{{-- ============================================================ --}}
{{-- MARKETING PLACEHOLDER METRICS --}}
{{-- ============================================================ --}}
{{-- Marketing placeholder metrics. Replace with analytics data later. --}}
<section class="bg-white py-12 sm:py-16" aria-label="Số liệu cộng đồng">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <dl class="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
            @foreach([
                ['2.4K+', 'UEers đã tham gia'],
                ['80+',   'Cộng đồng & CLB'],
                ['120+',  'Mentor & Alumni'],
                ['12K+',  'Tin nhắn mỗi ngày'],
            ] as [$number, $label])
                <div class="text-center p-6 rounded-2xl bg-ue-surface-subtle border border-ue-border">
                    <dt class="text-3xl font-bold text-ue-brand mb-1">{{ $number }}</dt>
                    <dd class="text-sm text-ue-text-secondary">{{ $label }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>
