# ASA-Tertib

ASA-Tertib adalah aplikasi antrian universal berbasis Laravel untuk kebutuhan layanan langsung di lokasi. Implementasi awal disiapkan untuk alur Pendaftaran Siswa Baru SMK, dengan Google SSO, login password lokal, antrian wajib hadir di tempat, QR/kode ambil antrian, dashboard pendaftar, dashboard petugas, manajemen layanan/loket, manajemen user, role-permission, dan pengaturan aplikasi.

## Kebutuhan

- PHP 8.2 atau lebih baru
- Composer
- MySQL/MariaDB
- Node.js dan npm

## Instalasi Baru

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan storage:link
php artisan serve
```

Sesuaikan konfigurasi database dan Google OAuth di `.env` sebelum menjalankan aplikasi di server.

## Akun Awal

Seeder membuat akun Super Admin:

- Email: `superadmin@asa-link.cloud`
- Password: `password123`

Ganti password default setelah login pertama atau ubah langsung di seeder sebelum deploy produksi.

## Dokumentasi

Dokumentasi lengkap tersedia di folder `docs`:

- `docs/INSTALLATION.md`
- `docs/IMPLEMENTATION.md`
- `docs/DESIGN_SYSTEM.md`
- `docs/PRESENCE_QUEUE_DESIGN.md`
- `docs/CHANGELOG.md`

## Catatan Repository

File `.env`, `vendor`, `node_modules`, build lokal, storage runtime, dan file cache tidak disimpan di repository. Gunakan `.env.example`, `composer.lock`, dan dokumentasi instalasi untuk menyiapkan environment baru.
