# Implementasi Saat Ini

## Stack

- Laravel Framework 12.62.0
- MySQL sebagai database utama
- Livewire 4 untuk halaman interaktif pengguna dan petugas
- Laravel Socialite untuk Google SSO
- Spatie Laravel Permission untuk role dan akses

## Alur autentikasi

1. Pendaftar membuka halaman login dari QR/link sekolah.
2. Pendaftar memilih `Masuk dengan Google`.
3. Callback Google dicek di aplikasi.
4. Jika `google_id` atau email sudah ada, user langsung login.
5. Jika belum ada, data Google disimpan sementara di session `google_registration`.
6. Pendaftar diarahkan ke `/register/complete`.
7. Form lanjutan mengisi Sekolah SMP, NISN, WhatsApp, dan password lokal.
8. Data disimpan ke tabel `users` dan `applicants`.
9. User otomatis login dan session sementara Google dibersihkan.

Jika callback Google menerima state OAuth yang sudah tidak cocok dengan session, sistem menangani `InvalidStateException` secara khusus. Jika user sebenarnya masih login, user diarahkan ke dashboard sesuai role dan tidak dikembalikan ke halaman login. Jika belum login, user diarahkan ke `/login` dengan pesan agar mengulang tombol `Masuk dengan Google`.

## Route utama

- `/login` - halaman login Google dan password.
- `/auth/google/redirect` - redirect ke Google OAuth.
- `/auth/google/callback` - callback dari Google OAuth.
- `/register/complete` - formulir lanjutan pendaftar baru.
- `/dashboard` - dashboard pendaftar.
- `/petugas` - konsol petugas loket.
- `/pengaturan-aplikasi` - halaman pengaturan aplikasi untuk Super Admin.
- `/manajemen-user` - halaman manajemen user untuk Super Admin.
- `/manajemen-layanan` - halaman manajemen layanan dan loket untuk Super Admin.
- `/check-in/{token}` - endpoint scan QR ambil antrian untuk konfirmasi hadir.
- `/manajemen-user/{user}/login-as` - aksi Super Admin untuk Login As user lain.
- `/impersonate/leave` - aksi kembali dari Login As ke akun asli.

## Halaman error default

Project menyediakan halaman error standalone untuk status umum `403`, `404`, `419`, `500`, dan `503`, ditambah fallback `minimal`. Halaman ini tidak memakai master layout aplikasi, sehingga tidak menampilkan header, drawer, atau bottom navigation.

Setiap halaman error menampilkan logo aplikasi di bagian atas dan tombol `Dashboard`. Jika user adalah Petugas/Super Admin, tombol mengarah ke `/petugas`; jika pelanggan/penanya, tombol mengarah ke `/dashboard`; jika belum login, tombol mengarah ke `/login`.

## Role

- `Super Admin` - akun utama instalasi, mendapat seluruh permission.
- `Petugas` - akses konsol petugas dan menu operasional petugas.
- `Pelanggan/Penanya` - akses dashboard pendaftar dan menu pengguna.

## Permission default

Permission dikelompokkan berdasarkan area akses.

Permission admin:

- `admin.pengaturan_aplikasi` - akses halaman dan menu Pengaturan Aplikasi.
- `admin.manajemen_layanan` - akses halaman dan menu Manajemen Layanan.
- `admin.manajemen_user` - akses halaman dan menu Manajemen User.
- `admin.reset_password_user` - reset password user.
- `admin.login_sebagai_user` - Login As user lain.

Permission petugas:

- `petugas.beranda` - akses navigasi Home petugas.
- `petugas.konsol_antrian` - akses konsol petugas/loket.

Permission pelanggan/penanya:

- `pelanggan.beranda` - akses navigasi Home pelanggan/penanya.
- `pelanggan.dashboard_antrian` - akses dashboard antrian.
- `pelanggan.status_antrian` - akses menu Status.
- `pelanggan.scan_qr` - akses menu Scan QR.
- `pelanggan.riwayat` - akses menu Riwayat.
- `pelanggan.profil` - akses menu Profil.

