<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="theme-color" content="#ffffff">
    <title>Cài đặt ứng dụng - UEConnect</title>
    
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="UEConnect">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-[100dvh] font-sans antialiased text-slate-900 bg-white flex flex-col items-center justify-center p-6 sm:p-12">
    <div x-data="{
        platform: 'unknown',
        isStandalone: window.matchMedia('(display-mode: standalone)').matches,
        canInstallDirectly: false,
        deferredPrompt: null,
        init() {
            const ua = navigator.userAgent || navigator.vendor || window.opera;
            if (/android/i.test(ua)) {
                this.platform = 'android';
            } else if (/iPad|iPhone|iPod/.test(ua) && !window.MSStream) {
                this.platform = 'ios';
            } else {
                this.platform = 'desktop';
            }
            
            // Listen to beforeinstallprompt directly
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.deferredPrompt = e;
                this.canInstallDirectly = true;
            });

            // Fallback: check if Alpine store has it (safely using optional chaining in window context)
            setTimeout(() => {
                if (window.Alpine && Alpine.store('pwa') && Alpine.store('pwa').deferredPrompt) {
                    this.deferredPrompt = Alpine.store('pwa').deferredPrompt;
                    this.canInstallDirectly = true;
                }
            }, 100);

            if(window.trackPwaEvent) {
                window.trackPwaEvent('pwa_install_page_viewed', { source: new URLSearchParams(window.location.search).get('source') || 'direct' });
            }
        },
        install() {
            if (this.deferredPrompt) {
                this.deferredPrompt.prompt();
                this.deferredPrompt.userChoice.then((choice) => {
                    if (choice.outcome === 'accepted') {
                        if (window.trackPwaEvent) window.trackPwaEvent('pwa_install_accepted');
                    } else {
                        if (window.trackPwaEvent) window.trackPwaEvent('pwa_install_dismissed');
                    }
                    this.deferredPrompt = null;
                    this.canInstallDirectly = false;
                });
            }
        }
    }" class="w-full max-w-md flex flex-col items-center text-center">
        
        <div class="mb-6">
            <img src="{{ asset('icons/icon-192x192.png') }}" alt="UEConnect" class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl shadow-lg border border-slate-100 mx-auto">
        </div>

        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mb-3 tracking-tight">Cài đặt UEConnect</h1>
        <p class="text-sm sm:text-base text-slate-600 mb-8 max-w-sm mx-auto leading-relaxed">
            Truy cập nhanh hơn, nhận thông báo đẩy và dùng UEConnect mượt mà như một ứng dụng gốc trên thiết bị của bạn.
        </p>

        {{-- If Already Installed / Standalone --}}
        <template x-if="isStandalone">
            <div class="bg-green-50 text-green-800 p-4 rounded-xl w-full mb-6 flex items-center gap-3 text-left ring-1 ring-green-600/10">
                <x-ui.icon name="check-circle" size="md" class="text-green-600 shrink-0" />
                <p class="text-sm font-medium">Bạn đang sử dụng UEConnect dưới dạng ứng dụng rồi. Tuyệt vời!</p>
            </div>
        </template>

        {{-- If Android or browser supporting beforeinstallprompt --}}
        <template x-if="!isStandalone && canInstallDirectly">
            <div class="w-full">
                <button 
                    @click="install()" 
                    class="w-full flex items-center justify-center gap-2 bg-ue-brand hover:bg-ue-brand-dark text-white font-bold py-3.5 px-6 rounded-xl shadow-lg shadow-ue-brand/30 transition-all transform hover:scale-[1.02] active:scale-[0.98]"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Cài đặt ứng dụng
                </button>
            </div>
        </template>

        {{-- If iOS (Safari) and not standalone --}}
        <template x-if="!isStandalone && !canInstallDirectly && platform === 'ios'">
            <div class="w-full bg-slate-50 border border-slate-200 p-5 rounded-2xl text-left shadow-sm">
                <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 384 512" fill="currentColor"><path d="M318.7 268.7c-.2-36.7 16.4-64.4 50-84.8-18.8-26.9-47.2-41.7-84.7-44.6-35.5-2.8-74.3 20.7-88.5 20.7-15 0-49.4-19.7-76.4-19.7C63.3 141.2 24 184.5 15.6 235.9c-8.1 48.8 33.6 130.6 60.1 169.6 26.4 38.6 49.6 107.4 87.2 107.4 39.5 0 54.4-23.7 96-23.7 41.5 0 56.4 23.7 96.1 23.7 37.4 0 63.8-69.7 89.2-109.1 23.6-39.6 33-82.6 33.3-88.2-1.3-.5-64.7-25-64.4-96.2zM213.7 87.4c20.2-24.5 32.7-56.1 29-87.4-24.7 1.5-54 16.6-73.4 39.2-20.1 23.5-31.5 56-27.4 86.8 25.8 2 52.8-13.6 71.8-38.6z"/></svg>
                    Hướng dẫn cài trên iOS
                </h3>
                <ol class="text-sm text-slate-600 space-y-3">
                    <li class="flex items-start gap-2">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-ue-brand/10 text-ue-brand flex items-center justify-center font-bold text-[10px]">1</span>
                        <span>Mở trang này bằng trình duyệt <strong>Safari</strong>.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-ue-brand/10 text-ue-brand flex items-center justify-center font-bold text-[10px]">2</span>
                        <span>Nhấn vào nút <strong class="inline-flex items-center text-blue-500 mx-1"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15m0-3-3-3m0 0-3 3m3-3V15" /></svg> Chia sẻ</strong> ở thanh công cụ dưới cùng.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-ue-brand/10 text-ue-brand flex items-center justify-center font-bold text-[10px]">3</span>
                        <span>Chọn <strong>"Thêm vào MH chính"</strong> (Add to Home Screen).</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-ue-brand/10 text-ue-brand flex items-center justify-center font-bold text-[10px]">4</span>
                        <span>Nhấn <strong>Thêm</strong> ở góc trên bên phải.</span>
                    </li>
                </ol>
            </div>
        </template>

        {{-- Fallback instruction for other devices without direct install prompt --}}
        <template x-if="!isStandalone && !canInstallDirectly && platform !== 'ios'">
            <div class="w-full bg-slate-50 border border-slate-200 p-5 rounded-2xl text-left shadow-sm">
                <h3 class="font-bold text-slate-900 mb-2">Cách cài đặt</h3>
                <p class="text-sm text-slate-600 leading-relaxed mb-3">Trình duyệt của bạn có thể không hỗ trợ nút cài đặt nhanh. Bạn có thể cài đặt thủ công bằng cách:</p>
                <ul class="text-sm text-slate-600 list-disc list-inside space-y-1">
                    <li>Trên Chrome/Edge máy tính: Nhấn vào biểu tượng <strong>Cài đặt ứng dụng</strong> ở góc phải thanh địa chỉ.</li>
                    <li>Trên thiết bị di động: Nhấn vào <strong>Menu (⋮)</strong> và chọn <strong>Thêm vào màn hình chính</strong> hoặc <strong>Cài đặt ứng dụng</strong>.</li>
                </ul>
            </div>
        </template>

        <div class="mt-8 pt-6 border-t border-slate-100 w-full text-left">
            <h4 class="font-semibold text-slate-900 text-sm mb-4">Lợi ích khi cài đặt:</h4>
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 text-ue-brand bg-ue-brand/10 p-2 rounded-xl shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Mở nhanh tức thì</p>
                        <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">Mở ứng dụng từ màn hình chính mà không cần nhớ đường link.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 text-ue-brand bg-ue-brand/10 p-2 rounded-xl shrink-0">
                        <x-ui.icon name="bell" size="md" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Nhận thông báo</p>
                        <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">Nhận thông báo bài viết và tin nhắn ngay cả khi không mở ứng dụng.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 text-ue-brand bg-ue-brand/10 p-2 rounded-xl shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Giao diện tối ưu</p>
                        <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">Màn hình rộng rãi, không bị thanh công cụ trình duyệt làm phiền.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <a href="{{ route('landing') }}" class="text-sm text-slate-500 hover:text-slate-800 transition-colors inline-flex items-center gap-1.5 font-medium">
                <x-ui.icon name="arrow-left" class="w-4 h-4" /> Quay lại trang chủ
            </a>
        </div>
    </div>
</body>
</html>
