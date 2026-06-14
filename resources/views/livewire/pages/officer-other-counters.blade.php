<div class="stack other-counters-page">
    <style>
        .other-counters-page { gap: 14px; }
        .other-counter-hero {
            display: grid;
            gap: 8px;
            border-color: #ddd6fe;
            background: linear-gradient(180deg, #fff 0%, #f5f3ff 100%);
        }
        .other-counter-hero .title { color: var(--primary-deep); }
        .counter-summary-list { display: grid; gap: 10px; }
        .counter-summary-card {
            display: grid;
            gap: 10px;
            border: 1px solid #ddd6fe;
            border-radius: 8px;
            padding: 12px;
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
        }
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
        @media (max-width: 720px) {
            .summary-head { grid-template-columns: 1fr; }
        }
    </style>

    <section class="panel other-counter-hero">
        <h1 class="title">Daftar Loket Lain</h1>
        <p class="subtitle">
            {{ $canManageAllCounters ? 'Ringkasan semua loket hari ini.' : 'Ringkasan loket selain loket tugas Anda hari ini.' }}
        </p>
    </section>

    <section class="counter-summary-list" aria-label="Daftar loket lain">
        @forelse($counters as $counter)
            <article class="counter-summary-card" wire:key="other-counter-page-{{ $counter->id }}">
                <div class="summary-head">
                    <div>
                        <strong>{{ $counter->service?->name ?: 'Layanan' }} - {{ $counter->name }}</strong>
                        <span>{{ $counter->assignedOfficer?->name ?: 'Belum ada petugas' }}</span>
                    </div>
                    <span class="counter-state {{ $counter->is_active ? 'is-open' : '' }}">{{ $counter->is_active ? 'Buka' : 'Tutup' }}</span>
                </div>

                <div class="status-grid">
                    <span class="status-pill is-waiting"><b>Menunggu</b><strong>{{ $counter->waiting_count }}</strong></span>
                    <span class="status-pill is-done"><b>Selesai</b><strong>{{ $counter->completed_count }}</strong></span>
                    <span class="status-pill is-noshow"><b>Tidak di Tempat</b><strong>{{ $counter->no_show_count }}</strong></span>
                </div>
            </article>
        @empty
            <div class="panel empty">Belum ada loket lain yang tersedia.</div>
        @endforelse
    </section>
</div>
