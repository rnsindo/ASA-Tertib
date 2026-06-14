<?php

namespace App\Livewire\Pages;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Manajemen User')]
class UserManagement extends Component
{
    private const PAGE_SIZE = 5;

    public string $search = '';
    public string $roleFilter = '';
    public int $visibleCount = self::PAGE_SIZE;
    public array $resetPasswords = [];
    public bool $isPermissionModalOpen = false;
    public ?int $editingPermissionUserId = null;
    public string $editingPermissionUserName = '';
    public string $editingPermissionUserEmail = '';
    public array $editingPermissionUserRoles = [];
    public bool $editingUserIsActive = true;
    public array $selectedRoleNames = [];
    public array $roleDefaultPermissionNames = [];
    public array $selectedDirectPermissions = [];

    public function mount(): void
    {
        $user = auth()->user();

        abort_unless(
            auth()->check()
            && ($user->can('admin.manajemen_user') || $user->hasAnyRole(['superadmin', 'Super Admin'])),
            403,
        );
    }

    public function updatedSearch(): void
    {
        $this->visibleCount = self::PAGE_SIZE;
    }

    public function updatedRoleFilter(): void
    {
        $this->visibleCount = self::PAGE_SIZE;
    }

    public function loadMore(): void
    {
        $this->visibleCount += self::PAGE_SIZE;
    }

