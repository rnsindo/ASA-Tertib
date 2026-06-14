<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QR Ambil Antrian - {{ $appName }}</title>
    <style>
        :root {
            --primary: #0f3d7a;
            --primary-strong: #082653;
            --ink: #172033;
            --muted: #536178;
            --line: #d7e3f5;
            --soft: #eef6ff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #dbe7f7;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 12px;
            background: rgba(255, 255, 255, .94);
            border-bottom: 1px solid var(--line);
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 16px;
            border: 0;
            border-radius: 8px;
            background: var(--primary);
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
        }

        .button.secondary {
            background: #fff;
            color: var(--primary);
            border: 1px solid var(--line);
        }

        .sheet-wrap {
            display: grid;
            place-items: start center;
            padding: 18px;
        }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            display: grid;
            grid-template-rows: auto auto 1fr auto;
            gap: 12mm;
            padding: 16mm 18mm 14mm;
            background: #fff;
            border: 1px solid var(--line);
            box-shadow: 0 20px 50px rgba(15, 61, 122, .18);
        }

        .brand {
            text-align: center;
        }

        .brand strong {
            display: block;
            color: var(--primary-strong);
            font-size: 22pt;
            line-height: 1.15;
        }

        .brand span {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 12pt;
        }

        .code-box {
            display: grid;
            gap: 5px;
            padding: 8mm 10mm;
            border: 2px solid var(--primary);
            border-radius: 8px;
            background: var(--soft);
            text-align: center;
        }

        .code-box span {
            color: var(--muted);
            font-size: 12pt;
            font-weight: 700;
        }

        .code-box strong {
            color: var(--primary-strong);
            font-size: 34pt;
            line-height: 1;
            letter-spacing: 3px;
        }

        .qr-area {
            display: grid;
            place-items: center;
            gap: 8mm;
            text-align: center;
        }

        .qr-frame {
            width: 142mm;
            height: 142mm;
            display: grid;
            place-items: center;
            padding: 8mm;
            border: 2px solid var(--line);
            border-radius: 8px;
            background: #fff;
        }

        .qr-frame svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .instruction {
            max-width: 160mm;
            color: var(--primary-strong);
            font-size: 17pt;
            font-weight: 900;
            line-height: 1.35;
        }

        .validity {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6mm;
        }

        .info-card {
            padding: 5mm;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fbff;
        }

        .info-card span {
            display: block;
            color: var(--muted);
            font-size: 10pt;
            font-weight: 700;
        }

        .info-card strong {
            display: block;
            margin-top: 4px;
            color: var(--primary-strong);
            font-size: 13pt;
        }

        .footer {
            color: var(--muted);
            font-size: 9pt;
            line-height: 1.45;
            text-align: center;
        }

        @page {
            size: A4;
            margin: 0;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .sheet-wrap {
                padding: 0;
            }

            .sheet {
                width: 210mm;
                min-height: 297mm;
                border: 0;
                box-shadow: none;
            }

            .validity {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            .sheet-wrap {
                padding: 0;
            }

            .sheet {
                width: 100%;
                min-height: 100vh;
                padding: 18px 14px;
                gap: 18px;
                border: 0;
                box-shadow: none;
            }

            .code-box strong {
                font-size: 34px;
            }

            .qr-frame {
                width: min(88vw, 420px);
                height: min(88vw, 420px);
            }

            .instruction {
                font-size: 20px;
            }

            .validity {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            .validity {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="button" type="button" onclick="window.print()">Cetak / Simpan PDF</button>
        <a class="button secondary" href="{{ route('officer.console') }}">Kembali</a>
    </div>

    <main class="sheet-wrap">
        <section class="sheet" aria-label="QR ambil antrian">
            <header class="brand">
                <strong>{{ $appName }}</strong>
                <span>{{ $sessionName }}</span>
            </header>

            <section class="code-box">
                <span>Kode alternatif jika QR tidak terbaca</span>
                <strong>{{ $manualCode }}</strong>
            </section>

            <section class="qr-area">
                <div class="qr-frame">{!! $qrSvg !!}</div>
                <div class="instruction">
                    Scan QR ini di lokasi layanan untuk mengambil antrian. Jika kamera bermasalah, masukkan kode alternatif di atas.
                </div>
            </section>

            <section class="validity">
                <div class="info-card">
                    <span>Mulai berlaku</span>
                    <strong>{{ \App\Support\AppClock::format($startsAt, 'd/m/Y H:i') }}</strong>
                </div>
                <div class="info-card">
                    <span>Berlaku sampai</span>
                    <strong>{{ $expiresAt ? \App\Support\AppClock::format($expiresAt, 'd/m/Y H:i') : 'Tanpa batas waktu' }}</strong>
                </div>
            </section>

            <footer class="footer">
                Link QR: {{ $checkInUrl }}<br>
                Zona waktu: {{ $timezone }}. Dicetak pada {{ \App\Support\AppClock::format($printedAt, 'd/m/Y H:i') }}. Gunakan hanya QR dan kode yang masih berlaku.
            </footer>
        </section>
    </main>
</body>
</html>
