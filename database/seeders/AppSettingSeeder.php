<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Database\Seeders\Concerns\SeedModeAware;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    use SeedModeAware;

    public function run(): void
    {
        collect($this->settings())->each(function (array $setting): void {
            $this->seedModel(AppSetting::class, ['key' => $setting['key']], $setting);
        });
    }

    private function settings(): array
    {
        return [
            [
                'key' => 'app.name',
                'group' => 'identity',
                'label' => 'Nama Aplikasi',
                'type' => AppSetting::TYPE_STRING,
                'value' => 'ASA-Tertib',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'app.logo',
                'group' => 'identity',
                'label' => 'Logo Aplikasi',
                'type' => AppSetting::TYPE_IMAGE,
                'value' => null,
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'app.logo_enabled',
                'group' => 'identity',
                'label' => 'Tampilkan Logo',
                'type' => AppSetting::TYPE_BOOLEAN,
                'value' => '1',
                'is_public' => true,
                'sort_order' => 3,
            ],
            [
                'key' => 'app.favicon',
                'group' => 'identity',
                'label' => 'Favicon Browser',
                'type' => AppSetting::TYPE_IMAGE,
                'value' => null,
                'is_public' => true,
                'sort_order' => 4,
            ],
            [
                'key' => 'app.primary_color',
                'group' => 'theme',
                'label' => 'Warna Utama',
                'type' => AppSetting::TYPE_STRING,
                'value' => '#1d4ed8',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'app.timezone',
                'group' => 'system',
                'label' => 'Zona Waktu Aplikasi',
                'type' => AppSetting::TYPE_STRING,
                'value' => 'Asia/Jakarta',
                'is_public' => false,
                'sort_order' => 1,
            ],
            [
                'key' => 'queue.default_service_minutes',
                'group' => 'queue',
                'label' => 'Estimasi Awal Pelayanan Per Pendaftar',
                'type' => AppSetting::TYPE_INTEGER,
                'value' => '10',
                'is_public' => false,
                'sort_order' => 1,
            ],
            [
                'key' => 'queue.daily_quota_enabled',
                'group' => 'queue',
                'label' => 'Aktifkan Quota Harian',
                'type' => AppSetting::TYPE_BOOLEAN,
                'value' => '1',
                'is_public' => false,
                'sort_order' => 2,
            ],
            [
                'key' => 'queue.daily_quota_limit',
                'group' => 'queue',
                'label' => 'Total Quota Harian',
                'type' => AppSetting::TYPE_INTEGER,
                'value' => '200',
                'is_public' => false,
                'sort_order' => 3,
            ],
            [
                'key' => 'queue.qr_expiry_limit_enabled',
                'group' => 'queue',
                'label' => 'Aktifkan Batas Durasi QR dan Kode Manual',
                'type' => AppSetting::TYPE_BOOLEAN,
                'value' => '0',
                'is_public' => false,
                'sort_order' => 4,
            ],
            [
                'key' => 'queue.qr_expiry_limit_hours',
                'group' => 'queue',
                'label' => 'Batas Durasi QR dan Kode Manual Dalam Jam',
                'type' => AppSetting::TYPE_INTEGER,
                'value' => '2',
                'is_public' => false,
                'sort_order' => 5,
            ],
        ];
    }
}
