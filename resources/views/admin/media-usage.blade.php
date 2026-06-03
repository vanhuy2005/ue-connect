<x-app-layout>
    <div class="mx-auto max-w-6xl px-4 py-8 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Media usage</h1>
            <p class="text-sm text-slate-500">Upload, storage, and Cloudinary sync quota status.</p>
        </div>

        @php
            $global = $report['global'];
            $bytes = fn (int $value): string => number_format($value / 1024 / 1024, 2).' MB';
        @endphp

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Global daily upload</div>
                <div class="mt-2 text-xl font-bold text-slate-900">{{ $bytes($global['daily_upload_bytes']) }}</div>
                <div class="text-sm text-slate-500">Limit {{ $bytes($global['limits']['global_daily_upload_bytes']) }}</div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Storage footprint</div>
                <div class="mt-2 text-xl font-bold text-slate-900">{{ $bytes($global['storage_bytes']) }}</div>
                <div class="text-sm text-slate-500">Media originals and variants</div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Cloudinary sync today</div>
                <div class="mt-2 text-xl font-bold text-slate-900">{{ $global['cloudinary_synced_today'] }}</div>
                <div class="text-sm text-slate-500">Limit {{ $global['limits']['cloudinary_daily_sync_limit'] }}</div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-bold text-slate-800">Top uploaders today</h2>
            </div>
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">User ID</th>
                        <th class="px-5 py-3">Uploads</th>
                        <th class="px-5 py-3">Upload MB</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($report['top_users_today'] as $row)
                        <tr>
                            <td class="px-5 py-3 font-semibold text-slate-700">#{{ $row->user_id }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $row->upload_count }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $bytes((int) $row->upload_bytes) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-slate-400">No uploads today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
