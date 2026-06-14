# Desain Sistem Antrian Wajib Di Tempat

Dokumen ini menjelaskan rancangan sistem antrian ASA-Tertib yang tetap digital, tetapi hanya mengizinkan pendaftar mengambil atau masuk antrian setelah terkonfirmasi berada di lokasi kegiatan.

## Tujuan

- Mencegah pendaftar mengambil antrian dari luar lokasi.
- Memisahkan proses registrasi dari proses ambil antrian.
- Menutup antrian layanan jika batas maksimal harian sudah tercapai, tanpa menutup registrasi akun.
- Membagi beban pendaftar ke loket secara rata berdasarkan kuota baku per loket.
- Membuat alur mudah dipahami oleh pendaftar dan petugas.
- Tetap fleksibel untuk PSB SMK, layanan sekolah, kantor, klinik, event, atau instansi lain.
- Memberi petugas kontrol jika ada kondisi khusus, misalnya pendaftar didampingi orang tua atau QR tidak bisa dipindai.

## Keputusan Sistem Utama

1. Registrasi akun dan data pendaftar tetap boleh dilakukan dari mana saja.
2. Ambil antrian hanya boleh dilakukan dari lokasi layanan melalui scan QR aktif atau kode manual aktif.
3. QR/kode ambil antrian dapat diganti per hari atau per jam oleh penyedia layanan, dengan masa berlaku default 2 jam.
4. Kuota maksimal dihitung per layanan per tanggal.
5. Semua tiket yang pernah masuk ke layanan pada tanggal tersebut tetap dihitung ke kuota, termasuk `waiting`, `called`, `in_progress`, `no_show`, `completed`, `transferred`, dan `cancelled`.
6. Jika layanan sudah mencapai batas maksimal, pendaftar tetap bisa registrasi tetapi tidak bisa ambil antrian untuk layanan tersebut.
7. Pembagian loket menggunakan target kuota per loket, misalnya 200 pendaftar per hari dibagi 10 loket menjadi 20 pendaftar per loket.
8. Loket yang sudah mencapai target tetap boleh membantu loket lain jika petugas membutuhkan, tetapi target distribusi awal tetap menjadi aturan baku sistem.

## Prinsip UX

- Pendaftar tidak perlu memahami istilah teknis seperti token, session, atau validasi lokasi.
- Sistem selalu menampilkan status berikutnya secara jelas: `Belum Mengantri`, `Antrian Penuh`, `Sedang antri`, `Dipanggil`, `Berlangsung`, `Tidak di Tempat`, `Selesai`.
- Tombol yang belum boleh digunakan dibuat nonaktif dengan alasan singkat.
- Pendaftar melihat tombol `Ambil Antrian` pada setiap layanan yang tersedia.
- Petugas tetap dapat melihat dan membantu konfirmasi kehadiran jika pendaftar tidak bisa memakai kamera atau kode manual.

## Alur Pendaftar

1. Pendaftar datang ke lokasi.
2. Pendaftar scan QR registrasi atau membuka link aplikasi.
3. Pendaftar login menggunakan Google atau password.
4. Jika data awal belum lengkap, pendaftar mengisi formulir lanjutan.
5. Setelah masuk dashboard, sistem menampilkan daftar layanan aktif.
6. Pada layanan yang tersedia, pendaftar menekan `Ambil Antrian`.
7. Sistem membuka layar kamera untuk scan QR ambil antrian di lokasi.
8. Jika kamera atau scan QR bermasalah, pendaftar dapat mengisi kode manual aktif dari petugas.
9. Sistem mengecek QR/kode, kuota layanan harian, tiket aktif, dan prasyarat layanan.
10. Jika semua syarat terpenuhi, sistem mengonfirmasi kehadiran, membuat tiket, dan menentukan loket sesuai aturan pembagian rata.
11. Setelah masuk antrian, dashboard pendaftar menampilkan nomor antrian, loket, urutan, estimasi, dan status berjalan.

Jika kuota layanan sudah penuh, dashboard menampilkan status `Antrian Penuh` dan pendaftar tidak dapat membuat tiket antrian baru untuk layanan tersebut.

