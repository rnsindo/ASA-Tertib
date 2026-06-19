<div class="student-room-page">
    <style>
        .student-room-page { display: grid; gap: 14px; }
        .student-room-hero {
            background: linear-gradient(135deg, var(--primary-deep), var(--primary));
            color: #fff;
            border: 0;
        }
        .student-room-hero .title { color: #fff; }
        .student-room-hero .subtitle { color: #dbeafe; }
        .student-room-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .student-room-upload {
            display: grid;
            gap: 10px;
            padding: 12px;
            border: 1px dashed #9fc5f8;
            border-radius: 8px;
            background: #f8fbff;
        }
        .student-room-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .student-room-stat {
            display: grid;
            gap: 4px;
            padding: 12px;
            border-radius: 8px;
            background: #eff6ff;
            color: var(--primary-deep);
        }
        .student-room-stat span { font-size: 12px; color: var(--muted); font-weight: 700; }
        .student-room-stat strong { font-size: 24px; line-height: 1; }
        .student-room-list { display: grid; gap: 10px; }
        .student-room-card {
            display: grid;
            gap: 10px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 13px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 61, 122, .06);
        }
        .student-room-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }
        .student-room-name {
            margin: 0;
            color: var(--primary-deep);
            font-size: 16px;
            line-height: 1.3;
        }
        .student-room-nisn { color: var(--muted); font-size: 12px; font-weight: 800; }
        .student-room-detail {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
        .student-room-detail span {
            display: grid;
            gap: 3px;
            padding: 9px;
            border-radius: 8px;
            background: #f8fbff;
            color: var(--ink);
            font-size: 13px;
            min-width: 0;
        }
        .student-room-detail b {
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
        }
        .student-room-toast {
            position: fixed;
            left: 14px;
            right: 14px;
            bottom: 96px;
            z-index: 80;
            display: none;
            padding: 12px 14px;
            border-radius: 8px;
            background: #0f172a;
            color: #fff;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .22);
            font-size: 13px;
            font-weight: 800;
        }
        .student-room-toast.is-visible { display: block; }
        .student-room-toast.is-error { background: #991b1b; }
        .student-room-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .student-room-pagination .btn { width: auto; }
        @media (max-width: 420px) {
            .student-room-actions,
            .student-room-summary,
            .student-room-detail { grid-template-columns: 1fr; }
        }
    </style>

    <section class="panel student-room-hero">
        <h1 class="title">Data Peserta Ruangan</h1>
        <p class="subtitle">Kelola data NISN, nama, asal SMP, tanggal lahir, dan ruangan melalui template Excel.</p>
    </section>

    <section class="panel stack">
        <div class="student-room-summary">
            <div class="student-room-stat">
                <span>Total Data</span>
                <strong>{{ number_format($totalRecords, 0, ',', '.') }}</strong>
            </div>
            <div class="student-room-stat">
                <span>Total Ruangan</span>
                <strong>{{ number_format($totalRooms, 0, ',', '.') }}</strong>
            </div>
        </div>

        <div class="student-room-actions">
            <a class="btn btn-primary" href="{{ route('student-room-data.template') }}">
                <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
                Download Template
            </a>
            <label class="btn btn-outline" for="templateFile">
                <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg>
                Pilih Excel
            </label>
        </div>

        <form class="student-room-upload" wire:submit="uploadTemplate">
            <input
                id="templateFile"
                class="input"
                type="file"
                wire:model="templateFile"
                accept=".xlsx,.xls,.csv"
            >
            <div class="muted" style="font-size: 13px;">Format kolom: NISN, Nama, SMP, Tanggal Lahir, Ruangan. Upload ulang akan memperbarui data dengan NISN yang sama.</div>
            @error('templateFile')
                <div class="error">{{ $message }}</div>
            @enderror
            <button class="btn btn-primary" type="submit" wire:loading.attr="disabled" wire:target="uploadTemplate,templateFile">
                <span wire:loading.remove wire:target="uploadTemplate">
                    <svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
                <span wire:loading wire:target="uploadTemplate" class="spinner"></span>
                Proses Upload
            </button>
        </form>

        @if($importSummary)
            <div class="alert alert-success">
                Berhasil diproses: {{ $importSummary['imported'] }} data baru, {{ $importSummary['updated'] }} data diperbarui, {{ $importSummary['skipped'] }} baris dilewati.
            </div>
            @if(! empty($importSummary['errors']))
                <div class="alert alert-danger">
                    <strong>Catatan baris dilewati:</strong>
                    <ul style="margin: 8px 0 0; padding-left: 18px;">
                        @foreach(array_slice($importSummary['errors'], 0, 8) as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    @if(count($importSummary['errors']) > 8)
                        <div style="margin-top: 6px;">Masih ada {{ count($importSummary['errors']) - 8 }} catatan lainnya.</div>
                    @endif
                </div>
            @endif
        @endif
    </section>

    <section class="panel stack">
        <div class="field">
            <label for="studentRoomSearch">Pencarian Cepat</label>
            <input
                id="studentRoomSearch"
                class="input"
                type="search"
                wire:model.live.debounce.350ms="search"
                placeholder="Cari NISN, nama, SMP, atau ruangan"
            >
        </div>

        <div class="student-room-list">
            @forelse($records as $record)
                <article class="student-room-card">
                    <div class="student-room-card-top">
                        <div>
                            <h2 class="student-room-name">{{ $record->name }}</h2>
                            <div class="student-room-nisn">NISN {{ $record->nisn }}</div>
                        </div>
                        <span class="badge">{{ $record->room }}</span>
                    </div>
                    <div class="student-room-detail">
                        <span><b>SMP</b>{{ $record->junior_school }}</span>
                        <span><b>Tanggal Lahir</b>{{ $record->birth_date?->format('d/m/Y') ?: '-' }}</span>
                    </div>
                </article>
            @empty
                <div class="empty">Belum ada data peserta ruangan.</div>
            @endforelse
        </div>

        @if($records->hasPages())
            <div class="student-room-pagination">
                <button class="btn btn-outline btn-small" type="button" wire:click="previousPage" @disabled($records->onFirstPage())>
                    <svg viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
                    Sebelumnya
                </button>
                <span class="muted" style="font-size: 12px;">Halaman {{ $records->currentPage() }} / {{ $records->lastPage() }}</span>
                <button class="btn btn-outline btn-small" type="button" wire:click="nextPage" @disabled(! $records->hasMorePages())>
                    Berikutnya
                    <svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        @endif
    </section>

    <div id="studentRoomToast" class="student-room-toast" role="status" aria-live="polite"></div>

    <script>
        (() => {
            const toast = document.getElementById('studentRoomToast');

            if (! toast || ! window.Livewire) {
                return;
            }

            Livewire.on('student-room-notify', (payload) => {
                const detail = Array.isArray(payload) ? payload[0] : payload;

                toast.textContent = detail?.message || 'Proses selesai.';
                toast.classList.toggle('is-error', detail?.type === 'error');
                toast.classList.add('is-visible');

                window.clearTimeout(toast.dataset.timer);
                toast.dataset.timer = window.setTimeout(() => {
                    toast.classList.remove('is-visible');
                }, 2600);
            });
        })();
    </script>
</div>
