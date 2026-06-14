<div class="stack app-settings-page">
    <style>
        .app-settings-page {
            gap: 14px;
        }

        .settings-card {
            display: grid;
            gap: 14px;
        }

        .settings-card-head {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .settings-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            background: #eef6ff;
            color: var(--primary);
        }

        .logo-preview-card {
            display: grid;
            grid-template-columns: 72px 1fr;
            gap: 12px;
            align-items: center;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fbff;
        }

        .logo-thumb {
            width: 72px;
            height: 72px;
            border-radius: 8px;
            border: 1px solid #bfdbfe;
            background: #eaf2ff;
            display: grid;
            place-items: center;
            overflow: hidden;
            color: var(--primary);
            font-weight: 900;
            font-size: 18px;
        }

        .logo-thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 8px;
            background: #fff;
        }

        .logo-thumb-fallback {
            display: grid;
            place-items: center;
            width: 100%;
            height: 100%;
        }

        .file-input {
            width: 100%;
            border: 1px dashed #9fc5f8;
            border-radius: 8px;
            padding: 12px;
            background: #fff;
            color: var(--primary-deep);
        }

        .settings-card-head strong {
            display: block;
            color: var(--primary-deep);
            font-size: 16px;
            line-height: 1.3;
        }

        .settings-card-head span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .settings-switch-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fbff;
        }

        .settings-switch-row strong {
            color: var(--primary-deep);
            font-size: 14px;
        }

        .settings-switch-row span {
            display: block;
            margin-top: 2px;
            color: var(--muted);
            font-size: 12px;
        }

        .switch {
            position: relative;
            width: 54px;
            height: 32px;
            flex: 0 0 auto;
        }

        .switch input {
            position: absolute;
            inset: 0;
            opacity: 0;
        }

        .switch-track {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            background: #cbd5e1;
            transition: background .18s ease;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, .18);
        }

        .switch-track::after {
            content: "";
            position: absolute;
            width: 26px;
            height: 26px;
            left: 3px;
            top: 3px;
            border-radius: 999px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, .24);
            transition: transform .18s ease;
        }

        .switch input:checked + .switch-track {
            background: var(--primary);
        }

        .switch input:checked + .switch-track::after {
            transform: translateX(22px);
        }

        .color-row {
            display: grid;
            grid-template-columns: 58px 1fr;
            gap: 10px;
            align-items: center;
        }

        .color-input {
            width: 58px;
            height: 46px;
            padding: 4px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
        }

        @media (max-width: 420px) {
            .settings-switch-row {
                align-items: flex-start;
            }

            .logo-preview-card {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @php
        $logoPreviewUrl = null;
        $faviconPreviewUrl = null;

        if ($logoUpload) {
            $logoPreviewUrl = $logoUpload->temporaryUrl();
        } elseif($appLogo !== '') {
            $logoPreviewUrl = \Illuminate\Support\Str::startsWith($appLogo, ['http://', 'https://', '/'])
                ? $appLogo
                : asset($appLogo);
        }

        if ($faviconUpload && ! \Illuminate\Support\Str::endsWith(strtolower($faviconUpload->getClientOriginalName()), '.ico')) {
            $faviconPreviewUrl = $faviconUpload->temporaryUrl();
        } elseif($appFavicon !== '') {
            $faviconPreviewUrl = \Illuminate\Support\Str::startsWith($appFavicon, ['http://', 'https://', '/'])
                ? $appFavicon
                : asset($appFavicon);
        } elseif($logoPreviewUrl && $appLogoEnabled) {
            $faviconPreviewUrl = $logoPreviewUrl;
        }
    @endphp

    @error('settings') <div class="alert alert-danger">{{ $message }}</div> @enderror

    <section class="panel settings-card">
        <div class="settings-card-head">
            <div class="settings-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h10"/></svg>
            </div>
            <div>
                <strong>Identitas Aplikasi</strong>
                <span>Nama dan logo yang tampil pada header dan halaman login.</span>
            </div>
        </div>

        <div class="field">
            <label for="appName">Nama Aplikasi</label>
            <input id="appName" class="input" type="text" wire:model="appName" placeholder="ASA-Tertib">
            @error('appName') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="settings-switch-row">
            <div>
                <strong>Tampilkan Logo</strong>
                <span>Matikan jika ingin header memakai teks nama aplikasi saja.</span>
            </div>
            <label class="switch" aria-label="Tampilkan Logo">
                <input type="checkbox" wire:model="appLogoEnabled">
                <span class="switch-track"></span>
            </label>
        </div>

        <div class="logo-preview-card">
            <div class="logo-thumb">
                @if($logoPreviewUrl && $appLogoEnabled)
                    <img src="{{ $logoPreviewUrl }}" alt="Logo aktif" onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">
                    <span class="logo-thumb-fallback" style="display: none;">{{ mb_substr($appName ?: 'A', 0, 1) }}</span>
                @else
                    <span class="logo-thumb-fallback">{{ mb_substr($appName ?: 'A', 0, 1) }}</span>
                @endif
            </div>
            <div class="stack" style="gap: 8px;">
                <div>
                    <strong style="color: var(--primary-deep);">Logo Aktif</strong>
                    <div class="muted" style="font-size: 12px;">Pilih file baru hanya jika ingin mengganti logo.</div>
                </div>
                <input id="logoUpload" class="file-input" type="file" wire:model="logoUpload" accept="image/png,image/jpeg,image/webp">
                @error('logoUpload') <span class="error">{{ $message }}</span> @enderror
                <div wire:loading wire:target="logoUpload" class="muted" style="font-size: 12px;">Mengunggah preview logo...</div>
            </div>
        </div>

        <div class="logo-preview-card">
            <div class="logo-thumb">
                @if($faviconPreviewUrl)
                    <img src="{{ $faviconPreviewUrl }}" alt="Favicon aktif" onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">
                    <span class="logo-thumb-fallback" style="display: none;">{{ mb_substr($appName ?: 'A', 0, 1) }}</span>
                @else
                    <span class="logo-thumb-fallback">{{ mb_substr($appName ?: 'A', 0, 1) }}</span>
                @endif
            </div>
            <div class="stack" style="gap: 8px;">
                <div>
                    <strong style="color: var(--primary-deep);">Ikon Browser</strong>
                    <div class="muted" style="font-size: 12px;">Upload favicon untuk tab browser. Jika kosong, sistem memakai logo atau ikon fallback otomatis.</div>
                </div>
                <input id="faviconUpload" class="file-input" type="file" wire:model="faviconUpload" accept=".ico,image/png,image/jpeg,image/webp">
                @error('faviconUpload') <span class="error">{{ $message }}</span> @enderror
                <div wire:loading wire:target="faviconUpload" class="muted" style="font-size: 12px;">Mengunggah favicon...</div>
            </div>
        </div>
    </section>

    <section class="panel settings-card">
        <div class="settings-card-head">
            <div class="settings-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22a5 5 0 0 0 5-5c0-2-1-3-3-5s-2-4-2-7c-4 4-6 7-6 11a6 6 0 0 0 6 6Z"/></svg>
            </div>
            <div>
                <strong>Tampilan</strong>
                <span>Warna utama untuk identitas visual aplikasi.</span>
            </div>
        </div>

        <div class="field">
            <label for="primaryColor">Warna Utama</label>
            <div class="color-row">
                <input id="primaryColor" class="color-input" type="color" wire:model="primaryColor">
                <input class="input" type="text" wire:model="primaryColor" placeholder="#1d4ed8">
            </div>
            @error('primaryColor') <span class="error">{{ $message }}</span> @enderror
        </div>
    </section>

    <section class="panel settings-card">
        <div class="settings-card-head">
            <div class="settings-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v5l3 3"/><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </div>
            <div>
                <strong>Waktu dan Estimasi</strong>
                <span>Zona waktu dipakai pada QR, dashboard, log, dan estimasi antrian.</span>
            </div>
        </div>

        <div class="field">
            <label for="appTimezone">Zona Waktu Aplikasi</label>
            <select id="appTimezone" class="select" wire:model="appTimezone">
                @foreach($timezoneOptions as $timezone => $label)
                    <option value="{{ $timezone }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('appTimezone') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label for="defaultServiceMinutes">Estimasi Awal Per Pendaftar</label>
            <input id="defaultServiceMinutes" class="input" type="number" min="1" max="240" wire:model="defaultServiceMinutes">
            @error('defaultServiceMinutes') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="settings-switch-row">
            <div>
                <strong>Aktifkan Quota Harian</strong>
                <span>Jika nonaktif, layanan tidak dibatasi total antrian harian.</span>
            </div>
            <label class="switch" aria-label="Aktifkan Quota Harian">
                <input type="checkbox" wire:model.live="dailyQuotaEnabled">
                <span class="switch-track"></span>
            </label>
        </div>

        @if($dailyQuotaEnabled)
            <div class="field">
                <label for="dailyQuotaLimit">Total Quota Harian</label>
                <input id="dailyQuotaLimit" class="input" type="number" min="1" max="100000" wire:model="dailyQuotaLimit">
                <div class="muted" style="font-size: 12px;">Angka ini menjadi batas harian untuk setiap layanan aktif pada sesi berjalan.</div>
                @error('dailyQuotaLimit') <span class="error">{{ $message }}</span> @enderror
            </div>
        @endif
    </section>

    <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
        <span wire:loading.remove wire:target="save">Simpan Pengaturan</span>
        <span wire:loading wire:target="save">Menyimpan...</span>
    </button>

</div>
