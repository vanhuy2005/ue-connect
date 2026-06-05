<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BanUserRequest;
use App\Http\Requests\Admin\ReactivateUserRequest;
use App\Http\Requests\Admin\SuspendUserRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    public function suspend(SuspendUserRequest $request, User $user): RedirectResponse
    {
        if (! auth()->user() || ! auth()->user()->can('manage_users')) {
            abort(403);
        }

        $currentUser = auth()->user();
        if ($currentUser->id === $user->id) {
            return back()->withErrors(['error' => 'Bạn không thể thay đổi trạng thái tài khoản của chính mình.']);
        }

        $before = $user->toArray();

        DB::beginTransaction();
        try {
            $user->account_status = AccountStatus::SUSPENDED;
            $user->account_status_reason = $request->input('reason');
            $user->account_restricted_until = $request->input('until') ? Carbon::parse($request->input('until')) : null;
            $user->save();

            $after = $user->toArray();

            AuditLogService::log(
                auth()->id(),
                'admin',
                'admin.user.suspend',
                'user',
                $user->id,
                null,
                null,
                $before,
                $after,
                $request->input('reason'),
                ['restricted_until' => $user->account_restricted_until?->toDateTimeString()]
            );

            DB::commit();

            return back()->with('success', 'User suspended successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withErrors(['error' => 'Failed to suspend user.']);
        }
    }

    public function ban(BanUserRequest $request, User $user): RedirectResponse
    {
        if (! auth()->user() || ! auth()->user()->can('manage_users')) {
            abort(403);
        }

        $currentUser = auth()->user();
        if ($currentUser->id === $user->id) {
            return back()->withErrors(['error' => 'Bạn không thể thay đổi trạng thái tài khoản của chính mình.']);
        }

        $before = $user->toArray();

        DB::beginTransaction();
        try {
            $user->account_status = AccountStatus::BANNED;
            $user->account_status_reason = $request->input('reason');
            $user->account_restricted_until = null;
            $user->save();

            $after = $user->toArray();

            AuditLogService::log(
                auth()->id(),
                'admin',
                'admin.user.ban',
                'user',
                $user->id,
                null,
                null,
                $before,
                $after,
                $request->input('reason'),
                null
            );

            DB::commit();

            return back()->with('success', 'User banned successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withErrors(['error' => 'Failed to ban user.']);
        }
    }

    public function reactivate(ReactivateUserRequest $request, User $user): RedirectResponse
    {
        if (! auth()->user() || ! auth()->user()->can('manage_users')) {
            abort(403);
        }

        $currentUser = auth()->user();
        if ($currentUser->id === $user->id) {
            return back()->withErrors(['error' => 'Bạn không thể thay đổi trạng thái tài khoản của chính mình.']);
        }

        $before = $user->toArray();

        DB::beginTransaction();
        try {
            $user->account_status = AccountStatus::ACTIVE;
            $user->account_status_reason = null;
            $user->account_restricted_until = null;
            $user->save();

            $after = $user->toArray();

            AuditLogService::log(
                auth()->id(),
                'admin',
                'admin.user.reactivate',
                'user',
                $user->id,
                null,
                null,
                $before,
                $after,
                $request->input('reason'),
                null
            );

            DB::commit();

            return back()->with('success', 'User reactivated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withErrors(['error' => 'Failed to reactivate user.']);
        }
    }
}
