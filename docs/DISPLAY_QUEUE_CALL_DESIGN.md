# Rancangan Display Panggilan Antrian

Dokumen ini merancang halaman standby untuk menampilkan status antrian per loket dan membunyikan panggilan ketika petugas menekan tombol `Panggil` pada dashboard `/petugas`.

## Tujuan

- Menyediakan halaman display khusus yang bisa dibuka di TV, monitor, tablet, atau browser HP.
- Saat petugas memanggil nomor dari loket mana pun, display berubah otomatis.
- Suara panggilan dibacakan satu per satu walaupun beberapa loket memanggil bersamaan.
- Panggilan ulang tetap terbaca sebagai panggilan baru.
- Tetap cocok untuk shared hosting/cPanel tanpa proses WebSocket wajib.

## Route yang disiapkan

- `/display-antrian` - halaman standby display.
- `/display-antrian/events` - endpoint polling event panggilan terbaru.

Implementasi tahap awal memakai route login dengan permission `petugas.display_antrian`.

Jika nanti display perlu dibuka tanpa login, route dapat memakai token khusus:

- `/display-antrian/{token}`

Token display sebaiknya disimpan di `app_settings`, misalnya `queue.display_token`.

## Model data

Tambahkan tabel baru `queue_call_events`.

Kolom yang disarankan:

- `id`
- `queue_session_id`
- `queue_ticket_id`
- `queue_service_id`
- `service_counter_id`
- `called_by`
- `ticket_code`
- `service_name`
- `counter_name`
- `applicant_name`
- `announcement_text`
- `called_at`
- `created_at`
- `updated_at`

Alasan membuat tabel event terpisah:

- Status tiket hanya menyimpan kondisi terakhir.
- Panggilan ulang pada tiket yang sama harus tetap menjadi event baru.
- Jika beberapa petugas memanggil bersamaan, semua event tetap tersimpan dan tidak hilang.
- Display bisa mengambil event baru berdasarkan `id` terakhir.

## Alur saat petugas klik Panggil

1. Petugas menekan tombol `Panggil` pada card antrian di `/petugas`.
2. Sistem tetap menjalankan validasi yang sudah ada:
   - tiket masih `waiting`;
   - jika layanan wajib urut, tiket adalah urutan menunggu paling awal;
   - jika layanan boleh acak, tiket menunggu mana pun pada loket tersebut dapat dipanggil.
3. Sistem mengubah tiket:
   - `status = called`;
   - `called_at = now()`;
   - `called_by = user id petugas`.
4. Sistem membuat record baru di `queue_call_events`.
5. Halaman `/display-antrian` mengambil event tersebut melalui polling.
6. Display memperbarui nomor utama dan memasukkan teks panggilan ke antrian audio browser.

## Format teks panggilan

Format default:

```text
Nomor antrian {ticket_code}, menuju {counter_name}, layanan {service_name}.
```

Contoh:

```text
Nomor antrian WW-003, menuju Loket Wawancara 1, layanan Wawancara.
```

Teks ini disimpan pada `queue_call_events.announcement_text` agar riwayat panggilan tetap sesuai dengan nama layanan/loket saat panggilan terjadi.

## Penanganan pemanggilan bersamaan

Jika lebih dari satu loket memanggil pada waktu hampir bersamaan:

1. Setiap klik `Panggil` membuat satu event baru di `queue_call_events`.
2. Display mengambil semua event baru dengan filter `id > last_event_id`.
3. Semua event baru dimasukkan ke queue JavaScript.
4. Tampilan utama boleh langsung menampilkan event terbaru.
5. Audio tetap dibacakan satu per satu.

Aturan utama:

- Event tidak boleh hilang.
- Suara tidak boleh tumpang tindih.
- Panggilan ulang harus tetap masuk sebagai suara baru.

Pseudo flow audio:

```js
let callQueue = [];
let speaking = false;

function enqueueCalls(events) {
    callQueue.push(...events);
    playNextCall();
}

function playNextCall() {
    if (speaking || callQueue.length === 0) {
        return;
    }

    speaking = true;

    const event = callQueue.shift();
    const utterance = new SpeechSynthesisUtterance(event.announcement_text);
    utterance.lang = 'id-ID';

    utterance.onend = () => {
        speaking = false;
        playNextCall();
    };

    utterance.onerror = () => {
        speaking = false;
        playNextCall();
    };

    speechSynthesis.speak(utterance);
}
```

## Catatan audio browser

Browser modern biasanya menolak audio otomatis sebelum ada interaksi pengguna. Karena itu halaman display harus memiliki tombol awal:

