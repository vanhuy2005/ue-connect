<x-app-layout shell="admin">
    <x-slot name="title">Nhật ký audit</x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-ue-brand-tint border border-ue-brand-border rounded-lg">
                        <x-ui.icon name="shield" class="w-6 h-6 text-ue-brand" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-2xl sm:text-3xl font-bold text-ue-text">{{ __('audit.title') }}</h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-ue-brand-tint text-ue-brand border border-ue-brand-border">
                                {{ number_format($logs->total()) }} bản ghi
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-ue-text-muted">{{ __('audit.subtitle') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Form -->
        <x-ui.card variant="admin" class="shadow-sm">
            <form method="GET" class="flex flex-col md:flex-row items-end gap-4 text-sm">
                <div class="w-full md:w-72">
                    <x-ui.label for="actor_id" class="text-ue-text-secondary font-medium">{{ __('audit.filters.actor_id') }}</x-ui.label>
                    <x-ui.input id="actor_id" name="actor_id" value="{{ request('actor_id') }}" placeholder="VD: 1" class="mt-1.5" />
                </div>
                <div class="w-full md:w-72">
                    <x-ui.label for="action" class="text-ue-text-secondary font-medium">{{ __('audit.filters.action') }}</x-ui.label>
                    <x-ui.input id="action" name="action" value="{{ request('action') }}" placeholder="VD: permission.granted" class="mt-1.5" />
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto mt-4 md:mt-0">
                    <x-ui.button type="submit" size="md" icon="search" class="w-full md:w-auto">
                        {{ __('audit.filters.filter') }}
                    </x-ui.button>
                    <x-ui.button variant="secondary" size="md" icon="x" href="{{ route('admin.audit-logs.index') }}" class="w-full md:w-auto">
                        {{ __('audit.filters.clear') }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <!-- Logs Table -->
        <x-ui.card variant="admin" padding="none" class="overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ue-border text-sm">
                    <thead class="bg-ue-surface-subtle text-xs font-bold uppercase tracking-wider text-ue-text-secondary border-b border-ue-border">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left">
                                <div class="flex items-center gap-1.5">
                                    <x-ui.icon name="clock" class="w-3.5 h-3.5 text-ue-text-muted" />
                                    <span>{{ __('audit.table.when') }}</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 text-left">
                                <div class="flex items-center gap-1.5">
                                    <x-ui.icon name="user" class="w-3.5 h-3.5 text-ue-text-muted" />
                                    <span>{{ __('audit.table.actor') }}</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 text-left">
                                <div class="flex items-center gap-1.5">
                                    <x-ui.icon name="key" class="w-3.5 h-3.5 text-ue-text-muted" />
                                    <span>{{ __('audit.table.action') }}</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 text-left">
                                <div class="flex items-center gap-1.5">
                                    <x-ui.icon name="tag" class="w-3.5 h-3.5 text-ue-text-muted" />
                                    <span>{{ __('audit.table.target') }}</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 text-left">
                                <div class="flex items-center gap-1.5">
                                    <x-ui.icon name="info" class="w-3.5 h-3.5 text-ue-text-muted" />
                                    <span>{{ __('audit.table.reason') }}</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ue-border bg-ue-surface">
                        @forelse($logs as $log)
                            <tr class="hover:bg-ue-surface-hover transition-colors duration-sm">
                                <td class="px-6 py-4 whitespace-nowrap align-middle text-ue-text-secondary text-sm">
                                    <span>{{ optional($log->created_at)->format('Y-m-d H:i') }}</span>
                                </td>
                                <td class="px-6 py-4 align-middle text-sm">
                                    <div class="flex items-center gap-2.5">
                                        <x-ui.avatar :user="$log->actor" size="xs" />
                                        <div class="flex flex-col">
                                            <span class="font-semibold text-ue-text whitespace-nowrap">{{ $log->actor?->name ?? 'System' }}</span>
                                            @if($log->actor)
                                                <span class="text-[10px] text-ue-text-muted font-mono leading-none mt-0.5">ID: {{ $log->actor->id }}</span>
                                            @else
                                                <span class="text-[10px] text-ue-text-muted font-mono leading-none mt-0.5">system</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-middle text-sm">
                                    @php
                                        $action = $log->action;
                                        $variant = 'neutral';
                                        if (str_contains($action, 'accepted') || str_contains($action, 'approved') || str_contains($action, 'granted') || str_contains($action, 'success')) {
                                            $variant = 'success';
                                        } elseif (str_contains($action, 'declined') || str_contains($action, 'rejected') || str_contains($action, 'revoked') || str_contains($action, 'failed') || str_contains($action, 'banned')) {
                                            $variant = 'danger';
                                        } elseif (str_contains($action, 'pending') || str_contains($action, 'need_more_info') || str_contains($action, 'requested')) {
                                            $variant = 'warning';
                                        } elseif (str_contains($action, 'create') || str_contains($action, 'update') || str_contains($action, 'edit') || str_contains($action, 'submit')) {
                                            $variant = 'info';
                                        }
                                    @endphp
                                    <x-ui.badge :variant="$variant" size="sm" class="font-mono text-[10px] tracking-tight uppercase">
                                        {{ $action }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-6 py-4 align-middle text-sm whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-2xs font-semibold bg-ue-surface-hover text-ue-text-secondary border border-ue-border">
                                        <span class="text-ue-text-muted">{{ $log->target_type }}</span>
                                        <span class="text-ue-border font-light">|</span>
                                        <span class="font-bold text-ue-brand">#{{ $log->target_id }}</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-middle text-ue-text-secondary text-sm max-w-xs md:max-w-md break-safe">
                                    <div class="line-clamp-2" title="{{ $log->reason }}">
                                        {{ $log->reason ?: '-' }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center align-middle">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <x-ui.icon name="info" class="w-8 h-8 text-ue-text-disabled" />
                                        <span class="text-ue-text-muted">{{ __('audit.table.empty') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-ue-border bg-ue-surface-subtle">
                    {{ $logs->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-app-layout>
