<div class="exam-room-page">
    <style>
        .exam-room-page { display: grid; gap: 14px; }
        .exam-room-hero {
            display: grid;
            gap: 12px;
            background: linear-gradient(135deg, var(--primary-deep), var(--primary));
            color: #fff;
            border: 0;
            overflow: hidden;
        }
        .exam-room-hero .title { color: #fff; }
        .exam-room-hero .subtitle { color: #dbeafe; }
        .exam-room-search {
            display: grid;
            gap: 10px;
        }
        .exam-room-card {
            display: grid;
            gap: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 14px;
            background: #fff;
            box-shadow: 0 10px 28px rgba(15, 61, 122, .08);
        }
        .exam-room-person {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }
        .exam-room-person h2 {
            margin: 0;
            color: var(--primary-deep);
            font-size: 18px;
            line-height: 1.25;
        }
        .exam-room-person span {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
        }
        .exam-room-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
        .exam-room-meta div,
        .exam-room-secret div {
            display: grid;
            gap: 4px;
            min-width: 0;
            padding: 10px;
            border-radius: 8px;
            background: #f8fbff;
            color: var(--ink);
        }
        .exam-room-meta b,
        .exam-room-secret b {
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
        }
        .exam-room-secret {
            display: grid;
            gap: 8px;
            padding: 12px;
            border-radius: 8px;
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
        }
        .exam-room-secret strong {
            color: #065f46;
            font-size: 24px;
            line-height: 1;
        }
        .exam-room-username {
            font-family: Consolas, "Liberation Mono", monospace;
            font-weight: 900;
            color: var(--primary-deep);
            word-break: break-word;
        }
        .exam-room-password-note {
            color: #065f46;
            font-weight: 800;
            line-height: 1.45;
        }
        @media (max-width: 420px) {
            .exam-room-meta { grid-template-columns: 1fr; }
            .exam-room-person { display: grid; }
        }
    </style>

    <section class="panel exam-room-hero">
        <div>
            <h1 class="title">Cek Ruangan Ujian</h1>
            <p class="subtitle">Cari data peserta dengan NISN, lalu verifikasi tanggal lahir untuk melihat ruangan ujian.</p>
        </div>
    </section>

    <section class="panel exam-room-search">
        <form class="exam-room-search" wire:submit="searchParticipant">
            <div class="field">
                <label for="nisn">NISN</label>
                <input
                    id="nisn"
                    class="input"
                    type="text"
                    inputmode="numeric"
                    autocomplete="off"
                    placeholder="Masukkan NISN"
                    wire:model.live.debounce.300ms="nisn"
                >
                @error('nisn')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
            <button class="btn btn-primary" type="submit" wire:loading.attr="disabled" wire:target="searchParticipant">
                <span wire:loading.remove wire:target="searchParticipant">
                    <svg viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35"/><path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
                </span>
                <span wire:loading wire:target="searchParticipant" class="spinner"></span>
                Cari Data
            </button>
        </form>

        @if($notice)
            <div class="alert">{{ $notice }}</div>
        @endif
    </section>

    @if($foundParticipant)
        <section class="exam-room-card">
            <div class="exam-room-person">
                <div>
                    <h2>{{ $foundParticipant['name'] }}</h2>
                    <span>NISN {{ $foundParticipant['nisn'] }}</span>
                </div>
                <span class="badge">Data ditemukan</span>
            </div>

            <div class="exam-room-meta">
                <div>
                    <b>Nama Lengkap</b>
                    <span>{{ $foundParticipant['name'] }}</span>
                </div>
                <div>
                    <b>SMP</b>
                    <span>{{ $foundParticipant['junior_school'] }}</span>
                </div>
            </div>

            @if(! $showBirthDateForm && ! $roomRevealed)
                <button class="btn btn-primary" type="button" wire:click="requestRoomCheck">
                    <svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>
                    Cek Ruangan
                </button>
            @endif

            @if($showBirthDateForm)
                <form class="stack" wire:submit="verifyBirthDate">
                    <div class="field">
                        <label for="birthDate">Tanggal Lahir</label>
                        <input id="birthDate" class="input" type="date" wire:model="birthDate">
                        @error('birthDate')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    <button class="btn btn-primary" type="submit" wire:loading.attr="disabled" wire:target="verifyBirthDate">
                        <span wire:loading.remove wire:target="verifyBirthDate">
                            <svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                        </span>
                        <span wire:loading wire:target="verifyBirthDate" class="spinner"></span>
                        Tampilkan Ruangan
                    </button>
                </form>
            @endif

            @if($roomRevealed)
                <div class="exam-room-secret">
                    <div>
                        <b>Ruangan Tempat Ujian</b>
                        <strong>{{ $this->room }}</strong>
                    </div>
                    <div>
                        <b>Username</b>
                        <span class="exam-room-username">{{ $this->username }}</span>
                    </div>
                    <div>
                        <b>Password</b>
                        <span class="exam-room-password-note">Akan diberikan saat sudah di ruang ujian.</span>
                    </div>
                </div>
            @endif
        </section>
    @endif
</div>
