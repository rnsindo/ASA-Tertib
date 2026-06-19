<?php

namespace Database\Seeders;

use App\Models\QueueService;
use App\Models\ServiceCounter;
use Database\Seeders\Concerns\SeedModeAware;
use Illuminate\Database\Seeder;

class QueueAnnouncementAudioSeeder extends Seeder
{
    use SeedModeAware;

    public function run(): void
    {
        $services = [
            'VB' => 'announcement/services/verifikasi-berkas.mp3',
            'WW' => 'announcement/services/wawancara.mp3',
        ];

        foreach ($services as $code => $path) {
            $query = QueueService::query()->where('code', $code);

            if (! $this->isSyncMode()) {
                $query->whereNull('announcement_audio_path');
            }

            $query->update(['announcement_audio_path' => $path]);
        }

        $counters = [
            'VB-1' => 'announcement/counters/loket-verifikasi-1.mp3',
            'VB-2' => 'announcement/counters/loket-verifikasi-2.mp3',
            'WW-1' => 'announcement/counters/loket-wawancara-1.mp3',
            'WW-2' => 'announcement/counters/loket-wawancara-2.mp3',
        ];

        foreach ($counters as $code => $path) {
            $query = ServiceCounter::query()->where('code', $code);

            if (! $this->isSyncMode()) {
                $query->whereNull('announcement_audio_path');
            }

            $query->update(['announcement_audio_path' => $path]);
        }
    }
}
