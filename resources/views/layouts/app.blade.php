<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $appName = config('app.name', 'ASA-Tertib');
        $appLogo = null;
        $appFavicon = null;
        $primaryColor = '#1d4ed8';
        $appTimezone = config('app.timezone', 'Asia/Jakarta');

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('app_settings')) {
                $appName = \App\Models\AppSetting::getValue('app.name', $appName);
                $logoEnabled = \App\Models\AppSetting::getValue('app.logo_enabled', true);
                $appLogo = $logoEnabled ? \App\Models\AppSetting::getValue('app.logo') : null;
                $appFavicon = \App\Models\AppSetting::getValue('app.favicon');
                $primaryColor = \App\Models\AppSetting::getValue('app.primary_color', $primaryColor);
                $appTimezone = \App\Support\AppClock::timezone();
            }
        } catch (\Throwable $exception) {
            $appName = config('app.name', 'ASA-Tertib');
            $appLogo = null;
            $appFavicon = null;
            $primaryColor = '#1d4ed8';
            $appTimezone = config('app.timezone', 'Asia/Jakarta');
        }

        if (! is_string($primaryColor) || ! preg_match('/^#[0-9A-Fa-f]{6}$/', $primaryColor)) {
            $primaryColor = '#1d4ed8';
        }

        $mixColor = function (string $hex, string $target, float $amount): string {
            $hex = ltrim($hex, '#');
            $target = ltrim($target, '#');
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $tr = hexdec(substr($target, 0, 2));
            $tg = hexdec(substr($target, 2, 2));
            $tb = hexdec(substr($target, 4, 2));

            return sprintf(
                '#%02x%02x%02x',
                (int) round($r + ($tr - $r) * $amount),
                (int) round($g + ($tg - $g) * $amount),
                (int) round($b + ($tb - $b) * $amount),
            );
        };

        $primaryDark = $mixColor($primaryColor, '#000000', .22);
        $primaryDeep = $mixColor($primaryColor, '#000000', .42);
        $primarySoft = $mixColor($primaryColor, '#ffffff', .86);
        $primaryAccent = $mixColor($primaryColor, '#ffffff', .12);

        $assetUrl = function (mixed $path): ?string {
            if (! is_string($path) || trim($path) === '') {
                return null;
            }

            $path = trim($path);

            return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/', 'data:'])
                ? $path
                : asset($path);
        };

        $iconLetter = e(mb_strtoupper(mb_substr(trim((string) $appName) ?: 'A', 0, 1)));
        $faviconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="14" fill="' . $primaryColor . '"/><text x="32" y="41" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="30" font-weight="700" fill="#ffffff">' . $iconLetter . '</text></svg>';
        $faviconFallbackUrl = 'data:image/svg+xml,' . rawurlencode($faviconSvg);
        $faviconUrl = $assetUrl($appFavicon) ?: ($assetUrl($appLogo) ?: $faviconFallbackUrl);

        $providedTitle = trim((string) ($title ?? ''));
        $sectionTitle = trim($__env->yieldContent('title'));
        $pageTitle = $providedTitle !== '' ? $providedTitle : $sectionTitle;
        $documentTitle = $pageTitle !== '' ? $pageTitle . ' - ' . $appName : $appName;
        $headerNowIso = \App\Support\AppClock::isoNow();
        $headerNowText = \App\Support\AppClock::format(\App\Support\AppClock::now(), 'd/m/Y H:i:s');
    @endphp
    <title>{{ $documentTitle }}</title>
    <link id="faviconLink" rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    <script>
        (() => {
            const faviconUrl = @json($faviconUrl);
            const fallbackUrl = @json($faviconFallbackUrl);

            if (! faviconUrl || faviconUrl === fallbackUrl) {
                return;
            }

            const probe = new Image();
            probe.onerror = () => {
                const link = document.getElementById('faviconLink');

                if (link) {
                    link.href = fallbackUrl;
                }
            };
            probe.src = faviconUrl;
        })();
    </script>
    @livewireStyles
    <style>
        :root {
            color-scheme: light;
            --bg: #edf5ff;
            --surface: #ffffff;
            --ink: #0f172a;
            --muted: #475569;
            --line: #cfe1f7;
            --primary: {{ $primaryColor }};
            --primary-dark: {{ $primaryDark }};
            --primary-deep: {{ $primaryDeep }};
            --primary-soft: {{ $primarySoft }};
            --accent: {{ $primaryAccent }};
            --danger: #b91c1c;
            --success: #166534;
            --warning: #92400e;
            --chrome: {{ $primaryDark }};
            --chrome-strong: {{ $primaryDeep }};
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            min-height: 100svh;
            background: var(--bg);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            letter-spacing: 0;
        }

        body.drawer-is-open {
            overflow: hidden;
        }

        a { color: inherit; }
        button, input, select, textarea { letter-spacing: 0; }

        .app-shell {
            position: relative;
            width: 100%;
            min-height: 100vh;
            min-height: 100svh;
            background: var(--bg);
            overflow-x: hidden;
        }

        .app-shell.is-guest .page {
            min-height: 100vh;
            min-height: 100svh;
            align-content: start;
            padding: 20px 14px 28px;
        }

        .app-header {
            margin: 0;
            padding: 18px 18px 16px;
            background: linear-gradient(135deg, var(--chrome) 0%, var(--accent) 100%);
            border-bottom: 1px solid rgba(255, 255, 255, .18);
            border-radius: 0 0 22px 22px;
            box-shadow: 0 16px 30px rgba(15, 61, 122, .22);
            color: #fff;
        }

        .header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .icon-button {
            width: 42px;
            height: 42px;
            border: 1px solid rgba(255, 255, 255, .28);
            border-radius: 999px;
            background: rgba(255, 255, 255, .16);
            color: #fff;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            cursor: pointer;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            color: #fff;
            font-weight: 800;
            font-size: 14px;
            text-decoration: none;
        }

        .brand-mark {
            min-width: 34px;
            height: 34px;
            padding: 0 10px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .18);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 12px;
            box-shadow: 0 10px 24px rgba(15, 61, 122, .18);
            flex: 0 0 auto;
            white-space: nowrap;
        }

        .brand-logo-img {
            display: block;
            max-width: 100%;
            max-height: 24px;
            object-fit: contain;
        }

        .brand-text {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .greeting {
            margin: 14px 0 0;
            color: #fff;
        }

        .greeting span {
            display: block;
            color: #dbeafe;
            font-size: 12px;
            margin-bottom: 3px;
        }

        .greeting strong {
            display: block;
            font-size: 20px;
            line-height: 1.2;
        }

        .header-clock {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            padding: 7px 9px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .13);
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.35;
        }

        .page {
            width: 100%;
            padding: 16px 14px 132px;
            display: grid;
            gap: 14px;
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 10px 28px rgba(15, 61, 122, .08);
        }

        .stack { display: grid; gap: 14px; }
        .grid { display: grid; gap: 14px; }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .title { margin: 0; font-size: 22px; line-height: 1.2; color: var(--primary-deep); }
        .subtitle { margin: 6px 0 0; color: var(--muted); line-height: 1.5; }

        .field { display: grid; gap: 6px; }
        .field label { font-size: 13px; font-weight: 700; color: var(--primary-deep); }
        .input, .select, .textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 11px 12px;
            font: inherit;
            background: #fff;
            color: var(--ink);
        }
        .textarea { min-height: 88px; resize: vertical; }
        .input[readonly] { background: #eef3f8; }
        .error { color: var(--danger); font-size: 12px; }

        .alert {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid var(--line);
            background: #f8fbff;
            color: var(--ink);
        }
        .alert-danger { border-color: #fecaca; background: #fef2f2; color: #991b1b; }
        .alert-success { border-color: #bbf7d0; background: #f0fdf4; color: var(--success); }

        .button-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 10px 14px;
            font: inherit;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            background: #eaf2ff;
            color: var(--ink);
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-outline { background: #fff; border-color: #9fc5f8; color: var(--primary-dark); }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-small { min-height: 34px; padding: 7px 10px; font-size: 13px; }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, .55);
            border-top-color: #fff;
            border-radius: 999px;
            display: inline-block;
            animation: spin .75s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .metric {
            display: grid;
            gap: 6px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 14px;
            background: #fff;
            box-shadow: 0 10px 28px rgba(15, 61, 122, .06);
        }
        .metric span { color: var(--muted); font-size: 12px; }
        .metric strong { font-size: 22px; color: var(--primary-deep); }

        .table-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: 8px; background: #fff; }
        table { width: 100%; border-collapse: collapse; min-width: 720px; }
        th, td { padding: 10px 12px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: top; }
        th { font-size: 12px; color: var(--muted); text-transform: uppercase; }
        tr:last-child td { border-bottom: 0; }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 700;
            background: var(--primary-soft);
            color: var(--primary-dark);
            white-space: nowrap;
        }

        .muted { color: var(--muted); }
        .empty { padding: 18px; border: 1px dashed var(--line); border-radius: 8px; color: var(--muted); }

        .bottom-nav {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 10;
            width: 100%;
            min-height: 78px;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            align-items: center;
            padding: 10px 8px calc(10px + env(safe-area-inset-bottom));
            background: linear-gradient(180deg, var(--chrome) 0%, var(--chrome-strong) 100%);
            border-top: 1px solid rgba(255, 255, 255, .16);
            border-radius: 22px 22px 0 0;
            box-shadow: 0 -16px 30px rgba(15, 61, 122, .24);
        }

        .nav-item {
            color: #c7dcff;
            display: grid;
            justify-items: center;
            gap: 4px;
            font-size: 10px;
            font-weight: 700;
            text-decoration: none;
        }

        .nav-item.active {
            color: #fff;
            transform: translateY(-10px);
        }

        .nav-item.active .nav-icon {
            width: 54px;
            height: 54px;
            color: var(--primary-dark);
            background: #fff;
            box-shadow: 0 14px 26px rgba(29, 78, 216, .35);
        }

        .nav-icon {
            width: 30px;
            height: 30px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, .13);
        }

        .screen-dim {
            position: fixed;
            inset: 0;
            background: rgba(8, 31, 67, .42);
            z-index: 20;
            border: 0;
            padding: 0;
            display: none;
        }

        body.drawer-is-open .screen-dim {
            display: block;
            animation: dim-in .24s ease-out both;
        }

        .drawer {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 30;
            width: min(314px, 86vw);
            background: #fff;
            border-right: 1px solid #d8e5f7;
            box-shadow: 20px 0 40px rgba(8, 31, 67, .22);
            padding: 20px 20px 24px;
            display: grid;
            align-content: start;
            gap: 16px;
            min-height: 100vh;
            min-height: 100svh;
            transform: translateX(-102%);
            transition: transform .28s cubic-bezier(.2, .8, .2, 1);
        }

        body.drawer-is-open .drawer {
            transform: translateX(0);
        }

        .drawer-top {
            display: flex;
            justify-content: flex-end;
            min-height: 34px;
        }

        .drawer-close {
            width: 34px;
            height: 34px;
            border: 1px solid #d7e5f7;
            border-radius: 999px;
            background: #fff;
            color: var(--primary-deep);
            display: grid;
            place-items: center;
            cursor: pointer;
        }

        .profile {
            text-align: center;
            display: grid;
            justify-items: center;
            gap: 8px;
        }

        .avatar {
            width: 94px;
            height: 94px;
            border-radius: 999px;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 14px 30px rgba(15, 61, 122, .22);
            background: var(--primary-soft);
        }

        .profile-name {
            margin: 8px 0 0;
            color: var(--primary-deep);
            font-size: 17px;
            font-weight: 900;
        }

        .profile-email {
            color: var(--muted);
            font-size: 12px;
            word-break: break-word;
        }

        .impersonation-card {
            width: 100%;
            display: grid;
            gap: 8px;
            margin-top: 8px;
            padding: 10px;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            background: #eff6ff;
            color: var(--primary-deep);
            text-align: left;
        }

        .impersonation-card strong {
            display: block;
            font-size: 12px;
        }

        .impersonation-card span {
            display: block;
            margin-top: 2px;
            color: var(--muted);
            font-size: 11px;
            word-break: break-word;
        }

        .impersonation-card button {
            width: 100%;
            min-height: 36px;
            border: 0;
            border-radius: 8px;
            background: var(--primary);
            color: #fff;
            font-weight: 800;
            cursor: pointer;
        }

        .logout-wrap {
            display: grid;
            justify-items: center;
        }

        .logout-button {
            border: 0;
            border-radius: 8px;
            background: var(--primary);
            color: #fff;
            padding: 12px 22px;
            min-height: 44px;
            font-weight: 800;
            box-shadow: 0 12px 24px rgba(29, 78, 216, .26);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
        }

        .drawer-menu {
            display: grid;
            gap: 8px;
            padding-top: 2px;
        }

        .drawer-item {
            display: grid;
            grid-template-columns: 34px 1fr;
            gap: 10px;
            align-items: center;
            min-height: 48px;
            padding: 8px 10px;
            border-radius: 8px;
            color: var(--primary-deep);
            font-weight: 800;
            font-size: 14px;
            text-decoration: none;
        }

        .drawer-item.active,
        .drawer-item:hover {
            background: var(--primary-soft);
            color: var(--primary);
        }

        .drawer-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: #eef6ff;
            color: var(--primary);
            display: grid;
            place-items: center;
        }

        svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
        }

        @keyframes dim-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (min-width: 860px) {
            .page {
                width: min(1120px, calc(100% - 28px));
                margin: 0 auto;
            }
        }

        @media (max-width: 720px) {
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .button-row { align-items: stretch; }
            .btn { width: 100%; }
            .title { font-size: 21px; }
            .panel { padding: 14px; }
        }
    </style>
