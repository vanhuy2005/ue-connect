<x-app-layout shell="admin">
    <x-slot name="title">Thử nghiệm RAG Vector Search</x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.source-documents.index') }}" class="text-ue-primary hover:underline text-sm font-semibold">&larr; Quay lại quản lý tài liệu</a>
            </div>
            <h1 class="text-3xl font-bold text-ue-text mt-2">Thử nghiệm RAG Vector Search</h1>
            <p class="mt-2 text-sm text-ue-text-muted">Kiểm tra kết quả truy vấn semantic và điểm tương đồng cosine trực tiếp từ Qdrant.</p>
        </div>

        <x-ui.card variant="admin" class="mb-8">
            <x-slot name="header">Bộ lọc tìm kiếm</x-slot>

            <form method="GET" action="{{ route('admin.source-documents.test-search') }}" class="space-y-4 text-sm">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <x-ui.label for="query">Câu hỏi/Nội dung cần tìm kiếm</x-ui.label>
                        <x-ui.input id="query" name="query" value="{{ $query }}" placeholder="Ví dụ: Quy chế học lại tính điểm thế nào?" required class="mt-1 w-full" />
                    </div>

                    <div>
                        <x-ui.label for="cohort">Khóa tuyển sinh</x-ui.label>
                        <x-ui.input id="cohort" name="cohort" value="{{ $cohort }}" placeholder="Ví dụ: K48" class="mt-1 w-full" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-ui.label for="document_type">Loại tài liệu</x-ui.label>
                        <select id="document_type" name="document_type" class="mt-1 block w-full rounded-xl border border-ue-border bg-ue-input-bg p-2 text-sm text-ue-text">
                            <option value="">Tất cả</option>
                            <option value="student_handbook" {{ $documentType === 'student_handbook' ? 'selected' : '' }}>Sổ tay sinh viên</option>
                            <option value="regulation" {{ $documentType === 'regulation' ? 'selected' : '' }}>Quy chế & Quy định học vụ</option>
                            <option value="general_policy" {{ $documentType === 'general_policy' ? 'selected' : '' }}>Chính sách chung</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <x-ui.button type="submit" class="w-full justify-center">
                            Tìm kiếm Vector
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </x-ui.card>

        @if (! empty($query))
            <div class="mb-6">
                <h2 class="text-lg font-bold text-ue-text">Kết quả tìm kiếm cho: "{{ $query }}"</h2>
                <p class="text-xs text-ue-text-muted mt-1">Tìm thấy {{ count($results) }} đoạn văn bản tương đồng cao.</p>
            </div>

            @if (empty($results))
                <div class="rounded-xl border border-ue-border p-8 text-center text-ue-text-muted">
                    Không tìm thấy kết quả nào vượt qua điểm tương đồng ngưỡng tối thiểu ({{ config('ai.retrieval.min_score', 0.65) }}).
                </div>
            @else
                <div class="space-y-6">
                    @foreach ($results as $index => $result)
                        <div class="rounded-xl border border-ue-border bg-ue-card-bg p-6 space-y-4">
                            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-ue-border pb-3">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-ue-primary/10 text-ue-primary">
                                        Đoạn #{{ $index + 1 }}
                                    </span>
                                    <span class="ml-2 text-sm font-semibold text-ue-text">{{ $result['document_name'] }}</span>
                                    <span class="ml-1 text-xs text-ue-text-muted">({{ str_replace('_', ' ', $result['document_type']) }})</span>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                        Điểm tương đồng: {{ number_format($result['score'], 4) }}
                                    </span>
                                </div>
                            </div>

                            @if ($result['part'] || $result['chapter'] || $result['section'] || $result['article'])
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs text-ue-text-muted bg-ue-primary/5 p-3 rounded-lg">
                                    <div><strong>Phần:</strong> {{ $result['part'] ?: 'N/A' }}</div>
                                    <div><strong>Chương:</strong> {{ $result['chapter'] ?: 'N/A' }}</div>
                                    <div><strong>Mục:</strong> {{ $result['section'] ?: 'N/A' }}</div>
                                    <div><strong>Điều:</strong> {{ $result['article'] ?: 'N/A' }}</div>
                                </div>
                            @endif

                            <div class="text-sm text-ue-text-muted flex items-center justify-between text-xs mt-1">
                                <span>Trang: {{ $result['page_start'] ?: 'N/A' }} &rarr; {{ $result['page_end'] ?: 'N/A' }}</span>
                                @if ($result['cohort'])
                                    <span>Khóa: {{ $result['cohort'] }}</span>
                                @endif
                            </div>

                            <div class="text-ue-text text-sm leading-relaxed whitespace-pre-line bg-ue-input-bg p-4 rounded-xl border border-ue-border">
                                {{ $result['chunk_text'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
