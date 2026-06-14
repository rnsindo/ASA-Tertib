# Instalasi Baru

## Kebutuhan sistem

- PHP 8.2 atau lebih baru.
- Composer 2.x.
- MySQL 8.x atau MariaDB yang kompatibel.
- Web server: Apache, Nginx, atau Laravel `php artisan serve` untuk lokal.
- Ekstensi PHP yang perlu aktif:
  - `intl`
  - `iconv`
  - `pdo_mysql`
  - `mbstring`
  - `openssl`
  - `tokenizer`
  - `xml`
  - `xmlwriter`
  - `ctype`
  - `json`
  - `curl`
  - `fileinfo`
  - `zip`
- Node.js dan npm bersifat opsional untuk pengembangan asset. MVP saat ini tetap bisa tampil tanpa build Vite khusus karena layout utama memakai CSS inline.

## Langkah instalasi lokal

1. Masuk ke folder project:

```powershell
cd E:\Project\Laravel_project
```

2. Install dependency PHP:

```powershell
composer install
```

3. Salin env bila instalasi baru:

```powershell
copy .env.example .env
```

4. Generate application key:

```powershell
php artisan key:generate
```

5. Atur database MySQL di `.env`:

```env
APP_NAME="ASA-Tertib"
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_project
DB_USERNAME=root
DB_PASSWORD=
```

6. Buat database MySQL:

