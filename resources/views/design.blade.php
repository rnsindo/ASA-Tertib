<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mobile Design - Antrian Universal</title>
    <style>
        :root {
            color-scheme: light;
            --blue-950: #082f5f;
            --blue-900: #0f3d7a;
            --blue-800: #174ea6;
            --blue-700: #1d4ed8;
            --blue-600: #2563eb;
            --blue-100: #dbeafe;
            --blue-50: #eff6ff;
            --chrome: #174ea6;
            --chrome-strong: #0f3d7a;
            --surface: #ffffff;
            --page: #edf5ff;
            --line: #c7d9f2;
            --muted: #64748b;
            --ink: #0f172a;
            --shadow: 0 24px 70px rgba(8, 47, 95, .22);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            min-height: 100svh;
            background: var(--page);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            letter-spacing: 0;
        }

        body.drawer-is-open {
            overflow: hidden;
        }

        .mobile-app {
            position: relative;
            width: 100%;
            min-height: 100vh;
            min-height: 100svh;
            margin: 0;
            overflow-x: hidden;
            background: var(--page);
        }

        .app-screen {
            position: relative;
            width: 100%;
            min-height: 100vh;
            min-height: 100svh;
            background: var(--page);
        }

        .dashboard {
            display: grid;
            grid-template-rows: auto 1fr;
            min-height: 100vh;
            min-height: 100svh;
            background: var(--page);
        }

        .app-header {
            margin: 0;
            padding: 18px 18px 16px;
            background: linear-gradient(135deg, var(--chrome) 0%, #2563eb 100%);
            border: 0;
            border-bottom: 1px solid rgba(255, 255, 255, .18);
            border-radius: 0 0 22px 22px;
            box-shadow: 0 16px 30px rgba(15, 61, 122, .22);
        }

        .header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .icon-button {
            width: 42px;
            height: 42px;
            border: 1px solid rgba(255, 255, 255, .28);
            border-radius: 999px;
            background: rgba(255, 255, 255, .16);
            color: #ffffff;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            color: #ffffff;
            font-weight: 800;
            font-size: 14px;
        }

        .logo-mark {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .18);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 13px;
            box-shadow: 0 10px 24px rgba(37, 99, 235, .24);
        }

        .greeting {
            margin: 14px 0 0;
            color: #ffffff;
        }

        .greeting span {
            display: block;
            color: #dbeafe;
            font-size: 12px;
            margin-bottom: 3px;
        }

        .greeting strong {
            font-size: 20px;
            line-height: 1.2;
        }

        .content {
            padding: 16px 14px 101px;
            display: grid;
            gap: 14px;
        }

        .card {
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
            background: linear-gradient(135deg, var(--blue-700), #2f80ed);
            color: #fff;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
        }

        .ticket-head span,
        .ticket-detail span,
        .section-title span,
        .log-item span {
            display: block;
            font-size: 12px;
        }

        .ticket-number {
            font-size: 42px;
            line-height: .95;
            font-weight: 900;
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
            color: var(--blue-950);
            font-size: 18px;
        }

        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 12px;
            margin: 2px 14px 0;
        }

        .section-title strong {
            color: var(--blue-950);
            font-size: 15px;
        }

        .section-title span {
            color: var(--muted);
        }

        .quick-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            padding: 14px;
        }

        .quick-action {
            min-width: 0;
            display: grid;
            justify-items: center;
            gap: 7px;
            color: var(--blue-950);
            font-size: 11px;
            font-weight: 700;
            text-align: center;
        }

        .quick-icon {
            width: 52px;
            height: 52px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: var(--blue-50);
            color: var(--blue-700);
            border: 1px solid #d5e5fb;
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
            color: var(--blue-700);
        }

        .log-item strong {
            display: block;
            color: var(--blue-950);
            font-size: 13px;
            margin-bottom: 3px;
        }

        .log-item span,
        .log-time {
            color: var(--muted);
            font-size: 11px;
        }

        .bottom-nav {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 10;
            width: 100%;
            min-height: 78px;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            align-items: center;
            padding: 10px 8px calc(10px + env(safe-area-inset-bottom));
            background: linear-gradient(180deg, #174ea6 0%, var(--chrome-strong) 100%);
            border: 0;
            border-top: 1px solid rgba(255, 255, 255, .16);
            border-radius: 22px 22px 0 0;
            box-shadow: 0 -16px 30px rgba(15, 61, 122, .24);
        }

        .nav-item {
            color: #c7dcff;
            display: grid;
            justify-items: center;
            gap: 4px;
            font-size: 10px;
            font-weight: 700;
        }

        .nav-item.active {
            color: #ffffff;
            transform: translateY(-10px);
        }

        .nav-item.active .nav-icon {
            width: 54px;
            height: 54px;
            color: var(--blue-800);
            background: #ffffff;
            box-shadow: 0 14px 26px rgba(29, 78, 216, .35);
        }

        .nav-icon {
            width: 30px;
            height: 30px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, .13);
        }

        .screen-dim {
            position: fixed;
            inset: 0;
            background: rgba(8, 31, 67, .42);
            z-index: 20;
            border: 0;
            padding: 0;
            display: none;
        }

        body.drawer-is-open .screen-dim {
            display: block;
            animation: dim-in .24s ease-out both;
        }

        .drawer {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 30;
            width: min(314px, 86vw);
            background: #fff;
            border-right: 1px solid #d8e5f7;
            box-shadow: 20px 0 40px rgba(8, 31, 67, .22);
            padding: 20px 20px 24px;
            display: grid;
            align-content: start;
            gap: 16px;
            min-height: 100vh;
            min-height: 100svh;
            transform: translateX(-102%);
            transition: transform .28s cubic-bezier(.2, .8, .2, 1);
        }

        body.drawer-is-open .drawer {
            transform: translateX(0);
        }

        .drawer-top {
            display: flex;
            justify-content: flex-end;
            min-height: 34px;
        }

        .drawer-close {
            width: 34px;
            height: 34px;
            border: 1px solid #d7e5f7;
            border-radius: 999px;
            background: #fff;
            color: var(--blue-900);
            display: grid;
            place-items: center;
        }

        .profile {
            text-align: center;
            display: grid;
            justify-items: center;
            gap: 8px;
        }

        .avatar {
            width: 94px;
            height: 94px;
            border-radius: 999px;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 14px 30px rgba(15, 61, 122, .22);
            background: var(--blue-50);
        }

        .profile-name {
            margin: 8px 0 0;
            color: var(--blue-950);
            font-size: 17px;
            font-weight: 900;
        }

        .profile-email {
            color: var(--muted);
            font-size: 12px;
            word-break: break-word;
        }

        .logout-wrap {
            display: grid;
            justify-items: center;
        }

        .logout-button {
            border: 0;
            border-radius: 8px;
            background: var(--blue-700);
            color: #fff;
            padding: 12px 22px;
            min-height: 44px;
            font-weight: 800;
            box-shadow: 0 12px 24px rgba(29, 78, 216, .26);
        }

        .drawer-menu {
            display: grid;
            gap: 8px;
            padding-top: 2px;
        }

        .drawer-item {
            display: grid;
            grid-template-columns: 34px 1fr;
            gap: 10px;
            align-items: center;
            min-height: 48px;
            padding: 8px 10px;
            border-radius: 8px;
            color: var(--blue-950);
            font-weight: 800;
            font-size: 14px;
        }

        .drawer-item:first-child {
            background: var(--blue-50);
            color: var(--blue-700);
        }

        .drawer-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: #eef6ff;
            color: var(--blue-700);
            display: grid;
            place-items: center;
        }

        svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
        }

        @keyframes dim-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 420px) {
            .mobile-app {
                width: 100vw;
            }

            .app-header {
                padding: 14px 14px 14px;
            }

            .content {
                padding-inline: 14px;
            }

            .drawer {
                width: min(304px, 88vw);
            }
        }
    </style>
