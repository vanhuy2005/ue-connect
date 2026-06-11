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
                        <select id="document_type" name="document_type" class="mt-1 block w-full rounded-xl border border-ue-border bg-ue-input-bg p-2 text-sm text-ue-text dark:bg-gray-800">
                            <option value="">Tất cả</option>
                            <option value="student_handbook" {{ $documentType === 'student_handbook' ? 'selected' : '' }}>Sổ tay sinh viên</option>
                            <option value="regulation" {{ $documentType === 'regulation' ? 'selected' : '' }}>Quy chế & Quy định học vụ</option>
                            <option value="general_policy" {{ $documentType === 'general_policy' ? 'selected' : '' }}>Chính sách chung</option>
                        </select>
                    </div>

                    <div class="flex flex-col justify-end space-y-2">
                        <div class="flex items-center space-x-2 py-2">
                            <input type="checkbox" id="debug" name="debug" value="1" {{ $debug ? 'checked' : '' }} class="rounded border-ue-border text-ue-primary focus:ring-ue-primary bg-ue-input-bg dark:bg-gray-800" />
                            <x-ui.label for="debug">Kích hoạt Chế độ Debug Retrieval</x-ui.label>
                        </div>
                        <x-ui.button type="submit" class="w-full justify-center">
                            Tìm kiếm Vector
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </x-ui.card>

        @if (! empty($query))
            <!-- Debug Info panel -->
            @if ($debug && !empty($debugData))
                <div class="mb-8 rounded-2xl border border-blue-200 bg-blue-50/50 p-6 space-y-4 dark:border-blue-900/50 dark:bg-blue-950/20">
                    <h3 class="text-base font-bold text-blue-900 dark:text-blue-400">Kết quả phân tích truy vấn (AcademicQueryAnalyzer)</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div class="space-y-2">
                            <h4 class="font-semibold text-ue-text">1. Phân tích thực thể:</h4>
                            <ul class="list-disc pl-5 space-y-1 text-ue-text-muted">
                                <li><strong>Khóa tuyển sinh:</strong> {{ $debugData['analysis']['cohort'] ?: 'Không phát hiện' }} (Năm tuyển sinh: {{ $debugData['analysis']['academic_year'] ?: 'N/A' }})</li>
                                <li><strong>Khoa:</strong> {{ $debugData['analysis']['faculty'] ?: 'Không phát hiện' }}</li>
                                <li><strong>Ngành học:</strong> {{ $debugData['analysis']['major'] ?: 'Không phát hiện' }}</li>
                                <li><strong>Loại tài liệu:</strong> <span class="uppercase text-ue-primary font-bold">{{ $debugData['analysis']['document_type'] }}</span></li>
                                <li><strong>Chủ đề (Topics):</strong> {{ !empty($debugData['analysis']['topics']) ? implode(', ', $debugData['analysis']['topics']) : 'Không phát hiện' }}</li>
                                <li><strong>Định tuyến (Selected Route):</strong> <span class="font-bold text-indigo-600 dark:text-indigo-400 uppercase">{{ $debugData['route'] }}</span> (Intent: {{ $debugData['intent'] }} | Confidence: {{ number_format($debugData['confidence'], 2) }})</li>
                            </ul>
                        </div>

                        <div class="space-y-2">
                            <h4 class="font-semibold text-ue-text">2. Phân tích bộ lọc (Qdrant Filters):</h4>
                            <pre class="bg-ue-input-bg border border-ue-border p-3 rounded-xl text-xs overflow-x-auto dark:bg-gray-800">{{ json_encode($debugData['resolved_filters'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-blue-200/50 dark:border-blue-900/50">
                        <h4 class="font-semibold text-ue-text">3. Truy vấn mở rộng (Multi-Query Variations):</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($debugData['variations'] as $v)
                                <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs bg-ue-primary/10 text-ue-primary border border-ue-primary/20">
                                    "{{ $v }}"
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

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
                                <div class="text-right flex items-center space-x-2">
                                    @if (isset($result['rerank_score']))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                                            Điểm Reranked: {{ number_format($result['rerank_score'], 4) }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                            Vector gốc: {{ number_format($result['score'], 4) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                            Điểm tương đồng: {{ number_format($result['score'], 4) }}
                                        </span>
                                    @endif
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

                            <div class="text-ue-text text-sm leading-relaxed whitespace-pre-line bg-ue-input-bg p-4 rounded-xl border border-ue-border dark:bg-gray-800">
                                {{ $result['chunk_text'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
