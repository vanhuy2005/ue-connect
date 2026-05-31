<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ReviewVerificationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VerificationActionRequest;
use App\Models\VerificationRequest;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;

class VerificationActionController extends Controller
{
    public function handle(VerificationActionRequest $request, VerificationRequest $verificationRequest, ReviewVerificationAction $action, AuditService $audit): RedirectResponse
    {
        $data = $request->validated();

        try {
            $action->execute($verificationRequest, $data, $audit);

            return back()->with('success', 'Verification action applied.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['error' => 'Unable to apply verification action.']);
        }
    }
}
