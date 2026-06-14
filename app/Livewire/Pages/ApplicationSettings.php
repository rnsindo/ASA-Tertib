<?php

namespace App\Livewire\Pages;

use App\Models\AppSetting;
use App\Support\AppClock;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Pengaturan Aplikasi')]
class ApplicationSettings extends Component
{
    use WithFileUploads;

    public string $appName = '';
    public string $appLogo = '';
    public string $appFavicon = '';
    public bool $appLogoEnabled = true;
    public string $primaryColor = '#1d4ed8';
    public string $appTimezone = 'Asia/Jakarta';
    public int $defaultServiceMinutes = 10;
    public ?TemporaryUploadedFile $logoUpload = null;
    public ?TemporaryUploadedFile $faviconUpload = null;

    public function mount(): void
    {
        $user = auth()->user();

        abort_unless(
            auth()->check()
            && ($user->can('admin.pengaturan_aplikasi') || $user->hasAnyRole(['superadmin', 'Super Admin'])),
            403,
        );

        $this->loadForm();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'appName' => ['required', 'string', 'max:120'],
            'appLogo' => ['nullable', 'string', 'max:255'],
            'appFavicon' => ['nullable', 'string', 'max:255'],
            'appLogoEnabled' => ['boolean'],
            'logoUpload' => ['nullable', 'image', 'max:2048'],
            'faviconUpload' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,webp', 'max:1024'],
            'primaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'appTimezone' => ['required', Rule::in(array_keys(AppClock::timezoneOptions()))],
            'defaultServiceMinutes' => ['required', 'integer', 'min:1', 'max:240'],
        ], [
            'primaryColor.regex' => 'Warna utama harus menggunakan format hex, contoh #1d4ed8.',
            'faviconUpload.mimes' => 'Favicon harus berupa file ico, png, jpg, jpeg, atau webp.',
            'appTimezone.in' => 'Zona waktu aplikasi tidak valid.',
        ], [
            'appName' => 'Nama Aplikasi',
            'appLogo' => 'Logo Aktif',
            'appFavicon' => 'Favicon Aktif',
            'appLogoEnabled' => 'Tampilkan Logo',
            'logoUpload' => 'File Logo',
            'faviconUpload' => 'File Favicon',
            'primaryColor' => 'Warna Utama',
            'appTimezone' => 'Zona Waktu',
            'defaultServiceMinutes' => 'Estimasi Awal Pelayanan',
        ]);

        if ($this->logoUpload) {
            $path = $this->logoUpload->store('logos', 'public');
            $validated['appLogo'] = 'storage/' . $path;
        }

        if ($this->faviconUpload) {
            $path = $this->faviconUpload->store('favicons', 'public');
            $validated['appFavicon'] = 'storage/' . $path;
        }

        AppSetting::putValue('app.name', $validated['appName'], [
            'group' => 'identity',
            'label' => 'Nama Aplikasi',
            'type' => AppSetting::TYPE_STRING,
            'is_public' => true,
            'sort_order' => 1,
        ]);

        AppSetting::putValue('app.logo', $validated['appLogo'], [
            'group' => 'identity',
            'label' => 'Logo Aplikasi',
            'type' => AppSetting::TYPE_IMAGE,
            'is_public' => true,
            'sort_order' => 2,
        ]);

        AppSetting::putValue('app.logo_enabled', $validated['appLogoEnabled'], [
            'group' => 'identity',
            'label' => 'Tampilkan Logo',
            'type' => AppSetting::TYPE_BOOLEAN,
            'is_public' => true,
            'sort_order' => 3,
        ]);

        AppSetting::putValue('app.favicon', $validated['appFavicon'], [
            'group' => 'identity',
            'label' => 'Favicon Browser',
            'type' => AppSetting::TYPE_IMAGE,
            'is_public' => true,
            'sort_order' => 4,
        ]);

        AppSetting::putValue('app.primary_color', $validated['primaryColor'], [
            'group' => 'theme',
            'label' => 'Warna Utama',
            'type' => AppSetting::TYPE_STRING,
            'is_public' => true,
            'sort_order' => 1,
        ]);

        AppSetting::putValue('app.timezone', $validated['appTimezone'], [
            'group' => 'system',
            'label' => 'Zona Waktu Aplikasi',
            'type' => AppSetting::TYPE_STRING,
            'is_public' => false,
            'sort_order' => 1,
        ]);
        AppClock::applyConfiguredTimezone();

        AppSetting::putValue('queue.default_service_minutes', $validated['defaultServiceMinutes'], [
            'group' => 'queue',
            'label' => 'Estimasi Awal Pelayanan Per Pendaftar',
            'type' => AppSetting::TYPE_INTEGER,
            'is_public' => false,
            'sort_order' => 1,
        ]);

        session()->flash('status', 'Pengaturan aplikasi berhasil disimpan.');
        $this->logoUpload = null;
        $this->faviconUpload = null;
        $this->loadForm();
    }

    private function loadForm(): void
    {
        $this->appName = (string) AppSetting::getValue('app.name', config('app.name', 'ASA-Tertib'));
        $this->appLogo = (string) AppSetting::getValue('app.logo', '');
        $this->appFavicon = (string) AppSetting::getValue('app.favicon', '');
        $this->appLogoEnabled = (bool) AppSetting::getValue('app.logo_enabled', true);
        $this->primaryColor = (string) AppSetting::getValue('app.primary_color', '#1d4ed8');
        $this->appTimezone = AppClock::timezone();
        $this->defaultServiceMinutes = (int) AppSetting::getValue('queue.default_service_minutes', 10);
    }

    public function render()
    {
        return view('livewire.pages.application-settings', [
            'timezoneOptions' => AppClock::timezoneOptions(),
        ]);
    }
}
