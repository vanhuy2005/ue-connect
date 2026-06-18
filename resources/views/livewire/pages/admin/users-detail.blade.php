<?php

use App\Enums\AccountStatus;
use App\Enums\IdentityType;
use App\Mail\Auth\ResetPasswordOtpMail;
use App\Models\User;
use App\Services\AuditLogService;
use App\Support\Mail\SmartMailer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component {
    public User $user;

    public string $action = 'suspend';

    public string $reason = '';

    public function mount(User $user): void
    {
        $this->authorize('manage_users');

        $this->user = $user->load('roles', 'profile', 'activeVerificationRequest');
    }

    public function process(): void
    {
        $this->authorize('manage_users');

        if (Auth::id() === $this->user->id) {
            $this->addError('action', 'Bạn không thể thay đổi trạng thái tài khoản của chính mình.');

            return;
        }

        $this->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'action' => ['required', 'string', 'in:suspend,ban,reactivate'],
        ]);

        DB::transaction(function (): void {
            $before = $this->user->fresh(['roles'])->toArray();

            if ($this->action === 'suspend') {
                $this->user->forceFill([
                    'account_status' => AccountStatus::SUSPENDED,
                    'account_status_reason' => $this->reason,
                    'account_restricted_until' => null,
                ])->save();
            } elseif ($this->action === 'ban') {
                $this->user->forceFill([
                    'account_status' => AccountStatus::BANNED,
                    'account_status_reason' => $this->reason,
                    'account_restricted_until' => null,
                ])->save();
            } elseif ($this->action === 'reactivate') {
                $this->user->forceFill([
                    'account_status' => AccountStatus::ACTIVE,
                    'account_status_reason' => null,
                    'account_restricted_until' => null,
                ])->save();
            }

            $this->user = $this->user->fresh(['roles', 'profile', 'activeVerificationRequest']);

            AuditLogService::log(
                actorId: Auth::id(),
                actorType: 'admin',
                actionKey: 'admin.user.'.$this->action,
                targetType: 'user',
                targetId: $this->user->id,
                beforeSnapshot: $before,
                afterSnapshot: $this->user->toArray(),
                reason: $this->reason
            );
        });

        $this->reset('reason');
        session()->flash('success', 'Đã thực hiện hành động quản trị.');
    }

    public function resendInvite(): void
    {
        $this->authorize('manage_users');

        $email = Str::lower($this->user->email);
        $otp = (string) random_int(100000, 999999);

        Cache::put('password_reset_otp_'.$email, $otp, now()->addMinutes(15));
        SmartMailer::to($email, new ResetPasswordOtpMail($otp));

        AuditLogService::log(
            actorId: Auth::id(),
            actorType: 'admin',
            actionKey: 'admin.user.invite_resend',
            targetType: 'user',
            targetId: $this->user->id,
            reason: 'Gửi lại OTP đặt mật khẩu từ chi tiết tài khoản.'
        );

        session()->flash('success', 'Đã gửi OTP đặt mật khẩu.');
    }

    public function statusLabel(mixed $status): string
    {
        $statusValue = $status instanceof AccountStatus ? $status->value : (string) $status;

        return match ($statusValue) {
            AccountStatus::ACTIVE->value => 'Hoạt động',
            AccountStatus::REGISTERED->value => 'Đăng ký',
            AccountStatus::PENDING_VERIFICATION->value => 'Chờ xác thực',
            AccountStatus::PROFILE_INCOMPLETE->value => 'Hồ sơ chưa hoàn tất',
            AccountStatus::RESTRICTED->value => 'Bị hạn chế',
            AccountStatus::SUSPENDED->value => 'Bị tạm khóa',
            AccountStatus::BANNED->value => 'Bị cấm',
            AccountStatus::DELETED->value => 'Đã xóa',
            default => $statusValue ?: 'Không rõ',
        };
    }

    public function statusVariant(mixed $status): string
    {
        $statusValue = $status instanceof AccountStatus ? $status->value : (string) $status;

        return match ($statusValue) {
            AccountStatus::ACTIVE->value => 'success',
            AccountStatus::REGISTERED->value,
            AccountStatus::PENDING_VERIFICATION->value => 'info',
            AccountStatus::PROFILE_INCOMPLETE->value,
            AccountStatus::RESTRICTED->value,
            AccountStatus::SUSPENDED->value => 'warning',
            AccountStatus::BANNED->value,
            AccountStatus::DELETED->value => 'danger',
            default => 'neutral',
        };
    }

    public function identityLabel(mixed $identityType): string
    {
        $identityValue = $identityType instanceof IdentityType ? $identityType->value : (string) $identityType;

        return match ($identityValue) {
            IdentityType::CURRENT_STUDENT->value => 'Sinh viên hiện tại',
            IdentityType::TEACHER_ADVISOR->value => 'Giảng viên/Cố vấn',
            IdentityType::ALUMNI->value => 'Cựu sinh viên',
            IdentityType::EXTERNAL_MENTOR->value => 'Mentor bên ngoài',
            default => 'Chưa xác định',
        };
    }
}; ?>

