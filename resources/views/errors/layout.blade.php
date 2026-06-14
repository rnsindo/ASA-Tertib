@php
    $statusCode = $statusCode ?? 500;
    $title = $title ?? 'Terjadi Kendala';
    $message = $message ?? 'Sistem belum bisa memproses permintaan ini.';
    $appName = config('app.name', 'ASA-Tertib');
    $appLogo = null;
    $primaryColor = '#1d4ed8';
    $user = auth()->user();
    $dashboardUrl = route('login');

    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('app_settings')) {
            $appName = \App\Models\AppSetting::getValue('app.name', $appName);
            $logoEnabled = \App\Models\AppSetting::getValue('app.logo_enabled', true);
            $appLogo = $logoEnabled ? \App\Models\AppSetting::getValue('app.logo') : null;
            $primaryColor = \App\Models\AppSetting::getValue('app.primary_color', $primaryColor);
        }
    } catch (\Throwable $exception) {
        $appName = config('app.name', 'ASA-Tertib');
        $appLogo = null;
        $primaryColor = '#1d4ed8';
    }

    if (! is_string($primaryColor) || ! preg_match('/^#[0-9A-Fa-f]{6}$/', $primaryColor)) {
        $primaryColor = '#1d4ed8';
    }

    $logoUrl = null;

    if (is_string($appLogo) && trim($appLogo) !== '') {
        $appLogo = trim($appLogo);
        $logoUrl = \Illuminate\Support\Str::startsWith($appLogo, ['http://', 'https://', '/', 'data:'])
            ? $appLogo
            : asset($appLogo);
    }

    if ($user) {
        $dashboardUrl = ($user->can('petugas.konsol_antrian') || $user->hasAnyRole(['superadmin', 'admin', 'officer', 'Super Admin', 'Petugas']))
            ? route('officer.console')
            : route('dashboard');
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $statusCode }} - {{ $appName }}</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #edf5ff;
            --surface: #ffffff;
            --ink: #0f172a;
            --muted: #475569;
            --line: #cfe1f7;
            --primary: {{ $primaryColor }};
            --primary-dark: #153b9d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            min-height: 100svh;
            display: grid;
            align-items: center;
            padding: 16px 14px;
            background: var(--bg);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            letter-spacing: 0;
        }

        .error-page {
            width: 100%;
            max-width: 430px;
            margin: 0 auto;
            display: grid;
            gap: 14px;
        }

        .error-card {
            display: grid;
            gap: 14px;
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--surface);
            box-shadow: 0 18px 40px rgba(15, 61, 122, .12);
        }

        .error-brand {
            display: grid;
            justify-items: center;
            gap: 8px;
            text-align: center;
        }

        .error-logo {
            width: 76px;
            height: 76px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border: 1px solid #bfdbfe;
            background: #eaf2ff;
            color: var(--primary);
            font-size: 24px;
            font-weight: 900;
        }

        .error-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 10px;
            background: #fff;
        }

        .error-app-name {
            color: var(--primary-dark);
            font-size: 13px;
            font-weight: 900;
        }

        .error-code {
            width: fit-content;
            justify-self: center;
            min-width: 76px;
            min-height: 44px;
            display: inline-grid;
            place-items: center;
            padding: 8px 12px;
            border-radius: 8px;
            background: #dbeafe;
            color: var(--primary-dark);
            font-size: 20px;
            font-weight: 900;
        }

        h1 {
            margin: 0;
            color: var(--primary-dark);
            font-size: 22px;
            line-height: 1.2;
        }

        p {
            margin: 0;
            color: var(--muted);
            line-height: 1.55;
        }

        .button-row {
            display: grid;
            gap: 8px;
        }

        .btn {
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 8px;
            padding: 10px 14px;
            border: 1px solid transparent;
            background: var(--primary);
            color: #fff;
            font-weight: 800;
            text-decoration: none;
        }

        .btn-outline {
            background: #fff;
            border-color: #9fc5f8;
            color: var(--primary-dark);
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

        @media (min-width: 520px) {
            body {
                padding: 24px;
            }

            .error-card {
                padding: 22px;
            }
        }
    </style>
</head>
<body>
    <main class="error-page">
        <section class="error-card">
            <div class="error-brand">
                <div class="error-logo">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">
                        <span style="display: none;">{{ mb_substr($appName ?: 'A', 0, 1) }}</span>
                    @else
                        <span>{{ mb_substr($appName ?: 'A', 0, 1) }}</span>
                    @endif
                </div>
                <div class="error-app-name">{{ $appName }}</div>
            </div>
            <div class="error-code">{{ $statusCode }}</div>
            <div>
                <h1>{{ $title }}</h1>
                <p>{{ $message }}</p>
            </div>
            <div class="button-row">
                <a class="btn" href="{{ $dashboardUrl }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg>
                    Dashboard
                </a>
                <a class="btn btn-outline" href="{{ url()->previous() ?: $dashboardUrl }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                    Kembali
                </a>
            </div>
        </section>
    </main>
</body>
</html>
