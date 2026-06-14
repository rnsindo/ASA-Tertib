<?php

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\AppSetting;
use App\Models\QueueServiceDependency;
use App\Models\QueueService;
use App\Models\ServiceCounter;
use App\Models\User;
use App\Models\ServiceDailyQuota;
use App\Services\QueueRuntimeService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::query()
            ->whereIn('name', [
                'page.dashboard',
                'page.officer-console',
                'page.app-settings',
                'page.user-management',
                'user.reset-password',
                'user.impersonate',
                'menu.status',
                'menu.scan-qr',
                'menu.home',
                'menu.riwayat',
                'menu.profil',
                'menu.konsol-petugas',
                'menu.app-settings',
                'menu.user-management',
                'menu.pelayanan-24-7',
                'menu.informasi-pendaftaran',
                'menu.panduan-lengkap',
            ])
            ->delete();

        $permissions = [
            'admin.pengaturan_aplikasi' => 'Admin - Pengaturan Aplikasi',
            'admin.manajemen_layanan' => 'Admin - Manajemen Layanan',
            'admin.manajemen_user' => 'Admin - Manajemen User',
            'admin.reset_password_user' => 'Admin - Reset Password User',
            'admin.login_sebagai_user' => 'Admin - Login Sebagai User',
            'petugas.beranda' => 'Petugas - Beranda',
            'petugas.konsol_antrian' => 'Petugas - Konsol Antrian',
            'pelanggan.beranda' => 'Pelanggan/Penanya - Beranda',
            'pelanggan.dashboard_antrian' => 'Pelanggan/Penanya - Dashboard Antrian',
            'pelanggan.status_antrian' => 'Pelanggan/Penanya - Status Antrian',
            'pelanggan.scan_qr' => 'Pelanggan/Penanya - Scan QR',
            'pelanggan.riwayat' => 'Pelanggan/Penanya - Riwayat',
            'pelanggan.profil' => 'Pelanggan/Penanya - Profil',
        ];

        collect($permissions)->keys()->each(function (string $permission): void {
            Permission::firstOrCreate(['name' => $permission]);
        });

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $officerRole = Role::firstOrCreate(['name' => 'Petugas']);
        $legacyApplicantRole = Role::query()->where('name', 'Pengguna')->first();

        if ($legacyApplicantRole && ! Role::query()->where('name', 'Pelanggan/Penanya')->exists()) {
            $legacyApplicantRole->forceFill(['name' => 'Pelanggan/Penanya'])->save();
        }

        $customerRole = Role::firstOrCreate(['name' => 'Pelanggan/Penanya']);

        $superAdminRole->syncPermissions(array_keys($permissions));
        $officerPermissions = [
            'petugas.beranda',
            'petugas.konsol_antrian',
        ];
        $customerPermissions = [
            'pelanggan.beranda',
            'pelanggan.dashboard_antrian',
            'pelanggan.status_antrian',
            'pelanggan.scan_qr',
            'pelanggan.riwayat',
            'pelanggan.profil',
        ];

        $officerRole->syncPermissions($officerPermissions);
        $customerRole->syncPermissions($customerPermissions);

        $legacyRolePermissions = [
            'superadmin' => array_keys($permissions),
            'admin' => array_keys($permissions),
            'officer' => $officerPermissions,
            'applicant' => $customerPermissions,
            'Pengguna' => $customerPermissions,
        ];

        foreach ($legacyRolePermissions as $roleName => $rolePermissions) {
            $legacyRole = Role::query()->where('name', $roleName)->first();

            if ($legacyRole) {
                $legacyRole->syncPermissions($rolePermissions);
            }
        }

        collect([
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
        ])->each(function (array $setting): void {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting,
            );
        });

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@asa-link.cloud'],
            [
                'name' => 'Super Admin ASA-Tertib',
                'is_active' => true,
                'password' => 'password123',
            ],
        );
        $superAdmin->syncRoles([$superAdminRole]);

        $officer = User::updateOrCreate(
            ['email' => 'petugas@example.test'],
            [
                'name' => 'Petugas Loket',
                'is_active' => true,
                'password' => 'password123',
            ],
        );
        $officer->syncRoles([$officerRole]);

        $applicantUser = User::updateOrCreate(
            ['email' => 'pendaftar@example.test'],
            [
                'name' => 'Budi Santoso',
                'phone' => '081234567890',
                'is_active' => true,
                'password' => 'password123',
            ],
        );
        $applicantUser->syncRoles([$customerRole]);

        Applicant::updateOrCreate(
            ['user_id' => $applicantUser->id],
            [
                'full_name' => 'Budi Santoso',
                'school_origin' => 'SMP Negeri 1 Contoh',
                'nisn' => '0098765432',
                'whatsapp' => '081234567890',
                'status' => 'registered',
            ],
        );

        $verification = QueueService::updateOrCreate(
            ['slug' => 'verifikasi-berkas'],
            [
                'name' => 'Verifikasi Berkas',
                'code' => 'VB',
                'description' => 'Pemeriksaan kelengkapan berkas pendaftaran.',
                'sort_order' => 1,
                'is_active' => true,
            ],
        );

        $interview = QueueService::updateOrCreate(
            ['slug' => 'wawancara'],
            [
                'name' => 'Wawancara',
                'code' => 'WW',
                'description' => 'Sesi wawancara calon siswa.',
                'sort_order' => 2,
                'is_active' => true,
            ],
        );

        QueueServiceDependency::updateOrCreate(
            [
                'queue_service_id' => $interview->id,
                'required_queue_service_id' => $verification->id,
            ],
            [
                'required_status_mode' => QueueServiceDependency::MODE_COMPLETED,
                'is_active' => true,
            ],
        );

        collect([
            [$verification, 'Loket Verifikasi 1', 'VB-1', 1],
            [$verification, 'Loket Verifikasi 2', 'VB-2', 2],
            [$interview, 'Loket Wawancara 1', 'WW-1', 1],
            [$interview, 'Loket Wawancara 2', 'WW-2', 2],
        ])->each(function (array $row) use ($officer): void {
            [$service, $name, $code, $sortOrder] = $row;

            ServiceCounter::updateOrCreate(
                ['code' => $code],
                [
                    'queue_service_id' => $service->id,
                    'assigned_user_id' => $sortOrder === 1 ? $officer->id : null,
                    'name' => $name,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ],
            );
        });

        $queueRuntime = app(QueueRuntimeService::class);
        $session = $queueRuntime->currentSession();

        collect([$verification, $interview])->each(function (QueueService $service) use ($session, $queueRuntime): void {
            ServiceDailyQuota::updateOrCreate(
                [
                    'queue_session_id' => $session->id,
                    'queue_service_id' => $service->id,
                ],
                [
                    'max_daily_quota' => 200,
                    'is_open' => true,
                ],
            );

            $queueRuntime->ensureAllocations($service, $session);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
