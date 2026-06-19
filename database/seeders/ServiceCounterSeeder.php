<?php

namespace Database\Seeders;

use App\Models\QueueService;
use App\Models\ServiceCounter;
use App\Models\User;
use Database\Seeders\Concerns\SeedModeAware;
use Illuminate\Database\Seeder;

class ServiceCounterSeeder extends Seeder
{
    use SeedModeAware;

    public function run(): void
    {
        $officer = User::query()->where('email', 'petugas@example.test')->first();
        $verification = QueueService::query()->where('slug', 'verifikasi-berkas')->firstOrFail();
        $interview = QueueService::query()->where('slug', 'wawancara')->firstOrFail();

        collect([
            [$verification, 'Loket Verifikasi 1', 'VB-1', 1, 'announcement/counters/loket-verifikasi-1.mp3'],
            [$verification, 'Loket Verifikasi 2', 'VB-2', 2, 'announcement/counters/loket-verifikasi-2.mp3'],
            [$interview, 'Loket Wawancara 1', 'WW-1', 1, 'announcement/counters/loket-wawancara-1.mp3'],
            [$interview, 'Loket Wawancara 2', 'WW-2', 2, 'announcement/counters/loket-wawancara-2.mp3'],
        ])->each(function (array $row) use ($officer): void {
            [$service, $name, $code, $sortOrder, $announcementAudioPath] = $row;

            $this->seedModel(ServiceCounter::class, ['code' => $code], [
                'queue_service_id' => $service->id,
                'assigned_user_id' => $sortOrder === 1 ? $officer?->id : null,
                'name' => $name,
                'announcement_audio_path' => $announcementAudioPath,
                'sort_order' => $sortOrder,
                'is_active' => true,
            ]);
        });
    }
}
