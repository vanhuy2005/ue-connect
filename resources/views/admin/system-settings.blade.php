<x-app-layout shell="admin">
    <x-slot name="title">Cài đặt hệ thống</x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-ue-text">Cài đặt hệ thống</h1>
            <p class="mt-2 text-sm text-ue-text-muted">Cấu hình runtime, snapshot và khôi phục cho các thiết lập an toàn của hệ thống.</p>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <x-ui.card variant="admin" class="mb-6">
            <x-slot name="header">Chỉnh sửa cấu hình</x-slot>

            <form method="POST" action="{{ route('admin.system-settings.update') }}" class="grid grid-cols-1 gap-4 text-sm">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-ui.label for="app_name">Tên ứng dụng</x-ui.label>
                        <x-ui.input id="app_name" name="app_name" value="{{ old('app_name', $settings['app_name'] ?? '') }}" class="mt-1" />
                    </div>

                    <div>
                        <x-ui.label for="app_env">Môi trường</x-ui.label>
                        <x-ui.input id="app_env" name="app_env" value="{{ old('app_env', $settings['app_env'] ?? '') }}" class="mt-1" />
                    </div>

                    <div>
                        <x-ui.label for="app_url">URL ứng dụng</x-ui.label>
                        <x-ui.input id="app_url" name="app_url" type="url" value="{{ old('app_url', $settings['app_url'] ?? '') }}" class="mt-1" />
                    </div>

                    <div>
                        <x-ui.label for="timezone">Timezone</x-ui.label>
                        <x-ui.input id="timezone" name="timezone" value="{{ old('timezone', $settings['timezone'] ?? '') }}" class="mt-1" />
                    </div>

                    <div>
                        <x-ui.label for="queue_driver">Queue driver</x-ui.label>
                        <x-ui.input id="queue_driver" name="queue_driver" value="{{ old('queue_driver', $settings['queue_driver'] ?? '') }}" class="mt-1" />
                    </div>

                    <div>
                        <x-ui.label for="mail_driver">Mail driver</x-ui.label>
                        <x-ui.input id="mail_driver" name="mail_driver" value="{{ old('mail_driver', $settings['mail_driver'] ?? '') }}" class="mt-1" />
                    </div>

                    <div>
                        <x-ui.label for="broadcasting">Broadcasting</x-ui.label>
                        <x-ui.input id="broadcasting" name="broadcasting" value="{{ old('broadcasting', $settings['broadcasting'] ?? '') }}" class="mt-1" />
                    </div>

                    <div>
                        <x-ui.label for="session_driver">Session driver</x-ui.label>
                        <x-ui.input id="session_driver" name="session_driver" value="{{ old('session_driver', $settings['session_driver'] ?? '') }}" class="mt-1" />
                    </div>
                </div>

                <div>
                    <x-ui.label for="reason">Lý do thao tác</x-ui.label>
                    <x-ui.textarea id="reason" name="reason" rows="3" class="mt-1" placeholder="Giải thích vì sao cần cập nhật cài đặt này">{{ old('reason') }}</x-ui.textarea>
                </div>

                <div class="flex items-center justify-end">
                    <x-ui.button type="submit">Lưu cài đặt</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <x-ui.card variant="admin">
                <x-slot name="header">Thông tin cấu hình</x-slot>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    @foreach($settings as $key => $value)
                        <div class="rounded-lg border border-ue-border p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-ue-text-muted">{{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="mt-2 font-semibold text-ue-text break-all">{{ is_array($value) || is_object($value) ? json_encode($value) : ($value ?? 'N/A') }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-ui.card>

            <x-ui.card variant="admin">
                <x-slot name="header">Snapshot</x-slot>

                <form method="POST" action="{{ route('admin.system-settings.snapshot') }}" class="grid grid-cols-1 gap-4 text-sm">
                    @csrf

                    <div>
                        <x-ui.label for="snapshot_name">Tên snapshot</x-ui.label>
                        <x-ui.input id="snapshot_name" name="name" value="{{ old('name') }}" class="mt-1" placeholder="Tuỳ chọn" />
                    </div>

                    <div>
                        <x-ui.label for="snapshot_reason">Lý do tạo snapshot</x-ui.label>
                        <x-ui.textarea id="snapshot_reason" name="reason" rows="3" class="mt-1" placeholder="Vì sao cần lưu snapshot này?">{{ old('reason') }}</x-ui.textarea>
                    </div>

                    <div class="flex items-center justify-end">
                        <x-ui.button type="submit" variant="secondary">Tạo snapshot</x-ui.button>
                    </div>
                </form>

                <div class="mt-6">
                    <h3 class="mb-3 text-sm font-semibold text-ue-text">Danh sách snapshot</h3>

                    @if (empty($snapshots))
                        <p class="text-sm text-ue-text-muted">Chưa có snapshot nào.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($snapshots as $snapshot)
                                <div class="rounded-xl border border-ue-border p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-semibold text-ue-text">{{ $snapshot }}</p>
                                            <p class="text-xs text-ue-text-muted">Khôi phục sẽ ghi đè các thiết lập hiện tại.</p>
                                        </div>

                                        <x-ui.button href="{{ route('admin.system-settings.snapshot.download', ['file' => $snapshot]) }}" variant="secondary" size="sm">
                                            Tải về
                                        </x-ui.button>
                                    </div>

                                    <form method="POST" action="{{ route('admin.system-settings.snapshot.restore') }}" class="mt-4 grid grid-cols-1 gap-3">
                                        @csrf
                                        <input type="hidden" name="snapshot" value="{{ $snapshot }}" />
                                        <div>
                                            <x-ui.label for="reason_{{ $loop->index }}">Lý do khôi phục</x-ui.label>
                                            <x-ui.textarea id="reason_{{ $loop->index }}" name="reason" rows="2" class="mt-1" placeholder="Giải thích vì sao cần khôi phục snapshot này"></x-ui.textarea>
                                        </div>
                                        <div class="flex justify-end">
                                            <x-ui.button type="submit" variant="danger">Khôi phục</x-ui.button>
                                        </div>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>

        <x-ui.card class="mt-6" variant="warning">
            <p class="text-sm text-ue-text">
                Các thao tác này đều được audit. Hãy chỉ chỉnh những giá trị runtime an toàn và có lý do rõ ràng.
            </p>
        </x-ui.card>
    </div>
</x-app-layout>