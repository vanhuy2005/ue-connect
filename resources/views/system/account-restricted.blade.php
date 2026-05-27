<x-app-layout>
    <x-slot name="title">Tài khoản bị hạn chế</x-slot>

    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <div class="mb-6">
                <x-ui.icon name="shield-x" size="2xl" class="text-danger mx-auto" />
            </div>
            <h1 class="text-2xl font-bold text-ue-text mb-3">Tài khoản bị hạn chế</h1>
            <p class="text-ue-text-secondary mb-6">
                Tài khoản của bạn hiện đang bị hạn chế hoặc tạm khóa. Vui lòng liên hệ với
                bộ phận hỗ trợ để được giải quyết.
            </p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit" variant="secondary">
                    Đăng xuất
                </x-ui.button>
            </form>
        </div>
    </div>
</x-app-layout>
