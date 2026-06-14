<?php

namespace App\Livewire\Pages;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Masuk')]
class LoginPage extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectRoute($this->dashboardRouteFor(Auth::user()));
        }
    }

    public function login()
    {
        $validated = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        $user = User::query()
            ->where('email', $validated['email'])
            ->first();

        if ($user && ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi admin.',
            ]);
        }

        if (! Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ], $validated['remember'])) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak sesuai.',
            ]);
        }

        request()->session()->regenerate();

        $authenticatedUser = Auth::user();

        if ($authenticatedUser && ! $authenticatedUser->is_active) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi admin.',
            ]);
        }

        return redirect()->intended(route($this->dashboardRouteFor($authenticatedUser)));
    }

    private function dashboardRouteFor(?User $user): string
    {
        if ($user?->can('petugas.konsol_antrian') || $user?->hasAnyRole(['superadmin', 'admin', 'officer', 'Super Admin', 'Petugas'])) {
            return 'officer.console';
        }

        return 'dashboard';
    }

    public function render()
    {
        return view('livewire.pages.login-page');
    }
}
