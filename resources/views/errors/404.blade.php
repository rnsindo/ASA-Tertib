@include('errors.layout', [
    'statusCode' => 404,
    'title' => 'Halaman Tidak Ditemukan',
    'message' => 'Link yang dibuka tidak ditemukan atau sudah berubah. Gunakan tombol Dashboard untuk kembali ke halaman utama aplikasi.',
])
