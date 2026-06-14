<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\QueueService;
use App\Models\ServiceDailyQuota;
use App\Services\QueueRuntimeService;
use Database\Seeders\Concerns\SeedModeAware;
use Illuminate\Database\Seeder;

class ServiceDailyQuotaSeeder extends Seeder
{
    use SeedModeAware;

    public function run(): void
    {
        $queueRuntime = app(QueueRuntimeService::class);
        $session = $queueRuntime->currentSession();
        $defaultDailyQuota = max(1, (int) AppSetting::getValue('queue.daily_quota_limit', 200));

        QueueService::query()
            ->whereIn('slug', ['verifikasi-berkas', 'wawancara'])
            ->get()
            ->each(function (QueueService $service) use ($session, $queueRuntime, $defaultDailyQuota): void {
                $this->seedModel(ServiceDailyQuota::class, [
                    'queue_session_id' => $session->id,
                    'queue_service_id' => $service->id,
                ], [
                    'max_daily_quota' => $defaultDailyQuota,
                    'is_open' => true,
                ]);

                $queueRuntime->ensureAllocations($service, $session);
            });
    }
}
