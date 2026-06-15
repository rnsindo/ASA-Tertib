<div class="stack account-profile-page">
    <style>
        .account-profile-page {
            gap: 14px;
        }

        .profile-hero {
            position: relative;
            overflow: hidden;
            display: grid;
            gap: 14px;
            background: linear-gradient(135deg, #0f3d7a 0%, #1d4ed8 55%, #4f8df7 100%);
            color: #fff;
            border: 0;
        }

        .profile-hero::after {
            content: "";
            position: absolute;
            right: -42px;
            top: -58px;
            width: 150px;
            height: 150px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .14);
        }

        .profile-identity {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 58px 1fr;
            gap: 12px;
            align-items: center;
        }

        .profile-avatar {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .24);
            color: #fff;
        }

        .profile-avatar svg,
        .profile-note-icon svg,
        .profile-section-icon svg {
            width: 24px;
            height: 24px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .profile-identity strong {
            display: block;
            font-size: 18px;
            line-height: 1.2;
            word-break: break-word;
        }

        .profile-identity span {
            display: block;
            margin-top: 4px;
            font-size: 12px;
            color: rgba(255, 255, 255, .78);
            word-break: break-word;
        }

        .profile-note {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 38px 1fr;
            gap: 10px;
            align-items: start;
            padding: 12px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .13);
            border: 1px solid rgba(255, 255, 255, .18);
        }

        .profile-note-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: rgba(255, 255, 255, .16);
        }

        .profile-note strong {
            display: block;
            font-size: 13px;
        }

        .profile-note span {
            display: block;
            margin-top: 3px;
            font-size: 12px;
            line-height: 1.45;
            color: rgba(255, 255, 255, .78);
        }

        .profile-form-card {
            display: grid;
            gap: 14px;
        }

        .profile-section-head {
            display: grid;
            grid-template-columns: 42px 1fr;
            gap: 12px;
            align-items: center;
        }

        .profile-section-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            color: var(--primary);
            background: #eef6ff;
        }

        .profile-section-head strong {
            display: block;
            color: var(--primary-deep);
            font-size: 16px;
            line-height: 1.25;
        }

        .profile-section-head span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .readonly-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 11px 12px;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            background: #f8fbff;
            color: var(--primary-deep);
            font-size: 13px;
            font-weight: 800;
            word-break: break-word;
        }

        .readonly-pill span {
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .profile-divider {
            height: 1px;
            background: var(--line);
            margin: 2px 0;
        }

        @media (min-width: 720px) {
            .profile-form-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
            }

            .profile-form-grid .field-wide {
                grid-column: 1 / -1;
            }
        }
    </style>

    <section class="panel profile-hero">
        <div class="profile-identity">
            <div class="profile-avatar" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M18 20a6 6 0 0 0-12 0"/><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
            </div>
            <div>
                <strong>{{ $name !== '' ? $name : 'Profil Akun' }}</strong>
                <span>{{ $email }}</span>
            </div>
        </div>

        <div class="profile-note">
            <div class="profile-note-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
            </div>
            <div>
                <strong>Kelola biodata akun</strong>
                <span>Email digunakan sebagai identitas login dan tidak bisa diubah dari halaman ini.</span>
            </div>
        </div>
    </section>

    <form class="panel profile-form-card" wire:submit.prevent="save">
        <div class="profile-section-head">
            <div class="profile-section-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M18 20a6 6 0 0 0-12 0"/><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
            </div>
            <div>
                <strong>Data Akun</strong>
                <span>Nama akan tampil pada header, dashboard, dan catatan antrian.</span>
            </div>
        </div>

        <div class="field">
            <label>Email</label>
            <div class="readonly-pill">
                {{ $email }}
                <span>Terkunci</span>
            </div>
        </div>

        <div class="profile-form-grid">
            <div class="field field-wide">
                <label for="profile-name">Nama Lengkap</label>
                <input id="profile-name" class="input" type="text" wire:model.defer="name" autocomplete="name" placeholder="Masukkan nama lengkap">
                @error('name') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field field-wide">
                <label for="profile-phone">Nomor Telepon</label>
                <input id="profile-phone" class="input" type="text" wire:model.defer="phone" inputmode="tel" autocomplete="tel" placeholder="Contoh: 081234567890">
                @error('phone') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        @if($hasApplicantProfile)
            <div class="profile-divider"></div>

            <div class="profile-section-head">
                <div class="profile-section-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M4 19.5V5a2 2 0 0 1 2-2h11"/><path d="M6 17h12a2 2 0 0 1 0 4H6a2 2 0 0 1 0-4Z"/><path d="M9 7h6"/><path d="M9 11h4"/></svg>
                </div>
                <div>
                    <strong>Data Pendaftar</strong>
                    <span>Nama dan sekolah akan disimpan dalam format huruf besar.</span>
                </div>
            </div>

            <div class="profile-form-grid">
                <div class="field">
                    <label for="profile-school-origin">Nama Sekolah</label>
                    <input id="profile-school-origin" class="input" type="text" wire:model.defer="schoolOrigin" placeholder="Contoh: SMP NEGERI 1">
                    @error('schoolOrigin') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="profile-nisn">NISN</label>
                    <input id="profile-nisn" class="input" type="text" wire:model.defer="nisn" inputmode="numeric" placeholder="Masukkan NISN">
                    @error('nisn') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field field-wide">
                    <label for="profile-whatsapp">Nomor WhatsApp</label>
                    <input id="profile-whatsapp" class="input" type="text" wire:model.defer="whatsapp" inputmode="tel" autocomplete="tel" placeholder="Contoh: 081234567890">
                    @error('whatsapp') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>
        @else
            <div class="alert">
                Akun ini belum memiliki data pendaftar. Jika akun ini digunakan sebagai petugas, cukup perbarui data akun di atas.
            </div>
        @endif

        <button class="btn btn-primary" type="submit" wire:loading.attr="disabled" wire:target="save">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
            <span wire:loading.remove wire:target="save">Simpan Profil</span>
            <span wire:loading wire:target="save"><span class="spinner"></span> Menyimpan...</span>
        </button>
    </form>
</div>