Setiap item menu di master layout dicek dengan permission berkelompok seperti `admin.*`, `petugas.*`, dan `pelanggan.*`, sehingga menu dapat diatur per role tanpa mengubah view satu per satu.

Seeder juga memberi permission kompatibilitas kepada role teknis lama jika role tersebut masih ada di database:

- `applicant` mendapat permission setara `Pelanggan/Penanya`.
- `officer` mendapat permission setara `Petugas`.
- `admin` dan `superadmin` mendapat seluruh permission.

Ini menjaga akun yang dibuat sebelum penamaan role baku tetap bisa melihat menu setelah permission menu diterapkan.

## Seeder

Seeder utama membuat:

- Super admin utama: `superadmin@asa-link.cloud`
- Petugas demo: `petugas@example.test`
- Pendaftar demo: `pendaftar@example.test`
- Role `Super Admin`, `Petugas`, dan `Pelanggan/Penanya`
- Permission halaman dan menu default.
- Permission `admin.manajemen_layanan` untuk halaman Manajemen Layanan.
- Layanan `Verifikasi Berkas` dengan loket `VB-1` dan `VB-2`
- Layanan `Wawancara` dengan loket `WW-1` dan `WW-2`
- Dependensi layanan: `Wawancara` wajib setelah `Verifikasi Berkas` selesai.
- Sesi antrian aktif untuk hari berjalan.
- Kuota default 200 per layanan pada sesi hari berjalan.
- Alokasi target loket otomatis berdasarkan kuota dan jumlah loket aktif.
- Setting awal `app.name`, `app.logo`, `app.logo_enabled`, `app.favicon`, `app.primary_color`, `app.timezone`, dan `queue.default_service_minutes`.

Demo account memakai password default `password123`.

## Model dan tabel domain

- `users` - akun login, termasuk `google_id`, `avatar_url`, `phone`, dan `is_active`.
- `applicants` - profil pendaftar PSB.
- `queue_services` - layanan antrian.
- `service_counters` - loket pada setiap layanan, termasuk petugas yang ditugaskan melalui `assigned_user_id`.
- `queue_tickets` - tiket antrian per layanan, loket, dan tanggal.
- `queue_sessions` - sesi antrian per tanggal.
- `queue_session_qr_codes` - token QR dan kode manual ambil antrian yang bisa diganti harian/jam.
- `attendance_checkins` - konfirmasi hadir pendaftar melalui QR atau petugas.
- `service_daily_quotas` - batas maksimal antrian layanan per sesi/tanggal.
- `counter_daily_allocations` - target pembagian pendaftar per loket.
- `applicant_service_records` - data khusus pendaftar per layanan.
- `queue_service_dependencies` - aturan prasyarat antar layanan.
- `app_settings` - setting aplikasi dinamis seperti nama aplikasi, logo, warna, dan variable lain yang bisa ditambah.

## Status tiket antrian

- `waiting` - Antrian
- `called` - Dipanggil
- `in_progress` - Berlangsung
- `no_show` - Tidak di Tempat
- `cancelled` - Dibatalkan
- `transferred` - Dipindahkan
- `completed` - Selesai

## Estimasi waktu antrian

Dashboard pendaftar menampilkan estimasi waktu berdasarkan posisi antrian aktif.

Aturan estimasi:

1. Jika belum ada tiket selesai pada layanan tersebut hari ini, sistem memakai setting `queue.default_service_minutes`.
2. Nilai default awal dari seeder adalah 10 menit per pendaftar.
3. Setelah ada tiket selesai, sistem menghitung rata-rata durasi pelayanan dari `started_at` sampai `completed_at` pada layanan dan tanggal yang sama.
4. Estimasi pendaftar = rata-rata menit pelayanan dikali posisi/urutan antrian.
5. Tiket `called` dan `in_progress` ditampilkan sebagai `Sekarang`, sedangkan tiket `no_show` ditampilkan sebagai `Lapor`.

## Aturan pendaftar tidak di tempat

Jika pendaftar sudah dipanggil tetapi tidak berada di tempat:

1. Petugas menekan tombol `Tidak di Tempat`.
2. Tiket berubah ke status `no_show`.
3. Tiket keluar dari daftar antrian aktif sehingga loket dapat memanggil nomor berikutnya.
4. Dashboard pendaftar menampilkan pesan agar pendaftar melapor ke petugas.
5. Jika pendaftar hadir kembali, petugas menekan `Masukkan Ulang`.
6. Sistem mengembalikan tiket yang sama ke status `waiting`.
7. Urutan panggil tiket disisipkan setelah dua antrian menunggu berikutnya pada loket yang sama, sehingga posisinya menjadi urutan ke-3.
8. Jika pendaftar dipanggil lagi dan tetap tidak di tempat, aturan yang sama bisa diulang. `no_show_count` bertambah setiap kali ditandai tidak di tempat.

Nomor tiket tidak berubah saat dimasukkan ulang. Sistem menggunakan `call_sequence` sebagai urutan panggil terpisah dari `queue_number`.

## Antrian wajib hadir di lokasi

Pendaftar wajib berada di lokasi sebelum mengambil antrian. Pada dashboard pendaftar, setiap layanan yang tersedia memiliki tombol `Ambil Antrian`.

Saat tombol ditekan, aplikasi membuka modal kamera untuk membaca QR aktif di lokasi layanan. Jika QR terbaca, aplikasi langsung memproses pengambilan antrian otomatis tanpa klik tombol. Jika kamera atau scan QR bermasalah, pendaftar dapat mengisi kode manual aktif yang ditampilkan/diberikan petugas. QR dan kode manual memiliki fungsi yang sama.

Konsol petugas menyediakan tombol `Download QR` saat QR/kode aktif tersedia. Tombol ini membuka halaman print A4 `/petugas/qr-ambil-antrian/print` tanpa header/nav aplikasi. Halaman tersebut menampilkan kode manual besar di bagian atas, QR SVG besar di tengah kertas, masa berlaku, link QR, dan tombol `Cetak / Simpan PDF`. QR print dibuat dari kode manual aktif sehingga tetap bisa dicetak ulang walaupun token QR mentah tidak disimpan di database.

Semua tampilan waktu operasional memakai setting `app.timezone`, default `Asia/Jakarta`. Header dashboard authenticated menampilkan hari, tanggal, jam, menit, dan detik berjalan sesuai timezone tersebut.

Metode konfirmasi hadir:

- Scan QR atau input kode manual dari tombol `Ambil Antrian` pada layanan. Jika valid, sistem mengonfirmasi hadir sekaligus membuat tiket layanan yang dipilih.
- Scan link QR aktif melalui route `/check-in/{token}` untuk konfirmasi hadir saja.
- Konfirmasi manual oleh petugas melalui tombol `Konfirmasi Hadir` di konsol petugas.

Petugas dapat membuat QR/kode baru dari konsol `/petugas`. Sistem menonaktifkan QR/kode lama pada sesi yang sama ketika QR baru dibuat. Token QR disimpan sebagai hash di database, sedangkan kode manual disimpan sebagai kode pendek aktif. Masa berlaku default QR/kode baru adalah 2 jam.

Link QR dan kode manual yang baru dibuat ditampilkan di konsol agar bisa ditempelkan, ditampilkan di layar, atau dicetak untuk lokasi layanan.

## Dashboard petugas

Dashboard `/petugas` dibuat berbasis tugas loket:

- Petugas biasa hanya melihat loket yang `assigned_user_id`-nya sesuai dengan akun petugas tersebut.
- Super Admin/admin tetap dapat melihat semua loket untuk kebutuhan supervisi.
- Halaman awal menampilkan data petugas, daftar loket tugas, status loket `Buka`/`Tutup`, dan ringkasan antrian `Menunggu`, `Dipanggil`, serta `Berlangsung`.
- Petugas dapat membuka atau menutup loket tugasnya dari tombol `Buka Loket`/`Tutup Loket`.
- Jika loket ditutup, petugas masih dapat melihat antrian aktif, tetapi tidak dapat memasukkan pendaftar baru sampai loket dibuka kembali.
- Jika petugas belum ditugaskan ke loket tertentu, dashboard menampilkan kondisi kosong yang meminta petugas menghubungi Super Admin agar ditugaskan melalui Manajemen Layanan.