</head>
<body>
    <div class="mobile-app" aria-label="Preview desain mobile aplikasi antrian">
        <div class="app-screen">
            <section class="dashboard" aria-label="Dashboard utama yang sedang diredupkan">
                <header class="app-header">
                    <div class="header-row">
                        <div class="header-left">
                            <button class="icon-button" id="openDrawer" type="button" aria-label="Buka menu" aria-controls="sideDrawer" aria-expanded="false">
                                <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                            </button>
                            <div class="logo" aria-label="App logo">
                                <div class="logo-mark">AU</div>
                                <span>Antrian Universal</span>
                            </div>
                        </div>
                        <button class="icon-button" aria-label="Notifikasi">
                            <svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                        </button>
                    </div>
                    <div class="greeting">
                        <span>Welcome, [User Name]</span>
                        <strong>Status Antrian Hari Ini</strong>
                    </div>
                </header>

                <main class="content">
                    <article class="card ticket-card" aria-label="Status tiket antrian">
                        <div class="ticket-head">
                            <div>
                                <span>Nomor Antrian</span>
                                <div class="ticket-number">A-023</div>
                            </div>
                            <div class="ticket-pill">Berlangsung</div>
                        </div>
                        <div class="ticket-cut"></div>
                        <div class="ticket-body">
                            <div class="ticket-detail">
                                <span>Urutan</span>
                                <strong>03</strong>
                            </div>
                            <div class="ticket-detail">
                                <span>Loket</span>
                                <strong>VB-2</strong>
                            </div>
                            <div class="ticket-detail">
                                <span>Estimasi</span>
                                <strong>12m</strong>
                            </div>
                        </div>
                    </article>

                    <div class="section-title">
                        <strong>Aksi Cepat</strong>
                        <span>Pilih kebutuhan</span>
                    </div>
                    <section class="card quick-grid" aria-label="Quick actions">
                        <div class="quick-action">
                            <div class="quick-icon"><svg viewBox="0 0 24 24"><path d="M3 11h18M5 7h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z"/><path d="M8 15h.01M12 15h.01"/></svg></div>
                            Tiket
                        </div>
                        <div class="quick-action">
                            <div class="quick-icon"><svg viewBox="0 0 24 24"><path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h3v3h-3zM18 18h3v3h-3zM18 14h3"/></svg></div>
                            Scan QR
                        </div>
                        <div class="quick-action">
                            <div class="quick-icon"><svg viewBox="0 0 24 24"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg></div>
                            Bantuan
                        </div>
                        <div class="quick-action">
                            <div class="quick-icon"><svg viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg></div>
                            Layanan
                        </div>
                    </section>

                    <div class="section-title">
                        <strong>Log Antrian</strong>
                        <span>Terbaru</span>
                    </div>
                    <section class="card log-list" aria-label="Info list atau queue log">
                        <div class="log-item">
                            <div class="log-dot"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg></div>
                            <div>
                                <strong>Registrasi berhasil</strong>
                                <span>Data awal sudah diterima sistem</span>
                            </div>
                            <div class="log-time">08:10</div>
                        </div>
                        <div class="log-item">
                            <div class="log-dot"><svg viewBox="0 0 24 24"><path d="M12 8v5l3 3"/><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div>
                            <div>
                                <strong>Menunggu verifikasi</strong>
                                <span>Loket VB-2 sedang memanggil A-020</span>
                            </div>
                            <div class="log-time">08:28</div>
                        </div>
                        <div class="log-item">
                            <div class="log-dot"><svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"/></svg></div>
                            <div>
                                <strong>Siapkan berkas</strong>
                                <span>Kartu keluarga dan ijazah SMP</span>
                            </div>
                            <div class="log-time">08:32</div>
                        </div>
                    </section>
                </main>

                <nav class="bottom-nav" aria-label="Navigasi bawah">
                    <div class="nav-item">
                        <div class="nav-icon"><svg viewBox="0 0 24 24"><path d="M12 8v5l3 3"/><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div>
                        Status
                    </div>
                    <div class="nav-item">
                        <div class="nav-icon"><svg viewBox="0 0 24 24"><path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h3v3h-3zM18 18h3v3h-3zM18 14h3"/></svg></div>
                        Scan QR
                    </div>
                    <div class="nav-item active">
                        <div class="nav-icon"><svg viewBox="0 0 24 24"><path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg></div>
                        Home
                    </div>
                    <div class="nav-item">
                        <div class="nav-icon"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h6"/></svg></div>
                        Riwayat
                    </div>
                    <div class="nav-item">
                        <div class="nav-icon"><svg viewBox="0 0 24 24"><path d="M18 20a6 6 0 0 0-12 0"/><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg></div>
                        Profil
                    </div>
                </nav>
            </section>

            <button class="screen-dim" id="drawerOverlay" type="button" aria-label="Tutup menu"></button>

            <aside class="drawer" id="sideDrawer" aria-label="Side navigation drawer" aria-hidden="true">
                <div class="drawer-top">
                    <button class="drawer-close" id="closeDrawer" type="button" aria-label="Tutup menu">
                        <svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="profile">
                    <img class="avatar" src="{{ asset('images/design-avatar.png') }}" alt="Avatar ilustrasi pengguna">
                    <div>
                        <div class="profile-name">[User Name]</div>
                        <div class="profile-email">[user.email@example.com]</div>
                    </div>
                </div>

                <div class="logout-wrap">
                    <button class="logout-button" type="button">Keluar / Logout</button>
                </div>

                <nav class="drawer-menu" aria-label="Menu drawer">
                    <div class="drawer-item">
                        <div class="drawer-icon"><svg viewBox="0 0 24 24"><path d="M12 6v6l4 2"/><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div>
                        <span>Pelayanan 24/7</span>
                    </div>
                    <div class="drawer-item">
                        <div class="drawer-icon"><svg viewBox="0 0 24 24"><path d="M12 17h.01"/><path d="M12 7v6"/><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div>
                        <span>Informasi Pendaftaran</span>
                    </div>
                    <div class="drawer-item">
                        <div class="drawer-icon"><svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"/></svg></div>
                        <span>Panduan Lengkap</span>
                    </div>
                </nav>
            </aside>
        </div>
    </div>

    <script>
        const body = document.body;
        const openDrawer = document.getElementById('openDrawer');
        const closeDrawer = document.getElementById('closeDrawer');
        const drawerOverlay = document.getElementById('drawerOverlay');
        const sideDrawer = document.getElementById('sideDrawer');

        function setDrawerState(isOpen) {
            body.classList.toggle('drawer-is-open', isOpen);
            openDrawer.setAttribute('aria-expanded', String(isOpen));
            sideDrawer.setAttribute('aria-hidden', String(! isOpen));
        }

        openDrawer.addEventListener('click', () => setDrawerState(true));
        closeDrawer.addEventListener('click', () => setDrawerState(false));
        drawerOverlay.addEventListener('click', () => setDrawerState(false));
        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setDrawerState(false);
            }
        });
    </script>
</body>
</html>
