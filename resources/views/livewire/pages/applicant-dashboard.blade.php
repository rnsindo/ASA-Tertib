<div class="stack applicant-dashboard">
    <style>
        .applicant-dashboard {
            gap: 14px;
        }

        .mobile-card {
            background: var(--surface);
            border: 1px solid #d9e7f9;
            border-radius: 8px;
            box-shadow: 0 10px 28px rgba(15, 61, 122, .08);
        }

        .ticket-card {
            overflow: hidden;
        }

        .ticket-head {
            padding: 16px;
            background: linear-gradient(135deg, var(--primary), #2f80ed);
            color: #fff;
            display: grid;
            gap: 10px;
        }

        .ticket-top-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            min-width: 0;
        }

        .ticket-title-label {
            min-width: 0;
            color: #dbeafe;
            font-weight: 800;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ticket-head span,
        .ticket-detail span,
        .section-title span,
        .log-item span,
        .service-card span {
            display: block;
            font-size: 12px;
        }

        .ticket-number {
            font-size: 42px;
            line-height: .95;
            font-weight: 900;
            text-align: center;
            white-space: nowrap;
            word-break: normal;
            overflow-wrap: normal;
        }

        .ticket-pill {
            padding: 7px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
            border: 1px solid transparent;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .16);
        }

        .ticket-pill-empty {
            background: #e2e8f0;
            border-color: #cbd5e1;
            color: #334155;
        }

        .ticket-pill-waiting {
            background: #fed7aa;
            border-color: #fdba74;
            color: #9a3412;
        }

        .ticket-pill-called {
            background: #fef08a;
            border-color: #fde047;
            color: #854d0e;
        }

        .ticket-pill-progress {
            background: #bbf7d0;
            border-color: #86efac;
            color: #166534;
        }

        .ticket-pill-missed {
            background: #fecaca;
            border-color: #fca5a5;
            color: #991b1b;
        }

        .ticket-pill-completed {
            background: #99f6e4;
            border-color: #5eead4;
            color: #115e59;
        }

        .ticket-service {
            color: #dbeafe;
            font-size: 13px;
            line-height: 1.4;
            text-align: center;
            overflow-wrap: anywhere;
        }

        .ticket-cut {
            height: 1px;
            border-top: 1px dashed #b8cdec;
            margin: 0 18px;
        }

        .ticket-body {
            padding: 14px 16px 16px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .ticket-detail {
            min-width: 0;
        }

        .ticket-detail span {
            color: var(--muted);
            margin-bottom: 4px;
        }

        .ticket-detail strong {
            color: var(--primary-deep);
            font-size: 18px;
            overflow-wrap: anywhere;
        }

        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 12px;
            margin: 2px 4px 0;
        }

        .section-title strong {
            color: var(--primary-deep);
            font-size: 15px;
        }

        .section-title span {
            color: var(--muted);
        }

        .service-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .service-card {
            display: grid;
            gap: 8px;
            padding: 14px;
        }

        .service-card span {
            color: var(--muted);
        }

        .service-card strong {
            color: var(--primary-deep);
            font-size: 16px;
            line-height: 1.3;
        }

        .service-quota-message {
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px;
            background: #fef2f2;
            color: #991b1b;
            font-size: 12px;
            line-height: 1.45;
        }

        .service-blocked-message {
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 10px;
            background: #fffbeb;
            color: #92400e;
            font-size: 12px;
            line-height: 1.45;
        }

        .blocked-action-wrap {
            display: grid;
            gap: 8px;
        }

        .btn-soft-disabled {
            border-color: #cbd5e1;
            background: #f1f5f9;
            color: #64748b;
            cursor: not-allowed;
            opacity: .82;
        }

        .btn-soft-disabled svg {
            color: #64748b;
        }

        .blocked-tooltip {
            position: relative;
            border: 1px solid #fbbf24;
            border-radius: 10px;
            padding: 9px 10px;
            background: #fffbeb;
            color: #78350f;
            font-size: 12px;
            line-height: 1.45;
            box-shadow: 0 10px 24px rgba(146, 64, 14, .12);
        }

        .blocked-tooltip::before {
            content: "";
            position: absolute;
            top: -6px;
            left: 22px;
            width: 10px;
            height: 10px;
            border-left: 1px solid #fbbf24;
            border-top: 1px solid #fbbf24;
            background: #fffbeb;
            transform: rotate(45deg);
        }

        .withdraw-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 72;
            display: grid;
            align-items: end;
            padding: 16px;
            background: rgba(15, 23, 42, .48);
        }

        .withdraw-modal {
            width: min(100%, 520px);
            margin: 0 auto;
            display: grid;
            gap: 12px;
            padding: 16px;
            border-radius: 16px 16px 8px 8px;
            border: 1px solid #fed7aa;
            background: #fff;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
        }

        .withdraw-modal strong {
            color: var(--primary-deep);
            font-size: 18px;
        }

        .withdraw-summary {
            display: grid;
            gap: 4px;
            border-radius: 8px;
            padding: 12px;
            background: #fff7ed;
            color: #9a3412;
            font-size: 13px;
        }

        .log-list {
            padding: 6px 14px 14px;
            display: grid;
            gap: 10px;
        }

        .log-item {
            display: grid;
            grid-template-columns: 40px 1fr auto;
            gap: 10px;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #edf3fb;
        }

        .log-item:last-child {
            border-bottom: 0;
        }

        .log-dot {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: #eef6ff;
            color: var(--primary);
        }

        .log-item strong {
            display: block;
            color: var(--primary-deep);
            font-size: 13px;
            margin-bottom: 3px;
        }

        .log-item span,
        .log-time {
            color: var(--muted);
            font-size: 11px;
        }

        .log-time {
            text-align: right;
            white-space: nowrap;
        }

        .queue-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: grid;
            place-items: end center;
            min-height: 100vh;
            min-height: 100svh;
            min-height: 100dvh;
            padding: 12px;
            padding-bottom: calc(12px + env(safe-area-inset-bottom));
            background: rgba(8, 31, 67, .52);
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .queue-modal {
            width: min(100%, 520px);
            max-height: calc(100vh - 24px - env(safe-area-inset-bottom));
            max-height: calc(100svh - 24px - env(safe-area-inset-bottom));
            max-height: calc(100dvh - 24px - env(safe-area-inset-bottom));
            display: grid;
            grid-template-rows: auto minmax(0, 1fr) auto;
            gap: 12px;
            overflow: hidden;
            padding: 16px;
            background: #fff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            box-shadow: 0 24px 70px rgba(8, 47, 95, .28);
        }

        .queue-scanner-content {
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding-right: 2px;
            -webkit-overflow-scrolling: touch;
        }

        .scanner-box {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            border: 1px solid #bfdbfe;
            background: #0f172a;
            aspect-ratio: 4 / 3;
            max-height: min(42vh, 300px);
            max-height: min(42svh, 300px);
            max-height: min(42dvh, 300px);
        }

        .scanner-box video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .scanner-frame {
            position: absolute;
            inset: 16%;
            border: 2px solid rgba(255, 255, 255, .9);
            border-radius: 12px;
            box-shadow: 0 0 0 999px rgba(15, 23, 42, .32);
            pointer-events: none;
        }

        .scanner-status {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.5;
        }

        .queue-manual-panel {
            display: grid;
            gap: 10px;
            margin: 0 -16px -16px;
            padding: 12px 16px 16px;
            padding-bottom: calc(16px + env(safe-area-inset-bottom));
            border-top: 1px solid #dbeafe;
            background: linear-gradient(180deg, rgba(255, 255, 255, .96) 0%, #fff 38%);
            box-shadow: 0 -14px 28px rgba(15, 61, 122, .08);
        }

        .queue-manual-panel .btn {
            width: 100%;
        }

        @media (max-width: 720px) {
            .service-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-height: 680px) {
            .queue-modal-backdrop {
                padding-top: 8px;
                padding-bottom: calc(8px + env(safe-area-inset-bottom));
            }

            .queue-modal {
                max-height: calc(100vh - 16px - env(safe-area-inset-bottom));
                max-height: calc(100svh - 16px - env(safe-area-inset-bottom));
                max-height: calc(100dvh - 16px - env(safe-area-inset-bottom));
                padding: 12px;
                gap: 10px;
            }

            .scanner-box {
                aspect-ratio: 16 / 9;
                max-height: min(30vh, 180px);
                max-height: min(30svh, 180px);
                max-height: min(30dvh, 180px);
            }

            .scanner-frame {
                inset: 12%;
            }

            .queue-manual-panel {
                margin: 0 -12px -12px;
                padding: 10px 12px 12px;
                padding-bottom: calc(12px + env(safe-area-inset-bottom));
            }
        }

        @media (max-height: 540px) {
            .queue-modal {
                grid-template-rows: auto auto auto;
            }

            .queue-scanner-content {
                max-height: 34vh;
                max-height: 34svh;
                max-height: 34dvh;
            }

            .scanner-box {
                max-height: 132px;
            }
        }

        @media (max-width: 520px) {
            .ticket-body {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 360px) {
            .ticket-body {
                grid-template-columns: 1fr;
            }

        }
    </style>

    @if(! $applicant)
        <section class="panel stack">
            <h2 class="title" style="font-size: 20px;">Akun Belum Terhubung ke Data Pendaftar</h2>
            <p class="subtitle">Akun ini belum memiliki profil pendaftar. Jika ini akun petugas, gunakan menu Konsol Petugas.</p>
        </section>
    @else
        @php
            $queueLabel = $activePosition ? str_pad((string) $activePosition, 2, '0', STR_PAD_LEFT) : ($activeTicket ? 'Aktif' : '-');
            $counterLabel = $activeTicket?->counter?->name ?: ($activeTicket?->counter?->code ?: '-');
            $ticketCode = $activeTicket?->ticket_code ?: 'Belum Ada';
            $ticketStatus = match ($activeTicket?->status) {
                \App\Models\QueueTicket::STATUS_WAITING => 'Menunggu',
                default => $activeTicket?->status_label ?: 'Belum Mengantri',
            };
            $ticketStatusClass = match ($activeTicket?->status) {
                \App\Models\QueueTicket::STATUS_WAITING => 'ticket-pill-waiting',
                \App\Models\QueueTicket::STATUS_CALLED => 'ticket-pill-called',
                \App\Models\QueueTicket::STATUS_IN_PROGRESS => 'ticket-pill-progress',
                \App\Models\QueueTicket::STATUS_NO_SHOW => 'ticket-pill-missed',
                \App\Models\QueueTicket::STATUS_COMPLETED => 'ticket-pill-completed',
                default => 'ticket-pill-empty',
            };
            $ticketService = $activeTicket?->service?->name ?: 'Petugas akan mengarahkan layanan setelah registrasi awal.';
            $waitingCount = $tickets->where('status', \App\Models\QueueTicket::STATUS_WAITING)->count();
            $finishedCount = $tickets->where('status', \App\Models\QueueTicket::STATUS_COMPLETED)->count();
            $missedCount = $tickets->where('status', \App\Models\QueueTicket::STATUS_NO_SHOW)->count();
        @endphp

        <article class="mobile-card ticket-card" aria-label="Status tiket antrian aktif">
            <div class="ticket-head">
                <div class="ticket-top-row">
                    <span class="ticket-title-label">Nomor Antrian</span>
                    <div class="ticket-pill {{ $ticketStatusClass }}">{{ $ticketStatus }}</div>
                </div>
                <div class="ticket-number">{{ $ticketCode }}</div>
                <div class="ticket-service">{{ $ticketService }}</div>
            </div>
            <div class="ticket-cut"></div>
            <div class="ticket-body">
                <div class="ticket-detail">
                    <span>Urutan</span>
                    <strong>{{ $queueLabel }}</strong>
                </div>
                <div class="ticket-detail">
                    <span>Loket</span>
                    <strong>{{ $counterLabel }}</strong>
                </div>
                <div class="ticket-detail">
                    <span>Estimasi</span>
                    <strong>{{ $activeEstimate }}</strong>
                </div>
            </div>
        </article>

        @if($activeTicket?->status === \App\Models\QueueTicket::STATUS_NO_SHOW)
            <div class="alert alert-danger">
                Nomor Anda sudah dipanggil tetapi tidak berada di tempat. Silakan lapor petugas untuk dimasukkan ulang.
            </div>
        @endif

        @if($dashboardNotice)
            <div class="alert alert-danger">{{ $dashboardNotice }}</div>
        @endif

        <section id="status-layanan" class="stack">
            <div class="section-title">
                <strong>Status Layanan</strong>
                <span>{{ $services->count() }} layanan aktif</span>
            </div>
            <div class="service-grid">
                @foreach($services as $service)
                    @php
                        $ticket = $tickets->first(fn ($candidate) => (int) $candidate->queue_service_id === (int) $service->id && $candidate->status !== \App\Models\QueueTicket::STATUS_CANCELLED);
                        $position = $ticket ? $this->positionFor($ticket) : null;
                        $serviceStatus = $serviceStatuses->get($service->id);
                        $quota = $serviceStatus['quota'] ?? null;
                        $activeBlockingTicket = $serviceStatus['active_blocking_ticket'] ?? null;
                        $activeBlockingMessage = $serviceStatus['active_blocking_message'] ?? null;
                    @endphp
                    <article class="mobile-card service-card">
                        <span>{{ $service->name }}</span>
                        @if($ticket)
                            <strong>{{ $ticket->ticket_code }}</strong>
                            <div class="muted">{{ $ticket->counter?->name ?? 'Belum ada loket' }}</div>
                            <div class="button-row">
                                <span class="badge">{{ $ticket->status_label }}</span>
                                @if($position)
                                    <span class="badge">Urutan {{ $position }}</span>
                                @endif
                            </div>
                            @if($ticket->status === \App\Models\QueueTicket::STATUS_WAITING)
                                <button type="button" class="btn btn-outline btn-small" wire:click="openWithdrawQueueModal({{ $ticket->id }})">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M6 7l1 14h10l1-14"/><path d="M9 7V4h6v3"/></svg>
                                    Cabut Antrian
                                </button>
                            @endif
                        @else
                            <strong>Belum Mengantri</strong>
                            <div class="muted">Kuota: {{ $quota['label'] ?? 'Tanpa batas' }}</div>
                            @if($activeBlockingTicket)
                                <div class="service-blocked-message">{{ $activeBlockingMessage }}</div>
                                <div class="blocked-action-wrap">
                                    <button
                                        type="button"
                                        class="btn btn-outline btn-small btn-soft-disabled"
                                        aria-disabled="true"
                                        title="{{ $activeBlockingMessage }}"
                                        wire:click="showActiveQueueMessage({{ $activeBlockingTicket->id }})"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 11V8a6 6 0 0 0-12 0v3"/><path d="M5 11h14v10H5z"/><path d="M12 16v2"/></svg>
                                        Ambil Antrian
                                    </button>
                                    @if($blockedTooltipTicketId === $activeBlockingTicket->id)
                                        <div class="blocked-tooltip" role="tooltip">{{ $activeBlockingMessage }}</div>
                                    @endif
                                </div>
                            @elseif($quota['is_full'] ?? false)
                                <span class="badge" style="background: #fee2e2; color: #991b1b;">Antrian Penuh</span>
                                <div class="service-quota-message">
                                    Antrian layanan {{ $service->name }} sudah penuh untuk hari ini. Registrasi Anda tetap berhasil tersimpan.
                                </div>
                                <button type="button" class="btn btn-outline btn-small" wire:click="showServiceUnavailableMessage({{ $service->id }})">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                                    Ambil Antrian
                                </button>
                            @elseif($serviceStatus['dependency_error'] ?? null)
                                <div class="muted">{{ $serviceStatus['dependency_error'] }}</div>
                            @else
                                <button type="button" class="btn btn-primary btn-small" wire:click="openQueueScanner({{ $service->id }})">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h3v3h-3zM18 18h3v3h-3zM18 14h3"/></svg>
                                    Ambil Antrian
                                </button>
                            @endif
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        @if($withdrawTicket)
            <div class="withdraw-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="withdrawQueueTitle">
                <section class="withdraw-modal">
                    <div>
                        <strong id="withdrawQueueTitle">Cabut Antrian?</strong>
                        <p class="subtitle">Nomor antrian akan hilang dari urutan layanan. Jika mengambil ulang, Anda akan mendapat urutan terakhir.</p>
                    </div>
                    <div class="withdraw-summary">
                        <span><b>{{ $withdrawTicket->ticket_code }}</b> - {{ $withdrawTicket->service?->name ?: 'Layanan tidak ditemukan' }}</span>
                        <span>{{ $withdrawTicket->counter?->name ?: 'Belum ada loket' }}</span>
                    </div>
                    <div class="button-row" style="justify-content: flex-end;">
                        <button type="button" class="btn btn-outline" wire:click="closeWithdrawQueueModal">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                            Batal
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="withdrawQueue">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M6 7l1 14h10l1-14"/><path d="M9 7V4h6v3"/></svg>
                            Ya, Cabut
                        </button>
                    </div>
                </section>
            </div>
        @endif

        @if($selectedService)
            <div class="queue-modal-backdrop" role="dialog" aria-modal="true" aria-label="Ambil antrian {{ $selectedService->name }}">
                <section class="queue-modal">
                    <div class="button-row" style="justify-content: space-between;">
                        <div>
                            <h2 class="title" style="font-size: 20px;">Ambil Antrian</h2>
                            <p class="subtitle">{{ $selectedService->name }}</p>
                        </div>
                        <button type="button" class="btn btn-outline btn-small" wire:click="closeQueueScanner">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                            Tutup
                        </button>
                    </div>

                    <div class="queue-scanner-content stack">
                        <div class="scanner-box" wire:ignore>
                            <video id="queueScannerVideo" playsinline muted></video>
                            <div class="scanner-frame"></div>
                        </div>
                        <div id="queueScannerStatus" class="scanner-status">
                            Arahkan kamera ke QR ambil antrian. Jika QR terbaca, sistem akan langsung memproses otomatis.
                        </div>
                    </div>

                    <div class="queue-manual-panel">
                        <div class="field">
                            <label for="queue_code">Kode Manual</label>
                            <input id="queue_code" class="input" type="text" wire:model="queue_code" autocomplete="one-time-code" placeholder="Contoh: A7K9Q2">
                            @error('queue_code') <span class="error">{{ $message }}</span> @enderror
                            <div class="muted">Gunakan kode manual hanya jika kamera atau scan QR bermasalah.</div>
                        </div>

                        <button id="queueAutoScanSubmit" type="button" style="display: none;" wire:click="claimSelectedService"></button>

                        <button id="queueManualCodeSubmit" type="button" class="btn btn-primary" wire:click="claimSelectedService" wire:loading.attr="disabled">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            <span wire:loading.remove wire:target="claimSelectedService">Gunakan Kode Manual</span>
                            <span wire:loading wire:target="claimSelectedService">Memproses...</span>
                        </button>
                    </div>
                </section>
            </div>
        @endif

        <section class="grid grid-3">
            <div class="metric">
                <span>Antrian Aktif</span>
                <strong>{{ $waitingCount }}</strong>
            </div>
            <div class="metric">
                <span>Selesai</span>
                <strong>{{ $finishedCount }}</strong>
            </div>
            <div class="metric">
                <span>Tidak di Tempat</span>
                <strong>{{ $missedCount }}</strong>
            </div>
        </section>

        <div id="log-antrian" class="section-title">
            <strong>Log Antrian</strong>
            <span>Terbaru</span>
        </div>
        <section class="mobile-card log-list" aria-label="Log antrian terbaru">
            @if($queueLogs->isEmpty())
                <div class="log-item">
                    <div class="log-dot"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></div>
                    <div>
                        <strong>Registrasi berhasil</strong>
                        <span>Data awal pendaftar sudah diterima sistem.</span>
                    </div>
                    <div class="log-time">{{ \App\Support\AppClock::format($applicant->created_at, 'd/m/Y H:i') }}</div>
                </div>
            @else
                @foreach($queueLogs as $ticket)
                    <div class="log-item">
                        <div class="log-dot">
                            @if($ticket->status === \App\Models\QueueTicket::STATUS_COMPLETED)
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            @elseif($ticket->status === \App\Models\QueueTicket::STATUS_NO_SHOW)
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M17 8l5 5"/><path d="M22 8l-5 5"/></svg>
                            @elseif($ticket->status === \App\Models\QueueTicket::STATUS_CALLED)
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v5l3 3"/><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            @endif
                        </div>
                        <div>
                            <strong>{{ $ticket->status_label }} - {{ $ticket->ticket_code }}</strong>
                            <span>{{ $ticket->service?->name }}{{ $ticket->counter ? ' di ' . $ticket->counter->name : '' }}</span>
                        </div>
                        <div class="log-time">{{ \App\Support\AppClock::format($ticket->created_at, 'd/m/Y H:i') }}</div>
                    </div>
                @endforeach
            @endif
        </section>
    @endif

    <script>
        (() => {
            if (window.__asaQueueScannerInitialized) {
                return;
            }

            window.__asaQueueScannerInitialized = true;

            let stream = null;
            let scanTimer = null;

            function submitScannedCredential(value) {
                const input = document.getElementById('queue_code');
                const submit = document.getElementById('queueAutoScanSubmit');
                const componentRoot = input?.closest('[wire\\:id]');
                const componentId = componentRoot?.getAttribute('wire:id');

                if (input) {
                    input.value = value;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }

                if (componentId && window.Livewire?.find) {
                    window.Livewire.find(componentId).call('claimScannedCredential', value);
                    return;
                }

                if (submit) {
                    setTimeout(() => submit.click(), 350);
                }
            }

            async function stopQueueScanner() {
                if (scanTimer) {
                    clearInterval(scanTimer);
                    scanTimer = null;
                }

                if (stream) {
                    stream.getTracks().forEach((track) => track.stop());
                    stream = null;
                }
            }

            async function startQueueScanner() {
                const video = document.getElementById('queueScannerVideo');
                const status = document.getElementById('queueScannerStatus');

                if (! video || video.dataset.started === '1') {
                    return;
                }

                video.dataset.started = '1';

                try {
                    if (! navigator.mediaDevices?.getUserMedia) {
                        status.textContent = 'Browser tidak mendukung kamera. Gunakan kode manual dari petugas.';
                        return;
                    }

                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: { ideal: 'environment' } },
                        audio: false,
                    });

                    video.srcObject = stream;
                    await video.play();

                    if (! ('BarcodeDetector' in window)) {
                        status.textContent = 'Kamera aktif. Browser ini belum mendukung pembaca QR otomatis, gunakan kode manual jika QR tidak terbaca.';
                        return;
                    }

                    const detector = new BarcodeDetector({ formats: ['qr_code'] });
                    status.textContent = 'Kamera aktif. Arahkan QR ke area kotak.';

                    scanTimer = setInterval(async () => {
                        try {
                            const codes = await detector.detect(video);
                            if (! codes.length) {
                                return;
                            }

                            const value = codes[0].rawValue || '';

                            if (value) {
                                status.textContent = 'QR terbaca. Memproses antrian otomatis...';
                                await stopQueueScanner();
                                submitScannedCredential(value);
                            }
                        } catch (error) {
                            status.textContent = 'QR belum terbaca. Pastikan pencahayaan cukup atau gunakan kode manual.';
                        }
                    }, 700);
                } catch (error) {
                    status.textContent = 'Kamera tidak bisa dibuka. Berikan izin kamera atau gunakan kode manual dari petugas.';
                }
            }

            const observer = new MutationObserver(() => {
                if (document.getElementById('queueScannerVideo')) {
                    startQueueScanner();
                } else {
                    stopQueueScanner();
                }
            });

            observer.observe(document.body, { childList: true, subtree: true });
            document.addEventListener('queue-scanner-stop', stopQueueScanner);

            if (document.getElementById('queueScannerVideo')) {
                startQueueScanner();
            }
        })();
    </script>
</div>