```text
Aktifkan Suara
```

Setelah tombol ditekan:

- `speechSynthesis` diaktifkan;
- display mulai polling event;
- event berikutnya boleh dibacakan otomatis.

Jika tombol belum ditekan:

- display tetap bisa menampilkan status loket;
- suara belum diputar;
- tampilkan indikator `Suara belum aktif`.

## Pola realtime

Untuk tahap awal gunakan polling ringan:

- interval polling: 1 sampai 2 detik;
- endpoint mengambil event baru berdasarkan `last_event_id`;
- endpoint juga dapat mengembalikan ringkasan status loket.

Alasan memilih polling:

- Aman untuk shared hosting/cPanel.
- Tidak membutuhkan Laravel Reverb/WebSocket.
- Tidak membutuhkan queue worker yang harus hidup terus.
- Lebih mudah diuji dan dirawat.

Di masa depan, polling bisa diganti Laravel Reverb jika server mendukung proses background permanen.

## Desain tampilan display

Halaman display tidak memakai header, drawer, atau bottom navigation aplikasi. Halaman dibuat full-screen.

Bagian utama:

- Nama aplikasi/logo di bagian atas.
- Jam dan tanggal berjalan.
- Panel nomor terbaru yang dipanggil:
  - nomor antrian besar;
  - nama layanan;
  - nama loket;
  - waktu panggil.
- Daftar status loket:
  - nama layanan;
  - nama loket;
  - nomor terakhir dipanggil;
  - jumlah menunggu.
- Daftar panggilan terakhir:
  - 5 sampai 10 event terakhir.

Warna mengikuti aturan ASA-Tertib:

- biru gelap untuk header/panel utama;
- putih dan abu muda untuk area status;
- orange untuk panggilan baru;
- hijau untuk loket sedang melayani;
- abu untuk loket kosong.

## Permission dan akses

Opsi aman:

- halaman display dilindungi permission `petugas.display_antrian`;
- permission diberikan ke `Super Admin`;
- permission tidak otomatis diberikan ke semua `Petugas`.

Opsi operasional untuk TV/monitor publik:

- halaman memakai token display;
- token bisa diganti dari pengaturan aplikasi;
- halaman tidak menampilkan data sensitif seperti email, NISN, atau WhatsApp.

Data yang boleh tampil di display publik:

- nomor antrian;
- layanan;
- loket;
- nama pendaftar opsional, sebaiknya tidak ditampilkan penuh untuk privasi.

## Integrasi dengan kode saat ini

Titik perubahan utama:

- `app/Livewire/Pages/OfficerQueueConsole.php`
  - method `callTicket()` perlu membuat `QueueCallEvent`.
- `app/Models/QueueTicket.php`
  - tetap menjadi sumber status tiket.
- `app/Models/ServiceCounter.php`
  - dipakai untuk status loket pada display.
- `routes/web.php`
  - tambah route display dan endpoint event.
- `resources/views/livewire/pages`
  - tambah view display antrian.
- `database/migrations`
  - tambah migration `queue_call_events`.
- `database/seeders/RolePermissionSeeder.php`
  - tambah permission `petugas.display_antrian`.

## Fallback dan kondisi khusus

- Jika display kehilangan koneksi, tampilkan status `Menghubungkan ulang`.
- Jika event polling gagal, jangan hapus queue audio yang belum dibacakan.
- Jika `speechSynthesis` tidak tersedia, tampilkan pesan bahwa browser tidak mendukung suara otomatis.
- Jika banyak panggilan menumpuk, batasi queue audio maksimal, misalnya 20 event, tetapi tetap tampilkan panggilan terakhir di layar.
- Jika halaman display baru dibuka, jangan membacakan semua event lama. Mulai dari event terbaru setelah tombol `Aktifkan Suara`.

## WBS Implementasi

### 1. Analisis dan persiapan

Deliverable:

- Keputusan final akses display: login permission atau token.
- Format teks panggilan final.
- Batas data yang boleh tampil pada monitor publik.

Task:

- Review method `callTicket()`.
- Review route dan layout standalone yang sudah ada, terutama halaman error/print QR.
- Tentukan apakah display memakai Livewire penuh atau Blade + endpoint JSON.

Estimasi: 0.5 hari.

### 2. Database dan model event

Deliverable:

- Migration `queue_call_events`.
- Model `QueueCallEvent`.
- Relasi ke tiket, layanan, loket, sesi, dan petugas.

Task:

