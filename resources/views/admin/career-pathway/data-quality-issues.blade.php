<x-app-layout shell="admin">
    <x-slot name="title">Dữ liệu cần xử lý</x-slot>

    @php
        $severityMeta = [
            'p0' => ['label' => 'P0 chặn import', 'class' => 'bg-red-50 text-red-700 border-red-200'],
            'p1' => ['label' => 'P1 cần xử lý', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
            'p2' => ['label' => 'P2 ghi chú', 'class' => 'bg-sky-50 text-sky-700 border-sky-200'],
        ];

        $issueLabels = [
            'unresolved_semester_structure' => 'Cấu trúc học kỳ chưa rõ',
            'empty_markdown' => 'Markdown rỗng',
            'missing_curriculum_pdf' => 'Thiếu PDF chương trình',
            'partial_semester_extraction' => 'Trích xuất học kỳ chưa đủ',
            'missing_course_descriptions' => 'Thiếu mô tả môn học',
            'invalid_course_row' => 'Dòng môn học lỗi',
            'invalid_semester_number' => 'Số học kỳ lỗi',
            'missing_program_metadata' => 'Thiếu metadata chương trình',
            'duplicate_course' => 'Trùng môn học',
            'unknown' => 'Chưa phân loại',
        ];

        $formatEnum = fn ($value) => $value instanceof BackedEnum ? $value->value : (string) $value;
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-4 border-b border-ue-border pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-ue-primary">Career Pathway</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-ue-text">Dữ liệu cần xử lý</h1>
                <p class="mt-2 max-w-3xl text-sm font-medium leading-6 text-ue-text-muted">
                    Theo dõi lỗi trích xuất chương trình đào tạo, ưu tiên những vấn đề ảnh hưởng trực tiếp đến bản đồ học tập công khai.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.career-pathway.import-runs.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-ue-border bg-white px-4 py-2 text-sm font-extrabold text-ue-text transition hover:bg-ue-primary/5">
                    <x-ui.icon name="refresh-cw" size="sm" />
                    Xem import runs
                </a>
                <a href="{{ route('app.career-pathway.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-ue-primary px-4 py-2 text-sm font-extrabold text-white transition hover:bg-ue-brand-active">
                    <x-ui.icon name="map" size="sm" />
                    Về Bản đồ học tập
                </a>
            </div>
        </header>

        <section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach([
                ['label' => 'Tổng vấn đề', 'value' => $stats['total'], 'hint' => 'Tất cả import runs'],
                ['label' => 'P0', 'value' => $stats['p0'], 'hint' => 'Cần chặn công khai'],
                ['label' => 'P1', 'value' => $stats['p1'], 'hint' => 'Nên xử lý sớm'],
                ['label' => 'P2', 'value' => $stats['p2'], 'hint' => 'Theo dõi sau'],
                ['label' => 'Chương trình ảnh hưởng', 'value' => $stats['programs_affected'], 'hint' => 'Có issue gắn chương trình'],
            ] as $card)
                <article class="rounded-2xl border border-ue-border bg-white p-4 shadow-sm">
                    <p class="text-xs font-bold text-ue-text-muted">{{ $card['label'] }}</p>
                    <p class="mt-2 font-mono text-3xl font-extrabold tabular-nums text-ue-text">{{ number_format($card['value']) }}</p>
                    <p class="mt-1 text-xs font-medium text-ue-text-muted">{{ $card['hint'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="mt-6 rounded-2xl border border-ue-border bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('admin.career-pathway.data-quality-issues.index') }}" class="grid gap-3 lg:grid-cols-[1fr_1fr_1fr_auto]">
                <div>
                    <label for="severity" class="text-xs font-bold text-ue-text-muted">Mức ưu tiên</label>
                    <select id="severity" name="severity" class="mt-1 w-full rounded-xl border border-ue-border bg-white px-3 py-2 text-sm font-bold text-ue-text">
                        <option value="">Tất cả mức</option>
                        @foreach($severityMeta as $value => $meta)
                            <option value="{{ $value }}" @selected($filters['severity'] === $value)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="issue_type" class="text-xs font-bold text-ue-text-muted">Loại vấn đề</label>
                    <select id="issue_type" name="issue_type" class="mt-1 w-full rounded-xl border border-ue-border bg-white px-3 py-2 text-sm font-bold text-ue-text">
                        <option value="">Tất cả loại</option>
                        @foreach($issueLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['issue_type'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="import_run_id" class="text-xs font-bold text-ue-text-muted">Import run</label>
                    <select id="import_run_id" name="import_run_id" class="mt-1 w-full rounded-xl border border-ue-border bg-white px-3 py-2 text-sm font-bold text-ue-text">
                        <option value="">Tất cả runs</option>
                        @foreach($importRuns as $run)
                            <option value="{{ $run->id }}" @selected($filters['import_run_id'] === (string) $run->id)>#{{ $run->id }} · {{ $formatEnum($run->status) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl bg-ue-primary px-4 text-sm font-extrabold text-white transition hover:bg-ue-brand-active">Lọc</button>
                    <a href="{{ route('admin.career-pathway.data-quality-issues.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-ue-border px-4 text-sm font-extrabold text-ue-text transition hover:bg-ue-primary/5">Xóa lọc</a>
                </div>
            </form>

            @if($issueTypeCounts->isNotEmpty())
                <div class="mt-4 flex flex-wrap gap-2 border-t border-ue-border pt-4">
                    @foreach($issueTypeCounts as $type => $count)
                        <a href="{{ route('admin.career-pathway.data-quality-issues.index', ['issue_type' => $type]) }}" class="rounded-xl border border-ue-border bg-white px-3 py-1.5 text-xs font-bold text-ue-text-muted transition hover:border-ue-primary/40 hover:text-ue-primary">
                            {{ $issueLabels[$type] ?? $type }} · {{ $count }}
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="mt-6 overflow-hidden rounded-2xl border border-ue-border bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-ue-border px-4 py-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-ue-text">Danh sách vấn đề</h2>
                    <p class="mt-1 text-sm font-medium text-ue-text-muted">Không hiển thị raw JSON; chỉ giữ ngữ cảnh đủ để xác định nguồn lỗi và bước xử lý.</p>
                </div>
                <p class="text-xs font-bold text-ue-text-muted">{{ number_format($issues->total()) }} kết quả</p>
            </div>

            @if($issues->isEmpty())
                <div class="p-10 text-center">
                    <h3 class="text-base font-extrabold text-ue-text">Không có vấn đề phù hợp bộ lọc</h3>
                    <p class="mt-2 text-sm font-medium text-ue-text-muted">Thử xóa bộ lọc hoặc kiểm tra import run khác để tiếp tục rà soát dữ liệu.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-ue-border text-left text-sm">
                        <thead class="bg-white">
                            <tr class="text-xs font-extrabold uppercase tracking-wider text-ue-text-muted">
                                <th class="px-4 py-3">Vấn đề</th>
                                <th class="px-4 py-3">Chương trình</th>
                                <th class="px-4 py-3">Nguồn</th>
                                <th class="px-4 py-3">Thời điểm</th>
                                <th class="px-4 py-3 text-right">Mã</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ue-border">
                            @foreach($issues as $issue)
                                @php
                                    $severity = $formatEnum($issue->severity);
                                    $issueType = $formatEnum($issue->issue_type);
                                    $program = $issue->careerProgram;
                                @endphp
                                <tr class="align-top transition hover:bg-ue-primary/5">
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex rounded-lg border px-2 py-1 text-[11px] font-extrabold {{ $severityMeta[$severity]['class'] ?? 'border-slate-200 bg-white text-slate-600' }}">
                                                {{ $severityMeta[$severity]['label'] ?? strtoupper($severity) }}
                                            </span>
                                            <span class="text-xs font-bold text-ue-primary">{{ $issueLabels[$issueType] ?? $issueType }}</span>
                                        </div>
                                        <p class="mt-2 max-w-xl text-sm font-semibold leading-6 text-ue-text">{{ $issue->message }}</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        @if($program)
                                            <p class="max-w-xs font-bold leading-5 text-ue-text">{{ $program->name }}</p>
                                            <p class="mt-1 text-xs font-medium text-ue-text-muted">Trạng thái: {{ $formatEnum($program->status) }}</p>
                                            <p class="mt-1 text-xs font-medium text-ue-text-muted">{{ $program->total_credits ?? 0 }} tín chỉ · {{ $program->total_semesters ?? 0 }} học kỳ</p>
                                        @else
                                            <span class="text-xs font-bold text-ue-text-muted">Chưa gắn chương trình</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-xs font-medium leading-5 text-ue-text-muted">
                                        <p class="font-bold text-ue-text">Run #{{ $issue->import_run_id }}</p>
                                        <p>Source document #{{ $issue->source_document_id ?? 'N/A' }}</p>
                                        @if($issue->sourceDocument)
                                            <p class="mt-1 max-w-xs truncate" title="{{ $issue->sourceDocument->file_path }}">{{ $issue->sourceDocument->file_path }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-xs font-medium text-ue-text-muted">
                                        {{ $issue->created_at?->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-4 text-right font-mono text-xs font-bold text-ue-text-muted">
                                        #{{ $issue->id }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-ue-border px-4 py-4">
                    {{ $issues->onEachSide(1)->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
