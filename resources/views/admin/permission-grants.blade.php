<x-app-layout shell="admin">
    <x-slot name="title">Cấp quyền</x-slot>
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-4">Cấp quyền</h1>

    @if(session('status'))
        <div class="mb-4 p-3 bg-green-100 text-green-800">{{ session('status') }}</div>
    @endif

    <div class="grid grid-cols-2 gap-6">
        <div class="bg-white p-4 rounded shadow">
            <h2 class="font-medium mb-2">Tạo cấp quyền</h2>
            <form method="POST" action="{{ route('admin.permission-grants.store') }}">
                @csrf

                <div class="mb-2">
                    <label class="block text-sm">ID người dùng</label>
                    <input name="user_id" class="w-full border rounded p-2" />
                </div>

                <div class="mb-2">
                    <label class="block text-sm">Quyền</label>
                    <select name="permission_key" class="w-full border rounded p-2">
                        @foreach($permissionKeys as $key)
                            <option value="{{ $key }}">{{ $key }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label class="block text-sm">Phạm vi</label>
                    <select name="scope_type" class="w-full border rounded p-2">
                        <option value="">Global</option>
                        <option value="community">Community</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="block text-sm">Scope ID (community_id nếu chọn Community)</label>
                    <input name="scope_id" class="w-full border rounded p-2" />
                </div>

                <div class="mb-2">
                    <label class="block text-sm">Lý do</label>
                    <textarea name="reason" class="w-full border rounded p-2" rows="3"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">Cấp</button>
                </div>
            </form>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <h2 class="font-medium mb-2">Các quyền đã cấp gần đây</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left">
                        <th>Người dùng</th>
                        <th>Quyền</th>
                        <th>Trạng thái</th>
                        <th>Được cấp bởi</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grants as $g)
                        <tr class="border-t">
                            <td>{{ $g->user?->id ?? $g->user_id }}</td>
                            <td>{{ $g->permission_key }}</td>
                            <td>{{ $g->status }}</td>
                            <td>{{ $g->granter?->id ?? $g->granted_by }}</td>
                            <td>
                                @if($g->status !== 'revoked')
                                    <form method="POST" action="{{ route('admin.permission-grants.revoke', $g) }}">
                                        @csrf
                                        <input type="hidden" name="reason" value="Admin revoke via UI" />
                                        <button class="text-red-600" onclick="return confirm('Bạn có chắc muốn thu hồi quyền này?')">Thu hồi</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">{{ $grants->links() }}</div>
        </div>
    </div>
</div>
</x-app-layout>