- Buat migration.
- Tambahkan index:
  - `queue_session_id`;
  - `queue_ticket_id`;
  - `service_counter_id`;
  - `called_at`;
  - `id`.
- Tambahkan cast tanggal pada model.

Estimasi: 0.5 hari.

### 3. Integrasi tombol Panggil

Deliverable:

- Setiap klik `Panggil` membuat event baru.
- Panggilan ulang tetap tersimpan sebagai event baru.

Task:

- Tambahkan pembuatan `QueueCallEvent` di `OfficerQueueConsole::callTicket()`.
- Simpan snapshot `ticket_code`, `service_name`, `counter_name`, dan `announcement_text`.
- Pastikan perubahan status tiket dan pembuatan event berada dalam transaksi.
- Tambahkan handling error agar petugas mendapat notifikasi gagal jika event tidak tersimpan.

Estimasi: 0.5 sampai 1 hari.

### 4. Endpoint display

Deliverable:

- Endpoint event baru untuk polling.
- Endpoint mengembalikan daftar status loket hari berjalan.

Task:

- Buat controller atau Livewire method untuk mengambil event dengan `after_id`.
- Batasi event hanya sesi hari berjalan.
- Tambahkan payload:
  - `latest_call`;
  - `new_events`;
  - `counter_statuses`;
  - `last_event_id`;
  - `server_time`.
- Pastikan endpoint tidak mengirim data pribadi yang tidak diperlukan.

Estimasi: 1 hari.

### 5. Halaman standby display

Deliverable:

- Halaman `/display-antrian` full-screen.
- Tombol `Aktifkan Suara`.
- Panel nomor terbaru.
- Grid status loket.
- Daftar panggilan terakhir.

Task:

- Buat route dan view.
- Gunakan layout standalone tanpa header/nav aplikasi.
- Buat desain responsive untuk TV, tablet, dan HP.
- Tambahkan jam berjalan sesuai `app.timezone`.
- Tambahkan state kosong saat belum ada panggilan.

Estimasi: 1 sampai 1.5 hari.

### 6. Audio queue browser

Deliverable:

- Panggilan suara Bahasa Indonesia.
- Pemanggilan bersamaan dibacakan satu per satu.
- Tidak ada suara tumpang tindih.

Task:

- Tambahkan JavaScript polling.
- Tambahkan queue audio.
- Tambahkan guard `speaking`.
- Tambahkan fallback saat audio error.
- Tambahkan tombol untuk mengulang panggilan terakhir pada display jika diperlukan.

Estimasi: 1 hari.

### 7. Permission dan setting

Deliverable:

- Permission `petugas.display_antrian`.
- Opsi token display jika dibutuhkan.
- Seeder add-only.

Task:

- Tambahkan permission ke `RolePermissionSeeder`.
- Berikan permission default ke `Super Admin`.
- Jika memakai token, tambahkan setting `queue.display_token`.
- Tambahkan menu drawer bila halaman perlu dibuka dari aplikasi.

Estimasi: 0.5 hari.

### 8. Testing

Deliverable:

- Test integrasi panggil membuat event.
- Test endpoint display mengembalikan event baru.
- Test permission/token.
- Test view bisa dicache.

Task:

- Feature test `callTicket()` membuat `queue_call_events`.
- Feature test panggil dua loket hampir bersamaan menghasilkan dua event.
- Feature test endpoint `after_id` hanya mengambil event baru.
- Jalankan:
  - `php artisan test`;
  - `php artisan view:cache`;
  - uji manual dua browser: satu `/petugas`, satu `/display-antrian`.

Estimasi: 1 hari.

### 9. Dokumentasi dan rilis

Deliverable:

- Dokumentasi implementasi diperbarui.
- Catatan deployment.
- Catatan operasional untuk petugas.

Task:

- Update `docs/IMPLEMENTATION.md`.
- Update `docs/CHANGELOG.md`.
- Tambahkan instruksi penggunaan:
  - buka display;
  - klik `Aktifkan Suara`;
  - biarkan halaman standby;
  - petugas memanggil dari `/petugas`.

Estimasi: 0.5 hari.

## Urutan implementasi yang disarankan

1. Buat migration/model event.
2. Integrasikan event ke tombol `Panggil`.
3. Buat endpoint polling.
4. Buat halaman display tanpa audio dulu.
5. Tambahkan audio queue.
6. Tambahkan permission/token.
7. Tambahkan test dan dokumentasi.

Dengan urutan ini, sistem bisa diuji bertahap: pertama pastikan event tersimpan, lalu pastikan display membaca event, terakhir pastikan suara berjalan rapi.
