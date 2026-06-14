<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedModeAware;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    use SeedModeAware;

    public const PERMISSIONS = [
        'admin.pengaturan_aplikasi' => 'Admin - Pengaturan Aplikasi',
        'admin.manajemen_layanan' => 'Admin - Manajemen Layanan',
        'admin.manajemen_user' => 'Admin - Manajemen User',
        'admin.reset_password_user' => 'Admin - Reset Password User',
        'admin.login_sebagai_user' => 'Admin - Login Sebagai User',
        'petugas.beranda' => 'Petugas - Beranda',
        'petugas.konsol_antrian' => 'Petugas - Konsol Antrian',
        'petugas.kelola_qr_antrian' => 'Petugas - Kelola QR Antrian',
        'pelanggan.beranda' => 'Pelanggan/Penanya - Beranda',
        'pelanggan.dashboard_antrian' => 'Pelanggan/Penanya - Dashboard Antrian',
        'pelanggan.status_antrian' => 'Pelanggan/Penanya - Status Antrian',
        'pelanggan.scan_qr' => 'Pelanggan/Penanya - Scan QR',
        'pelanggan.riwayat' => 'Pelanggan/Penanya - Riwayat',
        'pelanggan.profil' => 'Pelanggan/Penanya - Profil',
    ];

    public const OFFICER_PERMISSIONS = [
        'petugas.beranda',
        'petugas.konsol_antrian',
    ];

    public const CUSTOMER_PERMISSIONS = [
        'pelanggan.beranda',
        'pelanggan.dashboard_antrian',
        'pelanggan.status_antrian',
        'pelanggan.scan_qr',
        'pelanggan.riwayat',
        'pelanggan.profil',
    ];

    private const LEGACY_PERMISSIONS = [
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
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($this->isSyncMode()) {
            Permission::query()->whereIn('name', self::LEGACY_PERMISSIONS)->delete();
        }

        collect(self::PERMISSIONS)->keys()->each(function (string $permission): void {
            Permission::firstOrCreate(['name' => $permission]);
        });

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $officerRole = Role::firstOrCreate(['name' => 'Petugas']);
        $customerRole = Role::firstOrCreate(['name' => 'Pelanggan/Penanya']);

        $this->applyRolePermissions($superAdminRole, array_keys(self::PERMISSIONS));
        $this->applyRolePermissions($officerRole, self::OFFICER_PERMISSIONS);
        $this->applyRolePermissions($customerRole, self::CUSTOMER_PERMISSIONS);

        $legacyRolePermissions = [
            'superadmin' => array_keys(self::PERMISSIONS),
            'admin' => array_keys(self::PERMISSIONS),
            'officer' => self::OFFICER_PERMISSIONS,
            'applicant' => self::CUSTOMER_PERMISSIONS,
            'Pengguna' => self::CUSTOMER_PERMISSIONS,
        ];

        foreach ($legacyRolePermissions as $roleName => $rolePermissions) {
            $legacyRole = Role::query()->where('name', $roleName)->first();

            if ($legacyRole) {
                $this->applyRolePermissions($legacyRole, $rolePermissions);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function applyRolePermissions(Role $role, array $permissions): void
    {
        if ($this->isSyncMode()) {
            $role->syncPermissions($permissions);

            return;
        }

        $missingPermissions = collect($permissions)
            ->reject(fn (string $permission): bool => $role->hasPermissionTo($permission))
            ->values()
            ->all();

        if ($missingPermissions !== []) {
            $role->givePermissionTo($missingPermissions);
        }
    }
}
