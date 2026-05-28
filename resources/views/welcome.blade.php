@php
    $isAuthenticated = auth()->check();
    $dashboardRoute = route('dashboard');
@endphp
<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#124874">

    <title>UEConnect — Kết nối cộng đồng HCMUE</title>
    <meta name="description" content="UEConnect là nền tảng mạng xã hội xác thực dành cho sinh viên, cựu sinh viên và giảng viên trường Đại học Sư phạm TP. HCM.">

    {{-- Open Graph --}}
    <meta property="og:title" content="UEConnect — Kết nối cộng đồng HCMUE">
    <meta property="og:description" content="Kết nối sinh viên, cựu sinh viên và giảng viên HCMUE trong môi trường an toàn, xác thực.">
    <meta property="og:type" content="website">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/brand/ueconnect-mark-nobg.png') }}">

    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white text-ue-text">

    {{-- Skip to content --}}
    <a href="#main-content" class="skip-link sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-ue-brand focus:text-white focus:rounded-lg focus:text-sm focus:font-semibold">
        Bỏ qua và đến nội dung chính
    </a>

    {{-- ============================================================ --}}
    {{-- HEADER --}}
    {{-- ============================================================ --}}
    @include('partials.landing.header', ['isAuthenticated' => $isAuthenticated, 'dashboardRoute' => $dashboardRoute])

    {{-- ============================================================ --}}
    {{-- MAIN CONTENT --}}
    {{-- ============================================================ --}}
    <main id="main-content" tabindex="-1">

        {{-- Hero --}}
        @include('partials.landing.hero')

        {{-- Features --}}
        @include('partials.landing.features')

        {{-- Verification Trust --}}
        @include('partials.landing.verification-trust')

        {{-- Community Preview --}}
        @include('partials.landing.community-preview')

        {{-- Mentor & Alumni Teaser --}}
        @include('partials.landing.mentor-teaser')

        {{-- Safety --}}
        @include('partials.landing.safety')

        {{-- Final CTA --}}
        @include('partials.landing.cta-final')

    </main>

    {{-- ============================================================ --}}
    {{-- FOOTER --}}
    {{-- ============================================================ --}}
    @include('partials.landing.footer')

</body>
</html>
