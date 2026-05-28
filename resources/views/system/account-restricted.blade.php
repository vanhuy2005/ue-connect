<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#124874">
        <title>Tài khoản bị hạn chế — {{ config('app.name', 'UEConnect') }}</title>
        <meta name="description" content="Tài khoản của bạn hiện bị hạn chế quyền truy cập.">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="icon" type="image/png" href="{{ asset('images/brand/ueconnect-mark-nobg.png') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased h-full bg-ue-bg">

        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">

            {{-- Logo --}}
            <div class="mb-10">
                <x-brand.logo variant="horizontal" size="md" />
            </div>

            <div class="w-full max-w-md">
                @php
                    $user = auth()->user();
                    $status = $user?->account_status;

                    $config = match($status?->value ?? '') {
                        'suspended' => [
                            'icon'    => 'clock',
                            'color'   => 'text-warning',
                            'title'   => 'Tài khoản đang bị tạm khóa',
                            'body'    => 'Tài khoản của bạn đang bị tạm khóa. Vui lòng liên hệ bộ phận hỗ trợ để được giải quyết và khôi phục quyền truy cập.',
                            'badgeVariant' => 'warning',
                            'badge'   => 'Tạm khóa',
                        ],
                        'banned' => [
                            'icon'    => 'shield-x',
                            'color'   => 'text-danger',
                            'title'   => 'Tài khoản bị cấm vĩnh viễn',
                            'body'    => 'Tài khoản của bạn đã bị cấm do vi phạm chính sách cộng đồng UEConnect. Quyết định này không thể đảo ngược.',
                            'badgeVariant' => 'danger',
                            'badge'   => 'Bị cấm',
                        ],
                        'deleted' => [
                            'icon'    => 'trash',
                            'color'   => 'text-ue-text-muted',
                            'title'   => 'Tài khoản đã bị xóa',
                            'body'    => 'Tài khoản này đã bị xóa và không còn khả dụng.',
                            'badgeVariant' => 'neutral',
                            'badge'   => 'Đã xóa',
                        ],
                        default => [
                            'icon'    => 'shield-x',
                            'color'   => 'text-danger',
                            'title'   => 'Tài khoản bị hạn chế',
                            'body'    => 'Tài khoản của bạn hiện bị hạn chế quyền truy cập. Vui lòng liên hệ bộ phận hỗ trợ nếu bạn cho rằng đây là nhầm lẫn.',
                            'badgeVariant' => 'danger',
                            'badge'   => 'Bị hạn chế',
                        ],
                    };
                @endphp

                <div class="bg-ue-surface border border-ue-border rounded-2xl shadow-md p-8 text-center">
                    {{-- Icon --}}
                    <div class="mb-5 flex justify-center">
                        <x-ui.icon :name="$config['icon']" size="3xl" :class="$config['color']" />
                    </div>

                    {{-- Badge --}}
                    <div class="mb-4 flex justify-center">
                        <x-ui.badge :variant="$config['badgeVariant']">{{ $config['badge'] }}</x-ui.badge>
                    </div>

                    {{-- Title --}}
                    <h1 class="text-xl font-bold text-ue-text mb-3">{{ $config['title'] }}</h1>

                    {{-- Body --}}
                    <p class="text-sm text-ue-text-secondary mb-6 leading-relaxed">
                        {{ $config['body'] }}
                        @if ($user && $user->account_status_reason)
                            <span class="block mt-4 p-3 bg-ue-neutral-50 border border-ue-border rounded-xl text-xs text-left italic text-ue-text-secondary">
                                <strong>Lý do từ hệ thống:</strong> "{{ $user->account_status_reason }}"
                            </span>
                        @endif
                    </p>

                    {{-- Actions --}}
                    <div class="flex flex-col gap-3">
                        @if($status?->value === 'suspended')
                            <a
                                href="mailto:support@hcmue.edu.vn?subject=Yêu cầu xem xét tài khoản bị tạm khóa"
                                class="inline-flex items-center justify-center gap-2 h-11 px-6 rounded-xl bg-ue-brand text-white text-sm font-semibold hover:bg-ue-brand-hover transition-colors"
                            >
                                <x-ui.icon name="mail" size="sm" />
                                Liên hệ hỗ trợ
                            </a>
                        @endif

                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 h-11 px-6 w-full rounded-xl border border-ue-border text-ue-text-secondary text-sm font-medium hover:bg-ue-surface-hover transition-colors"
                            >
                                <x-ui.icon name="log-out" size="sm" />
                                Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Footer note --}}
                <p class="mt-6 text-center text-xs text-ue-text-muted">
                    Nếu bạn cần hỗ trợ, liên hệ
                    <a href="mailto:support@hcmue.edu.vn" class="text-ue-brand hover:underline">support@hcmue.edu.vn</a>
                </p>
            </div>
        </div>

    </body>
</html>
