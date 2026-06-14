@include('errors.layout', [
    'statusCode' => 403,
    'title' => 'Akses Tidak Diizinkan',
    'message' => 'Akun Anda belum memiliki izin untuk membuka halaman ini. Silakan kembali ke dashboard atau hubungi petugas jika akses memang diperlukan.',
])
