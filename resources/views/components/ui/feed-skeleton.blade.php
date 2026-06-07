@props(['count' => 5])

<div class="space-y-4 select-none">
    @for ($i = 0; $i < $count; $i++)
        <div class="bg-white rounded-2xl border border-slate-100 p-4 sm:p-5 shadow-2xs animate-pulse">
            <div class="flex items-start gap-3">
                {{-- Avatar column --}}
                <div class="w-10 h-10 rounded-full bg-slate-200 flex-shrink-0"></div>

                {{-- Content column --}}
                <div class="flex-grow space-y-3 min-w-0">
                    {{-- Header skeleton: Name, timestamp, sub-header --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            <div class="h-3.5 bg-slate-200 rounded-full w-24"></div>
                            <div class="h-2.5 bg-slate-200 rounded-full w-2"></div>
                            <div class="h-3 bg-slate-200 rounded-full w-12"></div>
                        </div>
                        <div class="h-2.5 bg-slate-200 rounded-full w-32"></div>
                    </div>

                    {{-- Text body skeleton --}}
                    <div class="space-y-2">
                        <div class="h-3 bg-slate-200 rounded-full w-11/12"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-full"></div>
                        <div class="h-3 bg-slate-200 rounded-full w-4/5"></div>
                    </div>

                    {{-- Image block skeleton (optional, alternate items) --}}
                    @if ($i % 2 === 1)
                        <div class="rounded-2xl bg-slate-200 aspect-video w-full max-w-lg"></div>
                    @endif

                    {{-- Actions block skeleton --}}
                    <div class="flex items-center gap-6 pt-2 border-t border-slate-50/50">
                        <div class="h-4 bg-slate-200 rounded-full w-12"></div>
                        <div class="h-4 bg-slate-200 rounded-full w-12"></div>
                        <div class="h-4 bg-slate-200 rounded-full w-12"></div>
                        <div class="h-4 bg-slate-200 rounded-full w-12"></div>
                    </div>
                </div>
            </div>
        </div>
    @endfor
</div>