```sql
CREATE DATABASE laravel_project CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

7. Jalankan migrasi dan seeder:

```powershell
php artisan migrate --seed
```

Jika akan menggunakan upload logo atau favicon dari halaman pengaturan, pastikan storage public sudah terhubung:

```powershell
php artisan storage:link
```

Seeder akan membuat data dasar layanan, loket, setting aplikasi, sesi antrian hari berjalan, quota harian default aktif 200 per layanan, dan alokasi target loket awal.

Data akun awal:

- Super admin: `superadmin@asa-link.cloud`
- Petugas demo: `petugas@example.test`
- Pendaftar demo: `pendaftar@example.test`
- Password default semua akun seeder: `password123`
- Status default semua akun seeder: `Aktif`

Data master awal:

- Role default: `Super Admin`, `Petugas`, `Pelanggan/Penanya`.
- Permission admin: `admin.pengaturan_aplikasi`, `admin.manajemen_layanan`, `admin.manajemen_user`, `admin.reset_password_user`, `admin.login_sebagai_user`.
- Permission petugas: `petugas.beranda`, `petugas.konsol_antrian`, `petugas.kelola_qr_antrian`.
- Role `Petugas` hanya mendapat default `petugas.beranda` dan `petugas.konsol_antrian`. Permission `petugas.kelola_qr_antrian` tersedia di seeder tetapi harus ditambahkan manual ke akun petugas tertentu jika petugas tersebut boleh membuat atau mengganti QR/kode ambil antrian.
- Permission pelanggan/penanya: `pelanggan.beranda`, `pelanggan.dashboard_antrian`, `pelanggan.status_antrian`, `pelanggan.scan_qr`, `pelanggan.riwayat`, `pelanggan.profil`.
- Layanan: `Verifikasi Berkas` dan `Wawancara`.
- Loket: `VB-1`, `VB-2`, `WW-1`, `WW-2`.
- Penugasan demo: `petugas@example.test` ditugaskan pada loket pertama tiap layanan agar dashboard petugas langsung memiliki contoh tugas.
- Dependensi: `Wawancara` baru bisa diambil setelah `Verifikasi Berkas` selesai.
- Quota harian: aktif, 200 antrian per layanan untuk sesi hari berjalan.
- Setting aplikasi: `app.name`, `app.logo`, `app.logo_enabled`, `app.favicon`, `app.primary_color`.
- Setting zona waktu: `app.timezone = Asia/Jakarta`.
- Setting estimasi: `queue.default_service_minutes = 10`.
- Setting quota: `queue.daily_quota_enabled = true`, `queue.daily_quota_limit = 200`.
- Setting QR/kode ambil antrian: `queue.qr_expiry_limit_enabled = false`, `queue.qr_expiry_limit_hours = 2`. Saat batas durasi nonaktif, QR/kode berlaku sampai pukul 23.00 hari yang sama. Saat aktif, QR/kode berlaku sesuai jumlah jam tetapi tetap tidak melewati pukul 23.00.

Seeder dipisah berdasarkan jenis data agar update bisa lebih terarah:

- `RolePermissionSeeder`
- `AppSettingSeeder`
- `DefaultUserSeeder`
- `QueueServiceSeeder`
- `QueueServiceDependencySeeder`
- `ServiceCounterSeeder`
- `ServiceDailyQuotaSeeder`

Mode seeder default adalah `add_only`, sehingga `php artisan db:seed` saat update aplikasi hanya menambahkan data default yang belum ada dan tidak menimpa pengaturan, layanan, loket, akun, role, atau relasi permission yang sudah diubah operator. Jika benar-benar ingin menyinkronkan ulang default dari kode seeder, set environment berikut sebelum menjalankan seeder:

```env
SEED_SYNC_MODE=sync
```

Seeder juga bisa dipanggil satu per satu jika hanya ingin mengisi ulang area tertentu, contoh:

```powershell
php artisan db:seed --class=AppSettingSeeder
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=ServiceCounterSeeder
```

8. Jalankan server lokal:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

9. Buka aplikasi:

```text
http://127.0.0.1:8000/login
```

## Pengaturan aplikasi

Halaman `/pengaturan-aplikasi` tersedia untuk role `Super Admin`. Menu `Pengaturan Aplikasi` muncul di drawer jika user memiliki permission `admin.pengaturan_aplikasi`.

Halaman ini mengelola data pada tabel `app_settings`, termasuk nama aplikasi, upload/aktif-nonaktif logo, upload favicon browser, warna utama, zona waktu aplikasi, estimasi awal pelayanan, switch quota harian, dan batas durasi QR/kode manual. Tampilan halaman berupa form mobile, bukan tabel daftar setting mentah.

Favicon memakai setting `app.favicon`. Jika kosong, aplikasi memakai logo aktif sebagai fallback. Jika logo juga kosong atau file ikon gagal dimuat di browser, layout memakai ikon SVG otomatis dari huruf awal nama aplikasi dan warna utama.

Variabel setting baru ditambahkan melalui seeder dan proses development, bukan dari UI operasional.

## Manajemen layanan

Halaman `/manajemen-layanan` tersedia untuk role `Super Admin`. Menu `Manajemen Layanan` muncul di drawer jika user memiliki permission `admin.manajemen_layanan`.

Fitur utama:

- Tambah/edit layanan dan loket melalui modal mobile.
- Kode layanan dan kode loket dibuat otomatis dari inisial nama, lalu dikunci saat edit agar riwayat antrian tetap konsisten.
- Petugas loket dapat dipilih dari user Petugas.
- Status layanan/loket bisa diaktifkan atau dinonaktifkan.
- Syarat layanan sebelumnya dapat diatur pada modal tambah/edit layanan. Contoh: `Wawancara` baru bisa diambil setelah `Verifikasi Berkas` `sudah selesai`.
- Sistem menolak alur syarat layanan yang berputar.

## Manajemen user

Halaman `/manajemen-user` tersedia untuk role `Super Admin`. Menu `Manajemen User` muncul di drawer jika user memiliki permission `admin.manajemen_user`.

Fitur awal:

- Cari user berdasarkan nama, email, atau no HP.
- Filter user berdasarkan role.
- Reset password user dengan password baru otomatis yang ditampilkan ke Super Admin.
- Login As untuk masuk sebagai user lain, termasuk akun yang sedang `Disable` jika perlu dicek Super Admin.
- Ubah status akun `Aktif`/`Disable`. Akun `Disable` tidak bisa login sendiri via password atau Google SSO.
- Guard keamanan mencegah admin menonaktifkan akun sendiri dan mencegah Super Admin aktif terakhir ikut dinonaktifkan.
- Saat sedang Login As, drawer menampilkan status akun asli dan tombol `Kembali ke Akun Asli`.

## Konfigurasi Google SSO

Isi credential Google Cloud Console di `.env`:

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback
```

Untuk hosting production, ganti `APP_URL` dan `GOOGLE_REDIRECT_URI` sesuai domain.

## Catatan deployment hosting

Untuk production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.test
```

Setelah upload dan konfigurasi:

```powershell
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
php artisan view:cache
```

Jika ada perubahan route/config/view saat production, jalankan:

```powershell
php artisan optimize:clear
php artisan optimize
```

## Operasional awal QR/kode ambil antrian

Setelah login sebagai petugas, buka `/petugas`, lalu klik `Buat QR Baru`. Sistem menampilkan link QR aktif dan kode manual aktif. Keduanya berlaku sebagai bukti pendaftar berada di lokasi layanan.

Masa berlaku default QR/kode baru adalah 2 jam. Jika petugas membuat QR/kode baru, QR/kode sebelumnya pada sesi yang sama otomatis tidak aktif.

Pada dashboard pendaftar, tombol `Ambil Antrian` di setiap layanan akan membuka kamera untuk scan QR. Jika scan bermasalah, pendaftar dapat mengisi kode manual aktif.

Pendaftar yang scan QR langsung melalui route `/check-in/{token}` akan diarahkan ke proses konfirmasi hadir. Jika belum login, pendaftar login terlebih dahulu lalu kembali ke proses check-in.
