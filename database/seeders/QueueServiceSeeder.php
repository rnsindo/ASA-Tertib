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
                'announcement_audio_path' => 'announcement/services/verifikasi-berkas.mp3',
                'sort_order' => 1,
                'enforce_call_order' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'wawancara',
                'name' => 'Wawancara',
                'code' => 'WW',
                'description' => 'Sesi wawancara calon siswa.',
                'announcement_audio_path' => 'announcement/services/wawancara.mp3',
                'sort_order' => 2,
                'enforce_call_order' => true,
                'is_active' => true,
            ],
        ])->each(function (array $service): void {
            $slug = $service['slug'];
            unset($service['slug']);

            $this->seedModel(QueueService::class, ['slug' => $slug], $service);
        });
    }
}
