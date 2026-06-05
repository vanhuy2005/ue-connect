<x-app-layout shell="admin">
    <x-slot name="title">Nhật ký audit</x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-end justify-between gap-4">
                <div>
                <h1 class="text-3xl font-bold text-ue-text">{{ __('audit.title') }}</h1>
                <p class="mt-2 text-sm text-ue-text-muted">{{ __('audit.subtitle') }}</p>
            </div>
        </div>

        <x-ui.card variant="admin" class="mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <x-ui.label for="actor_id">{{ __('audit.filters.actor_id') }}</x-ui.label>
                    <x-ui.input id="actor_id" name="actor_id" value="{{ request('actor_id') }}" class="mt-1" />
                </div>
                <div>
                    <x-ui.label for="action">{{ __('audit.filters.action') }}</x-ui.label>
                    <x-ui.input id="action" name="action" value="{{ request('action') }}" class="mt-1" />
                </div>
                <div class="flex items-end gap-2">
                    <x-ui.button type="submit" size="sm">Lọc</x-ui.button>
                    <x-ui.button variant="secondary" size="sm" href="{{ route('admin.audit-logs.index') }}">Xóa lọc</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card variant="admin" padding="none" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ue-border text-sm">
                    <thead class="bg-ue-surface-subtle text-xs font-semibold uppercase tracking-wider text-ue-text-muted">
                            <tr>
                                <th class="px-6 py-4 text-left">{{ __('audit.table.when') }}</th>
                                <th class="px-6 py-4 text-left">{{ __('audit.table.actor') }}</th>
                                <th class="px-6 py-4 text-left">{{ __('audit.table.action') }}</th>
                                <th class="px-6 py-4 text-left">{{ __('audit.table.target') }}</th>
                                <th class="px-6 py-4 text-left">{{ __('audit.table.reason') }}</th>
                            </tr>
                    </thead>
                    <tbody class="divide-y divide-ue-border bg-ue-surface">
                        @forelse($logs as $log)
                            <tr>
                                <td class="px-6 py-4 text-ue-text-muted">{{ optional($log->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="px-6 py-4 text-ue-text">{{ $log->actor?->name ?? ($log->actor?->id ?? 'system') }}</td>
                                <td class="px-6 py-4 text-ue-text">{{ $log->action }}</td>
                                <td class="px-6 py-4 text-ue-text-muted">{{ $log->target_type }}:{{ $log->target_id }}</td>
                                <td class="px-6 py-4 text-ue-text-muted max-w-xl">{{ \Illuminate\Support\Str::limit($log->reason, 160) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-ue-text-muted">{{ __('audit.table.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-ue-border">
                {{ $logs->links() }}
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
