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
        .counter-control-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .counter-control-head strong { color: var(--primary-deep); }
        .compact-metrics { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
        .compact-metrics .metric strong { font-size: 22px; }
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
            .compact-metrics { grid-template-columns: 1fr; }
            .quick-search-head { display: grid; }
            .quick-search-head span { text-align: left; }
            .applicant-meta { grid-template-columns: 1fr; }
            .applicant-actions { grid-template-columns: 1fr; }
        }
    </style>

    <section class="panel officer-hero">
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
                    <button type="button" class="btn {{ $selectedCounter->is_active ? 'btn-outline' : 'btn-primary' }}" wire:click="toggleSelectedCounterStatus">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v10"/><path d="M18.4 5.6a9 9 0 1 1-12.8 0"/></svg>
                        {{ $selectedCounter->is_active ? 'Tutup Loket' : 'Buka Loket' }}
                    </button>
                </div>

                <div class="compact-metrics">
                    <div class="metric"><span>Menunggu</span><strong>{{ $waitingCount }}</strong></div>
                    <div class="metric"><span>Dipanggil</span><strong>{{ $calledCount }}</strong></div>
                    <div class="metric"><span>Berlangsung</span><strong>{{ $inProgressCount }}</strong></div>
                </div>
            </div>
        @endif

        <section class="qr-panel">
            <div class="button-row" style="justify-content: space-between;">
                <div>
                    <strong>QR & Kode Ambil Antrian</strong>
                    <div class="muted">
                        Sesi {{ $currentSession->name }}.
                        @if($activeQrCode)
                            QR aktif sampai {{ $activeQrCode->expires_at ? \App\Support\AppClock::format($activeQrCode->expires_at, 'd/m/Y H:i') : 'tanpa batas waktu' }}.
                            Kode manual: <strong>{{ $activeQrCode->manual_code ?? '-' }}</strong>.
                        @else
                            Belum ada QR/kode aktif atau masa berlakunya sudah habis.
                        @endif
                    </div>
                </div>
                <div class="button-row">
                    @if($activeQrCode)
                        <a class="btn btn-outline btn-small" href="{{ route('officer.queue-qr.print') }}" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                            Download QR
                        </a>
                    @endif
                    <button type="button" class="btn btn-primary btn-small" wire:click="generateCheckInQr">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h3v3h-3zM18 18h3v3h-3zM18 14h3"/></svg>
                        Buat/Ganti QR & Kode
                    </button>
                </div>
            </div>

            @if($generatedCheckInUrl)
                <div class="field">
                    <label for="generatedCheckInUrl">Link QR aktif yang baru dibuat</label>
                    <input id="generatedCheckInUrl" class="input" type="text" value="{{ $generatedCheckInUrl }}" readonly>
                    <div class="muted">Kode manual: <strong>{{ $generatedCheckInCode }}</strong>. Berlaku sampai {{ $generatedCheckInExpiresAt ?? '2 jam dari sekarang' }}.</div>
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
        <section class="grid grid-3">
            <div class="metric">
                <span>Loket Tugas</span>
                <strong style="font-size: 18px;">{{ $selectedCounter->name }}</strong>
                <div class="muted">{{ $selectedCounter->is_active ? 'Buka untuk antrian' : 'Sedang ditutup' }}</div>
            </div>
            <div class="metric">
                <span>Kuota Layanan</span>
                <strong style="font-size: 18px;">{{ $selectedServiceQuota['label'] ?? 'Tanpa batas' }}</strong>
                <div class="muted">{{ ($selectedServiceQuota['is_full'] ?? false) ? 'Antrian penuh' : 'Masih tersedia' }}</div>
            </div>
            <div class="metric">
                <span>Target Loket</span>
                <strong style="font-size: 18px;">{{ $selectedCounterAllocation['label'] ?? '-' }}</strong>
                <div class="muted">Rekomendasi: {{ $recommendedCounter?->code ?? '-' }}</div>
            </div>
        </section>

        <section class="panel stack">
            <div>
                <h2 class="title" style="font-size: 20px;">Arahkan Pendaftar ke Loket</h2>
                <p class="subtitle">Cari pendaftar yang sudah hadir, lalu masukkan ke loket tugas yang sedang dibuka.</p>
            </div>

            @if(! $selectedCounter->is_active)
                <div class="alert alert-danger">Loket sedang ditutup. Buka loket terlebih dahulu sebelum memasukkan pendaftar baru.</div>
            @else
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
                            <article class="applicant-card" wire:key="officer-applicant-{{ $applicant->id }}">
                                <div class="applicant-card-head">
                                    <div>
                                        <strong>{{ $applicant->full_name }}</strong>
                                        <span>{{ $applicant->user?->email ?: 'Email belum tersedia' }}</span>
                                    </div>
                                    <div>
                                        @if($presence)
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
                                    <span><b>Status</b>{{ $presence ? 'Sudah hadir di lokasi' : 'Perlu konfirmasi hadir' }}</span>
                                </div>

                                <div class="applicant-actions">
                                    @if(! $presence)
                                        <button type="button" class="btn btn-outline btn-small" wire:click="confirmApplicantPresence({{ $applicant->id }})">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                            Konfirmasi Hadir
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-primary btn-small" wire:click="assignToSelectedCounter({{ $applicant->id }})">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                                        Masukkan
                                    </button>
                                </div>
                            </article>
                        @empty
                            <div class="scroll-status">{{ trim($search) !== '' ? 'Tidak ada hasil pencarian cepat.' : 'Belum ada pendaftar yang masuk hari ini.' }}</div>
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
            @endif
        </section>

        <section class="panel stack">
            <div class="button-row" style="justify-content: space-between;">
                <div>
                    <h2 class="title" style="font-size: 20px;">Antrian Loket Ini</h2>
                    <p class="subtitle">Panggil, mulai, selesaikan, batalkan, atau tandai pendaftar tidak di tempat.</p>
                </div>
                <div class="field" style="min-width: 240px;">
                    <label for="transferTargetCounterId">Loket Tujuan Pindah</label>
                    <select id="transferTargetCounterId" class="select" wire:model="transferTargetCounterId">
                        <option value="">Pilih loket</option>
                        @foreach($transferCounters as $counter)
                            <option value="{{ $counter->id }}">{{ $counter->code }} - {{ $counter->service->name }}</option>
                        @endforeach
                    </select>
                    @error('transferTargetCounterId') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>

            @if($activeTickets->isEmpty())
                <div class="empty">Belum ada antrian aktif pada loket ini.</div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nomor</th>
                                <th>Pendaftar</th>
                                <th>Status</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeTickets as $ticket)
                                <tr>
                                    <td><strong>{{ $ticket->ticket_code }}</strong><br><span class="muted">{{ $ticket->service?->name }}</span></td>
                                    <td>{{ $ticket->applicant?->full_name }}<br><span class="muted">{{ $ticket->applicant?->nisn }}</span></td>
                                    <td><span class="badge">{{ $ticket->status_label }}</span></td>
                                    <td class="muted">{{ $ticket->notes ?: '-' }}</td>
                                    <td>
                                        <div class="button-row">
                                            @if($ticket->status === \App\Models\QueueTicket::STATUS_WAITING)
                                                <button type="button" class="btn btn-primary btn-small" wire:click="callTicket({{ $ticket->id }})">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                                                    Panggil
                                                </button>
                                            @endif
                                            @if($ticket->status === \App\Models\QueueTicket::STATUS_CALLED)
                                                <button type="button" class="btn btn-outline btn-small" wire:click="startTicket({{ $ticket->id }})">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3l14 9-14 9V3Z"/></svg>
                                                    Mulai
                                                </button>
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
                                            <button type="button" class="btn btn-outline btn-small" wire:click="transferTicket({{ $ticket->id }})">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7h11l-3-3"/><path d="M18 7l-3 3"/><path d="M17 17H6l3 3"/><path d="M6 17l3-3"/></svg>
                                                Pindah
                                            </button>
                                            <button type="button" class="btn btn-danger btn-small" wire:click="cancelTicket({{ $ticket->id }})">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                                                Batal
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="panel stack">
            <div>
                <h2 class="title" style="font-size: 20px;">Tidak di Tempat Hari Ini</h2>
                <p class="subtitle">Pendaftar yang hadir kembali dimasukkan ulang setelah dua antrian menunggu berikutnya pada loket yang sama.</p>
            </div>

            @if($noShowTickets->isEmpty())
                <div class="empty">Belum ada pendaftar yang terlewat pada loket ini.</div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nomor</th>
                                <th>Pendaftar</th>
                                <th>Terlewat</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($noShowTickets as $ticket)
                                <tr>
                                    <td><strong>{{ $ticket->ticket_code }}</strong><br><span class="muted">{{ $ticket->service?->name }}</span></td>
                                    <td>{{ $ticket->applicant?->full_name }}<br><span class="muted">{{ $ticket->applicant?->nisn }}</span></td>
                                    <td>
                                        <span class="badge">{{ $ticket->no_show_count }}x</span>
                                        <div class="muted">{{ \App\Support\AppClock::format($ticket->no_show_at, 'H:i') }}</div>
                                    </td>
                                    <td class="muted">{{ $ticket->notes ?: '-' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-small" wire:click="requeueNoShow({{ $ticket->id }})">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/></svg>
                                            Masukkan Ulang
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="panel stack">
            <div>
                <h2 class="title" style="font-size: 20px;">Antrian Menunggu di Loket Lain</h2>
                <p class="subtitle">Referensi saat petugas perlu memindahkan nomor ke loket aktif lain.</p>
            </div>

            @if($otherWaitingTickets->isEmpty())
                <div class="empty">Tidak ada antrian menunggu di loket lain.</div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nomor</th>
                                <th>Pendaftar</th>
                                <th>Loket Asal</th>
                                <th>Layanan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($otherWaitingTickets as $ticket)
                                <tr>
                                    <td><strong>{{ $ticket->ticket_code }}</strong></td>
                                    <td>{{ $ticket->applicant?->full_name }}</td>
                                    <td>{{ $ticket->counter?->code }}</td>
                                    <td>{{ $ticket->service?->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif
</div>