## Alur Petugas

1. Petugas membuka konsol layanan.
2. Petugas melihat daftar pendaftar dengan badge kehadiran:
   - `Belum Konfirmasi Lokasi`
   - `Hadir di Lokasi`
   - `Sudah Diarahkan`
   - `Tidak di Tempat`
3. Petugas hanya bisa memasukkan pendaftar ke antrian jika statusnya `Hadir di Lokasi`.
4. Jika pendaftar belum konfirmasi tetapi benar-benar ada di depan petugas, petugas dapat menekan `Konfirmasi Hadir`.
5. Setelah hadir, petugas memilih layanan tujuan.
6. Sistem menampilkan sisa kuota layanan dan rekomendasi loket.
7. Jika layanan penuh, tombol `Masukkan ke Antrian` nonaktif dan petugas melihat pesan `Antrian layanan ini sudah penuh untuk hari ini`.
8. Jika layanan masih tersedia, sistem memilih loket yang belum mencapai target pembagian.
9. Petugas tetap bisa memilih loket lain jika diperlukan, tetapi sistem memberi peringatan jika loket tersebut sudah mencapai target hariannya.
10. Jika nomor dipanggil tetapi pendaftar tidak ada, petugas menekan `Tidak di Tempat`.
11. Jika pendaftar datang kembali, petugas menekan `Masukkan Ulang`, dan sistem menempatkan kembali ke urutan ke-3 sesuai aturan yang sudah ada.

## Rancangan Tampilan Pendaftar

### Kartu status tiket aktif

Isi:
- Nomor antrian.
- Layanan.
- Loket.
- Urutan.
- Estimasi.
- Status tiket.

Jika belum punya tiket, kartu menampilkan status `Belum Ada` dan daftar layanan menjadi area utama untuk mengambil antrian.

### Daftar layanan

Setiap layanan menampilkan:

- Nama layanan.
- Status tiket jika sudah pernah mengambil layanan tersebut.
- Kuota layanan hari ini.
- Tombol `Ambil Antrian` jika layanan masih tersedia dan prasyarat terpenuhi.
- Badge `Antrian Penuh` jika kuota layanan sudah tercapai.
- Keterangan prasyarat jika layanan wajib menunggu layanan lain.

Contoh keterangan prasyarat:

- `Layanan Wawancara baru bisa diambil setelah layanan Verifikasi Berkas selesai.`
- `Layanan Pengambilan Kartu baru bisa diambil setelah pendaftar masuk antrian layanan Wawancara.`

### Modal Ambil Antrian

Saat tombol `Ambil Antrian` ditekan:

- Sistem membuka layar kamera.
- Pendaftar mengarahkan kamera ke QR aktif di lokasi layanan.
- Jika QR terbaca, sistem langsung memproses permintaan antrian otomatis tanpa menunggu klik tombol.
- Jika scan QR bermasalah, pendaftar mengisi kode manual yang ditampilkan/diberikan petugas.
- Tombol `Gunakan Kode Manual` hanya dipakai untuk memproses kode manual.

### Pesan validasi yang disarankan

- `Scan QR atau isi kode ambil antrian terlebih dahulu.`
- `QR atau kode ambil antrian tidak valid atau sudah kedaluwarsa.`
- `Antrian layanan ini sudah penuh untuk hari ini. Registrasi Anda tetap tersimpan.`
- `Layanan Wawancara baru bisa diambil setelah layanan Verifikasi Berkas selesai.`
- `Loket ini sudah mencapai target pembagian hari ini. Gunakan loket rekomendasi atau lanjutkan dengan alasan petugas.`
- `Nomor Anda sudah dipanggil, tetapi Anda tidak berada di tempat. Silakan lapor petugas untuk dimasukkan ulang.`

## Rancangan Tampilan Petugas

### Daftar pendaftar

