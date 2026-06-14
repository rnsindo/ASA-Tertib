<?php

namespace App\Http\Controllers;

use App\Services\QueueRuntimeService;
use Illuminate\Http\RedirectResponse;

class QueueCheckInController extends Controller
{
    public function __invoke(string $token, QueueRuntimeService $queueRuntime): RedirectResponse
    {
        [$success, $message] = $queueRuntime->checkInWithQr(auth()->user(), $token);

        return redirect()
            ->route('dashboard')
            ->with($success ? 'status' : 'error', $message);
    }
}
