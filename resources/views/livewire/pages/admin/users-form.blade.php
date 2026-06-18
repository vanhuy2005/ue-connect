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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;

new class extends Component {
    public ?User $user = null;

    public string $name = '';

    public string $email = '';

    public string $intended_identity_type = '';

    public string $account_status = '';

    public string $account_status_reason = '';

    public string $account_restricted_until = '';

    /** @var list<string> */
    public array $roles = [];

    public bool $send_invite = true;

    public function mount(?User $user = null): void
    {
        $this->authorize('manage_users');

        $this->user = $user;

        if ($user) {
            $user->load('roles');

            $this->name = $user->name;
            $this->email = $user->email;
            $this->intended_identity_type = $user->intended_identity_type instanceof IdentityType
                ? $user->intended_identity_type->value
                : (string) ($user->intended_identity_type ?? '');
            $this->account_status = $user->account_status instanceof AccountStatus
                ? $user->account_status->value
                : (string) ($user->account_status ?? AccountStatus::REGISTERED->value);
            $this->account_status_reason = (string) ($user->account_status_reason ?? '');
            $this->account_restricted_until = $user->account_restricted_until?->format('Y-m-d\TH:i') ?? '';
            $this->roles = $user->roles->pluck('name')->values()->all();
            $this->send_invite = false;

            return;
        }

        $this->account_status = AccountStatus::REGISTERED->value;
        $this->roles = ['student'];
    }

    public function save(): void
    {
        $this->authorize('manage_users');

        if ($this->user && Auth::id() === $this->user->id) {
            $this->addError('user', 'Bạn không thể chỉnh sửa tài khoản của chính mình tại màn quản trị.');

            return;
        }

        $validated = $this->validate($this->rules());

        DB::transaction(function () use ($validated): void {
            $before = $this->user?->fresh(['roles'])?->toArray();

            if ($this->user) {
                $targetUser = $this->user->fresh();
                $targetUser->forceFill([
                    'name' => $validated['name'],
                    'email' => Str::lower($validated['email']),
                    'intended_identity_type' => $validated['intended_identity_type'] ?: null,
                    'account_status' => $validated['account_status'],
                    'account_status_reason' => $validated['account_status_reason'] ?: null,
                    'account_restricted_until' => $validated['account_restricted_until'] ?: null,
                ])->save();

                $targetUser->syncRoles($validated['roles']);

                $this->user = $targetUser->fresh(['roles']);

                AuditLogService::log(
                    actorId: Auth::id(),
                    actorType: 'admin',
                    actionKey: 'admin.user.update',
                    targetType: 'user',
                    targetId: $targetUser->id,
                    beforeSnapshot: $before,
                    afterSnapshot: $this->user->toArray(),
                    reason: 'Cập nhật tài khoản từ bảng quản trị.',
                    metadata: ['roles' => $validated['roles']]
                );

                session()->flash('success', 'Đã cập nhật tài khoản người dùng.');

                return;
            }

            $targetUser = User::create([
                'name' => $validated['name'],
                'email' => Str::lower($validated['email']),
                'password' => Hash::make(Str::password(32)),
                'intended_identity_type' => $validated['intended_identity_type'] ?: null,
                'account_status' => $validated['account_status'],
                'account_status_reason' => $validated['account_status_reason'] ?: null,
                'account_restricted_until' => $validated['account_restricted_until'] ?: null,
                'email_verified_at' => null,
            ]);

            $targetUser->syncRoles($validated['roles']);
            $targetUser->load('roles');

            if ($this->send_invite) {
                $this->sendInviteOtp($targetUser);
            }

            AuditLogService::log(
                actorId: Auth::id(),
                actorType: 'admin',
                actionKey: 'admin.user.create',
                targetType: 'user',
                targetId: $targetUser->id,
                beforeSnapshot: null,
                afterSnapshot: $targetUser->toArray(),
                reason: 'Tạo tài khoản từ bảng quản trị.',
                metadata: [
                    'roles' => $validated['roles'],
                    'invite_sent' => $this->send_invite,
                ]
            );

            session()->flash('success', $this->send_invite
                ? 'Đã tạo tài khoản và gửi OTP đặt mật khẩu.'
                : 'Đã tạo tài khoản người dùng.');

            $this->redirectRoute('admin.users.show', ['user' => $targetUser->id], navigate: true);
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $userId = $this->user?->id;
        $roleNames = $this->roleOptions()->keys()->all();
        $statusValues = array_map(fn (AccountStatus $status): string => $status->value, AccountStatus::cases());
        $identityValues = array_map(fn (IdentityType $type): string => $type->value, IdentityType::cases());

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'intended_identity_type' => ['nullable', 'string', Rule::in($identityValues)],
            'account_status' => ['required', 'string', Rule::in($statusValues)],
            'account_status_reason' => ['nullable', 'string', 'max:1000'],
            'account_restricted_until' => ['nullable', 'date', 'after:now'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in($roleNames)],
        ];
    }

    public function resendInvite(): void
    {
        $this->authorize('manage_users');

        if (! $this->user) {
            return;
        }

        $this->sendInviteOtp($this->user);

        AuditLogService::log(
            actorId: Auth::id(),
            actorType: 'admin',
            actionKey: 'admin.user.invite_resend',
            targetType: 'user',
            targetId: $this->user->id,
            reason: 'Gửi lại OTP đặt mật khẩu từ bảng quản trị.'
        );

        session()->flash('success', 'Đã gửi lại OTP đặt mật khẩu.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function roleOptions()
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->pluck('name', 'name');
    }

    /**
     * @return array<string, string>
     */
    public function statusLabels(): array
    {
        return [
            AccountStatus::REGISTERED->value => 'Đăng ký',
            AccountStatus::PENDING_VERIFICATION->value => 'Chờ xác thực',
            AccountStatus::ACTIVE->value => 'Hoạt động',
            AccountStatus::PROFILE_INCOMPLETE->value => 'Hồ sơ chưa hoàn tất',
            AccountStatus::RESTRICTED->value => 'Bị hạn chế',
            AccountStatus::SUSPENDED->value => 'Bị tạm khóa',
            AccountStatus::BANNED->value => 'Bị cấm',
            AccountStatus::DELETED->value => 'Đã xóa',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function identityLabels(): array
    {
        return [
            IdentityType::CURRENT_STUDENT->value => 'Sinh viên hiện tại',
            IdentityType::TEACHER_ADVISOR->value => 'Giảng viên/Cố vấn',
            IdentityType::ALUMNI->value => 'Cựu sinh viên',
            IdentityType::EXTERNAL_MENTOR->value => 'Mentor bên ngoài',
        ];
    }

    protected function sendInviteOtp(User $user): void
    {
        $email = Str::lower($user->email);
        $otp = (string) random_int(100000, 999999);

        Cache::put('password_reset_otp_'.$email, $otp, now()->addMinutes(15));
        SmartMailer::to($email, new ResetPasswordOtpMail($otp));
    }
}; ?>

<div class="w-full max-w-5xl py-6 px-4 sm:px-5 lg:px-6">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.08em] text-ue-text-muted">Người dùng & Quyền</p>
            <h1 class="ue-text-page-title mt-1">{{ $this->user ? 'Chỉnh sửa tài khoản' : 'Tạo tài khoản người dùng' }}</h1>
            <p class="mt-1 text-sm text-ue-text-secondary">
                Quản trị danh tính, vai trò và trạng thái vòng đời tài khoản.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <x-ui.button href="{{ route('admin.users.index') }}" variant="secondary" size="sm" icon="arrow-left">
                Danh sách
            </x-ui.button>
            @if($this->user)
                <x-ui.button href="{{ route('admin.users.show', ['user' => $this->user->id]) }}" variant="outline" size="sm" icon="eye">
                    Chi tiết
                </x-ui.button>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-[var(--success-border)] bg-[var(--success-bg-soft)] px-4 py-3 text-sm font-semibold text-[var(--success-text)]">
            {{ session('success') }}
        </div>
    @endif

    @error('user')
        <div class="mb-4 rounded-lg border border-[var(--danger-border)] bg-[var(--danger-bg-soft)] px-4 py-3 text-sm font-semibold text-[var(--danger-text)]">
            {{ $message }}
        </div>
    @enderror

    <form wire:submit="save" class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-5">
            <x-ui.card variant="admin" padding="lg">
                <div class="mb-5">
                    <h2 class="ue-text-section">Thông tin định danh</h2>
                    <p class="mt-1 text-sm text-ue-text-muted">Các trường cốt lõi từ bảng users.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1.5">
                        <x-ui.label for="name" :required="true">Tên hiển thị</x-ui.label>
                        <x-ui.input id="name" name="name" wire:model.live.debounce.250ms="name" :hasError="$errors->has('name')" />
                        <x-ui.field-error name="name" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="email" :required="true">Email</x-ui.label>
                        <x-ui.input id="email" name="email" type="email" wire:model.live.debounce.250ms="email" :hasError="$errors->has('email')" />
                        <x-ui.field-error name="email" />
                    </div>

                    <div class="space-y-1.5 md:col-span-2">
                        <x-ui.label for="intended_identity_type">Loại định danh dự kiến</x-ui.label>
                        <x-ui.select id="intended_identity_type" name="intended_identity_type" wire:model.live="intended_identity_type" :hasError="$errors->has('intended_identity_type')">
                            <option value="">Chưa xác định</option>
                            @foreach($this->identityLabels() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.field-error name="intended_identity_type" />
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card variant="admin" padding="lg">
                <div class="mb-5">
                    <h2 class="ue-text-section">Vòng đời tài khoản</h2>
                    <p class="mt-1 text-sm text-ue-text-muted">Trạng thái truy cập, lý do quản trị và thời hạn hạn chế.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1.5">
                        <x-ui.label for="account_status" :required="true">Trạng thái</x-ui.label>
                        <x-ui.select id="account_status" name="account_status" wire:model.live="account_status" :hasError="$errors->has('account_status')">
                            @foreach($this->statusLabels() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.field-error name="account_status" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="account_restricted_until">Hạn chế đến</x-ui.label>
                        <x-ui.input id="account_restricted_until" name="account_restricted_until" type="datetime-local" wire:model.live="account_restricted_until" :hasError="$errors->has('account_restricted_until')" />
                        <x-ui.field-error name="account_restricted_until" />
                    </div>

                    <div class="space-y-1.5 md:col-span-2">
                        <x-ui.label for="account_status_reason">Lý do quản trị</x-ui.label>
                        <textarea
                            id="account_status_reason"
                            name="account_status_reason"
                            wire:model.live.debounce.300ms="account_status_reason"
                            rows="4"
                            class="ue-input block w-full px-3.5 py-3 ue-text-input"
                            @if($errors->has('account_status_reason')) aria-invalid="true" @endif
                        ></textarea>
                        <x-ui.field-error name="account_status_reason" />
                    </div>
                </div>
            </x-ui.card>
        </div>

        <aside class="space-y-5">
            <x-ui.card variant="admin" padding="lg">
                <h2 class="ue-text-section">Vai trò</h2>
                <p class="mt-1 text-sm text-ue-text-muted">Tối thiểu một vai trò web.</p>

                <div class="mt-4 space-y-2">
                    @foreach($this->roleOptions() as $roleName)
                        <label class="flex items-center gap-3 rounded-lg border border-ue-border px-3 py-2 text-sm font-semibold text-ue-text hover:bg-ue-surface-hover">
                            <input
                                type="checkbox"
                                value="{{ $roleName }}"
                                wire:model.live="roles"
                                class="rounded border-ue-border text-ue-brand focus:ring-ue-brand"
                            >
                            <span>{{ ucfirst($roleName) }}</span>
                        </label>
                    @endforeach
                </div>
                <x-ui.field-error name="roles" />
                <x-ui.field-error name="roles.0" />
            </x-ui.card>

            <x-ui.card variant="admin" padding="lg">
                <h2 class="ue-text-section">Mời đặt mật khẩu</h2>
                <p class="mt-1 text-sm text-ue-text-muted">
                    Admin không đặt mật khẩu trực tiếp. Người dùng tự đặt qua OTP gửi email.
                </p>

                @if($this->user)
                    <x-ui.button type="button" variant="outline" size="sm" icon="mail" class="mt-4 w-full" wire:click="resendInvite" wire:loading.attr="disabled" wire:target="resendInvite">
                        Gửi lại OTP
                    </x-ui.button>
                @else
                    <label class="mt-4 flex items-start gap-3 rounded-lg border border-ue-border px-3 py-3 text-sm font-semibold text-ue-text">
                        <input type="checkbox" wire:model.live="send_invite" class="mt-1 rounded border-ue-border text-ue-brand focus:ring-ue-brand">
                        <span>
                            Gửi OTP đặt mật khẩu sau khi tạo
                            <span class="mt-1 block text-xs font-medium text-ue-text-muted">Khuyến nghị bật cho tài khoản mới.</span>
                        </span>
                    </label>
                @endif
            </x-ui.card>

            <div class="flex flex-col gap-2 rounded-lg border border-ue-border bg-ue-surface p-3">
                <x-ui.button type="submit" variant="primary" icon="check" class="w-full" wire:loading.attr="disabled" wire:target="save">
                    {{ $this->user ? 'Lưu thay đổi' : 'Tạo tài khoản' }}
                </x-ui.button>
                <x-ui.button href="{{ route('admin.users.index') }}" variant="ghost" class="w-full">
                    Hủy
                </x-ui.button>
            </div>
        </aside>
    </form>
</div>