Kolom yang disarankan:
- Nama pendaftar.
- NISN atau identitas kegiatan.
- Asal sekolah atau asal instansi.
- WhatsApp.
- Status kehadiran.
- Waktu konfirmasi.
- Kuota layanan hari ini.
- Sisa kuota layanan hari ini.
- Target dan jumlah masuk per loket.
- Aksi.

### Aksi petugas

- `Konfirmasi Hadir` untuk pendaftar yang ada di depan petugas tetapi belum bisa scan QR.
- `Masukkan ke Antrian` hanya aktif jika pendaftar sudah hadir.
- `Gunakan Loket Rekomendasi` untuk mengikuti pembagian rata sistem.
- `Pilih Loket Manual` untuk kondisi lapangan khusus.
- `Panggil`.
- `Mulai`.
- `Tidak di Tempat`.
- `Selesai`.
- `Pindah Loket`.
- `Batalkan`.
- `Masukkan Ulang`.

## Metode Konfirmasi Lokasi

### Opsi utama: QR ambil antrian

QR ambil antrian ditempel atau ditampilkan di area sekolah/instansi. QR ini memuat token lokasi/kegiatan yang hanya aktif pada sesi berjalan.

Aturan yang disarankan:
- QR dibuat per kegiatan dan per tanggal.
- QR memiliki kode manual pasangan untuk kondisi kamera/scan bermasalah.
- QR/kode dapat diganti per hari, per jam, atau sesuai kebutuhan petugas.
- Ketika QR baru dibuat untuk rentang waktu yang sama, QR lama otomatis tidak aktif.
- QR/kode memiliki masa berlaku default 2 jam.
- Pada dashboard pendaftar, QR/kode dipakai setelah memilih layanan melalui tombol `Ambil Antrian`.
- Jika valid, sistem mengonfirmasi hadir sekaligus membuat tiket layanan yang dipilih jika kuota dan prasyarat terpenuhi.

Contoh:
- QR pagi berlaku pukul 07:00 sampai 09:00.
- QR berikutnya berlaku pukul 09:00 sampai 11:00.
- Jika petugas mengganti QR pukul 09:00, QR sebelumnya langsung tidak dapat digunakan.

QR sebaiknya menyimpan token acak yang panjang, dan database hanya menyimpan hash token agar QR tidak mudah dipalsukan dari data database.

Kode manual disimpan terpisah sebagai kode pendek aktif, misalnya 6 karakter, agar petugas dapat membacakannya saat kamera pendaftar bermasalah.

### Opsi cadangan: konfirmasi oleh petugas

Petugas dapat menandai pendaftar sebagai hadir jika:
- HP pendaftar bermasalah.
- Kamera tidak bisa scan QR.
- Pendaftar datang bersama wali/orang tua.
- Ada kondisi lapangan yang membutuhkan bantuan manual.

Setiap konfirmasi manual sebaiknya mencatat petugas yang melakukan konfirmasi.

## Status Kehadiran yang Disarankan

- `not_checked_in` - Belum Konfirmasi Lokasi
- `checked_in` - Hadir di Lokasi
- `queued` - Sudah Masuk Antrian
- `serving` - Sedang Dilayani
- `completed` - Selesai
- `no_show` - Tidak di Tempat

Status kehadiran berbeda dari status tiket. Status kehadiran menjawab "orangnya ada di lokasi atau tidak", sedangkan status tiket menjawab "posisi layanan/antriannya sedang apa".

## Data Tambahan yang Disarankan

Untuk implementasi berikutnya, tabel `applicants` atau tabel baru `attendance_checkins` dapat menyimpan:

- `presence_status`
- `presence_confirmed_at`
- `presence_confirmed_by`
- `presence_method` dengan nilai `qr` atau `officer`
- `presence_location_code`
- `presence_notes`

Jika ASA-Tertib akan dipakai untuk banyak kegiatan, lebih baik membuat tabel baru `event_checkins` atau `attendance_checkins` agar riwayat hadir per kegiatan tetap terpisah.

## Data Konfigurasi yang Disarankan

### Tabel `queue_sessions`

Mewakili kegiatan antrian pada tanggal tertentu.

- `name`
- `session_date`
- `starts_at`
- `ends_at`
- `is_active`