## Kuota layanan dan alokasi loket

Sistem mengecek `service_daily_quotas` sebelum membuat tiket.

- Jika kuota layanan penuh, registrasi tetap bisa dilakukan tetapi tiket layanan tersebut tidak dibuat.
- Hitungan kuota mencakup semua tiket yang pernah dibuat pada layanan dan tanggal tersebut, termasuk selesai, batal, pindah, dan tidak di tempat.
- Kuota penuh hanya berlaku untuk layanan terkait; layanan lain tetap bisa diambil jika masih tersedia dan prasyaratnya terpenuhi.

Saat kuota tersedia, sistem membuat atau memperbarui `counter_daily_allocations` lalu memilih loket rekomendasi berdasarkan rasio beban paling rendah. Jika loket pilihan petugas sudah memenuhi target dan ada loket lain yang masih di bawah target, tiket diarahkan ke loket rekomendasi.

## Aturan layanan dinamis

Nama dan daftar layanan dibaca dari tabel `queue_services`, bukan dari hardcode aplikasi. Loket dibaca dari `service_counters`.

Jika sebuah layanan wajib mengikuti layanan lain terlebih dahulu, aturan disimpan di `queue_service_dependencies`.

Mode prasyarat yang tersedia:

- `queued` - cukup sudah pernah masuk antrian layanan prasyarat.
- `called` - minimal nomor layanan prasyarat sudah dipanggil.
- `in_progress` - minimal layanan prasyarat sedang atau sudah diproses.
- `completed` - layanan prasyarat harus selesai.

Implementasi awal seeder mengatur Wawancara bergantung pada Verifikasi Berkas dengan mode `completed`. Artinya pendaftar yang baru punya tiket Verifikasi Berkas tetapi belum selesai tidak bisa mengambil Wawancara.

Pada dashboard pendaftar, layanan yang belum memenuhi prasyarat menampilkan keterangan nama layanan yang wajib dipenuhi, misalnya `Layanan Wawancara baru bisa diambil setelah layanan Verifikasi Berkas selesai.`

Aturan ini tetap dinamis karena record dependensi dapat diubah di database tanpa mengubah kode.

Semua pengecekan tiket baru melewati `App\Services\QueueRuntimeService`, yang menggabungkan validasi hadir, kuota layanan, prasyarat layanan, dan rekomendasi loket.

## Setting aplikasi dinamis

Tabel `app_settings` disiapkan untuk menyimpan setting aplikasi yang dapat berkembang, misalnya:

- `app.name` - Nama Aplikasi.
- `app.logo` - Logo Aplikasi.
- `app.logo_enabled` - aktif/nonaktif tampilan logo pada header dan login.
- `app.favicon` - ikon tab browser.
- `app.primary_color` - Warna utama.
- `app.timezone` - Zona waktu operasional untuk QR, dashboard, log, dan print.
- `queue.default_service_minutes` - estimasi awal pelayanan per pendaftar.

Kolom `key`, `group`, `label`, `type`, `value`, `options`, `is_public`, dan `sort_order` memungkinkan penambahan variable setting baru tanpa membuat tabel baru.

Halaman `/pengaturan-aplikasi` memungkinkan Super Admin mengubah setting utama aplikasi. Tampilan halaman dibuat sebagai form mobile, bukan tabel mentah, sehingga pengguna hanya melihat kontrol yang relevan seperti input teks untuk nama aplikasi, browse/upload file untuk logo dan favicon, thumbnail aktif dengan fallback, color picker untuk warna utama, pilihan zona waktu, input angka untuk estimasi, dan switch untuk aktif/nonaktif logo. Akses halaman dan menu drawer dilindungi permission `admin.pengaturan_aplikasi`.

Favicon browser menggunakan setting `app.favicon`. Jika kosong, layout memakai logo aktif sebagai fallback. Jika favicon/logo gagal dimuat, layout mengganti ikon tab ke SVG otomatis dari huruf awal nama aplikasi dan warna utama.

