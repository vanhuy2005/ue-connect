@props(['worktree'])

<style>
    /* Desktop Worktree Specific Styles */
    .cp-desktop-worktree {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .cp-semester-row {
        display: grid;
        grid-template-columns: 200px 1fr;
        gap: 1.5rem;
        position: relative;
    }

    /* Rail line */
    .cp-semester-rail::before {
        content: "";
        position: absolute;
        top: 2.25rem;
        bottom: -1.5rem;
        left: 0.75rem; /* 12px from left, centers under a 24px icon */
        width: 2px;
        background-color: #e2e8f0; /* slate-200 */
        z-index: 0;
    }

    /* Remove bottom rail line for the last semester */
    .cp-semester-row:last-child .cp-semester-rail::before {
        display: none;
    }

    /* Horizontal branch from rail to course cluster */
    .cp-semester-row::after {
        content: "";
        position: absolute;
        top: 1.5rem; /* align with the middle of the semester label */
        left: 200px;
        width: 1.5rem;
        height: 2px;
        background-color: #e2e8f0; /* slate-200 */
        z-index: 0;
    }

    .cp-course-cluster {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 0.75rem;
        position: relative;
        z-index: 10;
    }
</style>

<div class="cp-desktop-worktree">
    @foreach($worktree['semesters'] as $semester)
        @php
            $totalCourses = count($semester['courses']);
            $knownCreditsCount = collect($semester['courses'])->filter(fn($c) => is_numeric($c['course']['credits']))->count();
            $totalCredits = collect($semester['courses'])->sum('course.credits');
            $missingDescriptionsCount = collect($semester['courses'])->filter(fn($c) => empty($c['course']['description']))->count();
        @endphp
        
        <section class="cp-semester-row group">
            {{-- Semester Rail Node --}}
            <div class="cp-semester-rail relative">
                <div class="relative z-10 flex items-start gap-3 py-2 pr-2">
                    <div class="w-6 h-6 mt-0.5 flex-shrink-0 rounded-full border-4 border-slate-50 bg-ue-brand text-white flex items-center justify-center shadow-sm relative z-10">
                        {{-- Small inner dot --}}
                        <div class="w-1.5 h-1.5 bg-white rounded-full"></div>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-800 text-sm uppercase tracking-wide">Học kỳ {{ $semester['semester_number'] }}</h3>
                        <div class="text-xs text-slate-500 font-medium mt-1 flex flex-col gap-0.5">
                            <span>{{ $totalCourses }} môn học</span>
                            @if($knownCreditsCount === 0 && $totalCourses > 0)
                                <span class="text-amber-600">Chưa rõ tín chỉ</span>
                            @elseif($knownCreditsCount < $totalCourses)
                                <span class="text-slate-500">{{ $totalCredits }} tín chỉ (còn thiếu)</span>
                            @else
                                <span class="text-slate-500">{{ $totalCredits }} tín chỉ</span>
                            @endif
                        </div>
                        @if($missingDescriptionsCount > 0)
                            <div class="mt-2 inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-amber-50 text-amber-600 text-[10px] font-bold border border-amber-100">
                                <x-ui.icon name="alert-triangle" size="xs" />
                                {{ $missingDescriptionsCount }} môn thiếu mô tả
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Course Cluster Grid --}}
            <div class="cp-course-cluster">
                @foreach($semester['courses'] as $programCourse)
                    <x-career-pathway.course-card :program-course="$programCourse" />
                @endforeach
                
                @if($totalCourses === 0)
                    <div class="border border-dashed border-slate-300 rounded-xl p-4 flex items-center justify-center bg-white text-slate-400 text-sm">
                        Chưa có môn học
                    </div>
                @endif
            </div>
        </section>
    @endforeach
</div>
