<?php

namespace Database\Seeders;

use App\Models\QueueService;
use App\Models\QueueServiceDependency;
use Database\Seeders\Concerns\SeedModeAware;
use Illuminate\Database\Seeder;

class QueueServiceDependencySeeder extends Seeder
{
    use SeedModeAware;

    public function run(): void
    {
        $verification = QueueService::query()->where('slug', 'verifikasi-berkas')->firstOrFail();
        $interview = QueueService::query()->where('slug', 'wawancara')->firstOrFail();

        $this->seedModel(
            QueueServiceDependency::class,
            [
                'queue_service_id' => $interview->id,
                'required_queue_service_id' => $verification->id,
            ],
            [
                'required_status_mode' => QueueServiceDependency::MODE_COMPLETED,
                'is_active' => true,
            ],
        );
    }
}
