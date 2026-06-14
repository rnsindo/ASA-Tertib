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
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
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
            margin-top: 4px;
            font-size: 42px;
            line-height: .95;
            font-weight: 900;
            word-break: break-word;
        }

        .ticket-pill {
            padding: 7px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .18);
            border: 1px solid rgba(255, 255, 255, .32);
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .ticket-service {
            margin-top: 8px;
            color: #dbeafe;
            font-size: 13px;
            line-height: 1.4;
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

        .queue-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: grid;
            place-items: end center;
            padding: 16px;
            background: rgba(8, 31, 67, .52);
        }

        .queue-modal {
            width: min(100%, 520px);
            max-height: calc(100vh - 32px);
            overflow: auto;
            padding: 16px;
            background: #fff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            box-shadow: 0 24px 70px rgba(8, 47, 95, .28);
        }

        .scanner-box {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            border: 1px solid #bfdbfe;
            background: #0f172a;
            aspect-ratio: 4 / 3;
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

        @media (max-width: 720px) {
            .service-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 380px) {
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
            $counterLabel = $activeTicket?->counter?->code ?: ($activeTicket?->counter?->name ?: '-');
            $ticketCode = $activeTicket?->ticket_code ?: 'Belum Ada';
            $ticketStatus = $activeTicket?->status_label ?: 'Belum Mengantri';
            $ticketService = $activeTicket?->service?->name ?: 'Petugas akan mengarahkan layanan setelah registrasi awal.';
            $waitingCount = $tickets->where('status', \App\Models\QueueTicket::STATUS_WAITING)->count();
            $finishedCount = $tickets->where('status', \App\Models\QueueTicket::STATUS_COMPLETED)->count();
            $missedCount = $tickets->where('status', \App\Models\QueueTicket::STATUS_NO_SHOW)->count();
        @endphp

        <article class="mobile-card ticket-card" aria-label="Status tiket antrian aktif">
            <div class="ticket-head">
                <div>
                    <span>Nomor Antrian</span>
                    <div class="ticket-number">{{ $ticketCode }}</div>
                    <div class="ticket-service">{{ $ticketService }}</div>
                </div>
                <div class="ticket-pill">{{ $ticketStatus }}</div>
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

        <section id="status-layanan" class="stack">
            <div class="section-title">
                <strong>Status Layanan</strong>
                <span>{{ $services->count() }} layanan aktif</span>
            </div>
            <div class="service-grid">
                @foreach($services as $service)
                    @php
                        $ticket = $tickets->firstWhere('queue_service_id', $service->id);
                        $position = $ticket ? $this->positionFor($ticket) : null;
                        $serviceStatus = $serviceStatuses->get($service->id);
                        $quota = $serviceStatus['quota'] ?? null;
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
                        @else
                            <strong>Belum Mengantri</strong>
                            <div class="muted">Kuota: {{ $quota['label'] ?? 'Tanpa batas' }}</div>
                            @if($quota['is_full'] ?? false)
                                <span class="badge" style="background: #fee2e2; color: #991b1b;">Antrian Penuh</span>
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

        @if($selectedService)
            <div class="queue-modal-backdrop" role="dialog" aria-modal="true" aria-label="Ambil antrian {{ $selectedService->name }}">
                <section class="queue-modal stack">
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

                    <div class="scanner-box" wire:ignore>
                        <video id="queueScannerVideo" playsinline muted></video>
                        <div class="scanner-frame"></div>
                    </div>
                    <div id="queueScannerStatus" class="scanner-status">
                        Arahkan kamera ke QR ambil antrian. Jika QR terbaca, sistem akan langsung memproses otomatis.
                    </div>

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
                    <div class="log-time">{{ \App\Support\AppClock::format($applicant->created_at, 'H:i') }}</div>
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
                        <div class="log-time">{{ \App\Support\AppClock::format($ticket->created_at, 'H:i') }}</div>
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
                            const input = document.getElementById('queue_code');
                            const submit = document.getElementById('queueAutoScanSubmit');

                            if (input && submit && value) {
                                input.value = value;
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                                status.textContent = 'QR terbaca. Memproses antrian otomatis...';
                                await stopQueueScanner();
                                setTimeout(() => submit.click(), 250);
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
