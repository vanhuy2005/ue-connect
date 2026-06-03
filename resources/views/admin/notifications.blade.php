<x-app-layout shell="admin">
    <x-slot name="title">Trung tâm thông báo</x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-ue-text">Trung tâm thông báo</h1>
                <p class="mt-2 text-sm text-ue-text-muted">Theo dõi các thông báo hệ thống và cảnh báo vận hành dành cho admin.</p>
            </div>

            <div class="rounded-xl border border-ue-border px-4 py-3 text-right">
                <p class="text-xs uppercase tracking-wide text-ue-text-muted">Chưa đọc</p>
                <p class="mt-1 text-2xl font-bold text-ue-text">{{ $unreadCount }}</p>
            </div>
        </div>

        <x-ui.card variant="admin" padding="none" class="overflow-hidden">
            @if($notifications->isEmpty())
                <div class="px-6 py-12 text-center text-sm text-ue-text-muted">
                    Chưa có thông báo nào.
                </div>
            @else
                <div class="divide-y divide-ue-border">
                    @foreach($notifications as $notification)
                        <div class="px-6 py-4 {{ $notification->read_at ? 'bg-ue-surface' : 'bg-amber-50/40' }}">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-ue-text">
                                        {{ $notification->data['title'] ?? class_basename($notification->type) }}
                                    </p>
                                    <p class="mt-1 text-sm text-ue-text-muted">
                                        {{ $notification->data['message'] ?? ($notification->data['reason'] ?? 'Thông báo hệ thống') }}
                                    </p>

                                    @if(!empty($notification->data['action_url']))
                                        <a href="{{ $notification->data['action_url'] }}" class="mt-2 inline-flex text-sm font-semibold text-ue-brand hover:underline">
                                            Mở chi tiết
                                        </a>
                                    @endif
                                </div>

                                <div class="text-right text-xs text-ue-text-muted">
                                    <p>{{ optional($notification->created_at)->diffForHumans() }}</p>
                                    <p class="mt-1">{{ $notification->read_at ? 'Đã đọc' : 'Chưa đọc' }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="px-6 py-4 border-t border-ue-border">
                {{ $notifications->links() }}
            </div>
        </x-ui.card>
    </div>
</x-app-layout>