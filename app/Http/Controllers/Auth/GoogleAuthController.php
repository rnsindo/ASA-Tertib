<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route($this->dashboardRouteFor(Auth::user()));
        }

        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()
                ->route('login')
                ->with('error', 'Google SSO belum dikonfigurasi. Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET di .env.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $exception) {
            session()->forget('state');

            if (Auth::check()) {
                return redirect()
                    ->route($this->dashboardRouteFor(Auth::user()))
                    ->with('status', 'Sesi Google sebelumnya sudah tidak aktif. Anda sudah masuk ke aplikasi.');
            }

            return redirect()
                ->route('login')
                ->with('error', 'Sesi login Google sudah kedaluwarsa. Silakan klik Masuk dengan Google kembali.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->with('error', 'Login Google gagal. Silakan coba lagi atau gunakan password.');
        }

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            if (! $user->is_active) {
                return redirect()
                    ->route('login')
                    ->with('error', 'Akun Anda sedang dinonaktifkan. Silakan hubungi admin.');
            }

            $user->forceFill([
                'google_id' => $user->google_id ?: $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
            ])->save();

            Auth::login($user, true);
            session()->forget('url.intended');
            session()->regenerate();

            return redirect()->route($this->dashboardRouteFor($user));
        }

        session([
            'google_registration' => [
                'google_id' => $googleUser->getId(),
                'name' => $googleUser->getName() ?: $googleUser->getNickname(),
                'email' => $googleUser->getEmail(),
                'avatar_url' => $googleUser->getAvatar(),
            ],
        ]);

        return redirect()->route('register.complete');
    }

    private function dashboardRouteFor(User $user): string
    {
        if ($user->can('petugas.konsol_antrian') || $user->hasAnyRole(['superadmin', 'admin', 'officer', 'Super Admin', 'Petugas'])) {
            return 'officer.console';
        }

        return 'dashboard';
    }
}
