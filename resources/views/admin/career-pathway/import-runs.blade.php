<x-app-layout shell="admin">
    <x-slot name="title">Import runs Career Pathway</x-slot>

    @php
        $statusMeta = [
            'running' => ['label' => 'Đang chạy', 'class' => 'bg-sky-50 text-sky-700 border-sky-200'],
            'completed' => ['label' => 'Hoàn tất', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'failed' => ['label' => 'Thất bại', 'class' => 'bg-red-50 text-red-700 border-red-200'],
            'aborted' => ['label' => 'Đã hủy', 'class' => 'bg-slate-50 text-slate-700 border-slate-200'],
        ];

        $formatEnum = fn ($value) => $value instanceof BackedEnum ? $value->value : (string) $value;
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-4 border-b border-ue-border pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-ue-primary">Career Pathway</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-ue-text">Import runs</h1>
                <p class="mt-2 max-w-3xl text-sm font-medium leading-6 text-ue-text-muted">
                    Quản lý lịch sử nạp dữ liệu chương trình đào tạo, xem số tài liệu đã đọc và số vấn đề cần xử lý sau mỗi lần import.
                </p>
            </div>

            <a href="{{ route('admin.career-pathway.data-quality-issues.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-ue-border bg-white px-4 py-2 text-sm font-extrabold text-ue-text transition hover:bg-ue-primary/5">
                <x-ui.icon name="alert-triangle" size="sm" />
                Xem dữ liệu cần xử lý
            </a>
        </header>

        @if (session('status'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm font-bold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach([
                ['label' => 'Tổng runs', 'value' => $stats['total'], 'hint' => 'Đã ghi nhận'],
                ['label' => 'Đang chạy', 'value' => $stats['running'], 'hint' => 'Cần theo dõi'],
                ['label' => 'Hoàn tất', 'value' => $stats['completed'], 'hint' => 'Có thể dùng dữ liệu'],
                ['label' => 'Lỗi hoặc hủy', 'value' => $stats['failed'], 'hint' => 'Cần kiểm tra log'],
                ['label' => 'Vấn đề dữ liệu', 'value' => $stats['issues'], 'hint' => 'Từ tất cả runs'],
            ] as $card)
                <article class="rounded-2xl border border-ue-border bg-white p-4 shadow-sm">
                    <p class="text-xs font-bold text-ue-text-muted">{{ $card['label'] }}</p>
                    <p class="mt-2 font-mono text-3xl font-extrabold tabular-nums text-ue-text">{{ number_format($card['value']) }}</p>
                    <p class="mt-1 text-xs font-medium text-ue-text-muted">{{ $card['hint'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="mt-6 grid gap-6 lg:grid-cols-[360px_1fr]">
            <aside class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm lg:sticky lg:top-6 lg:self-start">
                <h2 class="text-base font-extrabold text-ue-text">Tạo import run</h2>
                <p class="mt-2 text-sm font-medium leading-6 text-ue-text-muted">Nhập đường dẫn thư mục dữ liệu đã chuẩn bị trên server. Tác vụ sẽ được đưa vào hàng đợi để parser xử lý.</p>

                <form method="POST" action="{{ route('admin.career-pathway.import-runs.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="path" class="text-xs font-bold text-ue-text-muted">Đường dẫn dữ liệu</label>
                        <input id="path" name="path" value="{{ old('path') }}" placeholder="Ví dụ: database/HCMUE-db_md" class="mt-1 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium text-ue-text focus:border-ue-primary focus:ring-2 focus:ring-ue-primary/15">
                        @error('path') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-ue-primary px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-ue-brand-active">
                        <x-ui.icon name="refresh-cw" size="sm" />
                        Bắt đầu import
                    </button>
                </form>

                <div class="mt-5 rounded-2xl border border-dashed border-ue-border bg-white p-4">
                    <h3 class="text-sm font-extrabold text-ue-text">Quy trình đề xuất</h3>
                    <ol class="mt-3 space-y-2 text-xs font-medium leading-5 text-ue-text-muted">
                        <li>1. Import dữ liệu nguồn.</li>
                        <li>2. Mở trang Dữ liệu cần xử lý.</li>
                        <li>3. Ưu tiên P0/P1 trước khi cho chương trình hiển thị công khai.</li>
                    </ol>
                </div>
            </aside>

            <div class="space-y-6">
                <section class="rounded-2xl border border-ue-border bg-white p-4 shadow-sm">
                    <form method="GET" action="{{ route('admin.career-pathway.import-runs.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div class="sm:w-72">
                            <label for="status" class="text-xs font-bold text-ue-text-muted">Trạng thái</label>
                            <select id="status" name="status" class="mt-1 w-full rounded-xl border border-ue-border bg-white px-3 py-2 text-sm font-bold text-ue-text">
                                <option value="">Tất cả trạng thái</option>
                                @foreach($statusMeta as $value => $meta)
                                    <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl bg-ue-primary px-4 text-sm font-extrabold text-white transition hover:bg-ue-brand-active">Lọc</button>
                        <a href="{{ route('admin.career-pathway.import-runs.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-ue-border px-4 text-sm font-extrabold text-ue-text transition hover:bg-ue-primary/5">Xóa lọc</a>
                    </form>
                </section>

                <section class="overflow-hidden rounded-2xl border border-ue-border bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-ue-border px-4 py-4">
                        <div>
                            <h2 class="text-base font-extrabold text-ue-text">Lịch sử import</h2>
                            <p class="mt-1 text-sm font-medium text-ue-text-muted">Mỗi run hiển thị log tóm tắt, số tài liệu và số issue phát sinh.</p>
                        </div>
                        <p class="text-xs font-bold text-ue-text-muted">{{ number_format($runs->total()) }} runs</p>
                    </div>

                    @if($runs->isEmpty())
                        <div class="p-10 text-center">
                            <h3 class="text-base font-extrabold text-ue-text">Chưa có import run phù hợp</h3>
                            <p class="mt-2 text-sm font-medium text-ue-text-muted">Hãy tạo import run mới hoặc xóa bộ lọc trạng thái để xem toàn bộ lịch sử.</p>
                        </div>
                    @else
                        <div class="divide-y divide-ue-border">
                            @foreach($runs as $run)
                                @php
                                    $status = $formatEnum($run->status);
                                @endphp
                                <article class="grid gap-4 px-4 py-4 transition hover:bg-ue-primary/5 lg:grid-cols-[120px_1fr_180px]">
                                    <div>
                                        <p class="font-mono text-sm font-extrabold text-ue-text">#{{ $run->id }}</p>
                                        <span class="mt-2 inline-flex rounded-lg border px-2 py-1 text-[11px] font-extrabold {{ $statusMeta[$status]['class'] ?? 'border-slate-200 bg-white text-slate-600' }}">
                                            {{ $statusMeta[$status]['label'] ?? $status }}
                                        </span>
                                    </div>

                                    <div>
                                        <p class="text-sm font-bold text-ue-text">
                                            Bắt đầu: {{ $run->started_at?->format('d/m/Y H:i') ?? 'Chưa ghi nhận' }}
                                            @if($run->completed_at)
                                                · Kết thúc: {{ $run->completed_at->format('d/m/Y H:i') }}
                                            @endif
                                        </p>
                                        <p class="mt-2 line-clamp-2 text-sm font-medium leading-6 text-ue-text-muted">
                                            {{ $run->log ?: 'Run này chưa có log tóm tắt. Kiểm tra số tài liệu và issue để đánh giá kết quả import.' }}
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap gap-2 lg:justify-end">
                                        <a href="{{ route('admin.career-pathway.data-quality-issues.index', ['import_run_id' => $run->id]) }}" class="rounded-xl border border-ue-border bg-white px-3 py-2 text-xs font-extrabold text-ue-text transition hover:border-ue-primary/40 hover:text-ue-primary">
                                            {{ $run->data_quality_issues_count }} vấn đề
                                        </a>
                                        <span class="rounded-xl border border-ue-border bg-white px-3 py-2 text-xs font-extrabold text-ue-text-muted">
                                            {{ $run->source_documents_count }} tài liệu
                                        </span>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="border-t border-ue-border px-4 py-4">
                            {{ $runs->onEachSide(1)->links() }}
                        </div>
                    @endif
                </section>
            </div>
        </section>
    </div>
</x-app-layout>
