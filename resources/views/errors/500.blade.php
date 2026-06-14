@include('errors.layout', [
    'statusCode' => 500,
    'title' => 'Sistem Sedang Bermasalah',
    'message' => 'Terjadi kendala pada sistem. Silakan kembali ke dashboard dan coba beberapa saat lagi.',
])