    public function resetPassword(int $userId): void
    {
        $actor = auth()->user();

        abort_unless(
            $actor
            && ($actor->can('admin.reset_password_user') || $actor->hasAnyRole(['superadmin', 'Super Admin'])),
            403,
        );

        $user = User::query()->findOrFail($userId);
        $password = $this->generateResetPassword();

        $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => Str::random(60),
        ])->save();

        $this->resetPasswords[$user->id] = $password;

        session()->flash('status', 'Password user berhasil direset.');
    }

    public function openPermissionModal(int $userId): void
    {
        $this->authorizeUserManagement();

        $user = User::query()
            ->with(['roles.permissions', 'permissions'])
            ->findOrFail($userId);

        $this->editingPermissionUserId = $user->id;
        $this->editingPermissionUserName = (string) $user->name;
        $this->editingPermissionUserEmail = (string) $user->email;
        $this->editingPermissionUserRoles = $user->roles->pluck('name')->values()->all();
        $this->editingUserIsActive = (bool) $user->is_active;
        $this->selectedRoleNames = $this->editingPermissionUserRoles;
        $this->roleDefaultPermissionNames = $user->getPermissionsViaRoles()->pluck('name')->values()->all();
        $this->selectedDirectPermissions = $user->getDirectPermissions()
            ->pluck('name')
            ->diff($this->roleDefaultPermissionNames)
            ->values()
            ->all();
        $this->isPermissionModalOpen = true;
        $this->resetErrorBag(['selectedDirectPermissions']);
    }

    public function closePermissionModal(): void
    {
        $this->resetPermissionModal();
    }

    public function savePermissions(): void
    {
        $this->authorizeUserManagement();

        $user = $this->editingPermissionUserId
            ? User::query()->findOrFail($this->editingPermissionUserId)
            : null;

        if (! $user) {
            $this->notify('error', 'Permission gagal disimpan. Alasan: user tidak ditemukan.');

            return;
        }

        try {
            $validated = $this->validate([
                'editingUserIsActive' => ['boolean'],
                'selectedRoleNames' => ['array'],
                'selectedRoleNames.*' => ['string', 'exists:roles,name'],
                'selectedDirectPermissions' => ['array'],
                'selectedDirectPermissions.*' => ['string', 'exists:permissions,name'],
            ], [], [
                'editingUserIsActive' => 'status akun',
                'selectedRoleNames' => 'role user',
                'selectedDirectPermissions' => 'permission tambahan',
            ]);
        } catch (ValidationException $exception) {
            $message = collect($exception->validator->errors()->all())->first()
                ?: 'data permission tidak valid.';
            $this->notify('error', 'Permission gagal disimpan. Alasan: ' . $message);

            throw $exception;
        }

        try {
            $roleNames = array_values(array_unique($validated['selectedRoleNames'] ?? []));
            $willBeActive = (bool) ($validated['editingUserIsActive'] ?? false);
            $this->guardRoleChanges($user, $roleNames);
            $this->guardAccountStatusChange($user, $willBeActive);

            $user->forceFill(['is_active' => $willBeActive])->save();
            $user->syncRoles($roleNames);
            $user->syncPermissions($validated['selectedDirectPermissions'] ?? []);
            $this->resetPermissionModal();
            $this->notify('success', 'Status, role, dan permission user berhasil disimpan.');
        } catch (\Throwable $exception) {
            $this->notify('error', 'Status/role/permission gagal disimpan. Alasan: ' . $exception->getMessage());
        }
    }

    private function generateResetPassword(): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $password = '';
        $maxIndex = strlen($characters) - 1;

        for ($i = 0; $i < 5; $i++) {
            $password .= $characters[random_int(0, $maxIndex)];
        }

        return $password;
    }

    public function render()
    {
        $query = $this->filteredUserQuery();
        $totalUsers = (clone $query)->count();
        $users = $query
            ->limit($this->visibleCount)
            ->get();

        return view('livewire.pages.user-management', [
            'users' => $users,
            'roles' => Role::query()->orderBy('name')->pluck('name'),
            'allRoles' => Role::query()->with('permissions')->orderBy('name')->get(),
            'permissionsByGroup' => Permission::query()
                ->orderBy('name')
                ->get()
                ->groupBy(fn (Permission $permission): string => Str::headline(Str::before($permission->name, '.'))),
            'hasMoreUsers' => $users->count() < $totalUsers,
            'totalUsers' => $totalUsers,
        ]);
    }

    private function filteredUserQuery(): Builder
    {
        return User::query()
            ->with('roles')
            ->when($this->search !== '', function (Builder $query): void {
                $keyword = '%' . $this->search . '%';

                $query->where(function (Builder $query) use ($keyword): void {
                    $query->where('name', 'like', $keyword)
                        ->orWhere('email', 'like', $keyword)
                        ->orWhere('phone', 'like', $keyword);
                });
            })
            ->when($this->roleFilter !== '', function (Builder $query): void {
                $query->role($this->roleFilter);
            })
            ->orderBy('name')
            ->orderBy('id');
    }

    private function resetPermissionModal(): void
    {
        $this->isPermissionModalOpen = false;
        $this->editingPermissionUserId = null;
        $this->editingPermissionUserName = '';
        $this->editingPermissionUserEmail = '';
        $this->editingPermissionUserRoles = [];
        $this->editingUserIsActive = true;
        $this->selectedRoleNames = [];
        $this->roleDefaultPermissionNames = [];
        $this->selectedDirectPermissions = [];
        $this->resetErrorBag(['editingUserIsActive', 'selectedRoleNames', 'selectedDirectPermissions']);
    }

    private function authorizeUserManagement(): void
    {
        $user = auth()->user();

        abort_unless(
            $user && ($user->can('admin.manajemen_user') || $user->hasAnyRole(['superadmin', 'Super Admin'])),
            403,
        );
    }

    private function notify(string $type, string $message): void
    {
        $this->dispatch('user-notify', type: $type, message: $message);
    }

    private function guardRoleChanges(User $targetUser, array $roleNames): void
    {
        $actor = auth()->user();
        $targetHadSuperAdmin = $targetUser->hasRole('Super Admin');
        $targetWillHaveSuperAdmin = in_array('Super Admin', $roleNames, true);
        $superAdminChanged = $targetHadSuperAdmin !== $targetWillHaveSuperAdmin;

        if ($superAdminChanged && ! $actor?->hasRole('Super Admin')) {
            throw new \RuntimeException('hanya Super Admin yang boleh mengubah role Super Admin.');
        }

        if ($targetUser->id === $actor?->id && $targetHadSuperAdmin && ! $targetWillHaveSuperAdmin) {
            throw new \RuntimeException('Anda tidak bisa mencabut role Super Admin dari akun sendiri.');
        }

        if ($targetHadSuperAdmin && ! $targetWillHaveSuperAdmin) {
            $otherSuperAdmins = User::query()
                ->whereKeyNot($targetUser->id)
                ->where('is_active', true)
                ->role('Super Admin')
                ->count();

            if ($otherSuperAdmins < 1) {
                throw new \RuntimeException('minimal harus ada satu akun Super Admin aktif.');
            }
        }
    }

    private function guardAccountStatusChange(User $targetUser, bool $willBeActive): void
    {
        $actor = auth()->user();

        if ($targetUser->id === $actor?->id && ! $willBeActive) {
            throw new \RuntimeException('Anda tidak bisa menonaktifkan akun sendiri.');
        }

        if ($targetUser->hasRole('Super Admin') && ! $willBeActive) {
            $otherActiveSuperAdmins = User::query()
                ->whereKeyNot($targetUser->id)
                ->where('is_active', true)
                ->role('Super Admin')
                ->count();

            if ($otherActiveSuperAdmins < 1) {
                throw new \RuntimeException('minimal harus ada satu akun Super Admin aktif.');
            }
        }
    }
}
