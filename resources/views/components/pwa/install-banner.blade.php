<div 
    x-data 
    x-show="$store.pwa.showBanner" 
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
    x-transition:leave="transition ease-in duration-200 transform"
    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    class="fixed bottom-0 inset-x-0 pb-20 lg:pb-6 px-4 sm:px-6 z-[9999] sm:flex sm:justify-center sm:items-center pointer-events-none"
    style="display: none;"
>
    <div class="bg-white shadow-2xl rounded-2xl ring-1 ring-black/5 p-4 sm:p-5 pointer-events-auto max-w-sm w-full mx-auto flex flex-col gap-3">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <img class="h-12 w-12 rounded-xl shadow-sm border border-slate-100" src="{{ asset('icons/icon-128x128.png') }}" alt="UEConnect App Icon">
            </div>
            <div class="flex-1 pt-0.5">
                <h3 class="text-sm font-bold text-slate-900 leading-snug">Cài đặt UEConnect</h3>
                <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                    Truy cập nhanh hơn, nhận thông báo đẩy và trải nghiệm mượt mà như một ứng dụng gốc.
                </p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button @click="$store.pwa.dismissBanner(false)" type="button" class="bg-white rounded-md inline-flex text-slate-400 hover:text-slate-500 focus:outline-none">
                    <span class="sr-only">Đóng</span>
                    <x-ui.icon name="x" size="sm" />
                </button>
            </div>
        </div>
        <div class="flex gap-2 mt-1">
            <button @click="$store.pwa.install()" type="button" class="flex-1 w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-xs font-semibold text-white bg-ue-brand hover:bg-ue-brand-dark focus:outline-none transition-colors">
                Cài đặt ngay
            </button>
            <button @click="$store.pwa.dismissBanner(true)" type="button" class="flex-none px-3 py-2 border border-slate-200 rounded-lg text-xs font-medium text-slate-600 bg-white hover:bg-slate-50 focus:outline-none transition-colors">
                Không hiện lại
            </button>
        </div>
    </div>
</div>
