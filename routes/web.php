<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\QueueCheckInController;
use App\Http\Controllers\QueueQrPrintController;
use App\Http\Controllers\UserImpersonationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return view('test');
});
Route::view('/design', 'design')->name('design');

if (app()->environment('testing')) {
    Route::get('/testing-error/{code}', function (int $code) {
        abort_unless(in_array($code, [403, 404, 419, 500, 503], true), 404);

        abort($code);
    });
}

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::livewire('/login', 'pages.login-page')->name('login');
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::livewire('/register/complete', 'pages.complete-registration')->name('register.complete');

Route::middleware(['auth', 'account.active'])->group(function () {
    Route::livewire('/dashboard', 'pages.applicant-dashboard')->name('dashboard');
    Route::livewire('/petugas', 'pages.officer-queue-console')->name('officer.console');
    Route::livewire('/pengaturan-aplikasi', 'pages.application-settings')->name('settings.application');
    Route::livewire('/manajemen-user', 'pages.user-management')->name('users.management');
    Route::livewire('/manajemen-layanan', 'pages.service-management')->name('services.management');
    Route::get('/check-in/{token}', QueueCheckInController::class)->name('queue.check-in');
    Route::get('/petugas/qr-ambil-antrian/print', QueueQrPrintController::class)->name('officer.queue-qr.print');
    Route::post('/manajemen-user/{user}/login-as', [UserImpersonationController::class, 'take'])->name('users.impersonate.take');
    Route::post('/impersonate/leave', [UserImpersonationController::class, 'leave'])->name('users.impersonate.leave');

    Route::post('/logout', function (Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});