</head>
<body>
    @php
        $currentUser = auth()->user();
        $isAuthenticated = auth()->check();
        $displayName = $currentUser?->name ?: '[User Name]';
        $displayEmail = $currentUser?->email ?: '[user.email@example.com]';
        $avatarUrl = $currentUser?->avatar_url ?: asset('images/design-avatar.png');
        $isImpersonating = session()->has('impersonator_id');
        $impersonatorName = session('impersonator_name');
        $impersonatorEmail = session('impersonator_email');
        $canDashboard = ($currentUser?->can('pelanggan.dashboard_antrian') ?? false)
            || ($currentUser?->hasAnyRole(['applicant', 'Pengguna', 'Pelanggan/Penanya']) ?? false);
        $canOfficerConsole = ($currentUser?->can('petugas.konsol_antrian') ?? false)
            || ($currentUser?->hasAnyRole(['superadmin', 'admin', 'officer', 'Super Admin', 'Petugas']) ?? false);
        $canUserManagement = ($currentUser?->can('admin.manajemen_user') ?? false)
            || ($currentUser?->hasAnyRole(['superadmin', 'Super Admin']) ?? false);
        $canServiceManagement = ($currentUser?->can('admin.manajemen_layanan') ?? false)
            || ($currentUser?->hasAnyRole(['superadmin', 'Super Admin']) ?? false);
        $canCustomerHome = ($currentUser?->can('pelanggan.beranda') ?? false)
            || ($currentUser?->hasAnyRole(['applicant', 'Pengguna', 'Pelanggan/Penanya']) ?? false);
        $canOfficerHome = ($currentUser?->can('petugas.beranda') ?? false)
            || ($currentUser?->hasAnyRole(['superadmin', 'admin', 'officer', 'Super Admin', 'Petugas']) ?? false);
        $canHome = $canCustomerHome || $canOfficerHome;
        $homeUrl = $isAuthenticated
            ? ($canOfficerConsole ? route('officer.console') : ($canDashboard ? route('dashboard') : route('login')))
            : route('login');
    @endphp

    <div class="app-shell {{ $isAuthenticated ? 'is-authenticated' : 'is-guest' }}">
        @auth
            <header class="app-header">
                <div class="header-row">
                    <div class="header-left">
                        <button class="icon-button" id="openDrawer" type="button" aria-label="Buka menu" aria-controls="sideDrawer" aria-expanded="false">
                            <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <a class="brand" href="{{ $homeUrl }}" aria-label="{{ $appName }}">
                            <span class="brand-mark">
                                @if($appLogo)
                                    <img class="brand-logo-img" src="{{ asset($appLogo) }}" alt="{{ $appName }}">
                                @else
                                    {{ $appName }}
                                @endif
                            </span>
                        </a>
                    </div>
                    <button class="icon-button" type="button" aria-label="Notifikasi">
                        <svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                    </button>
                </div>
                <div class="greeting">
                    <span>Welcome, {{ $displayName }}</span>
                    <strong>Status Antrian Hari Ini</strong>
                    <div
                        id="headerLiveClock"
                        class="header-clock"
                        data-now="{{ $headerNowIso }}"
                        data-timezone="{{ $appTimezone }}"
                    >
                        {{ $headerNowText }}
                    </div>
                </div>
            </header>
        @endauth

        <main class="page">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @isset($slot)
                {{ $slot }}
            @endisset

            @yield('content')
        </main>

        @auth
            <nav class="bottom-nav" aria-label="Navigasi bawah">
                @can('pelanggan.status_antrian')
                    <a class="nav-item" href="{{ route('dashboard') }}">
                        <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M12 8v5l3 3"/><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>
                        Status
                    </a>
                @endcan
                @can('pelanggan.scan_qr')
                    <a class="nav-item" href="#">
                        <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h3v3h-3zM18 18h3v3h-3zM18 14h3"/></svg></span>
                        Scan QR
                    </a>
                @endcan
                @if($canHome)
                <a class="nav-item {{ request()->routeIs('dashboard') || request()->routeIs('officer.console') ? 'active' : '' }}" href="{{ $homeUrl }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg></span>
                    Home
                </a>
                @endif
                @can('pelanggan.riwayat')
                    <a class="nav-item" href="#">
                        <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h6"/></svg></span>
                        Riwayat
                    </a>
                @endcan
                @can('pelanggan.profil')
                    <a class="nav-item" href="#">
                        <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M18 20a6 6 0 0 0-12 0"/><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg></span>
                        Profil
                    </a>
                @endcan
            </nav>

            <button class="screen-dim" id="drawerOverlay" type="button" aria-label="Tutup menu"></button>

            <aside class="drawer" id="sideDrawer" aria-label="Side navigation drawer" aria-hidden="true">
                <div class="drawer-top">
                    <button class="drawer-close" id="closeDrawer" type="button" aria-label="Tutup menu">
                        <svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="profile">
                    <img class="avatar" src="{{ $avatarUrl }}" alt="Avatar pengguna">
                    <div>
                        <div class="profile-name">{{ $displayName }}</div>
                        <div class="profile-email">{{ $displayEmail }}</div>
                    </div>
                    @if($isImpersonating)
                        <div class="impersonation-card">
                            <div>
                                <strong>Sedang Login As</strong>
                                <span>Akun asli: {{ $impersonatorName ?: 'Akun Asli' }}</span>
                                <span>{{ $impersonatorEmail }}</span>
                            </div>
                            <form method="POST" action="{{ route('users.impersonate.leave') }}">
                                @csrf
                                <button type="submit">Kembali ke Akun Asli</button>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="logout-wrap">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-button" type="submit">
                            <svg viewBox="0 0 24 24"><path d="M17 16l4-4-4-4"/><path d="M21 12H9"/><path d="M13 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7"/></svg>
                            Keluar / Logout
                        </button>
                    </form>
                </div>

                <nav class="drawer-menu" aria-label="Menu drawer">
                    @if($canOfficerConsole)
                        <a class="drawer-item {{ request()->routeIs('officer.console') ? 'active' : '' }}" href="{{ route('officer.console') }}">
                            <span class="drawer-icon"><svg viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg></span>
                            <span>Konsol Petugas</span>
                        </a>
                    @endif
                    @if($canServiceManagement)
                        <a class="drawer-item {{ request()->routeIs('services.management') ? 'active' : '' }}" href="{{ route('services.management') }}">
                            <span class="drawer-icon"><svg viewBox="0 0 24 24"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h10"/><path d="M18 17v4M16 19h4"/></svg></span>
                            <span>Manajemen Layanan</span>
                        </a>
                    @endif
                    @if($canUserManagement)
                        <a class="drawer-item {{ request()->routeIs('users.management') ? 'active' : '' }}" href="{{ route('users.management') }}">
                            <span class="drawer-icon"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
                            <span>Manajemen User</span>
                        </a>
                    @endif
                    @can('admin.pengaturan_aplikasi')
                        <a class="drawer-item {{ request()->routeIs('settings.application') ? 'active' : '' }}" href="{{ route('settings.application') }}">
                            <span class="drawer-icon"><svg viewBox="0 0 24 24"><path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 .6 1.65 1.65 0 0 0-.33 1.82V22a2 2 0 1 1-4 0v-.18A1.65 1.65 0 0 0 8.6 20a1.65 1.65 0 0 0-1.82-.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-.6-1 1.65 1.65 0 0 0-1.82-.33H2a2 2 0 1 1 0-4h.18A1.65 1.65 0 0 0 4 8.6a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 8.6 4a1.65 1.65 0 0 0 1-.6A1.65 1.65 0 0 0 9.82 2H10a2 2 0 1 1 4 0v.18A1.65 1.65 0 0 0 15 4.6a1.65 1.65 0 0 0 1.82.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c.23.36.36.77.6 1h.18a2 2 0 1 1 0 4H20a1.65 1.65 0 0 0-.6 1Z"/></svg></span>
                            <span>Pengaturan Aplikasi</span>
                        </a>
                    @endcan
                </nav>
            </aside>
        @endauth
    </div>

    @auth
        <script>
            const body = document.body;
            const openDrawer = document.getElementById('openDrawer');
            const closeDrawer = document.getElementById('closeDrawer');
            const drawerOverlay = document.getElementById('drawerOverlay');
            const sideDrawer = document.getElementById('sideDrawer');

            function setDrawerState(isOpen) {
                body.classList.toggle('drawer-is-open', isOpen);
                openDrawer.setAttribute('aria-expanded', String(isOpen));
                sideDrawer.setAttribute('aria-hidden', String(! isOpen));
            }

            openDrawer.addEventListener('click', () => setDrawerState(true));
            closeDrawer.addEventListener('click', () => setDrawerState(false));
            drawerOverlay.addEventListener('click', () => setDrawerState(false));
            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    setDrawerState(false);
                }
            });

            const headerLiveClock = document.getElementById('headerLiveClock');

            if (headerLiveClock) {
                const timezone = headerLiveClock.dataset.timezone || 'Asia/Jakarta';
                let currentTime = new Date(headerLiveClock.dataset.now || Date.now());
                let formatter;

                try {
                    formatter = new Intl.DateTimeFormat('id-ID', {
                        weekday: 'long',
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false,
                        timeZone: timezone,
                    });
                } catch (error) {
                    formatter = new Intl.DateTimeFormat('id-ID', {
                        weekday: 'long',
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false,
                    });
                }

                const tickClock = () => {
                    headerLiveClock.textContent = formatter.format(currentTime).replace(' pukul ', ', ');
                    currentTime = new Date(currentTime.getTime() + 1000);
                };

                tickClock();
                window.setInterval(tickClock, 1000);
            }
        </script>
    @endauth
    @livewireScripts
</body>
</html>
