<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserImpersonationController extends Controller
{
    public function take(Request $request, User $user): RedirectResponse
    {
        $impersonator = $request->user();

        abort_unless(
            $impersonator
            && ! $request->session()->has('impersonator_id')
            && ($impersonator->can('admin.login_sebagai_user') || $impersonator->hasAnyRole(['superadmin', 'Super Admin'])),
            403,
        );

        abort_if($impersonator->is($user), 422, 'Tidak bisa login as ke akun sendiri.');

        Auth::login($user);

        $request->session()->put([
            'impersonator_id' => $impersonator->id,
            'impersonator_name' => $impersonator->name,
            'impersonator_email' => $impersonator->email,
        ]);

        if (Auth::id() !== $user->id) {
            Auth::guard('web')->loginUsingId($user->id);
        }

        $request->session()->regenerate();

        return redirect()
            ->route($this->dashboardRouteFor($user))
            ->with('status', 'Anda sedang login sebagai ' . $user->name . '.');
    }

    public function leave(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->get('impersonator_id');

        abort_unless($impersonatorId, 403);

        $impersonator = User::query()->find($impersonatorId);

        if (! $impersonator) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'Akun asli tidak ditemukan. Silakan login ulang.');
        }

        Auth::login($impersonator);

        if (Auth::id() !== $impersonator->id) {
            Auth::guard('web')->loginUsingId($impersonator->id);
        }

        $request->session()->forget([
            'impersonator_id',
            'impersonator_name',
            'impersonator_email',
        ]);
        $request->session()->regenerate();

        return redirect()
            ->route('users.management')
            ->with('status', 'Anda sudah kembali ke akun asli.');
    }

    private function dashboardRouteFor(User $user): string
    {
        if ($user->can('petugas.konsol_antrian') || $user->hasAnyRole(['superadmin', 'admin', 'officer', 'Super Admin', 'Petugas'])) {
            return 'officer.console';
        }

        return 'dashboard';
    }
}
