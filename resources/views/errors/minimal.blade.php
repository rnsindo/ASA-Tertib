@include('errors.layout', [
    'statusCode' => method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500,
    'title' => 'Terjadi Kendala',
    'message' => 'Permintaan belum bisa diproses. Silakan kembali ke dashboard dan coba lagi.',
])
