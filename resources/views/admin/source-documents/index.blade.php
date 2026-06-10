<x-app-layout shell="admin">
    <x-slot name="title">Quản lý Tài liệu RAG</x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-ue-text">Quản lý Tài liệu RAG</h1>
                <p class="mt-2 text-sm text-ue-text-muted">Quản lý Sổ tay sinh viên, quy chế học vụ và tài liệu dài để huấn luyện chatbot.</p>
            </div>
            <div>
                <x-ui.button href="{{ route('admin.source-documents.test-search') }}" variant="secondary">
                    Thử nghiệm tìm kiếm Vector
                </x-ui.button>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-950/30 dark:border-emerald-800 dark:text-emerald-400">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-950/30 dark:border-red-800 dark:text-red-400">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Upload Form -->
            <div class="lg:col-span-1">
                <x-ui.card variant="admin" class="sticky top-6">
                    <x-slot name="header">Tải lên tài liệu mới</x-slot>

                    <form method="POST" action="{{ route('admin.source-documents.store') }}" enctype="multipart/form-data" class="space-y-4 text-sm">
                        @csrf

                        <div>
                            <x-ui.label for="file">Tệp tài liệu (PDF, MD, TXT)</x-ui.label>
                            <input type="file" id="file" name="file" required class="mt-1 block w-full text-sm text-ue-text file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-ue-primary/10 file:text-ue-primary hover:file:bg-ue-primary/20 bg-ue-input-bg border border-ue-border rounded-xl p-2" />
                        </div>

                        <div>
                            <x-ui.label for="title">Tiêu đề tài liệu</x-ui.label>
                            <x-ui.input id="title" name="title" value="{{ old('title') }}" placeholder="Nhập tiêu đề hoặc tự động lấy tên tệp" class="mt-1 w-full" />
                        </div>

                        <div>
                            <x-ui.label for="document_type">Loại tài liệu</x-ui.label>
                            <select id="document_type" name="document_type" required class="mt-1 block w-full rounded-xl border border-ue-border bg-ue-input-bg p-2 text-sm text-ue-text">
                                <option value="student_handbook" {{ old('document_type') === 'student_handbook' ? 'selected' : '' }}>Sổ tay sinh viên</option>
                                <option value="regulation" {{ old('document_type') === 'regulation' ? 'selected' : '' }}>Quy chế & Quy định học vụ</option>
                                <option value="general_policy" {{ old('document_type') === 'general_policy' ? 'selected' : '' }}>Chính sách chung</option>
                            </select>
                        </div>

                        <div>
                            <x-ui.label for="cohort">Khóa tuyển sinh áp dụng</x-ui.label>
                            <x-ui.input id="cohort" name="cohort" value="{{ old('cohort') }}" placeholder="Ví dụ: K48, K49, K50" class="mt-1 w-full" />
                        </div>

                        <div>
                            <x-ui.label for="effective_year">Năm hiệu lực</x-ui.label>
                            <x-ui.input id="effective_year" name="effective_year" type="number" value="{{ old('effective_year', date('Y')) }}" placeholder="Ví dụ: 2026" class="mt-1 w-full" />
                        </div>

                        <div>
                            <x-ui.label for="source_url">Nguồn URL (nếu có)</x-ui.label>
                            <x-ui.input id="source_url" name="source_url" type="url" value="{{ old('source_url') }}" placeholder="https://hcmue.edu.vn/..." class="mt-1 w-full" />
                        </div>

                        <div class="flex items-center justify-end pt-4">
                            <x-ui.button type="submit" class="w-full justify-center">
                                Tải lên & Phân tích
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>

            <!-- Documents List -->
            <div class="lg:col-span-2">
                <x-ui.card variant="admin">
                    <x-slot name="header">Danh sách tài liệu nguồn</x-slot>

                    @if ($documents->isEmpty())
                        <div class="py-12 text-center text-ue-text-muted">
                            Chưa có tài liệu nguồn nào được tải lên.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-ue-border text-sm text-left">
                                <thead>
                                    <tr class="text-xs font-semibold uppercase tracking-wider text-ue-text-muted">
                                        <th class="py-3 px-4">Tài liệu</th>
                                        <th class="py-3 px-4">Thông tin bổ sung</th>
                                        <th class="py-3 px-4">Trạng thái</th>
                                        <th class="py-3 px-4">Số chunk</th>
                                        <th class="py-3 px-4 text-right">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-ue-border text-ue-text">
                                    @foreach ($documents as $doc)
                                        <tr>
                                            <td class="py-4 px-4">
                                                <div class="font-semibold text-ue-text break-words max-w-xs">{{ $doc->title }}</div>
                                                <div class="text-xs text-ue-text-muted mt-1 uppercase">{{ str_replace('_', ' ', $doc->document_type) }}</div>
                                            </td>
                                            <td class="py-4 px-4 text-xs text-ue-text-muted">
                                                <div>Khóa: {{ $doc->cohort ?: 'Tất cả' }}</div>
                                                <div>Năm: {{ $doc->effective_year ?: 'N/A' }}</div>
                                            </td>
                                            <td class="py-4 px-4">
                                                @if ($doc->status === 'active')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                                        Hoạt động
                                                    </span>
                                                @elseif ($doc->status === 'processing')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 animate-pulse">
                                                        Đang xử lý
                                                    </span>
                                                @elseif ($doc->status === 'uploaded')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-yellow-300">
                                                        Mới tải lên
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300">
                                                        Thất bại
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-4 px-4 font-semibold text-ue-primary">
                                                {{ $doc->chunks_count }}
                                            </td>
                                            <td class="py-4 px-4 text-right space-y-1 sm:space-y-0 sm:space-x-1">
                                                <form method="POST" action="{{ route('admin.source-documents.ingest', $doc) }}" class="inline">
                                                    @csrf
                                                    <x-ui.button type="submit" size="xs" variant="secondary" title="Re-ingest / embed document">
                                                        Phân tích lại
                                                    </x-ui.button>
                                                </form>

                                                <form method="POST" action="{{ route('admin.source-documents.destroy', $doc) }}" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài liệu này cùng toàn bộ vector liên quan?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-ui.button type="submit" size="xs" variant="danger">
                                                        Xóa
                                                    </x-ui.button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $documents->links() }}
                        </div>
                    @endif
                </x-ui.card>
            </div>
        </div>
    </div>
</x-app-layout>
