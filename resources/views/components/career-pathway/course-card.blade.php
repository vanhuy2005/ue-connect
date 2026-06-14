@props(['programCourse'])

@php
    $course = $programCourse['course'];
    $isMandatory = $programCourse['is_mandatory'];
    $isMissingDescription = empty($course['description']);
    $hasCredits = is_numeric($course['credits']);
    
    // Determine card styling based on state
    $baseClasses = "block w-full text-left bg-white border rounded-xl p-3 md:p-4 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-ue-brand focus:border-ue-brand hover:-translate-y-0.5 hover:shadow-md cursor-pointer relative overflow-hidden group";
    
    if ($isMandatory) {
        $borderClass = "border-slate-200 hover:border-ue-brand/50";
        $indicatorClass = "bg-ue-brand";
    } else {
        $borderClass = "border-slate-200 hover:border-amber-400/50";
        $indicatorClass = "bg-amber-400";
    }
    
    if ($isMissingDescription) {
        // Subtle warning state for missing description
        $borderClass .= " border-amber-100 bg-amber-50/30";
    }
@endphp

<button 
    type="button"
    class="{{ $baseClasses }} {{ $borderClass }}"
    @click="selectedCourse = @js($programCourse); showDrawer = true"
    :class="{ 'ring-2 ring-ue-brand border-ue-brand bg-white shadow-md': selectedCourse && selectedCourse.id === {{ $programCourse['id'] }} }"
>
    {{-- Left color indicator --}}
    <div class="absolute left-0 top-0 bottom-0 w-1 {{ $indicatorClass }}"></div>

    <div class="flex justify-between items-start mb-2 pl-2">
        <div class="text-[11px] font-mono font-bold text-slate-500 bg-slate-100/80 border border-slate-200/60 px-2 py-0.5 rounded-md tracking-tight">
            {{ $course['code'] }}
        </div>
        
        <div class="flex items-center gap-1 shrink-0">
            @if($isMissingDescription)
                <div class="text-amber-500 bg-amber-100/50 rounded p-0.5" title="Thiếu mô tả chi tiết">
                    <x-ui.icon name="alert-triangle" size="xs" />
                </div>
            @endif
        </div>
    </div>

    <h4 class="font-bold text-slate-800 text-sm leading-snug mb-3 pl-2 group-hover:text-ue-brand transition-colors line-clamp-2">
        {{ $course['name'] }}
    </h4>

    <div class="flex flex-wrap items-center gap-x-2 gap-y-1.5 text-[11px] pl-2 mt-auto">
        <span class="font-bold px-1.5 py-0.5 rounded-md {{ $isMandatory ? 'bg-ue-brand/10 text-ue-brand' : 'bg-amber-400/10 text-amber-700' }}">
            {{ $isMandatory ? 'Bắt buộc' : 'Tự chọn' }}
        </span>
        <span class="text-slate-300">&bull;</span>
        @if($hasCredits)
            <span class="text-slate-600 font-bold bg-slate-100 px-1.5 py-0.5 rounded-md">{{ $course['credits'] }} TC</span>
        @else
            <span class="text-amber-600 font-bold bg-amber-50 px-1.5 py-0.5 rounded-md border border-amber-100/50">? TC</span>
        @endif
        
        @if($programCourse['knowledge_block'])
            <span class="text-slate-300">&bull;</span>
            <span class="text-slate-500 font-medium truncate max-w-[100px]" title="{{ $programCourse['knowledge_block'] }}">
                {{ $programCourse['knowledge_block'] }}
            </span>
        @endif
    </div>
</button>
