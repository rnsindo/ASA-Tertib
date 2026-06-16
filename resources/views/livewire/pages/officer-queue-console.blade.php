<div class="stack officer-console-page">
    <style>
        .officer-console-page { gap: 14px; }
        .officer-hero, .counter-control, .qr-panel { display: grid; gap: 14px; }
        .officer-profile {
            display: grid;
            grid-template-columns: 52px 1fr;
            gap: 12px;
            align-items: center;
        }
        .officer-avatar {
            width: 52px;
            height: 52px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: var(--primary-soft);
            color: var(--primary-deep);
            font-weight: 900;
            border: 1px solid var(--line);
            overflow: hidden;
        }
        .officer-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .officer-profile strong { display: block; color: var(--primary-deep); font-size: 18px; line-height: 1.2; }
        .officer-profile span { display: block; margin-top: 3px; color: var(--muted); font-size: 12px; word-break: break-word; }
        .counter-tabs { display: grid; gap: 8px; }
        .counter-tab {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
            text-align: left;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 12px;
            background: #fff;
            color: var(--ink);
            cursor: pointer;
        }
        .counter-tab.is-selected { border-color: var(--primary); background: var(--primary-soft); }
        .counter-tab strong { display: block; color: var(--primary-deep); font-size: 14px; }
        .counter-tab span span { display: block; margin-top: 2px; color: var(--muted); font-size: 12px; }
        .counter-state {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 5px 8px;
            font-size: 11px;
            font-weight: 900;
            background: #fee2e2;
            color: #991b1b;
        }
        .counter-state.is-open { background: #dcfce7; color: #166534; }
        .counter-control, .qr-panel {
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fbff;
        }
        .manual-code-card {
            display: grid;
            gap: 8px;
            margin-top: 10px;
            padding: 12px;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            background: linear-gradient(180deg, #fff7cc 0%, #fffbeb 100%);
            box-shadow: 0 12px 26px rgba(245, 158, 11, .16);
        }
        .manual-code-card span {
            color: #92400e;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0;
        }
        .manual-code-otp {
            display: flex;
            flex-wrap: nowrap;
            gap: 8px;
            align-items: center;
            width: 100%;
            overflow: hidden;
        }
        .manual-code-digit {
            flex: 0 1 38px;
            width: 38px;
            min-width: 0;
            min-height: 44px;
            display: grid;
            place-items: center;
            border: 1px solid #93c5fd;
            border-radius: 8px;
            background: #fff;
            color: #0f3d7a;
            font-size: 20px;
            font-weight: 900;
            line-height: 1;
            box-shadow: inset 0 -3px 0 rgba(37, 99, 235, .08), 0 8px 18px rgba(146, 64, 14, .12);
        }
        @media (max-width: 380px) {
            .manual-code-otp { gap: 5px; }
            .manual-code-digit {
                flex-basis: 32px;
                width: 32px;
                min-height: 38px;
                border-radius: 7px;
                font-size: 18px;
            }
        }
        .btn-counter-open {
            background: #f97316;
            border-color: #ea580c;
            color: #fff;
        }
        .btn-counter-open:hover { background: #ea580c; }
        .btn-counter-closed {
            background: #16a34a;
            border-color: #15803d;
            color: #fff;
        }
        .btn-counter-closed:hover { background: #15803d; }
        .counter-control-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .counter-control-head strong { color: var(--primary-deep); }
        .compact-metrics { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
        .status-metric {
            display: grid;
            justify-items: center;
            gap: 6px;
            min-width: 0;
            padding: 9px;
        }
        .status-metric-icon {
            position: relative;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 0;
            background: var(--primary-soft);
            color: var(--primary-deep);
            cursor: pointer;
        }
        .status-metric-icon svg {
            width: 17px;
            height: 17px;
            display: block;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .status-metric-icon:focus {
            outline: 2px solid rgba(59, 130, 246, .38);
            outline-offset: 2px;
        }
        .status-metric-icon:focus::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 50%;
            bottom: calc(100% + 8px);
            transform: translateX(-50%);
            z-index: 20;
            border-radius: 8px;
            padding: 6px 8px;
            background: var(--primary-deep);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
            box-shadow: 0 12px 24px rgba(15, 23, 42, .18);
        }
        .status-metric-icon:focus::before {
            content: "";
            position: absolute;
            left: 50%;
            bottom: calc(100% + 3px);
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: var(--primary-deep);
        }
        .status-metric strong {
            display: block;
            color: var(--primary-deep);
            font-size: 18px;
            line-height: 1;
        }
        .applicant-directory { display: grid; gap: 12px; }
        .quick-search-panel {
            display: grid;
            gap: 10px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fbff;
        }
        .quick-search-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .quick-search-head strong { color: var(--primary-deep); }
        .quick-search-head span { font-size: 12px; color: var(--muted); text-align: right; }
        .applicant-scroll {
            max-height: min(58vh, 520px);
            overflow-y: auto;
            overscroll-behavior: contain;
            display: grid;
            gap: 10px;
            padding: 2px 2px 8px;
        }
        .applicant-card {
            display: grid;
            gap: 10px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            padding: 12px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        }
        .applicant-card-head {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: start;
        }
        .applicant-card-head strong {
            display: block;
            color: var(--primary-deep);
            font-size: 15px;
            line-height: 1.25;
        }
        .applicant-card-head span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
            word-break: break-word;
        }
        .applicant-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
        .applicant-meta span {
            display: grid;
            gap: 2px;
            padding: 8px;
            border-radius: 8px;
            background: #f1f5f9;
            color: var(--ink);
            font-size: 12px;
            word-break: break-word;
        }
        .applicant-meta b {
            color: var(--muted);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0;
        }
        .applicant-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
        .applicant-actions .btn { width: 100%; justify-content: center; }
        .applicant-actions .btn:only-child { grid-column: 1 / -1; }
        .applicant-actions .scroll-status { grid-column: 1 / -1; }
        .ticket-list { display: grid; gap: 10px; }
        .ticket-card {
            display: grid;
            gap: 10px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 12px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        }
        .ticket-card.is-current {
            border-color: var(--primary);
            background: #f8fbff;
        }
        .ticket-card-head {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 10px;
            align-items: start;
        }
        .ticket-number {
            min-width: 56px;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            background: var(--primary-deep);
            color: #fff;
            font-weight: 900;
            line-height: 1.15;
        }
        .ticket-number span {
            display: block;
            margin-top: 3px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 10px;
        }
        .ticket-title strong {
            display: block;
            color: var(--primary-deep);
            font-size: 15px;
            line-height: 1.25;
        }
        .ticket-title span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
        }
        .ticket-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
        .ticket-meta span {
            display: grid;
            gap: 2px;
            padding: 8px;
            border-radius: 8px;
            background: #f1f5f9;
            color: var(--ink);
            font-size: 12px;
            word-break: break-word;
        }
        .ticket-meta b {
            color: var(--muted);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0;
        }
        .ticket-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
        .ticket-actions .btn { width: 100%; justify-content: center; }
        .modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: grid;
            align-items: end;
            background: rgba(15, 23, 42, 0.46);
            padding: 16px;
        }
        .transfer-modal {
            width: min(100%, 520px);
            margin: 0 auto;
            display: grid;
            gap: 12px;
            border-radius: 16px 16px 8px 8px;
            background: #fff;
            padding: 16px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
        }
        .transfer-modal-head {
            display: flex;
            align-items: start;
            justify-content: space-between;
            gap: 10px;
        }
        .transfer-modal-head strong { display: block; color: var(--primary-deep); font-size: 17px; }
        .section-dashboard { background: linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%); border-color: #bfdbfe; }
        .section-active { background: linear-gradient(180deg, #ffffff 0%, #eff6ff 100%); border-color: #93c5fd; }
        .section-assign { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-color: #bbf7d0; }
        .section-noshow { background: linear-gradient(180deg, #ffffff 0%, #fff7ed 100%); border-color: #fed7aa; }
        .section-counters { background: linear-gradient(180deg, #ffffff 0%, #f5f3ff 100%); border-color: #ddd6fe; }
        .no-show-list, .counter-summary-list { display: grid; gap: 10px; }
        .no-show-card, .counter-summary-card {
            display: grid;
            gap: 10px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 12px;
            background: rgba(255, 255, 255, .88);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
        }
        .no-show-card { border-color: #fed7aa; }
        .counter-summary-card { border-color: #ddd6fe; }
        .summary-head {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: start;
        }
        .summary-head strong {
            display: block;
            color: var(--primary-deep);
            font-size: 15px;
            line-height: 1.25;
        }
        .summary-head span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }
        .status-pill {
            display: grid;
            gap: 2px;
            border-radius: 8px;
            padding: 8px;
            background: #f1f5f9;
            color: var(--ink);
            text-align: center;
        }
        .status-pill b {
            color: var(--muted);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0;
        }
        .status-pill strong {
            color: var(--primary-deep);
            font-size: 18px;
            line-height: 1;
        }
        .status-pill.is-waiting { background: #eff6ff; }
        .status-pill.is-done { background: #ecfdf5; }
        .status-pill.is-noshow { background: #fff7ed; }
        .scroll-status {
            border: 1px dashed var(--line);
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            color: var(--muted);
            background: #f8fbff;
            font-size: 13px;
        }
        @media (max-width: 720px) {
            .officer-profile { grid-template-columns: 44px 1fr; }
            .officer-avatar { width: 44px; height: 44px; }
            .counter-control-head { display: grid; }
            .quick-search-head { display: grid; }
            .quick-search-head span { text-align: left; }
            .applicant-meta { grid-template-columns: 1fr; }
            .applicant-actions { grid-template-columns: 1fr; }
            .ticket-card-head { grid-template-columns: 1fr; }
            .ticket-number { width: fit-content; min-width: 72px; }
            .ticket-meta { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .ticket-actions { grid-template-columns: 1fr; }
            .summary-head { grid-template-columns: 1fr; }
        }
    </style>

    <section class="panel officer-hero section-dashboard">
        <div class="officer-profile">
            <div class="officer-avatar">
                @if(auth()->user()?->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="Foto {{ auth()->user()->name }}">
                @else
                    {{ mb_substr(auth()->user()?->name ?: 'P', 0, 1) }}
                @endif
            </div>
            <div>
                <h1 class="title">Dashboard Petugas</h1>
                <strong>{{ auth()->user()?->name }}</strong>
                <span>{{ auth()->user()?->email }}</span>
                <span>{{ $isCounterManager ? 'Mode supervisi: melihat semua loket.' : 'Loket tugas: ' . $assignedCountersCount . ' loket.' }}</span>
            </div>
        </div>

        @if($counters->isNotEmpty())
            <div class="counter-tabs" aria-label="Daftar loket tugas">
                @foreach($counters as $counter)
                    <button type="button" class="counter-tab {{ $selectedCounter?->id === $counter->id ? 'is-selected' : '' }}" wire:click="selectCounter({{ $counter->id }})">
                        <span>
                            <strong>{{ $counter->name }} - {{ $counter->code }}</strong>
                            <span>{{ $counter->service->name }}{{ $counter->assignedOfficer ? ' - ' . $counter->assignedOfficer->name : '' }}</span>
                        </span>
                        <span class="counter-state {{ $counter->is_active ? 'is-open' : '' }}">{{ $counter->is_active ? 'Buka' : 'Tutup' }}</span>
                    </button>
                @endforeach
            </div>
        @endif

        @error('selectedCounterId') <span class="error">{{ $message }}</span> @enderror

        @if($selectedCounter)
            <div class="counter-control">
                <div class="counter-control-head">
                    <div>
                        <strong>{{ $selectedCounter->name }} sedang {{ $selectedCounter->is_active ? 'dibuka' : 'ditutup' }}</strong>
                        <div class="muted">{{ $selectedCounter->service->name }} - {{ $selectedCounter->code }}</div>
                    </div>
                    <button type="button" class="btn {{ $selectedCounter->is_active ? 'btn-counter-open' : 'btn-counter-closed' }}" wire:click="toggleSelectedCounterStatus">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v10"/><path d="M18.4 5.6a9 9 0 1 1-12.8 0"/></svg>
                        {{ $selectedCounter->is_active ? 'Tutup Loket' : 'Buka Loket' }}
                    </button>
                </div>

                <div class="compact-metrics">
                    <div class="metric status-metric">
                        <button type="button" class="status-metric-icon" data-tooltip="Menunggu" aria-label="Menunggu: {{ $waitingCount }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v5l3 3"/><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </button>
                        <strong>{{ $waitingCount }}</strong>
                    </div>
                    <div class="metric status-metric">
                        <button type="button" class="status-metric-icon" data-tooltip="Selesai" aria-label="Selesai: {{ $completedCount }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        </button>
                        <strong>{{ $completedCount }}</strong>
                    </div>
                    <div class="metric status-metric">
                        <button type="button" class="status-metric-icon" data-tooltip="Tidak di Tempat" aria-label="Tidak di Tempat: {{ $noShowCount }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M17 8l5 5"/><path d="M22 8l-5 5"/></svg>
                        </button>
                        <strong>{{ $noShowCount }}</strong>
                    </div>
                </div>
            </div>
        @endif

        <section class="qr-panel">
            <div class="button-row" style="justify-content: space-between;">
                <div>
                    <strong>QR & Kode Ambil Antrian</strong>
                    <div class="muted">
                        Sesi {{ $currentSession->name }}.<br>
                        @if($activeQrCode)
                            QR aktif sampai {{ $activeQrCode->expires_at ? \App\Support\AppClock::format($activeQrCode->expires_at, 'd/m/Y H:i') : 'tanpa batas waktu' }}.
                        @else
                            Belum ada QR/kode aktif atau masa berlakunya sudah habis.
                        @endif
                    </div>
                    @if($activeQrCode?->manual_code)
                        <div class="manual-code-card" aria-label="Kode manual {{ $activeQrCode->manual_code }}">
                            <span>Kode Manual</span>
                            <div class="manual-code-otp">
                                @foreach(str_split($activeQrCode->manual_code) as $character)
                                    <strong class="manual-code-digit">{{ $character }}</strong>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <div class="button-row">
                    @if($activeQrCode)
                        <a class="btn btn-outline btn-small" href="{{ route('officer.queue-qr.print') }}" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                            Download QR
                        </a>
                    @endif
                    @if($canManageQueueQr)
                        <button type="button" class="btn btn-primary btn-small" wire:click="generateCheckInQr">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h3v3h-3zM18 18h3v3h-3zM18 14h3"/></svg>
                            Buat/Ganti QR & Kode
                        </button>
                    @endif
                </div>
            </div>

            @if($generatedCheckInUrl && $activeQrCode?->manual_code === $generatedCheckInCode)
                <div class="field">
                    <label for="generatedCheckInUrl">Link QR aktif yang baru dibuat</label>
                    <input id="generatedCheckInUrl" class="input" type="text" value="{{ $generatedCheckInUrl }}" readonly>
                    <div class="manual-code-card" aria-label="Kode manual baru {{ $generatedCheckInCode }}">
                        <span>Kode Manual Baru</span>
                        <div class="manual-code-otp">
                            @foreach(str_split((string) $generatedCheckInCode) as $character)
                                <strong class="manual-code-digit">{{ $character }}</strong>
                            @endforeach
                        </div>
                    </div>
                    <div class="muted">Berlaku sampai {{ $generatedCheckInExpiresAt ?? '2 jam dari sekarang' }}.</div>
                </div>
            @endif
        </section>
    </section>

    @if(! $selectedCounter)
        <section class="panel">
            @if($isCounterManager)
                <div class="empty">Belum ada loket yang tersedia. Tambahkan loket melalui menu Manajemen Layanan.</div>
            @else
                <div class="empty">Anda belum ditugaskan ke loket tertentu. Hubungi Super Admin agar nama Anda dipilih sebagai petugas loket pada Manajemen Layanan.</div>
            @endif
        </section>
    @else
        <section class="panel stack section-active">
            <div>
                <h2 class="title" style="font-size: 20px;">Antrian Loket Ini</h2>
                <p class="subtitle">Antrian aktif pada {{ $selectedCounter->name }}. Layanan berurutan hanya bisa memanggil nomor menunggu paling awal; layanan acak bisa dipanggil atau dimulai langsung.</p>
            </div>

            @if($activeTickets->isEmpty())
                <div class="empty">Belum ada antrian aktif pada loket ini.</div>
            @else
                <div class="ticket-list">
                    @foreach($activeTickets as $ticket)
                        @php
                            $enforceCallOrder = $ticket->service?->enforce_call_order ?? true;
                            $canCallTicket = $ticket->status === \App\Models\QueueTicket::STATUS_WAITING
                                && (! $enforceCallOrder || $ticket->id === $firstWaitingTicketId);
                            $canStartTicket = $ticket->status === \App\Models\QueueTicket::STATUS_CALLED
                                || ($ticket->status === \App\Models\QueueTicket::STATUS_WAITING && ! $enforceCallOrder);
                        @endphp
                        <article class="ticket-card {{ $ticket->id === $firstWaitingTicketId ? 'is-current' : '' }}" wire:key="active-ticket-{{ $ticket->id }}">
                            <div class="ticket-card-head">
                                <div class="ticket-number">
                                    {{ $ticket->ticket_code }}
                                    <span>{{ $ticket->status_label }}</span>
                                </div>
                                <div class="ticket-title">
                                    <strong>{{ $ticket->applicant?->full_name ?: 'Pendaftar tidak ditemukan' }}</strong>
                                    <span>{{ $ticket->applicant?->school_origin ?: 'Sekolah belum tersedia' }}</span>
                                    <span>{{ $ticket->applicant?->nisn ?: '-' }} - {{ $ticket->service?->name ?: '-' }}</span>
                                </div>
                                <span class="badge">{{ $ticket->counter?->code ?: '-' }}</span>
                            </div>

                            <div class="ticket-meta">
                                <span><b>Status</b>{{ $ticket->status_label }}</span>
                                <span><b>Masuk</b>{{ $ticket->assigned_at ? \App\Support\AppClock::format($ticket->assigned_at, 'H:i') : '-' }}</span>
                            </div>

                            <div class="ticket-actions">
                                @if($canCallTicket)
                                    <button type="button" class="btn btn-primary btn-small" wire:click="callTicket({{ $ticket->id }})">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                                        Panggil
                                    </button>
                                @endif
                                @if($canStartTicket)
                                    <button type="button" class="btn btn-outline btn-small" wire:click="startTicket({{ $ticket->id }})">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3l14 9-14 9V3Z"/></svg>
                                        Mulai
                                    </button>
                                @endif
                                @if($ticket->status === \App\Models\QueueTicket::STATUS_CALLED)
                                    <button type="button" class="btn btn-outline btn-small" wire:click="markNoShow({{ $ticket->id }})">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M17 8l5 5"/><path d="M22 8l-5 5"/></svg>
                                        Tidak di Tempat
                                    </button>
                                @endif
                                @if(in_array($ticket->status, [\App\Models\QueueTicket::STATUS_CALLED, \App\Models\QueueTicket::STATUS_IN_PROGRESS], true))
                                    <button type="button" class="btn btn-outline btn-small" wire:click="completeTicket({{ $ticket->id }})">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                        Selesai
                                    </button>
                                @endif
                                <button type="button" class="btn btn-outline btn-small" wire:click="openTransferModal({{ $ticket->id }})">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7h11l-3-3"/><path d="M18 7l-3 3"/><path d="M17 17H6l3 3"/><path d="M6 17l3-3"/></svg>
                                    Pindah
                                </button>
                                <button type="button" class="btn btn-danger btn-small" wire:click="cancelTicket({{ $ticket->id }})">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                                    Batal
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        @if($canDirectApplicants)
            <section class="panel stack section-assign">
                <div>
                    <h2 class="title" style="font-size: 20px;">Arahkan Pendaftar ke Loket</h2>
                    <p class="subtitle">Data pelanggan/pendaftar hari ini diurutkan dari check-in paling awal. Pendaftar yang sudah antri dipindahkan ke bawah daftar.</p>
                </div>

                @if(! $selectedCounter->is_active)
                    <div class="alert">Loket yang sedang dipilih ditutup. Pendaftar tetap dapat diarahkan ke layanan lain yang memiliki loket buka.</div>
                @endif

                <div class="applicant-directory">
                    <div class="quick-search-panel">
                        <div class="quick-search-head">
                            <strong>Pencarian Cepat</strong>
                            <span>Menampilkan {{ $applicants->count() }} dari {{ $totalApplicants }} data hari ini</span>
                        </div>
                        <div class="grid grid-2">
                            <div class="field">
                                <label for="search">Cari Pendaftar</label>
                                <input id="search" class="input" type="search" wire:model.live.debounce.250ms="search" placeholder="Nama, NISN, WhatsApp, sekolah, email">
                                @error('search') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label for="notes">Catatan Loket</label>
                                <input id="notes" class="input" type="text" wire:model="notes" placeholder="Opsional">
                            </div>
                        </div>
                    </div>

                    <div class="applicant-scroll" aria-label="Daftar pendaftar hari ini">
                        @forelse($applicants as $applicant)
                            @php($presence = $presenceByApplicantId->get($applicant->id))
                            @php($activeQueueTicket = $activeTicketByApplicantId->get($applicant->id))
                            <article class="applicant-card" wire:key="officer-applicant-{{ $applicant->id }}">
                                <div class="applicant-card-head">
                                    <div>
                                        <strong>{{ $applicant->full_name }}</strong>
                                        <span>{{ $applicant->user?->email ?: 'Email belum tersedia' }}</span>
                                    </div>
                                    <div>
                                        @if($activeQueueTicket)
                                            <span class="badge">Sedang Antri</span>
                                        @elseif($presence)
                                            <span class="badge">Hadir {{ \App\Support\AppClock::format($presence->presence_confirmed_at, 'H:i') }}</span>
                                        @else
                                            <span class="badge" style="background: #fee2e2; color: #991b1b;">Belum Hadir</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="applicant-meta">
                                    <span><b>NISN</b>{{ $applicant->nisn ?: '-' }}</span>
                                    <span><b>Sekolah SMP</b>{{ $applicant->school_origin ?: '-' }}</span>
                                    <span><b>WhatsApp</b>{{ $applicant->whatsapp ?: '-' }}</span>
                                    <span>
                                        <b>Status</b>
                                        @if($activeQueueTicket)
                                            Sedang antri di {{ $activeQueueTicket->counter?->name ?: 'loket aktif' }} ({{ $activeQueueTicket->counter?->code ?: '-' }}) - {{ $activeQueueTicket->status_label }}
                                        @else
                                            {{ $presence ? 'Sudah hadir di lokasi' : 'Perlu konfirmasi hadir' }}
                                        @endif
                                    </span>
                                </div>

                                <div class="applicant-actions">
                                    @if(! $presence && ! $activeQueueTicket)
                                        <button type="button" class="btn btn-outline btn-small" wire:click="confirmApplicantPresence({{ $applicant->id }})">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                            Konfirmasi Hadir
                                        </button>
                                    @endif
                                    @if(! $activeQueueTicket)
                                        <button type="button" class="btn btn-primary btn-small" wire:click="openAssignServiceModal({{ $applicant->id }})">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                                            Masukkan
                                        </button>
                                    @else
                                        <div class="scroll-status" style="padding: 10px;">Sudah berada di {{ $activeQueueTicket->counter?->name ?: 'loket lain' }}. Tombol Masukkan disembunyikan.</div>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="scroll-status">{{ trim($search) !== '' ? 'Tidak ada hasil pencarian cepat.' : 'Belum ada pelanggan/pendaftar yang masuk hari ini.' }}</div>
                        @endforelse

                        @if($hasMoreApplicants)
                            <div class="scroll-status" wire:key="applicant-load-more-{{ $visibleApplicantCount }}-{{ md5($search) }}" wire:poll.visible.700ms="loadMoreApplicants">
                                Memuat 5 data berikutnya...
                            </div>
                        @elseif($totalApplicants > 0)
                            <div class="scroll-status">Semua data sudah ditampilkan.</div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        <section class="panel stack section-noshow">
            <div>
                <h2 class="title" style="font-size: 20px;">Tidak di Tempat Hari Ini</h2>
                <p class="subtitle">Pendaftar yang hadir kembali dimasukkan ulang setelah dua antrian menunggu berikutnya pada loket yang sama.</p>
            </div>

            <div class="quick-search-panel">
                <div class="quick-search-head">
                    <strong>Pencarian Tidak di Tempat</strong>
                    <span>Menampilkan {{ $noShowTickets->count() }} dari {{ $noShowCount }} data</span>
                </div>
                <div class="field">
                    <label for="noShowSearch">Cari Pendaftar Terlewat</label>
                    <input id="noShowSearch" class="input" type="search" wire:model.live.debounce.250ms="noShowSearch" placeholder="Nama, NISN, sekolah, WhatsApp, kode tiket">
                    @error('noShowSearch') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>

            @if($noShowTickets->isEmpty())
                <div class="empty">{{ trim($noShowSearch) !== '' ? 'Tidak ada data tidak di tempat yang cocok dengan pencarian.' : 'Belum ada pendaftar yang terlewat pada loket ini.' }}</div>
            @else
                <div class="no-show-list">
                    @foreach($noShowTickets as $ticket)
                        <article class="no-show-card" wire:key="no-show-card-{{ $ticket->id }}">
                            <div class="summary-head">
                                <div>
                                    <strong>{{ $ticket->applicant?->full_name ?: 'Pendaftar tidak ditemukan' }}</strong>
                                    <span>{{ $ticket->ticket_code }} - {{ $ticket->service?->name ?: '-' }}</span>
                                </div>
                                <span class="badge" style="background: #ffedd5; color: #9a3412;">{{ $ticket->no_show_count }}x tidak di tempat</span>
                            </div>

                            <div class="ticket-meta">
                                <span><b>NISN</b>{{ $ticket->applicant?->nisn ?: '-' }}</span>
                                <span><b>Waktu</b>{{ $ticket->no_show_at ? \App\Support\AppClock::format($ticket->no_show_at, 'H:i') : '-' }}</span>
                            </div>

                            @if($ticket->notes)
                                <div class="scroll-status" style="text-align: left; background: #fff7ed;">{{ $ticket->notes }}</div>
                            @endif

                            <button type="button" class="btn btn-primary btn-small" wire:click="requeueNoShow({{ $ticket->id }})">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/></svg>
                                Masukkan Ulang
                            </button>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        @if($assigningApplicant)
            <div class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="assignServiceTitle" wire:key="assign-service-modal-{{ $assigningApplicant->id }}">
                <div class="transfer-modal">
                    <div class="transfer-modal-head">
                        <div>
                            <strong id="assignServiceTitle">Masukkan ke Layanan</strong>
                            <div class="muted">
                                {{ $assigningApplicant->full_name }} - {{ $assigningApplicant->nisn ?: 'NISN belum tersedia' }}
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline btn-small" wire:click="closeAssignServiceModal">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                            Tutup
                        </button>
                    </div>

                    <div class="field">
                        <label for="assigningServiceId">Pilih Layanan</label>
                        <select id="assigningServiceId" class="select" wire:model.live="assigningServiceId" data-autocomplete-select data-autocomplete-placeholder="Cari layanan">
                            <option value="">Pilih layanan yang akan diambil</option>
                            @foreach($assignmentServices as $service)
                                @php($status = $assignmentServiceStatuses->get($service->id))
                                @php($quota = $status['quota'] ?? null)
                                <option value="{{ $service->id }}" @disabled(! ($status['can_queue'] ?? false))>
                                    {{ $service->name }}{{ ($quota['is_enabled'] ?? false) ? ' - Kuota ' . ($quota['label'] ?? 'Tanpa batas') : '' }}{{ ! ($status['can_queue'] ?? false) ? ' - ' . ($status['unavailable_message'] ?? 'Tidak tersedia') : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigningServiceId') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    @if($assigningServiceId)
                        @php($selectedAssignmentStatus = $assignmentServiceStatuses->get((int) $assigningServiceId))
                        <div class="scroll-status" style="text-align: left;">
                            @if($selectedAssignmentStatus['can_queue'] ?? false)
                                Pilih loket tujuan. Kuota layanan hanya menjadi informasi dan tidak membatasi aksi khusus panitia ini.
                                @if($selectedAssignmentStatus['quota']['is_full'] ?? false)
                                    Kuota layanan sudah penuh, tetapi pendaftar tetap dapat dimasukkan oleh panitia untuk keadaan tertentu.
                                @endif
                            @else
                                {{ $selectedAssignmentStatus['unavailable_message'] ?? 'Layanan belum dapat diambil.' }}
                            @endif
                        </div>
                    @else
                        <div class="scroll-status" style="text-align: left;">
                            Pilih layanan terlebih dahulu, lalu pilih loket tujuan yang akan menerima pendaftar.
                        </div>
                    @endif

                    <div class="field">
                        <label for="assigningCounterId">Pilih Loket Tujuan</label>
                        <select id="assigningCounterId" class="select" wire:model.live="assigningCounterId" data-autocomplete-select data-autocomplete-placeholder="Cari loket" @disabled(! $assigningServiceId || ! ($assignmentServiceStatuses->get((int) $assigningServiceId)['can_queue'] ?? false))>
                            <option value="">Pilih loket tujuan</option>
                            @foreach($assignmentCounters as $counter)
                                <option value="{{ $counter->id }}">
                                    {{ $counter->service?->name }} - {{ $counter->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigningCounterId') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label for="assignNotes">Catatan</label>
                        <input id="assignNotes" class="input" type="text" wire:model="notes" placeholder="Opsional">
                    </div>

                    <div class="button-row" style="justify-content: flex-end;">
                        <button type="button" class="btn btn-outline" wire:click="closeAssignServiceModal">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                            Batal
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="confirmAssignApplicantToService">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            Masukkan
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if($transferTicket)
            <div class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="transferTicketTitle" wire:key="transfer-ticket-modal">
                <div class="transfer-modal">
                    <div class="transfer-modal-head">
                        <div>
                            <strong id="transferTicketTitle">Pindah Loket</strong>
                            <div class="muted">
                                {{ $transferTicket->ticket_code }} - {{ $transferTicket->applicant?->full_name ?: 'Pendaftar tidak ditemukan' }}
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline btn-small" wire:click="closeTransferModal">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                            Tutup
                        </button>
                    </div>

                    <div class="applicant-meta">
                        <span><b>Loket Saat Ini</b>{{ $transferTicket->counter?->name ?: '-' }} ({{ $transferTicket->counter?->code ?: '-' }})</span>
                        <span><b>Layanan Saat Ini</b>{{ $transferTicket->service?->name ?: '-' }}</span>
                    </div>

                    <div class="field">
                        <label for="transferTargetCounterId">Pilih Loket Tujuan</label>
                        <select id="transferTargetCounterId" class="select" wire:model="transferTargetCounterId" data-autocomplete-select data-autocomplete-placeholder="Cari loket tujuan">
                            <option value="">Pilih loket tujuan</option>
                            @foreach($transferCounters as $counter)
                                <option value="{{ $counter->id }}" @disabled($transferTicket->service_counter_id === $counter->id)>
                                    {{ $counter->service?->name }} - {{ $counter->name }}{{ ! $counter->is_active ? ' - tutup' : '' }}{{ $transferTicket->service_counter_id === $counter->id ? ' - loket saat ini' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('transferTargetCounterId') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label for="transferNotes">Catatan Pindah</label>
                        <input id="transferNotes" class="input" type="text" wire:model="notes" placeholder="Opsional">
                    </div>

                    <div class="button-row" style="justify-content: flex-end;">
                        <button type="button" class="btn btn-outline" wire:click="closeTransferModal">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                            Batal
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="confirmTransferTicket">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7h11l-3-3"/><path d="M18 7l-3 3"/><path d="M17 17H6l3 3"/><path d="M6 17l3-3"/></svg>
                            Pindahkan
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
