@props(['worktree'])

<div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm relative overflow-hidden">
    {{-- Decorative background --}}
    <div class="absolute top-0 right-0 p-8 opacity-5 pointer-events-none">
        <x-ui.icon name="book-open" class="w-32 h-32" />
    </div>

    <div class="relative z-10 flex flex-col gap-5">
        <div>
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-800 tracking-tight mb-2">
                {{ $worktree['name'] }}
            </h2>
            <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500 font-medium">
                <span class="flex items-center gap-1.5"><x-ui.icon name="hash" size="xs" class="text-slate-400" /> Mã ngành: <span class="text-slate-700 font-bold">{{ $worktree['code'] ?? 'Chưa rõ' }}</span></span>
                <span class="w-1.5 h-1.5 rounded-full bg-slate-200"></span>
                <span class="flex items-center gap-1.5">
                    <x-ui.icon name="book" size="xs" class="text-slate-400" /> 
                    Tổng tín chỉ: 
                    @if(isset($worktree['total_credits']) && $worktree['total_credits'] > 0)
                        <span class="text-slate-700 font-bold">{{ $worktree['total_credits'] }}</span>
                    @else
                        <span class="text-amber-600 font-bold">Chưa rõ</span>
                    @endif
                </span>
                
                @if($worktree['source_document'])
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-200 hidden md:block"></span>
                    <span class="flex items-center gap-1.5 w-full md:w-auto mt-2 md:mt-0 text-xs">
                        <x-ui.icon name="file-text" size="xs" class="text-slate-400" />
                        <span class="truncate max-w-[200px]" title="{{ $worktree['source_document']['original_filename'] }}">{{ $worktree['source_document']['original_filename'] }}</span>
                        <span class="text-slate-400">·</span>
                        <span title="Cập nhật"> {{ \Carbon\Carbon::parse($worktree['source_document']['extracted_at'])->format('d/m/Y') }}</span>
                    </span>
                @endif
            </div>
        </div>

        @php
            $statusValue = $worktree['status'] instanceof \App\Enums\ProgramStatus 
                ? $worktree['status']->value 
                : $worktree['status'];
        @endphp
        
        <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-slate-100">
            @if($statusValue === \App\Enums\ProgramStatus::READY->value)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                    <x-ui.icon name="check-circle" size="xs" />
                    Dữ liệu đã sẵn sàng
                </span>
            @elseif($statusValue === \App\Enums\ProgramStatus::READY_WITH_MISSING_DESCRIPTIONS->value)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200">
                    <x-ui.icon name="alert-triangle" size="xs" />
                    Một số môn còn thiếu mô tả
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 text-slate-700 text-xs font-bold border border-slate-200">
                    <x-ui.icon name="refresh-cw" size="xs" />
                    Dữ liệu đang được đối chiếu
                </span>
            @endif
            
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-200">
                <x-ui.icon name="layers" size="xs" />
                {{ count($worktree['semesters']) }} Học kỳ
            </span>

            @if(!empty($worktree['quality_warnings']))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-red-50 text-red-700 text-xs font-bold border border-red-200">
                    <x-ui.icon name="alert-triangle" size="xs" />
                    {{ count($worktree['quality_warnings']) }} vấn đề dữ liệu
                </span>
            @endif
        </div>
    </div>
</div>