Variabel setting baru ditambahkan melalui seeder dan proses development, bukan dari UI operasional.

## Manajemen user dan Login As

Halaman `/manajemen-user` dan menu drawer dilindungi permission `admin.manajemen_user`. Implementasi awal dibuat untuk Super Admin.

Fitur halaman:

- Pencarian user berdasarkan nama, email, atau no HP.
- Filter berdasarkan role Spatie.
- Edit permission user melalui modal checklist.
- Role user dapat ditambah/dihapus dari modal yang sama.
- Status akun dapat diubah dari modal yang sama: `Aktif` berarti bisa login, `Disable` berarti tidak bisa login.
- Perubahan role memakai `syncRoles()` sehingga permission bawaan role otomatis mengikuti role terbaru.
- Permission bawaan role ditampilkan sebagai checklist aktif dengan label `Bawaan role`, tetapi tidak diedit dari modal ini.
- Checklist yang bisa diubah adalah direct permission/permission tambahan langsung pada user.
- Fallback keamanan role:
  - Super Admin tidak dapat mencabut role Super Admin dari akun sendiri.
  - Sistem menolak penghapusan jika akan membuat tidak ada akun Super Admin tersisa.
  - Pemberian/pencabutan role Super Admin hanya boleh dilakukan oleh user yang memiliki role Super Admin.
  - Admin tidak dapat menonaktifkan akun sendiri.
  - Sistem menolak penonaktifan jika akan membuat tidak ada akun Super Admin aktif tersisa.
- Setelah permission disimpan, sistem menampilkan toast status berhasil atau gagal. Jika gagal, notifikasi menyertakan alasan validasi atau error penyimpanan.
- Reset password user melalui permission `admin.reset_password_user`.
- Login As user lain melalui permission `admin.login_sebagai_user`.
- Daftar user dimuat bertahap 5 data per batch. Saat pengguna scroll sampai bagian bawah daftar, sistem memuat 5 data berikutnya sampai data habis. Pola yang sama berlaku saat pencarian atau filter role aktif.

Reset password membuat password baru 5 karakter dari karakter aman tanpa huruf/angka ambigu, menyimpan hash ke tabel `users`, mengganti `remember_token`, lalu menampilkan password baru langsung pada card akun yang direset. Card tersebut menyediakan tombol copy untuk menyalin detail akun berisi Nama, Email, dan Password baru.

Login As memakai session Laravel tanpa paket tambahan. Saat Super Admin masuk sebagai user lain, session menyimpan `impersonator_id`, `impersonator_name`, dan `impersonator_email`. Drawer menampilkan status `Sedang Login As` di bawah profil user aktif, lengkap dengan akun asli dan tombol `Kembali ke Akun Asli`.

Login password dan Google SSO menolak akun dengan status `Disable`. Middleware aplikasi juga mengeluarkan session normal yang masih aktif jika akun user berubah menjadi `Disable`.

Pengecualian berlaku untuk Login As: Super Admin tetap dapat masuk sebagai akun `Disable` melalui `/manajemen-user` untuk kebutuhan pemeriksaan. Selama session memiliki `impersonator_id`, middleware tidak mengeluarkan akun target walaupun statusnya `Disable`, dan Super Admin tetap bisa kembali melalui tombol `Kembali ke Akun Asli`.

Proses kembali ke akun asli memverifikasi hasil login ke guard aktif dan memakai fallback `loginUsingId` jika perlu, agar session benar-benar kembali ke akun Super Admin.

## Manajemen data

Panel `/admin` lama sudah dihapus dari project.

## Manajemen layanan

Halaman `/manajemen-layanan` dan menu drawer dilindungi permission `admin.manajemen_layanan`. Implementasi awal dibuat untuk Super Admin.

Fitur halaman:

- Menambah layanan dinamis ke tabel `queue_services`.
- Membuat slug layanan otomatis dari nama layanan.
- Form tambah layanan dibuka melalui modal dari tombol `Tambah Layanan`, sehingga halaman utama tetap ringkas.
- Kode layanan dibuat otomatis dari inisial nama layanan. Jika kode sudah dipakai, sistem menambahkan angka urutan seperti `VB2`, `VB3`, dan seterusnya.
- Memberi kuota harian awal 200 untuk sesi berjalan saat layanan baru dibuat.
- Menampilkan card layanan berisi status, total loket, loket aktif, dan total tiket.
- Card layanan menampilkan badge `Ada Syarat` jika layanan memiliki prasyarat layanan lain.
- Saat card layanan diklik, sistem membuka popup detail loket.
- Setelah tambah layanan berhasil, modal tambah tertutup dan popup detail layanan baru langsung dibuka agar data yang baru dibuat terlihat.
- Layanan dapat diedit dari popup detail layanan.
- Edit layanan mengubah nama, deskripsi, status aktif, dan syarat layanan sebelumnya. Kode layanan dikunci dan tidak ikut berubah agar nomor tiket dan riwayat/log lama tetap konsisten.
- Modal tambah/edit layanan dapat menentukan apakah layanan wajib melewati layanan lain terlebih dahulu. Admin memilih layanan prasyarat dan status minimal yang harus terpenuhi: `sudah masuk antrian`, `nomornya sudah dipanggil`, `sedang atau sudah diproses`, atau `sudah selesai`.
- Sistem menolak dependensi yang membuat alur layanan berputar, misalnya layanan A wajib setelah B sementara B wajib setelah A.
- Popup menampilkan jumlah loket tersedia, ringkasan `Syarat Ambil Antrian`, dan daftar loket pada layanan tersebut.
- Daftar loket menampilkan nama loket, kode loket, status aktif, dan petugas yang ditugaskan. Jika belum ada petugas, sistem menampilkan `Belum ada petugas`.
- Di dalam popup tersedia tombol `Tambah Loket` yang membuka modal tambah loket.
- Tambah dan edit loket memakai modal khusus.
- Kode loket dibuat otomatis dari inisial nama loket. Angka pada nama loket diabaikan untuk inisial, sehingga `Loket Administrasi 1` menghasilkan `LA`. Jika `LA` sudah dipakai, sistem membuat `LA2`, `LA3`, dan seterusnya.
- Edit loket hanya mengubah nama, petugas, dan status aktif. Kode loket dikunci agar riwayat tiket/loket tetap konsisten.
- Petugas loket dipilih dari user yang memiliki role `Petugas`/`officer` atau permission `petugas.konsol_antrian`.
- Status layanan dan status loket dapat diaktifkan/nonaktifkan dengan switch.
- Setelah loket ditambah atau status loket berubah, sistem menjalankan ulang alokasi target loket melalui `QueueRuntimeService::ensureAllocations()`.
- Setiap proses utama menampilkan toast/splash notifikasi untuk hasil berhasil atau gagal. Jika gagal, notifikasi menyertakan alasan seperti validasi tidak lengkap, petugas tidak valid, atau error penyimpanan.

Data master layanan tidak lagi hanya bergantung pada seeder. Seeder tetap menyediakan data awal, sedangkan penyesuaian operasional bisa dilakukan dari halaman Manajemen Layanan.

## Dashboard petugas

Halaman `/petugas` menampilkan data petugas, loket yang menjadi tugasnya, status buka/tutup loket, ringkasan antrian aktif, QR/kode ambil antrian, dan daftar pendaftar yang bisa diarahkan ke loket.

Bagian `Arahkan Pendaftar ke Loket` memakai card mobile, bukan tabel, agar nyaman dibuka melalui HP. Daftar pendaftar dibatasi pada data yang masuk hari tersebut, termasuk pendaftar lama yang sudah check-in atau sudah punya tiket pada sesi berjalan. Sistem hanya memuat 5 data paling awal terlebih dahulu, lalu memuat 5 data berikutnya ketika petugas scroll sampai bagian bawah section daftar. Pencarian cepat berdasarkan nama, NISN, WhatsApp, sekolah, atau email akan mereset batch ke 5 data pertama dari hasil filter.