### Tabel `queue_session_qr_codes`

Menyimpan QR dan kode manual ambil antrian yang bisa diganti harian, per jam, atau sesuai kebutuhan petugas.

- `queue_session_id`
- `token_hash`
- `manual_code`
- `label`
- `starts_at`
- `expires_at`
- `is_active`
- `created_by`
- `revoked_at`
- `revoked_by`

### Tabel `service_daily_quotas`

Menyimpan batas maksimal layanan per hari.

- `queue_session_id`
- `queue_service_id`
- `max_daily_quota`
- `is_open`

Contoh: layanan Wawancara pada 2026-06-12 memiliki `max_daily_quota = 200`.

### Tabel `counter_daily_allocations`

Menyimpan target pembagian setiap loket pada layanan dan tanggal tertentu.

- `queue_session_id`
- `queue_service_id`
- `service_counter_id`
- `target_quota`
- `manual_overflow_allowed`

Contoh: layanan Wawancara 200 pendaftar dengan 10 loket aktif menghasilkan 10 record alokasi, masing-masing `target_quota = 20`.

## Aturan Kuota Layanan

Hitung jumlah antrian layanan dengan formula:

```text
jumlah_masuk_layanan = count(queue_tickets)
where queue_service_id = layanan
and queue_date = tanggal_layanan
```

Status tiket tidak dikecualikan. Tiket yang sudah selesai, sedang menunggu, sedang berlangsung, tidak di tempat, dipindahkan, atau dibatalkan tetap dihitung sebagai pernah masuk layanan.

Layanan dianggap penuh jika:

```text
jumlah_masuk_layanan >= max_daily_quota
```

Jika layanan penuh:

- Registrasi akun tetap dibuka.
- Formulir data pendaftar tetap bisa diselesaikan.
- Tombol ambil antrian untuk layanan tersebut nonaktif.
- Dashboard pendaftar menampilkan `Antrian Penuh`.
- Konsol petugas tidak bisa membuat tiket baru untuk layanan tersebut, kecuali ada hak override khusus yang nanti dirancang terpisah.

Kuota penuh hanya berlaku untuk layanan yang penuh. Jika layanan Wawancara sudah mencapai 200 antrian, pendaftar tetap bisa mengambil antrian Verifikasi Berkas selama Verifikasi Berkas belum penuh dan pendaftar memenuhi syarat kehadiran.

## Data Pendaftar Lintas Layanan

Identitas pendaftar tidak boleh digandakan per layanan. Sistem menggunakan satu data induk pendaftar, lalu membuat data layanan secara terpisah.

Struktur yang disarankan:

- `applicants` sebagai data induk pendaftar.
- `queue_tickets` sebagai tiket antrian per layanan, per tanggal, dan per loket.
- `applicant_service_records` sebagai data khusus pendaftar pada layanan tertentu jika dibutuhkan.

Contoh data induk di `applicants`:

- Nama lengkap.
- NISN.
- Asal sekolah.
- WhatsApp.
- Akun pengguna.

Contoh data khusus layanan di `applicant_service_records`:

- `applicant_id`
- `queue_service_id`
- `queue_session_id`
- `service_status`
- `form_data` untuk jawaban/berkas khusus layanan.
- `verified_by`
- `verified_at`
- `notes`

Dengan pola ini, satu pendaftar dapat memiliki:

- Satu data induk di `applicants`.
- Satu record Verifikasi Berkas.
- Satu record Wawancara.
- Satu tiket Verifikasi Berkas pada tanggal layanan.
- Satu tiket Wawancara pada tanggal layanan, jika kuota Wawancara masih tersedia.

Jika Wawancara penuh tetapi Verifikasi Berkas masih tersedia:

1. Sistem menolak pembuatan tiket Wawancara dan menampilkan `Antrian Wawancara Penuh`.
2. Sistem tetap menampilkan Verifikasi Berkas sebagai layanan yang tersedia.
3. Jika pendaftar mengambil Verifikasi Berkas, tiket hanya dibuat untuk `queue_service_id` Verifikasi Berkas.
4. Data Wawancara tidak dibuat sebagai tiket, tetapi boleh dibuat sebagai status minat/tertunda jika nanti dibutuhkan.

