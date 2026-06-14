@props(['worktree'])

<div class="space-y-3" x-data="{ activeSemester: null }">
    @foreach($worktree['semesters'] as $index => $semester)
        @php
            $totalCourses = count($semester['courses']);
            $knownCreditsCount = collect($semester['courses'])->filter(fn($c) => is_numeric($c['course']['credits']))->count();
            $totalCredits = collect($semester['courses'])->sum('course.credits');
            $isFirst = $index === 0;
        @endphp
        
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm transition-all"
             x-data="{ 
                 id: {{ $semester['id'] }}, 
                 get expanded() { return this.activeSemester === this.id || (this.activeSemester === null && {{ $isFirst ? 'true' : 'false' }}) },
                 toggle() { this.activeSemester = this.expanded ? null : this.id }
             }"
             :class="expanded ? 'ring-2 ring-ue-brand/20 border-ue-brand/30' : ''"
        >
            {{-- Accordion Header --}}
            <button @click="toggle" class="w-full text-left px-5 py-4 flex items-center justify-between bg-white hover:bg-ue-brand-soft/50 transition-colors focus:outline-none">
                <div class="flex items-center gap-3.5">
                    <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm border border-slate-200 transition-colors"
                         :class="expanded ? 'bg-ue-brand text-white border-ue-brand' : ''">
                        {{ $semester['semester_number'] }}
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-800 text-sm uppercase tracking-wide">Học kỳ {{ $semester['semester_number'] }}</h3>
                        <div class="text-[11px] text-slate-500 font-medium mt-1 flex items-center gap-1.5">
                            <span class="bg-slate-100 px-1.5 py-0.5 rounded-md text-slate-600">{{ $totalCourses }} môn</span>
                            @if($knownCreditsCount === 0 && $totalCourses > 0)
                                <span class="bg-amber-50 px-1.5 py-0.5 rounded-md text-amber-600 font-bold border border-amber-100/50">? TC</span>
                            @elseif($knownCreditsCount < $totalCourses)
                                <span class="bg-slate-100 px-1.5 py-0.5 rounded-md text-slate-600">{{ $totalCredits }} TC (thiếu)</span>
                            @else
                                <span class="bg-slate-100 px-1.5 py-0.5 rounded-md text-slate-600">{{ $totalCredits }} TC</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-slate-400 transition-transform duration-300" :class="expanded ? 'rotate-180 text-ue-brand' : ''">
                    <x-ui.icon name="chevron-down" size="sm" />
                </div>
            </button>

            {{-- Accordion Body --}}
            <div x-show="expanded" x-collapse x-cloak>
                <div class="p-4 pt-1 bg-white border-t border-slate-100">
                    <div class="flex flex-col gap-2 mt-2">
                        @foreach($semester['courses'] as $programCourse)
                            <x-career-pathway.course-card :program-course="$programCourse" />
                        @endforeach

                        @if($totalCourses === 0)
                            <div class="text-center py-8 text-sm text-slate-400 bg-white rounded-xl border border-dashed border-slate-200 shadow-sm">
                                Chưa có môn học
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
