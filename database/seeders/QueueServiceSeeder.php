<?php

namespace Database\Seeders;

use App\Models\QueueService;
use Database\Seeders\Concerns\SeedModeAware;
use Illuminate\Database\Seeder;

class QueueServiceSeeder extends Seeder
{
    use SeedModeAware;

    public function run(): void
    {
        collect([
            [
                'slug' => 'verifikasi-berkas',
                'name' => 'Verifikasi Berkas',
                'code' => 'VB',
                'description' => 'Pemeriksaan kelengkapan berkas pendaftaran.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'slug' => 'wawancara',
                'name' => 'Wawancara',
                'code' => 'WW',
                'description' => 'Sesi wawancara calon siswa.',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ])->each(function (array $service): void {
            $slug = $service['slug'];
            unset($service['slug']);

            $this->seedModel(QueueService::class, ['slug' => $slug], $service);
        });
    }
}
