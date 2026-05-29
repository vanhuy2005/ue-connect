{{--
    UEConnect Floating Action Button
    Source: docs/04-design/12-component-primitives.md §FAB
    
    Fixed floating trigger bottom-right for creating a new post.
--}}
<button
    type="button"
    onclick="const modal = document.getElementById('create-post-modal'); if (modal) { modal.classList.remove('hidden'); modal.querySelector('textarea').focus(); }"
    class="fixed bottom-[calc(var(--layout-bottom-nav-h)+16px)] right-4 lg:bottom-8 lg:right-8 w-12 h-12 rounded-full bg-ue-brand hover:bg-ue-brand-hover text-white flex items-center justify-center shadow-lg hover:shadow-xl transition-all hover:scale-105 active:scale-95 focus:outline-none ue-focus-ring z-sticky"
    aria-label="Tạo bài viết mới"
>
    <x-ui.icon name="edit" size="md" class="text-white" />
</button>
