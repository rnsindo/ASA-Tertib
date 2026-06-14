@include('errors.layout', [
    'statusCode' => 503,
    'title' => 'Layanan Sementara Tidak Tersedia',
    'message' => 'Aplikasi sedang dalam perawatan atau layanan sementara belum tersedia. Silakan coba kembali beberapa saat lagi.',
])
