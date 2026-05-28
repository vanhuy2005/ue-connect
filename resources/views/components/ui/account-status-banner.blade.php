@php
    $user = auth()->user();
@endphp

@if ($user)
    @if ($user->isRestricted())
        <div class="bg-amber-50 border-b border-amber-200 text-amber-800 px-4 py-3 text-xs sm:text-sm shadow-sm" role="alert">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <x-ui.icon name="alert-triangle" size="sm" class="text-amber-600 flex-shrink-0" />
                    <div>
                        <span class="font-bold">Tài khoản bị hạn chế tính năng:</span>
                        <span>
                            Hệ thống đã tạm dừng một số quyền tương tác của bạn
                            @if ($user->account_restricted_until)
                                cho đến ngày {{ $user->account_restricted_until->format('d/m/Y H:i') }}
                            @endif.
                        </span>
                        @if ($user->account_status_reason)
                            <span class="italic block mt-0.5 text-ue-text-muted">Lý do: "{{ $user->account_status_reason }}"</span>
                        @endif
                    </div>
                </div>
                <a href="mailto:support@hcmue.edu.vn" class="text-xs font-bold text-ue-brand hover:underline flex-shrink-0">
                    Liên hệ hỗ trợ
                </a>
            </div>
        </div>
    @endif
@endif
