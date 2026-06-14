@php
    $loginAppName = config('app.name', 'ASA-Tertib');
    $loginAppLogo = null;
    $loginPrimaryColor = '#1d4ed8';

    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('app_settings')) {
            $loginAppName = \App\Models\AppSetting::getValue('app.name', $loginAppName);
            $loginLogoEnabled = \App\Models\AppSetting::getValue('app.logo_enabled', true);
            $loginAppLogo = $loginLogoEnabled ? \App\Models\AppSetting::getValue('app.logo') : null;
            $loginPrimaryColor = \App\Models\AppSetting::getValue('app.primary_color', $loginPrimaryColor);
        }
    } catch (\Throwable $exception) {
        $loginAppName = config('app.name', 'ASA-Tertib');
        $loginAppLogo = null;
        $loginPrimaryColor = '#1d4ed8';
    }

    if (! is_string($loginPrimaryColor) || ! preg_match('/^#[0-9A-Fa-f]{6}$/', $loginPrimaryColor)) {
        $loginPrimaryColor = '#1d4ed8';
    }
@endphp

<div class="login-screen">
    <style>
        .app-shell.is-guest .page {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .login-screen {
            width: 100%;
            min-height: 100vh;
            min-height: 100svh;
            --login-primary: {{ $loginPrimaryColor }};
            padding: 16px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        }

        .login-wrap {
            width: min(100%, 448px);
        }

        .login-brand {
            margin-bottom: 32px;
            text-align: center;
        }

        .login-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 16px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            color: #fff;
            background: var(--login-primary);
            box-shadow: 0 16px 32px rgba(37, 99, 235, .25);
        }

        .login-logo svg {
            width: 40px;
            height: 40px;
        }

        .login-logo img {
            max-width: 56px;
            max-height: 56px;
            object-fit: contain;
        }

        .login-title {
            margin: 0 0 6px;
            color: var(--login-primary);
            font-size: 30px;
            line-height: 1.15;
            font-weight: 900;
        }

        .login-subtitle {
            margin: 0 auto;
            max-width: 390px;
            color: var(--login-primary);
            font-size: 12px;
            line-height: 1.55;
        }

        .login-card {
            padding: 24px;
            background: #fff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            box-shadow: 0 22px 48px rgba(30, 64, 175, .17);
        }

        .login-stack {
            display: grid;
            gap: 16px;
        }

        .login-google,
        .login-submit {
            width: 100%;
            min-height: 48px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font: inherit;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
        }

        .login-google {
            border: 2px solid #e5e7eb;
            background: #fff;
            color: #374151;
            box-shadow: 0 4px 12px rgba(15, 23, 42, .05);
        }

        .login-google:hover {
            background: #f9fafb;
        }

        .login-submit {
            border: 0;
            background: #2563eb;
            color: #fff;
            box-shadow: 0 12px 24px rgba(37, 99, 235, .22);
        }

        .login-submit:hover {
            background: #1d4ed8;
        }

        .login-submit:disabled {
            opacity: .75;
            cursor: wait;
        }

        .login-separator {
            position: relative;
            height: 22px;
            display: grid;
            align-items: center;
        }

        .login-separator::before {
            content: "";
            display: block;
            height: 1px;
            background: #e5e7eb;
        }

        .login-separator span {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            padding: 0 8px;
            background: #fff;
            color: #6b7280;
            font-size: 12px;
            white-space: nowrap;
        }

        .login-form {
            display: grid;
            gap: 16px;
            padding-top: 4px;
        }

        .login-field {
            display: grid;
            gap: 8px;
        }

        .login-field label {
            color: #1e3a8a;
            font-size: 14px;
            font-weight: 800;
        }

        .login-input-wrap {
            position: relative;
        }

        .login-input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
        }

        .login-input {
            width: 100%;
            min-height: 48px;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 11px 12px 11px 42px;
            background: #fff;
            color: #0f172a;
            font: inherit;
        }

        .login-input::placeholder {
            color: #9ca3af;
        }

        .login-input:focus {
            outline: 0;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .14);
        }

        .login-remember {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #374151;
            font-size: 14px;
            cursor: pointer;
            user-select: none;
        }

        .login-remember input {
            width: 16px;
            height: 16px;
            accent-color: #2563eb;
        }

        .login-footer {
            margin-top: 24px;
            text-align: center;
            color: #1d4ed8;
            font-size: 12px;
        }

        .login-error {
            color: #b91c1c;
            font-size: 12px;
        }

        .login-screen svg {
            flex: 0 0 auto;
        }
    </style>

    <div class="login-wrap">
        <header class="login-brand">
            <div class="login-logo" aria-hidden="true">
                @if($loginAppLogo)
                    <img src="{{ asset($loginAppLogo) }}" alt="{{ $loginAppName }}">
                @else
                    <svg viewBox="0 0 24 24" width="40" height="40">
                        <path d="M22 10 12 5 2 10l10 5 10-5Z"/>
                        <path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/>
                        <path d="M22 10v6"/>
                    </svg>
                @endif
            </div>
            <h1 class="login-title">{{ $loginAppName }}</h1>
            <p class="login-subtitle">
                Gunakan Google untuk registrasi awal pendaftar. Akun yang sudah selesai dibuat juga bisa masuk memakai email dan password.
            </p>
        </header>

        <section class="login-card" aria-label="Form masuk aplikasi">
            <div class="login-stack">
                <a class="login-google" href="{{ route('auth.google.redirect') }}">
                    <svg width="20" height="20" viewBox="0 0 48 48" aria-hidden="true">
                        <path d="M43.611 20.083H42V20H24v8h11.303C33.977 32.124 29.427 35 24 35c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z" fill="#FFC107" stroke="none"/>
                        <path d="M6.306 14.691l6.571 4.819C14.655 16.108 19.001 13 24 13c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z" fill="#FF3D00" stroke="none"/>
                        <path d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.406 0-9.944-3.854-11.274-9.02l-6.523 5.025C9.505 39.556 16.227 44 24 44z" fill="#4CAF50" stroke="none"/>
                        <path d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571.001-.001.002-.001.003-.002l6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z" fill="#1976D2" stroke="none"/>
                    </svg>
                    Masuk dengan Google
                </a>

                <div class="login-separator" aria-hidden="true">
                    <span>atau masuk dengan password</span>
                </div>

                <form class="login-form" wire:submit="login">
                    <div class="login-field">
                        <label for="email">Email</label>
                        <div class="login-input-wrap">
                            <span class="login-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="m22 6-10 7L2 6"/></svg>
                            </span>
                            <input id="email" class="login-input" type="email" wire:model="email" autocomplete="email" placeholder="nama@email.com" required>
                        </div>
                        @error('email') <span class="login-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="login-field">
                        <label for="password">Password</label>
                        <div class="login-input-wrap">
                            <span class="login-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M7 11V7a5 5 0 0 1 10 0v4"/><path d="M5 11h14v10H5z"/></svg>
                            </span>
                            <input id="password" class="login-input" type="password" wire:model="password" autocomplete="current-password" placeholder="Masukkan password" required>
                        </div>
                        @error('password') <span class="login-error">{{ $message }}</span> @enderror
                    </div>

                    <label class="login-remember" for="remember">
                        <input id="remember" type="checkbox" wire:model="remember">
                        <span>Ingat saya</span>
                    </label>

                    <button class="login-submit" type="submit" wire:loading.attr="disabled">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>
                        Masuk
                    </button>
                </form>
            </div>
        </section>

        <footer class="login-footer">
            &copy; 2026 {{ $loginAppName }} - Sistem Antrian SPMB
        </footer>
    </div>
</div>