Aturan unik yang disarankan:

```text
unique(applicant_id, queue_service_id, queue_session_id)
```

Aturan tersebut mencegah satu pendaftar mengambil dua tiket pada layanan yang sama dalam sesi/tanggal yang sama, tetapi tetap mengizinkan pendaftar mengambil layanan berbeda.

Contoh:

```text
Pendaftar A
- Verifikasi Berkas: boleh ambil antrian, tiket VB-115
- Wawancara: ditolak karena kuota 200 / 200

Pendaftar B
- Verifikasi Berkas: sudah selesai, tiket VB-020
- Wawancara: boleh ambil antrian jika kuota Wawancara masih tersedia
```

Pertanyaan "bagaimana jika ada data pendaftar yang berbeda pada dua layanan" dijawab dengan pemisahan data:

- Data yang sama untuk semua layanan disimpan satu kali di `applicants`.
- Data yang hanya berlaku pada layanan tertentu disimpan di `applicant_service_records`.
- Tiket antrian tetap disimpan per layanan di `queue_tickets`.

Jangan membuat dua `applicants` untuk orang yang sama hanya karena layanan berbeda. Identitas utama tetap satu agar riwayat pendaftar tidak pecah.

## Aturan Lanjutan Antar Layanan

Jika alur kegiatan harus berurutan, sistem perlu mendukung prasyarat antar layanan. Contoh: pendaftar hanya boleh mengambil antrian Wawancara jika sudah pernah masuk antrian Verifikasi Berkas pada sesi yang sama.

Aturan yang disarankan:

- Layanan pertama tidak memiliki prasyarat.
- Layanan berikutnya dapat memiliki satu atau lebih prasyarat layanan.
- Prasyarat dicek berdasarkan tiket pendaftar pada `queue_session_id` yang sama.
- Kuota tetap dicek per layanan setelah prasyarat terpenuhi.
- Jika prasyarat belum terpenuhi, tombol ambil antrian layanan berikutnya nonaktif.

Mode prasyarat yang perlu didukung:

- `queued` - pendaftar cukup sudah memiliki tiket pada layanan prasyarat.
- `called` - pendaftar minimal sudah pernah dipanggil pada layanan prasyarat.
- `in_progress` - pendaftar minimal sedang atau pernah diproses pada layanan prasyarat.
- `completed` - pendaftar harus sudah selesai pada layanan prasyarat.

Untuk permintaan "hanya pendaftar yang sudah antri pada satu layanan yang bisa lanjut ke layanan berikutnya", gunakan mode `queued`.

Untuk kasus "pendaftar sudah punya tiket layanan pertama tetapi belum selesai maka belum bisa mengambil layanan berikutnya", gunakan mode `completed`.

Setiap layanan tujuan dapat menentukan mode prasyaratnya sendiri. Jadi aturan ini tidak kaku:

- Wawancara bisa mensyaratkan Verifikasi Berkas `completed`.
- Pengukuran Seragam bisa mensyaratkan Wawancara `queued`.
- Pengambilan Kartu bisa tidak punya prasyarat sama sekali.

Nama layanan tidak boleh ditulis hardcode di kode aplikasi. Daftar layanan berasal dari tabel `queue_services`, sedangkan aturan hubungan antar layanan berasal dari tabel `queue_service_dependencies`.

Contoh:

```text
Layanan:
1. Verifikasi Berkas
2. Wawancara

Aturan:
Wawancara requires Verifikasi Berkas with mode queued
```

Hasilnya:

```text
Pendaftar A
- Belum punya tiket Verifikasi Berkas
- Tidak bisa ambil Wawancara

Pendaftar B
- Sudah punya tiket Verifikasi Berkas dengan status waiting
- Bisa ambil Wawancara jika kuota Wawancara belum penuh

Pendaftar C
- Sudah selesai Verifikasi Berkas
- Bisa ambil Wawancara jika kuota Wawancara belum penuh
```

