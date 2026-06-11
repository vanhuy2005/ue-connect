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

        <!-- Data Coverage Dashboard -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-5 gap-6">
            <div class="rounded-2xl border border-ue-border bg-ue-input-bg p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-ue-text-muted font-bold">Tổng tài liệu</div>
                <div class="mt-2 text-3xl font-extrabold text-ue-primary">{{ $stats['total_files'] }}</div>
                <div class="mt-1 text-xs text-ue-text-muted">Lập chỉ mục: {{ $stats['total_files'] }} / Thư mục: {{ $stats['total_files_in_dir'] }}</div>
            </div>
            <div class="rounded-2xl border border-ue-border bg-ue-input-bg p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-ue-text-muted font-bold">Dữ liệu Vectors</div>
                <div class="mt-2 text-3xl font-extrabold text-indigo-600">{{ $stats['total_vectors'] }}</div>
                <div class="mt-1 text-xs text-ue-text-muted">Tổng chunks: {{ $stats['total_chunks'] }}</div>
            </div>
            <div class="rounded-2xl border border-ue-border bg-ue-input-bg p-5 shadow-sm col-span-1">
                <div class="text-xs font-semibold uppercase tracking-wider text-ue-text-muted font-bold">Trạng thái xử lý</div>
                <div class="mt-2 space-y-1 text-xs text-ue-text">
                    <div class="flex justify-between">
                        <span>Hoạt động:</span>
                        <span class="font-bold text-emerald-600">{{ $stats['file_ingested'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Chưa Ingest:</span>
                        <span class="font-bold text-blue-600">{{ $stats['file_not_ingested'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Cần OCR:</span>
                        <span class="font-bold text-yellow-600">{{ $stats['file_needs_ocr'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Thất bại:</span>
                        <span class="font-bold text-red-600">{{ $stats['file_failed'] }}</span>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-ue-border bg-ue-input-bg p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-ue-text-muted font-bold">Phân loại tài liệu</div>
                <div class="mt-2 space-y-1 text-xs text-ue-text">
                    @foreach($stats['type_distribution'] as $type => $count)
                        <div class="flex justify-between">
                            <span class="capitalize">{{ str_replace('_', ' ', $type) }}:</span>
                            <span class="font-bold text-ue-primary">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="rounded-2xl border border-ue-border bg-ue-input-bg p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-ue-text-muted font-bold">Phân phối theo Khóa</div>
                <div class="mt-2 max-h-20 overflow-y-auto space-y-1 text-xs text-ue-text">
                    @forelse($stats['cohort_distribution'] as $cohort => $count)
                        <div class="flex justify-between">
                            <span>Khóa {{ $cohort }}:</span>
                            <span class="font-bold text-ue-primary">{{ $count }}</span>
                        </div>
                    @empty
                        <div class="text-ue-text-muted text-xs">Không có khóa cụ thể.</div>
                    @endforelse
                </div>
            </div>
        </div>

        @if(!empty($stats['faculty_distribution']))
            <div class="mb-6 rounded-2xl border border-ue-border bg-ue-input-bg p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-ue-text uppercase tracking-wider mb-3">Phân phối tài liệu theo Khoa</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($stats['faculty_distribution'] as $fac => $count)
                        <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-medium bg-ue-primary/10 text-ue-primary">
                            {{ $fac }}: <strong class="ml-1 text-ue-primary">{{ $count }}</strong>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        @if(!empty($stats['major_distribution']))
            <div class="mb-8 rounded-2xl border border-ue-border bg-ue-input-bg p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-ue-text uppercase tracking-wider mb-3">Phân phối tài liệu theo Ngành học</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($stats['major_distribution'] as $maj => $count)
                        <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950 dark:text-indigo-300 dark:border-indigo-800">
                            {{ $maj }}: <strong class="ml-1 font-bold">{{ $count }}</strong>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

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
                                                <x-ui.button type="button" size="xs" variant="secondary" onclick="openRepairModal({{ json_encode($doc) }})">
                                                    Sửa Metadata
                                                </x-ui.button>

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

    <!-- Repair Metadata Modal -->
    <div id="repair-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeRepairModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-ue-card-bg border border-ue-border rounded-2xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <form id="repair-form" method="POST" action="">
                    @csrf
                    <div class="space-y-4">
                        <h3 class="text-lg leading-6 font-bold text-ue-text" id="modal-title">Sửa Metadata & payload tài liệu</h3>
                        
                        <div>
                            <x-ui.label for="repair_document_type">Loại tài liệu</x-ui.label>
                            <select id="repair_document_type" name="document_type" required class="mt-1 block w-full rounded-xl border border-ue-border bg-ue-input-bg p-2 text-sm text-ue-text dark:bg-gray-800">
                                <option value="student_handbook">Sổ tay sinh viên</option>
                                <option value="regulation">Quy chế & Quy định học vụ</option>
                                <option value="general_policy">Chính sách chung</option>
                            </select>
                        </div>

                        <div>
                            <x-ui.label for="repair_cohort">Khóa tuyển sinh</x-ui.label>
                            <x-ui.input id="repair_cohort" name="cohort" placeholder="Ví dụ: K51, K50" class="mt-1 w-full" />
                        </div>

                        <div>
                            <x-ui.label for="repair_effective_year">Năm hiệu lực</x-ui.label>
                            <x-ui.input id="repair_effective_year" name="effective_year" type="number" class="mt-1 w-full" />
                        </div>

                        <div>
                            <x-ui.label for="repair_faculty">Khoa áp dụng</x-ui.label>
                            <x-ui.input id="repair_faculty" name="faculty" placeholder="Ví dụ: Khoa Công nghệ thông tin" class="mt-1 w-full" />
                        </div>

                        <div>
                            <x-ui.label for="repair_major">Ngành học áp dụng</x-ui.label>
                            <x-ui.input id="repair_major" name="major" placeholder="Ví dụ: Sư phạm Tin học" class="mt-1 w-full" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-2">
                        <x-ui.button type="button" variant="secondary" onclick="closeRepairModal()">Hủy</x-ui.button>
                        <x-ui.button type="submit">Lưu thay đổi</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openRepairModal(doc) {
            const modal = document.getElementById('repair-modal');
            const form = document.getElementById('repair-form');
            
            form.action = `/admin/source-documents/${doc.id}/repair`;
            
            document.getElementById('repair_document_type').value = doc.document_type || 'student_handbook';
            document.getElementById('repair_cohort').value = doc.cohort || '';
            document.getElementById('repair_effective_year').value = doc.effective_year || new Date().getFullYear();
            
            let faculty = '';
            let major = '';
            if (doc.first_chunk && doc.first_chunk.metadata_json) {
                let meta = doc.first_chunk.metadata_json;
                if (typeof meta === 'string') {
                    try {
                        meta = JSON.parse(meta);
                    } catch(e) {}
                }
                faculty = meta.faculty || '';
                major = meta.major || '';
            }
            
            document.getElementById('repair_faculty').value = faculty;
            document.getElementById('repair_major').value = major;

            modal.classList.remove('hidden');
        }

        function closeRepairModal() {
            document.getElementById('repair-modal').classList.add('hidden');
        }
    </script>
</x-app-layout>