<div class="w-full max-w-6xl py-6 px-4 sm:px-5 lg:px-6">
    <div class="mb-5 flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.08em] text-ue-text-muted">Người dùng & Quyền</p>
            <h1 class="ue-text-page-title mt-1">Chi tiết tài khoản</h1>
            <p class="mt-1 text-sm text-ue-text-secondary">ID: {{ $this->user->id }} · {{ $this->user->email }}</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <x-ui.button href="{{ route('admin.users.index') }}" variant="secondary" size="sm" icon="arrow-left">
                Danh sách
            </x-ui.button>
            <x-ui.button href="{{ route('admin.users.edit', ['user' => $this->user->id]) }}" variant="outline" size="sm" icon="edit">
                Chỉnh sửa
            </x-ui.button>
            <x-ui.button type="button" variant="outline" size="sm" icon="mail" wire:click="resendInvite" wire:loading.attr="disabled" wire:target="resendInvite">
                Gửi OTP
            </x-ui.button>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-[var(--success-border)] bg-[var(--success-bg-soft)] px-4 py-3 text-sm font-semibold text-[var(--success-text)]">
            {{ session('success') }}
        </div>
    @endif

    @error('action')
        <div class="mb-4 rounded-lg border border-[var(--danger-border)] bg-[var(--danger-bg-soft)] px-4 py-3 text-sm font-semibold text-[var(--danger-text)]">
            {{ $message }}
        </div>
    @enderror

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-5">
            <x-ui.card variant="admin" padding="lg">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-ue-brand-soft text-lg font-bold text-ue-brand">
                            {{ Str::of($this->user->name)->substr(0, 1)->upper() }}
                        </div>
                        <div>
                            <h2 class="ue-text-section">{{ $this->user->name }}</h2>
                            <p class="text-sm text-ue-text-muted">{{ $this->user->email }}</p>
                        </div>
                    </div>
                    <x-ui.badge :variant="$this->statusVariant($this->user->account_status)">
                        {{ $this->statusLabel($this->user->account_status) }}
                    </x-ui.badge>
                </div>
            </x-ui.card>

            <x-ui.card variant="admin" padding="lg">
                <h2 class="ue-text-section">Thông tin vận hành</h2>
                <dl class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-[0.06em] text-ue-text-muted">Vai trò</dt>
                        <dd class="mt-1 flex flex-wrap gap-1">
                            @forelse($this->user->roles as $role)
                                <x-ui.badge variant="{{ $role->name === 'admin' ? 'admin' : ($role->name === 'student' ? 'student' : 'neutral') }}" no-icon="true">
                                    {{ ucfirst($role->name) }}
                                </x-ui.badge>
                            @empty
                                <span class="text-sm text-ue-text-muted">Chưa có vai trò</span>
                            @endforelse
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-[0.06em] text-ue-text-muted">Định danh dự kiến</dt>
                        <dd class="mt-1 text-sm font-semibold text-ue-text">{{ $this->identityLabel($this->user->intended_identity_type) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-[0.06em] text-ue-text-muted">Email</dt>
                        <dd class="mt-1 text-sm font-semibold text-ue-text">{{ $this->user->email_verified_at ? 'Đã xác minh' : 'Chưa xác minh' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-[0.06em] text-ue-text-muted">Hiện diện</dt>
                        <dd class="mt-1 text-sm font-semibold text-ue-text">{{ $this->user->last_seen_at?->format('H:i d/m/Y') ?? 'Chưa ghi nhận' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-[0.06em] text-ue-text-muted">Đăng nhập cuối</dt>
                        <dd class="mt-1 text-sm font-semibold text-ue-text">{{ $this->user->last_login_at?->format('H:i d/m/Y') ?? 'Chưa đăng nhập' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-[0.06em] text-ue-text-muted">Tạo lúc</dt>
                        <dd class="mt-1 text-sm font-semibold text-ue-text">{{ $this->user->created_at?->format('H:i d/m/Y') }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card variant="admin" padding="lg">
                <h2 class="ue-text-section">Lý do trạng thái hiện tại</h2>
                <p class="mt-3 text-sm leading-relaxed text-ue-text-secondary">
                    {{ $this->user->account_status_reason ?: 'Chưa có lý do quản trị được ghi nhận.' }}
                </p>
                @if($this->user->account_restricted_until)
                    <p class="mt-2 text-sm font-semibold text-[var(--warning-text)]">
                        Hạn chế đến {{ $this->user->account_restricted_until->format('H:i d/m/Y') }}
                    </p>
                @endif
            </x-ui.card>
        </div>

        <aside class="space-y-5">
            <x-ui.card variant="admin" padding="lg">
                <h2 class="ue-text-section">Hành động quản trị</h2>
                <p class="mt-1 text-sm text-ue-text-muted">Các thao tác nhạy cảm đều ghi audit log.</p>

                <form wire:submit="process" class="mt-4 space-y-4">
                    <div class="space-y-1.5">
                        <x-ui.label for="action">Hành động</x-ui.label>
                        <x-ui.select id="action" wire:model.live="action" :disabled="auth()->id() === $this->user->id">
                            <option value="suspend">Tạm khóa</option>
                            <option value="ban">Cấm</option>
                            <option value="reactivate">Kích hoạt lại</option>
                        </x-ui.select>
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="reason" :required="true">Lý do</x-ui.label>
                        <textarea
                            id="reason"
                            wire:model.live.debounce.300ms="reason"
                            rows="5"
                            class="ue-input block w-full px-3.5 py-3 ue-text-input"
                            @disabled(auth()->id() === $this->user->id)
                        ></textarea>
                        <x-ui.field-error name="reason" />
                    </div>

                    <x-ui.button type="submit" variant="primary" icon="check" class="w-full" :disabled="auth()->id() === $this->user->id" wire:loading.attr="disabled" wire:target="process">
                        Thực hiện
                    </x-ui.button>
                </form>
            </x-ui.card>
        </aside>
    </div>
</div>