Jika ingin alur yang lebih ketat, misalnya Wawancara hanya boleh setelah Verifikasi Berkas selesai, ubah mode prasyarat menjadi `completed`.

Contoh mode `completed`:

```text
Pendaftar D
- Sudah punya tiket Verifikasi Berkas dengan status waiting
- Belum bisa ambil Wawancara

Pendaftar E
- Tiket Verifikasi Berkas sudah completed
- Bisa ambil Wawancara jika kuota Wawancara belum penuh
```

Struktur data yang disarankan:

### Tabel `queue_service_dependencies`

- `queue_session_id`
- `queue_service_id` sebagai layanan tujuan.
- `required_queue_service_id` sebagai layanan prasyarat.
- `required_status_mode` dengan nilai `queued`, `called`, `in_progress`, atau `completed`.
- `is_active`

Formula pengecekan:

```text
boleh_ambil_layanan_tujuan =
qr_atau_kode_aktif_valid
and kuota_layanan_tujuan_belum_penuh
and semua_prasyarat_layanan_terpenuhi
and pendaftar_belum_punya_tiket_layanan_tujuan_di_sesi_ini
```

Pesan UI yang disarankan:

- `Selesaikan atau ambil antrian Verifikasi Berkas terlebih dahulu sebelum mengambil Wawancara.`
- `Anda sudah masuk antrian Verifikasi Berkas. Layanan Wawancara sudah tersedia jika kuota masih ada.`
- `Wawancara belum tersedia karena prasyarat layanan belum terpenuhi.`

Dengan desain ini, layanan yang penuh tetap hanya menutup layanan tersebut. Namun layanan berikutnya juga bisa ditutup secara logis jika prasyarat layanan sebelumnya belum terpenuhi.

## Setting Aplikasi Dinamis

ASA-Tertib perlu memiliki tabel setting agar identitas aplikasi dan variable konfigurasi lain bisa bertambah tanpa membuat struktur baru setiap kali.

Tabel yang disarankan: `app_settings`.

Kolom:

- `key` sebagai kode unik setting, misalnya `app.name`.
- `group` untuk pengelompokan, misalnya `identity`, `theme`, atau `queue`.
- `label` untuk nama yang ditampilkan ke admin.
- `type` untuk tipe input, misalnya `string`, `text`, `image`, `boolean`, `integer`, atau `json`.
- `value` untuk nilai setting.
- `options` untuk konfigurasi tambahan.
- `is_public` untuk menandai setting yang boleh dipakai di frontend.
- `sort_order` untuk urutan tampilan.

Contoh setting awal:

```text
app.name = ASA-Tertib
app.logo = path/logo.png
app.primary_color = #1d4ed8
```

Jika nanti dibutuhkan setting baru seperti `queue.max_requeue_count`, `notification.whatsapp_enabled`, atau `theme.bottom_nav_color`, data cukup ditambahkan ke `app_settings`.

## Aturan Pembagian Rata Loket

Target loket dihitung dari kuota layanan dan jumlah loket aktif:

```text
target_per_loket = floor(max_daily_quota / jumlah_loket_aktif)
sisa = max_daily_quota % jumlah_loket_aktif
```

Jika ada sisa, sisa dibagikan ke loket paling awal berdasarkan `sort_order`.

Contoh:

```text
max_daily_quota = 200
jumlah_loket_aktif = 10
target_per_loket = 20
sisa = 0
setiap loket mendapat target 20 pendaftar
```

Contoh lain:

```text
max_daily_quota = 205
jumlah_loket_aktif = 10
target dasar = 20
sisa = 5
5 loket pertama mendapat 21 pendaftar
5 loket berikutnya mendapat 20 pendaftar
```

## Algoritma Rekomendasi Loket

Saat membuat tiket baru untuk layanan:

1. Ambil semua loket aktif pada layanan tersebut.
2. Hitung jumlah tiket yang sudah masuk ke setiap loket pada tanggal layanan.
3. Prioritaskan loket yang jumlah masuknya masih di bawah `target_quota`.
4. Pilih loket dengan persentase beban paling rendah:

