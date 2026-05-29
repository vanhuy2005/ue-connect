@props([
    'body' => '',
    'visibility' => 'verified_users',
])

<div
    id="create-post-modal"
    class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden ue-animate-fade-in"
    role="dialog"
    aria-modal="true"
    aria-labelledby="create-post-title"
    x-data="{
        closeModal() {
            const textarea = document.getElementById('modal-post-body');
            if (textarea && textarea.value.trim() !== '') {
                if (!confirm('Bạn có muốn hủy bài viết này không? Nội dung chưa đăng sẽ bị mất.')) {
                    return;
                }
                @this.set('body', '');
            }
            document.getElementById('create-post-modal').classList.add('hidden');
        }
    }"
    @keydown.escape.window="closeModal()"
    @click="closeModal()"
    @post-created.window="document.getElementById('create-post-modal').classList.add('hidden')"
>
    <div 
        class="bg-white rounded-2xl max-w-lg w-full border border-slate-200 shadow-2xl overflow-hidden ue-animate-scale-in"
        @click.stopPropagation()
    >
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 id="create-post-title" class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <x-ui.icon name="edit" size="xs" class="text-ue-brand" />
                Tạo bài viết mới
            </h3>
            <button 
                type="button" 
                @click="closeModal()" 
                class="text-slate-400 hover:text-slate-600 transition-colors"
                aria-label="Đóng hộp thoại"
            >
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>

        <form wire:submit.prevent="submitPost" class="p-6 space-y-4">
            <div class="flex gap-3">
                <div class="flex-shrink-0">
                    <div class="w-9 h-9 rounded-full bg-ue-brand-soft border border-slate-100 flex items-center justify-center font-bold text-ue-brand text-xs shadow-xs select-none">
                        {{ mb_substr(auth()->user()->name, 0, 2) }}
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <label for="modal-post-body" class="sr-only">Nội dung bài viết</label>
                    <textarea
                        id="modal-post-body"
                        wire:model="body"
                        placeholder="Có gì mới trong cộng đồng HCMUE hôm nay?"
                        rows="4"
                        class="w-full border-0 focus:ring-0 p-0 text-slate-700 placeholder-slate-400 text-sm sm:text-base resize-none bg-transparent focus:outline-none"
                        maxlength="3000"
                    ></textarea>
                    @error('body')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between pt-4 border-t border-slate-100 gap-3">
                <div class="flex items-center gap-3 justify-between sm:justify-start">
                    {{-- Character counter --}}
                    <span class="text-xxs text-slate-400 font-semibold">
                        {{ mb_strlen($body) }}/3000
                    </span>

                    {{-- Visibility chip --}}
                    <div class="relative">
                        <label for="modal-post-visibility" class="sr-only">Quyền xem</label>
                        <select
                            id="modal-post-visibility"
                            wire:model="visibility"
                            class="absolute inset-0 w-full h-full opacity-0 z-10 cursor-pointer"
                        >
                            <option value="verified_users">Chỉ sinh viên xác thực</option>
                            <option value="connections_only" disabled>Bạn bè (Sắp ra mắt)</option>
                            <option value="community" disabled>Cộng đồng (Sắp ra mắt)</option>
                        </select>
                        <div class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 text-slate-500 rounded-lg select-none pointer-events-none">
                            <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand/10" />
                            <span class="hidden sm:inline text-xxs font-bold">Chỉ sinh viên xác thực</span>
                            <span class="sm:hidden text-[10px] font-bold">Xác thực</span>
                            <x-ui.icon name="chevron-down" size="xs" class="text-slate-400" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5">
                    <button 
                        type="button" 
                        @click="closeModal()" 
                        class="px-4 py-2 text-xs font-bold text-slate-550 hover:text-slate-750 transition-colors"
                    >
                        Hủy bỏ
                    </button>
                    <x-ui.button
                        type="submit"
                        variant="primary"
                        size="sm"
                        icon="send"
                    >
                        Đăng bài
                    </x-ui.button>
                </div>
            </div>
        </form>
    </div>
</div>
