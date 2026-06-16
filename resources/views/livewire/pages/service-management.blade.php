<div class="stack service-management-page">
    <style>
        .service-management-page { gap: 14px; }

        .service-form,
        .counter-modal-card,
        .counter-form {
            display: grid;
            gap: 12px;
        }

        .service-list {
            display: grid;
            gap: 12px;
        }

        .service-form-grid {
            display: grid;
            grid-template-columns: 1fr 120px;
            gap: 10px;
        }

        .service-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .service-toolbar-copy {
            min-width: 0;
        }

        .service-card {
            display: grid;
            gap: 12px;
            text-align: left;
            width: 100%;
            border: 1px solid var(--line);
            background: var(--surface);
            border-radius: 8px;
            padding: 14px;
            box-shadow: 0 10px 28px rgba(15, 61, 122, .08);
            color: var(--ink);
        }

        .service-card.is-dragging {
            opacity: .72;
            border-color: var(--primary);
            box-shadow: 0 18px 42px rgba(15, 61, 122, .2);
        }

        .service-card.is-drop-target {
            outline: 2px dashed #60a5fa;
            outline-offset: 3px;
        }

        .service-card-head {
            display: grid;
            grid-template-columns: 40px 44px 1fr auto;
            gap: 10px;
            align-items: center;
        }

        .service-drag-handle {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            background: #f8fbff;
            color: var(--primary-deep);
            cursor: grab;
            touch-action: none;
        }

        .service-drag-handle:active {
            cursor: grabbing;
        }

        .service-drag-handle svg,
        .service-open-button svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .service-icon {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: var(--primary-soft);
            color: var(--primary);
        }

        .service-title strong {
            display: block;
            color: var(--primary-deep);
            font-size: 16px;
            line-height: 1.3;
        }

        .service-title span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
        }

        .service-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .service-badges {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 6px;
        }

        .service-stat {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fbff;
            padding: 10px;
        }

        .service-stat span {
            display: block;
            color: var(--muted);
            font-size: 11px;
        }

        .service-stat strong {
            display: block;
            margin-top: 4px;
            color: var(--primary-deep);
            font-size: 18px;
        }

        .service-card-actions {
            display: grid;
            grid-template-columns: 1fr;
        }

        .service-open-button {
            min-height: 40px;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            background: #f8fbff;
            color: var(--primary-deep);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
        }

        .switch-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fbff;
        }

        .switch-line strong {
            color: var(--primary-deep);
            font-size: 13px;
        }

        .switch {
            position: relative;
            width: 52px;
            height: 30px;
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
        }

        .switch-track::after {
            content: "";
            position: absolute;
            width: 24px;
            height: 24px;
            left: 3px;
            top: 3px;
            border-radius: 999px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, .22);
            transition: transform .18s ease;
        }

        .switch input:checked + .switch-track {
            background: var(--primary);
        }

        .switch input:checked + .switch-track::after {
            transform: translateX(22px);
        }

        .service-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: grid;
            align-items: end;
            background: rgba(8, 31, 67, .44);
            padding: 18px 12px;
        }

        .counter-modal-card,
        .service-modal-card {
            width: min(680px, 100%);
            max-height: min(82vh, 720px);
            margin: 0 auto;
            overflow: auto;
            background: #fff;
            border: 1px solid #d8e5f7;
            border-radius: 18px 18px 8px 8px;
            padding: 16px;
            box-shadow: 0 24px 60px rgba(8, 31, 67, .28);
        }

        .modal-head {
            display: grid;
            grid-template-columns: 1fr 40px;
            gap: 10px;
            align-items: start;
        }

        .modal-head strong {
            display: block;
            color: var(--primary-deep);
            font-size: 18px;
        }

        .modal-head span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
        }

        .icon-only {
            width: 40px;
            height: 40px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #fff;
            color: var(--primary-deep);
            display: grid;
            place-items: center;
            cursor: pointer;
        }

        .counter-list {
            display: grid;
            gap: 8px;
        }

        .modal-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .counter-item {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 10px;
            align-items: center;
            padding: 10px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fbff;
        }

        .counter-item strong {
            display: block;
            color: var(--primary-deep);
            font-size: 14px;
        }

        .counter-item span {
            display: block;
            margin-top: 2px;
            color: var(--muted);
            font-size: 12px;
        }

        .counter-officer {
            margin-top: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 4px 8px;
            background: #eef6ff;
            color: var(--primary-deep);
            font-size: 11px;
            font-weight: 800;
        }

        .dependency-box {
            display: grid;
            gap: 8px;
            padding: 10px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fbff;
        }

        .dependency-box strong {
            display: block;
            color: var(--primary-deep);
            font-size: 13px;
        }

        .dependency-box span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .service-toast {
            position: fixed;
            left: 14px;
            right: 14px;
            bottom: 98px;
            z-index: 80;
            display: none;
            border-radius: 8px;
            padding: 12px 14px;
            color: #fff;
            font-weight: 800;
            box-shadow: 0 16px 34px rgba(15, 23, 42, .24);
        }

        .service-toast.is-visible { display: block; }
        .service-toast.success { background: var(--success); }
        .service-toast.error { background: var(--danger); }

        @media (max-width: 640px) {
            .service-form-grid,
            .service-stats {
                grid-template-columns: 1fr;
            }

            .service-toolbar {
                display: grid;
            }

            .counter-item {
                grid-template-columns: 1fr auto;
            }

            .counter-item .icon-only {
                grid-column: 2;
            }

            .service-card-head {
                grid-template-columns: 40px 44px 1fr;
            }

            .service-badges {
                grid-column: 1 / -1;
                justify-content: flex-start;
            }
        }
    </style>

    <div id="serviceToast" class="service-toast" role="status" aria-live="polite"></div>

    <section class="panel service-toolbar">
        <div class="service-toolbar-copy">
            <h1 class="title">Manajemen Layanan</h1>
            <p class="subtitle">Kelola layanan antrian dan loket yang tersedia.</p>
        </div>

        <button class="btn btn-primary" type="button" wire:click="openServiceModal">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Layanan
        </button>
    </section>

    <div class="service-list" data-service-sort-list wire:ignore.self>
        @forelse($services as $service)
            @php
                $activeDependency = $service->dependencies->first(
                    fn ($dependency) => is_null($dependency->queue_session_id) && $dependency->is_active
                );
            @endphp
            <article class="service-card" data-service-id="{{ $service->id }}" wire:key="service-{{ $service->id }}">
                <div class="service-card-head">
                    <button class="service-drag-handle" type="button" aria-label="Geser urutan {{ $service->name }}" data-service-drag-handle>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5h.01M15 5h.01M9 12h.01M15 12h.01M9 19h.01M15 19h.01"/></svg>
                    </button>
                    <span class="service-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h10"/></svg>
                    </span>
                    <span class="service-title">
                        <strong>{{ $service->name }}</strong>
                        <span>Urutan {{ $service->sort_order }} - {{ $service->code }}{{ $service->description ? ' - ' . $service->description : '' }}</span>
                    </span>
                    <span class="service-badges">
                        <span class="badge">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        @if($activeDependency)
                            <span class="badge">Ada Syarat</span>
                        @endif
                    </span>
                </div>

                <div class="service-stats">
                    <span class="service-stat">
                        <span>Total Loket</span>
                        <strong>{{ $service->counters_count }}</strong>
                    </span>
                    <span class="service-stat">
                        <span>Loket Aktif</span>
                        <strong>{{ $service->active_counters_count }}</strong>
                    </span>
                    <span class="service-stat">
                        <span>Total Tiket</span>
                        <strong>{{ $service->tickets_count }}</strong>
                    </span>
                </div>
                <div class="service-card-actions">
                    <button class="service-open-button" type="button" wire:click="openCounters({{ $service->id }})">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                        Detail Layanan & Loket
                    </button>
                </div>
            </article>
        @empty
            <div class="empty">Belum ada layanan. Gunakan tombol Tambah Layanan untuk membuat layanan pertama.</div>
        @endforelse
    </div>

    @if($isServiceModalOpen)
        <div class="service-modal-backdrop" style="{{ $editingServiceId ? 'z-index: 70;' : '' }}" wire:key="service-create-modal">
            <section class="service-modal-card">
                <div class="modal-head">
                    <div>
                        <strong>{{ $editingServiceId ? 'Edit Layanan' : 'Tambah Layanan' }}</strong>
                        <span>
                            @if($editingServiceId)
                                Kode layanan {{ $editingServiceCode }} tidak diubah agar riwayat tiket tetap konsisten.
                            @else
                                Kode layanan dibuat otomatis dari inisial nama layanan.
                            @endif
                        </span>
                    </div>
                    <button class="icon-only" type="button" wire:click="closeServiceModal" aria-label="Tutup modal tambah layanan">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="service-form">
                    <div class="field">
                        <label for="serviceName">Nama Layanan</label>
                        <input id="serviceName" class="input" type="text" wire:model="serviceName" placeholder="Contoh: Verifikasi Berkas">
                        @error('serviceName') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    @if($editingServiceId)
                        <div class="field">
                            <label for="serviceCodeLocked">Kode Layanan</label>
                            <input id="serviceCodeLocked" class="input" type="text" value="{{ $editingServiceCode }}" readonly>
                            <span class="muted" style="font-size: 12px;">Kode dikunci karena dipakai pada nomor tiket dan riwayat antrian.</span>
                        </div>

                        <div class="field">
                            <label for="serviceSortOrder">Urutan Tampil Pendaftar</label>
                            <input id="serviceSortOrder" class="input" type="number" min="0" max="65535" wire:model="serviceSortOrder" inputmode="numeric" placeholder="Contoh: 1">
                            <span class="muted" style="font-size: 12px;">Nilai kecil tampil lebih awal saat pendaftar belum memiliki nomor antrian.</span>
                            @error('serviceSortOrder') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="field">
                        <label for="serviceDescription">Deskripsi</label>
                        <textarea id="serviceDescription" class="textarea" wire:model="serviceDescription" placeholder="Keterangan singkat layanan"></textarea>
                        @error('serviceDescription') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="switch-line">
                        <strong>Layanan Aktif</strong>
                        <label class="switch" aria-label="Layanan Aktif">
                            <input type="checkbox" wire:model="serviceIsActive">
                            <span class="switch-track"></span>
                        </label>
                    </div>

                    <div class="switch-line">
                        <span>
                            <strong>Wajib Panggil Berurutan</strong>
                            <span class="muted" style="font-size: 12px;">Jika nonaktif, petugas bisa memanggil atau memulai nomor menunggu secara acak.</span>
                        </span>
                        <label class="switch" aria-label="Wajib panggil berurutan">
                            <input type="checkbox" wire:model="serviceEnforceCallOrder">
                            <span class="switch-track"></span>
                        </label>
                    </div>

                    <div class="switch-line">
                        <span>
                            <strong>Wajib Melewati Layanan Lain</strong>
                            <span class="muted" style="font-size: 12px;">Aktifkan jika antrian layanan ini hanya boleh diambil setelah layanan prasyarat terpenuhi.</span>
                        </span>
                        <label class="switch" aria-label="Wajib melewati layanan lain">
                            <input type="checkbox" wire:model.live="serviceRequiresPrevious">
                            <span class="switch-track"></span>
                        </label>
                    </div>

                    @if($serviceRequiresPrevious)
                        <div class="dependency-box">
                            <div class="field">
                                <label for="requiredServiceId">Layanan Prasyarat</label>
                                <select id="requiredServiceId" class="select" wire:model="requiredServiceId" data-autocomplete-select data-autocomplete-placeholder="Cari layanan prasyarat">
                                    <option value="">Pilih layanan prasyarat</option>
                                    @foreach($serviceOptions as $option)
                                        <option value="{{ $option->id }}" @disabled($editingServiceId && $editingServiceId === $option->id)>
                                            {{ $option->name }} - {{ $option->code }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('requiredServiceId') <span class="error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field">
                                <label for="requiredStatusMode">Syarat Status Layanan Prasyarat</label>
                                <select id="requiredStatusMode" class="select" wire:model="requiredStatusMode" data-autocomplete-select data-autocomplete-placeholder="Cari status prasyarat">
                                    @foreach($dependencyModes as $mode => $label)
                                        <option value="{{ $mode }}">{{ ucfirst($label) }}</option>
                                    @endforeach
                                </select>
                                @error('requiredStatusMode') <span class="error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif

                    <button
                        class="btn btn-primary"
                        type="button"
                        wire:click="{{ $editingServiceId ? 'updateService' : 'createService' }}"
                        wire:loading.attr="disabled"
                        wire:target="{{ $editingServiceId ? 'updateService' : 'createService' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                        <span wire:loading.remove wire:target="{{ $editingServiceId ? 'updateService' : 'createService' }}">
                            {{ $editingServiceId ? 'Simpan Perubahan' : 'Simpan Layanan' }}
                        </span>
                        <span wire:loading wire:target="{{ $editingServiceId ? 'updateService' : 'createService' }}">Menyimpan...</span>
                    </button>
                </div>
            </section>
        </div>
    @endif

    @if($selectedService)
        @php
            $selectedDependency = $selectedService->dependencies->first(
                fn ($dependency) => is_null($dependency->queue_session_id) && $dependency->is_active
            );
        @endphp
        <div class="service-modal-backdrop" wire:key="counter-modal-{{ $selectedService->id }}">
            <section class="counter-modal-card">
                <div class="modal-head">
                    <div>
                        <strong>{{ $selectedService->name }}</strong>
                        <span>{{ $selectedService->active_counters_count }} loket aktif dari {{ $selectedService->counters_count }} loket tersedia.</span>
                    </div>
                    <button class="icon-only" type="button" wire:click="closeCounters" aria-label="Tutup popup loket">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="switch-line">
                    <strong>Status layanan</strong>
                    <label class="switch" aria-label="Status layanan {{ $selectedService->name }}">
                        <input type="checkbox" @checked($selectedService->is_active) wire:click="toggleService({{ $selectedService->id }})">
                        <span class="switch-track"></span>
                    </label>
                </div>

                <div class="dependency-box">
                    <strong>Syarat Ambil Antrian</strong>
                    @if($selectedDependency)
                        <span>
                            Pendaftar harus melewati layanan {{ $selectedDependency->requiredService?->name ?: 'prasyarat' }}
                            sampai status {{ $dependencyModes[$selectedDependency->required_status_mode] ?? 'terpenuhi' }}
                            sebelum mengambil antrian {{ $selectedService->name }}.
                        </span>
                    @else
                        <span>Layanan ini belum memiliki syarat layanan sebelumnya.</span>
                    @endif
                </div>

                <div class="modal-actions">
                    <button class="btn btn-outline" type="button" wire:click="openEditServiceModal({{ $selectedService->id }})">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                        Edit Layanan
                    </button>
                    <button class="btn btn-primary" type="button" wire:click="closeCounters">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        Selesai
                    </button>
                </div>

                <div class="counter-list">
                    @forelse($selectedService->counters as $counter)
                        <div class="counter-item" wire:key="counter-{{ $counter->id }}">
                            <div>
                                <strong>{{ $counter->name }}</strong>
                                <span>{{ $counter->code }} - {{ $counter->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                <span class="counter-officer">
                                    <svg viewBox="0 0 24 24" aria-hidden="true" style="width: 14px; height: 14px;"><path d="M18 20a6 6 0 0 0-12 0"/><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
                                    {{ $counter->assignedOfficer?->name ?: 'Belum ada petugas' }}
                                </span>
                            </div>
                            <button class="icon-only" type="button" wire:click="openEditCounterModal({{ $counter->id }})" aria-label="Edit loket {{ $counter->name }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </button>
                            <label class="switch" aria-label="Status loket {{ $counter->name }}">
                                <input type="checkbox" @checked($counter->is_active) wire:click="toggleCounter({{ $counter->id }})">
                                <span class="switch-track"></span>
                            </label>
                        </div>
                    @empty
                        <div class="empty">Belum ada loket untuk layanan ini.</div>
                    @endforelse
                </div>

                <button class="btn btn-primary" type="button" wire:click="openCounterModal">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Tambah Loket
                </button>
            </section>
        </div>
    @endif

    @if($isCounterModalOpen)
        <div class="service-modal-backdrop" style="z-index: 75;" wire:key="counter-form-modal">
            <section class="service-modal-card">
                <div class="modal-head">
                    <div>
                        <strong>{{ $editingCounterId ? 'Edit Loket' : 'Tambah Loket' }}</strong>
                        <span>
                            @if($editingCounterId)
                                Kode loket {{ $editingCounterCode }} tidak diubah agar riwayat antrian tetap konsisten.
                            @else
                                Kode loket dibuat otomatis dari inisial nama loket.
                            @endif
                        </span>
                    </div>
                    <button class="icon-only" type="button" wire:click="closeCounterModal" aria-label="Tutup modal loket">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="counter-form">
                    <div class="field">
                        <label for="counterName">Nama Loket</label>
                        <input id="counterName" class="input" type="text" wire:model="counterName" placeholder="Contoh: Loket Verifikasi 3">
                        @error('counterName') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    @if($editingCounterId)
                        <div class="field">
                            <label for="counterCodeLocked">Kode Loket</label>
                            <input id="counterCodeLocked" class="input" type="text" value="{{ $editingCounterCode }}" readonly>
                            <span class="muted" style="font-size: 12px;">Kode dikunci karena dipakai pada riwayat tiket loket.</span>
                        </div>
                    @endif

                    <div class="field">
                        <label for="counterOfficerId">Petugas Loket</label>
                        <select id="counterOfficerId" class="select" wire:model="counterOfficerId" data-autocomplete-select data-autocomplete-placeholder="Cari petugas loket">
                            <option value="">Belum ditentukan</option>
                            @foreach($officerUsers as $officer)
                                <option value="{{ $officer->id }}">{{ $officer->name }} - {{ $officer->email }}</option>
                            @endforeach
                        </select>
                        @error('counterOfficerId') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="switch-line">
                        <strong>Loket Aktif</strong>
                        <label class="switch" aria-label="Loket Aktif">
                            <input type="checkbox" wire:model="counterIsActive">
                            <span class="switch-track"></span>
                        </label>
                    </div>

                    <button
                        class="btn btn-primary"
                        type="button"
                        wire:click="{{ $editingCounterId ? 'updateCounter' : 'addCounter' }}"
                        wire:loading.attr="disabled"
                        wire:target="{{ $editingCounterId ? 'updateCounter' : 'addCounter' }}"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                        <span wire:loading.remove wire:target="{{ $editingCounterId ? 'updateCounter' : 'addCounter' }}">
                            {{ $editingCounterId ? 'Simpan Perubahan' : 'Simpan Loket' }}
                        </span>
                        <span wire:loading wire:target="{{ $editingCounterId ? 'updateCounter' : 'addCounter' }}">Menyimpan...</span>
                    </button>
                </div>
            </section>
        </div>
    @endif

    <script>
        (() => {
            if (window.__asaServiceSortInitialized) {
                return;
            }

            window.__asaServiceSortInitialized = true;

            const initializeServiceSort = () => {
                document.querySelectorAll('[data-service-sort-list]').forEach((list) => {
                    if (list.dataset.sortReady === '1') {
                        return;
                    }

                    list.dataset.sortReady = '1';
                    let draggedItem = null;
                    let activePointerId = null;
                    let startY = 0;
                    let hasMoved = false;

                    const orderedIds = () => Array.from(list.querySelectorAll('[data-service-id]'))
                        .map((item) => Number.parseInt(item.dataset.serviceId || '0', 10))
                        .filter((id) => Number.isFinite(id) && id > 0);

                    const clearDropTargets = () => {
                        list.querySelectorAll('.is-drop-target').forEach((item) => item.classList.remove('is-drop-target'));
                    };

                    const commitOrder = () => {
                        const componentRoot = list.closest('[wire\\:id]');
                        const componentId = componentRoot?.getAttribute('wire:id');
                        const ids = orderedIds();

                        if (componentId && ids.length && window.Livewire?.find) {
                            window.Livewire.find(componentId).call('reorderServices', ids);
                        }
                    };

                    const itemAfterPointer = (clientY) => {
                        const items = Array.from(list.querySelectorAll('[data-service-id]:not(.is-dragging)'));

                        return items.reduce((closest, item) => {
                            const box = item.getBoundingClientRect();
                            const offset = clientY - box.top - (box.height / 2);

                            if (offset < 0 && offset > closest.offset) {
                                return { offset, item };
                            }

                            return closest;
                        }, { offset: Number.NEGATIVE_INFINITY, item: null }).item;
                    };

                    list.addEventListener('pointerdown', (event) => {
                        const handle = event.target.closest('[data-service-drag-handle]');

                        if (! handle || ! list.contains(handle)) {
                            return;
                        }

                        draggedItem = handle.closest('[data-service-id]');
                        activePointerId = event.pointerId;
                        startY = event.clientY;
                        hasMoved = false;

                        handle.setPointerCapture(event.pointerId);
                        draggedItem?.classList.add('is-dragging');
                    });

                    list.addEventListener('pointermove', (event) => {
                        if (! draggedItem || event.pointerId !== activePointerId) {
                            return;
                        }

                        event.preventDefault();

                        if (Math.abs(event.clientY - startY) > 4) {
                            hasMoved = true;
                        }

                        const afterItem = itemAfterPointer(event.clientY);
                        clearDropTargets();

                        if (afterItem) {
                            afterItem.classList.add('is-drop-target');
                            list.insertBefore(draggedItem, afterItem);
                        } else {
                            list.appendChild(draggedItem);
                        }
                    }, { passive: false });

                    const finishDrag = (event) => {
                        if (! draggedItem || event.pointerId !== activePointerId) {
                            return;
                        }

                        draggedItem.classList.remove('is-dragging');
                        clearDropTargets();

                        if (hasMoved) {
                            commitOrder();
                        }

                        draggedItem = null;
                        activePointerId = null;
                        hasMoved = false;
                    };

                    list.addEventListener('pointerup', finishDrag);
                    list.addEventListener('pointercancel', finishDrag);
                });
            };

            document.addEventListener('DOMContentLoaded', initializeServiceSort);
            document.addEventListener('livewire:navigated', initializeServiceSort);

            if (window.Livewire?.hook) {
                window.Livewire.hook('morph.updated', initializeServiceSort);
            }

            window.setTimeout(initializeServiceSort, 0);
        })();

        document.addEventListener('livewire:init', () => {
            Livewire.on('service-notify', (payload) => {
                const event = Array.isArray(payload) ? payload[0] : payload;
                const toast = document.getElementById('serviceToast');

                if (! toast || ! event) {
                    return;
                }

                toast.textContent = event.message || 'Proses selesai.';
                toast.className = 'service-toast is-visible ' + (event.type === 'error' ? 'error' : 'success');

                window.clearTimeout(window.__serviceToastTimer);
                window.__serviceToastTimer = window.setTimeout(() => {
                    toast.className = 'service-toast';
                }, 2400);
            });
        });
    </script>
</div>
