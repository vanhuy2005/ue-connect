@props([
    'id',
    'title' => '',
])

{{-- Mobile bottom sheet drawer container --}}
<div
    data-ue-sheet="{{ $id }}"
    class="ue-bottom-sheet"
    aria-hidden="true"
    role="dialog"
    {{ $attributes }}
>
    <div class="ue-bottom-sheet__handle" aria-hidden="true"></div>
    @if($title)
        <div class="ue-bottom-sheet__header">
            <h4 class="text-xs font-bold text-slate-800 text-center">{{ $title }}</h4>
        </div>
    @endif
    <div class="ue-bottom-sheet__body">
        {{ $slot }}
    </div>
</div>
