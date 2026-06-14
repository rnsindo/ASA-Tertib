<?php

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\Concerns\SeedModeAware;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DefaultUserSeeder extends Seeder
{
    use SeedModeAware;

    public function run(): void
    {
        $superAdminRole = Role::findByName('Super Admin');
        $officerRole = Role::findByName('Petugas');
        $customerRole = Role::findByName('Pelanggan/Penanya');

        $superAdmin = $this->seedModel(User::class, ['email' => 'superadmin@asa-link.cloud'], [
            'name' => 'Super Admin ASA-Tertib',
            'is_active' => true,
            'password' => 'password123',
        ]);
        $this->applyUserRoles($superAdmin, [$superAdminRole]);

        $officer = $this->seedModel(User::class, ['email' => 'petugas@example.test'], [
            'name' => 'Petugas Loket',
            'is_active' => true,
            'password' => 'password123',
        ]);
        $this->applyUserRoles($officer, [$officerRole]);

        $applicantUser = $this->seedModel(User::class, ['email' => 'pendaftar@example.test'], [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'is_active' => true,
            'password' => 'password123',
        ]);
        $this->applyUserRoles($applicantUser, [$customerRole]);

        $this->seedModel(Applicant::class, ['user_id' => $applicantUser->id], [
            'full_name' => 'Budi Santoso',
            'school_origin' => 'SMP Negeri 1 Contoh',
            'nisn' => '0098765432',
            'whatsapp' => '081234567890',
            'status' => 'registered',
        ]);
    }

    private function applyUserRoles(User $user, array $roles): void
    {
        if ($this->isSyncMode()) {
            $user->syncRoles($roles);

            return;
        }

        foreach ($roles as $role) {
            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }
        }
    }
}