```text
rasio_beban = jumlah_masuk_loket / target_quota
```

5. Jika rasio sama, pilih loket dengan `sort_order` paling kecil.
6. Jika semua loket sudah mencapai target tetapi kuota layanan masih tersisa karena ada override atau konfigurasi khusus, sistem boleh memilih loket dengan jumlah aktif paling sedikit.

Setelah loket mencapai target, loket tersebut tidak menjadi rekomendasi utama lagi. Namun petugas tetap bisa membantu loket lain melalui pilihan manual jika operasional di lapangan membutuhkan.

## Status Kuota di UI

### Untuk pendaftar

Tampilkan informasi sederhana:

- `Tersedia` jika layanan masih bisa menerima antrian.
- `Sisa 24 antrian hari ini` jika ingin lebih informatif.
- `Antrian Penuh` jika kuota sudah tercapai.

Jangan tampilkan detail pembagian loket ke pendaftar kecuali sudah mendapat tiket.

### Untuk petugas

Tampilkan informasi operasional:

- Kuota layanan: `145 / 200`
- Sisa kuota: `55`
- Rekomendasi loket: `W-04`
- Beban loket: `W-04: 13 / 20`
- Peringatan: `Loket W-01 sudah mencapai target 20 / 20`

## Aturan Bisnis

1. Pendaftar boleh registrasi dari mana saja.
2. Pendaftar hanya boleh masuk antrian setelah memilih layanan dan memvalidasi QR/kode aktif dari lokasi.
3. Petugas tidak bisa memasukkan pendaftar yang belum hadir, kecuali menekan `Konfirmasi Hadir` terlebih dahulu.
4. QR/kode yang valid pada tombol `Ambil Antrian` langsung mengonfirmasi hadir dan membuat tiket layanan jika syarat layanan terpenuhi.
5. Pengarahan loket ditentukan otomatis oleh rekomendasi sistem berdasarkan pembagian rata, sementara petugas tetap bisa membantu dari konsol.
6. Sistem wajib mengecek kuota layanan sebelum membuat tiket.
7. Sistem wajib menentukan rekomendasi loket berdasarkan target pembagian rata.
8. Jika pendaftar dipanggil tetapi tidak ada, gunakan aturan `Tidak di Tempat` yang sudah berjalan.
9. Jika pendaftar hadir kembali, gunakan `Masukkan Ulang` dan tempatkan setelah dua antrian berikutnya.
10. Tiket yang dimasukkan ulang tidak menambah kuota layanan karena masih menggunakan tiket yang sama.

## Rekomendasi Implementasi Bertahap

### Tahap 1

- Tambahkan status kehadiran pendaftar.
- Tambahkan tombol `Ambil Antrian` pada setiap layanan di dashboard pendaftar.
- Tambahkan modal kamera QR dan input kode manual.
- Tambahkan tombol `Konfirmasi Hadir` di konsol petugas.
- Blokir aksi `Masukkan ke Antrian` jika belum hadir.
- Tambahkan konfigurasi batas maksimal harian per layanan.
- Blokir pembuatan tiket jika kuota layanan sudah penuh.

### Tahap 2

- Buat QR/kode ambil antrian dinamis per kegiatan/hari dengan masa berlaku default 2 jam.
- Tambahkan log konfirmasi kehadiran.
- Tambahkan filter petugas: `Belum Hadir`, `Hadir`, `Sudah Antri`.
- Tambahkan generator QR harian/jam dan fitur menonaktifkan QR lama.
- Tambahkan tampilan kuota layanan untuk pendaftar dan petugas.

### Tahap 3

- Tambahkan dashboard monitoring jumlah hadir, belum hadir, sedang antri, selesai, dan tidak di tempat.
- Tambahkan pengaturan kegiatan agar ASA-Tertib bisa dipakai untuk event atau instansi lain.
- Tambahkan alokasi target per loket dan rekomendasi loket otomatis.
- Tambahkan audit override jika petugas memilih loket yang sudah mencapai target.
