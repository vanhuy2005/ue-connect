<?php

namespace App\Http\Controllers;

use App\Actions\Push\RevokePushSubscriptionAction;
use App\Actions\Push\StorePushSubscriptionAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PushSubscriptionController extends Controller
{
    public function store(Request $request, StorePushSubscriptionAction $action)
    {
        try {
            $data = $request->validate([
                'endpoint' => ['required', 'string'],
                'keys.auth' => ['required', 'string'],
                'keys.p256dh' => ['required', 'string'],
                'contentEncoding' => ['nullable', 'string'],
            ]);

            $action->execute(Auth::user(), $data);

            return response()->json(['success' => true]);
        } catch (ValidationException $e) {
            Log::error('Validation Failed', $e->errors());
            throw $e;
        } catch (\Exception $e) {
            Log::error('StorePush Error', ['msg' => $e->getMessage()]);
            throw $e;
        }
    }

    public function destroy(Request $request, RevokePushSubscriptionAction $action)
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string'],
        ]);

        $action->execute(Auth::user(), $data['endpoint']);

        return response()->json(['success' => true]);
    }
}
