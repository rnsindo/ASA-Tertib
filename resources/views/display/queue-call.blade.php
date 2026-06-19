<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Display Antrian - {{ $appName }}</title>
    <style>
        :root {
            color-scheme: light;
            --primary: {{ $primaryColor }};
            --primary-dark: color-mix(in srgb, var(--primary) 72%, #000 28%);
            --primary-deep: color-mix(in srgb, var(--primary) 50%, #000 50%);
            --bg: #edf5ff;
            --surface: #ffffff;
            --surface-soft: #f8fbff;
            --line: #cfe1f7;
            --ink: #0f172a;
            --muted: #475569;
            --warning: #f59e0b;
            --success: #16a34a;
            --danger: #dc2626;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            min-height: 100svh;
            background: var(--bg);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            letter-spacing: 0;
        }

        button {
            font: inherit;
            letter-spacing: 0;
        }

        .display-shell {
            min-height: 100vh;
            min-height: 100svh;
            display: grid;
            grid-template-rows: auto 1fr;
            background:
                linear-gradient(135deg, rgba(29, 78, 216, .18), rgba(255, 255, 255, 0) 35%),
                var(--bg);
        }

        .display-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 18px clamp(16px, 3vw, 36px);
            background: linear-gradient(135deg, var(--primary-deep), var(--primary-dark));
            color: #fff;
            box-shadow: 0 16px 36px rgba(15, 61, 122, .24);
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .brand-mark {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .16);
            display: grid;
            place-items: center;
            font-weight: 800;
            overflow: hidden;
            flex: 0 0 auto;
        }

        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 6px;
        }

        .brand-name {
            display: grid;
            gap: 2px;
            min-width: 0;
        }

        .brand-name strong {
            font-size: clamp(19px, 2.2vw, 30px);
            line-height: 1.05;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .brand-name span,
        .clock span {
            color: #dbeafe;
            font-size: clamp(12px, 1.3vw, 15px);
        }

        .clock {
            display: grid;
            gap: 2px;
            text-align: right;
            flex: 0 0 auto;
        }

        .clock strong {
            font-size: clamp(20px, 2.8vw, 38px);
            line-height: 1;
        }

        .display-main {
            width: min(1440px, 100%);
            margin: 0 auto;
            padding: clamp(14px, 2.4vw, 30px);
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(320px, .95fr);
            gap: clamp(14px, 2vw, 24px);
            align-content: start;
        }

        .hero-call,
        .panel {
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, .96);
            border-radius: 18px;
            box-shadow: 0 18px 42px rgba(15, 61, 122, .12);
        }

        .hero-call {
            min-height: clamp(360px, 52vh, 640px);
            padding: clamp(22px, 3vw, 42px);
            display: grid;
            align-content: center;
            gap: 20px;
            text-align: center;
            background:
                radial-gradient(circle at top left, rgba(245, 158, 11, .2), transparent 30%),
                linear-gradient(160deg, #ffffff, #f8fbff);
        }

        .hero-label {
            margin: 0;
            color: var(--muted);
            font-size: clamp(15px, 1.6vw, 20px);
            font-weight: 700;
            text-transform: uppercase;
        }

        .ticket-code {
            color: var(--primary-deep);
            font-size: clamp(68px, 13vw, 180px);
            font-weight: 900;
            line-height: .9;
            white-space: nowrap;
            overflow-wrap: normal;
        }

        .call-meta {
            display: grid;
            gap: 10px;
            justify-items: center;
        }

        .service-name {
            color: var(--ink);
            font-size: clamp(24px, 3.4vw, 48px);
            font-weight: 800;
            line-height: 1.08;
        }

        .counter-name {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 10px 18px;
            border-radius: 999px;
            background: #dbeafe;
            color: var(--primary-deep);
            font-size: clamp(18px, 2.2vw, 30px);
            font-weight: 800;
        }

        .sound-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            color: var(--muted);
            font-size: 14px;
        }

        .sound-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 999px;
            padding: 12px 16px;
            background: var(--primary);
            color: #fff;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(29, 78, 216, .22);
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: var(--warning);
            display: inline-block;
        }

        .status-dot.is-on {
            background: var(--success);
        }

        .side-stack {
            display: grid;
            gap: clamp(14px, 2vw, 20px);
        }

        .panel {
            padding: 18px;
            display: grid;
            gap: 14px;
        }

        .panel-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 12px;
        }

        .panel-title {
            margin: 0;
            color: var(--primary-deep);
            font-size: clamp(18px, 2vw, 24px);
            line-height: 1.1;
        }

        .panel-subtitle {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .counter-grid,
        .recent-list {
            display: grid;
            gap: 10px;
            max-height: 42vh;
            overflow: auto;
            padding-right: 2px;
        }

        .counter-card,
        .recent-card {
            border: 1px solid #d8e8fb;
            background: var(--surface-soft);
            border-radius: 14px;
            padding: 12px;
            display: grid;
            gap: 8px;
        }

        .counter-top,
        .recent-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .counter-title,
        .recent-code {
            font-weight: 800;
            color: var(--ink);
            line-height: 1.15;
        }

        .counter-service,
        .recent-meta {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.3;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 28px;
            border-radius: 999px;
            padding: 5px 9px;
            background: #e0f2fe;
            color: #075985;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .pill.is-serving {
            background: #dcfce7;
            color: #166534;
        }

        .pill.is-closed {
            background: #fee2e2;
            color: #991b1b;
        }

        .counter-bottom {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .mini-stat {
            border-radius: 12px;
            background: #fff;
            border: 1px solid #e2edf9;
            padding: 9px;
            display: grid;
            gap: 2px;
        }

        .mini-stat span {
            color: var(--muted);
            font-size: 11px;
        }

        .mini-stat strong {
            color: var(--primary-deep);
            font-size: 18px;
        }

        .empty {
            border: 1px dashed #b7d0ee;
            border-radius: 14px;
            padding: 18px;
            text-align: center;
            color: var(--muted);
            background: #f8fbff;
        }

        .connection {
            color: #dbeafe;
            font-size: 12px;
        }

        .connection.is-error {
            color: #fecaca;
        }

        @media (max-width: 900px) {
            .display-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .clock {
                width: 100%;
                text-align: left;
            }

            .display-main {
                grid-template-columns: 1fr;
            }

            .hero-call {
                min-height: 360px;
            }

            .counter-grid,
            .recent-list {
                max-height: none;
            }
        }
    </style>
</head>
<body>
    <div class="display-shell">
        <header class="display-header">
            <div class="brand">
                <div class="brand-mark">
                    @if($appLogo)
                        <img src="{{ $appLogo }}" alt="Logo {{ $appName }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">
                        <span style="display: none;">{{ mb_substr($appName, 0, 1) }}</span>
                    @else
                        <span>{{ mb_substr($appName, 0, 1) }}</span>
                    @endif
                </div>
                <div class="brand-name">
                    <strong>{{ $appName }}</strong>
                    <span>Display Panggilan Antrian</span>
                </div>
            </div>

            <div class="clock">
                <span id="dateText">-</span>
                <strong id="timeText">--:--:--</strong>
                <span id="connectionText" class="connection">Menghubungkan display...</span>
            </div>
        </header>

        <main class="display-main">
            <section class="hero-call" aria-live="polite">
                <p class="hero-label">Nomor Dipanggil</p>
                <div id="latestTicketCode" class="ticket-code">---</div>
                <div class="call-meta">
                    <div id="latestServiceName" class="service-name">Belum ada panggilan</div>
                    <div id="latestCounterName" class="counter-name">Standby</div>
                    <div id="latestCalledAt" class="panel-subtitle">Menunggu panggilan dari petugas</div>
                </div>

                <div class="sound-bar">
                    <button id="enableSoundButton" class="sound-button" type="button">
                        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M11 5 6 9H3v6h3l5 4V5Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M15.5 8.5a5 5 0 0 1 0 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M18.5 5.5a9 9 0 0 1 0 13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Aktifkan Suara
                    </button>
                    <span><i id="soundDot" class="status-dot"></i> <span id="soundStatus">Suara belum aktif</span></span>
                </div>
            </section>

            <div class="side-stack">
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2 class="panel-title">Status Loket</h2>
                            <p class="panel-subtitle">Nomor terakhir dan jumlah menunggu hari ini.</p>
                        </div>
                    </div>
                    <div id="counterGrid" class="counter-grid">
                        <div class="empty">Memuat data loket...</div>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2 class="panel-title">Panggilan Terakhir</h2>
                            <p class="panel-subtitle">Riwayat singkat event panggilan display.</p>
                        </div>
                    </div>
                    <div id="recentList" class="recent-list">
                        <div class="empty">Belum ada panggilan hari ini.</div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        (() => {
            const eventsUrl = @json($eventsUrl);
            const activationAudioUrl = @json($activationAudioUrl);
            const timezone = @json($timezone);
            const pollMs = 1500;
            let lastEventId = 0;
            let soundEnabled = false;
            let playing = false;
            let callQueue = [];
            let pollTimer = null;
            let activeAudio = null;

            const latestTicketCode = document.getElementById('latestTicketCode');
            const latestServiceName = document.getElementById('latestServiceName');
            const latestCounterName = document.getElementById('latestCounterName');
            const latestCalledAt = document.getElementById('latestCalledAt');
            const counterGrid = document.getElementById('counterGrid');
            const recentList = document.getElementById('recentList');
            const connectionText = document.getElementById('connectionText');
            const enableSoundButton = document.getElementById('enableSoundButton');
            const soundStatus = document.getElementById('soundStatus');
            const soundDot = document.getElementById('soundDot');
            const dateText = document.getElementById('dateText');
            const timeText = document.getElementById('timeText');

            function escapeHtml(value) {
                return String(value ?? '').replace(/[&<>"']/g, (char) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;',
                }[char]));
            }

            function updateClock() {
                const now = new Date();
                dateText.textContent = new Intl.DateTimeFormat('id-ID', {
                    timeZone: timezone,
                    weekday: 'long',
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric',
                }).format(now);
                timeText.textContent = new Intl.DateTimeFormat('id-ID', {
                    timeZone: timezone,
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false,
                }).format(now);
            }

            function renderLatest(call) {
                if (! call) {
                    latestTicketCode.textContent = '---';
                    latestServiceName.textContent = 'Belum ada panggilan';
                    latestCounterName.textContent = 'Standby';
                    latestCalledAt.textContent = 'Menunggu panggilan dari petugas';
                    return;
                }

                latestTicketCode.textContent = call.ticket_code || '---';
                latestServiceName.textContent = call.service_name || 'Layanan';
                latestCounterName.textContent = call.counter_name || 'Loket';
                latestCalledAt.textContent = call.called_at ? `Dipanggil ${call.called_at}` : 'Baru dipanggil';
            }

            function renderCounters(counters) {
                if (! counters.length) {
                    counterGrid.innerHTML = '<div class="empty">Belum ada loket yang terdaftar.</div>';
                    return;
                }

                counterGrid.innerHTML = counters.map((counter) => {
                    const statusClass = counter.current_ticket_code ? 'is-serving' : (counter.is_active ? '' : 'is-closed');
                    const statusText = counter.current_ticket_code
                        ? (counter.current_status_label || 'Dipanggil')
                        : (counter.is_active ? 'Siap' : 'Tutup');
                    const currentCode = counter.current_ticket_code || counter.last_ticket_code || '-';
                    const lastTime = counter.last_called_at || '-';

                    return `
                        <article class="counter-card">
                            <div class="counter-top">
                                <div>
                                    <div class="counter-title">${escapeHtml(counter.counter_name)}</div>
                                    <div class="counter-service">${escapeHtml(counter.service_name)}</div>
                                </div>
                                <span class="pill ${statusClass}">${escapeHtml(statusText)}</span>
                            </div>
                            <div class="counter-bottom">
                                <div class="mini-stat">
                                    <span>Nomor</span>
                                    <strong>${escapeHtml(currentCode)}</strong>
                                </div>
                                <div class="mini-stat">
                                    <span>Menunggu</span>
                                    <strong>${escapeHtml(counter.waiting_count)}</strong>
                                </div>
                            </div>
                            <div class="counter-service">Terakhir dipanggil: ${escapeHtml(lastTime)}</div>
                        </article>
                    `;
                }).join('');
            }

            function renderRecent(calls) {
                if (! calls.length) {
                    recentList.innerHTML = '<div class="empty">Belum ada panggilan hari ini.</div>';
                    return;
                }

                recentList.innerHTML = calls.slice().reverse().map((call) => `
                    <article class="recent-card">
                        <div class="recent-top">
                            <div class="recent-code">${escapeHtml(call.ticket_code)}</div>
                            <span class="pill">${escapeHtml(call.called_at_time || '-')}</span>
                        </div>
                        <div class="recent-meta">${escapeHtml(call.service_name)} - ${escapeHtml(call.counter_name)}</div>
                    </article>
                `).join('');
            }

            function enqueueCalls(events) {
                if (! soundEnabled || ! events.length) {
                    return;
                }

                callQueue.push(...events);
                playNextCall();
            }

            async function playAudioUrl(url) {
                await new Promise((resolve) => {
                    const audio = new Audio(url);
                    activeAudio = audio;
                    audio.preload = 'auto';
                    audio.onended = resolve;
                    audio.onerror = resolve;

                    const promise = audio.play();

                    if (promise) {
                        promise.catch(resolve);
                    }
                });
            }

            async function playNextCall() {
                if (playing || ! callQueue.length) {
                    return;
                }

                const event = callQueue.shift();
                const audioUrls = Array.isArray(event.audio_urls) ? event.audio_urls : [];
                playing = true;

                for (const url of audioUrls) {
                    await playAudioUrl(url);
                }

                activeAudio = null;
                playing = false;
                playNextCall();
            }

            async function fetchEvents(initial = false) {
                const url = new URL(eventsUrl, window.location.origin);
                url.searchParams.set('after_id', String(lastEventId));

                if (initial) {
                    url.searchParams.set('initial', '1');
                }

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (! response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const payload = await response.json();
                renderLatest(payload.latest_call);
                renderCounters(payload.counter_statuses || []);
                renderRecent(payload.recent_calls || []);
                enqueueCalls(payload.new_events || []);

                lastEventId = Math.max(lastEventId, Number.parseInt(payload.last_event_id || '0', 10));
                connectionText.textContent = `Terhubung - ${payload.server_time || ''}`;
                connectionText.classList.remove('is-error');
            }

            function startPolling() {
                if (pollTimer) {
                    window.clearInterval(pollTimer);
                }

                pollTimer = window.setInterval(() => {
                    fetchEvents().catch(() => {
                        connectionText.textContent = 'Koneksi terputus, mencoba ulang...';
                        connectionText.classList.add('is-error');
                    });
                }, pollMs);
            }

            enableSoundButton.addEventListener('click', async () => {
                soundEnabled = true;
                enableSoundButton.style.display = 'none';
                soundStatus.textContent = 'Audio lokal aktif';
                soundDot.classList.add('is-on');
                await playAudioUrl(activationAudioUrl);
            });

            updateClock();
            window.setInterval(updateClock, 1000);

            fetchEvents(true)
                .catch(() => {
                    connectionText.textContent = 'Gagal memuat data awal display.';
                    connectionText.classList.add('is-error');
                })
                .finally(startPolling);
        })();
    </script>
</body>
</html>
