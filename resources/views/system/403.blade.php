<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Không có quyền truy cập | UEConnect</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-ue-bg text-ue-text flex items-center justify-center min-h-screen px-4">
    <div class="max-w-md w-full text-center">
        <p class="text-8xl font-black text-ue-brand/20 mb-2">403</p>
        <h1 class="text-2xl font-bold text-ue-text mb-3">Không có quyền truy cập</h1>
        <p class="text-ue-text-secondary mb-6">
            Bạn không có quyền thực hiện hành động này hoặc truy cập vào trang này.
        </p>
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-ue-brand text-ue-text-inverse font-semibold text-sm hover:bg-ue-brand-hover transition-colors">
            Về trang chủ
        </a>
    </div>
</body>
</html>
