<?php

namespace App\Livewire\Pages;

use App\Models\AppSetting;
use App\Models\QueueService;
use App\Models\ServiceDailyQuota;
use App\Services\QueueRuntimeService;
use App\Support\AppClock;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
    public bool $dailyQuotaEnabled = true;
    public int $dailyQuotaLimit = 200;
    public bool $qrExpiryLimitEnabled = false;
    public int $qrExpiryLimitHours = 2;
    public bool $qrAutoRegenerateEnabled = true;
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
        try {
            $validated = $this->validatedSettingsPayload();

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

            AppSetting::putValue('queue.daily_quota_enabled', $validated['dailyQuotaEnabled'], [
                'group' => 'queue',
                'label' => 'Aktifkan Quota Harian',
                'type' => AppSetting::TYPE_BOOLEAN,
                'is_public' => false,
                'sort_order' => 2,
            ]);

            AppSetting::putValue('queue.daily_quota_limit', $validated['dailyQuotaLimit'], [
                'group' => 'queue',
                'label' => 'Total Quota Harian',
                'type' => AppSetting::TYPE_INTEGER,
                'is_public' => false,
                'sort_order' => 3,
            ]);

            AppSetting::putValue('queue.qr_expiry_limit_enabled', $validated['qrExpiryLimitEnabled'], [
                'group' => 'queue',
                'label' => 'Aktifkan Batas Durasi QR dan Kode Manual',
                'type' => AppSetting::TYPE_BOOLEAN,
                'is_public' => false,
                'sort_order' => 4,
            ]);

            AppSetting::putValue('queue.qr_expiry_limit_hours', $validated['qrExpiryLimitHours'], [
                'group' => 'queue',
                'label' => 'Batas Durasi QR dan Kode Manual Dalam Jam',
                'type' => AppSetting::TYPE_INTEGER,
                'is_public' => false,
                'sort_order' => 5,
            ]);

            AppSetting::putValue('queue.qr_auto_regenerate_enabled', $validated['qrAutoRegenerateEnabled'], [
                'group' => 'queue',
                'label' => 'QR dan Kode Manual Otomatis Berubah Saat Kedaluwarsa',
                'type' => AppSetting::TYPE_BOOLEAN,
                'is_public' => false,
                'sort_order' => 6,
            ]);

            $this->syncCurrentSessionDailyQuotas(
                (bool) $validated['dailyQuotaEnabled'],
                (int) $validated['dailyQuotaLimit'],
            );

            session()->flash('status', 'Pengaturan aplikasi berhasil disimpan.');
            $this->notify('success', 'Pengaturan aplikasi berhasil disimpan.');
            $this->logoUpload = null;
            $this->faviconUpload = null;
            $this->loadForm();
        } catch (ValidationException $exception) {
            $message = collect($exception->validator->errors()->all())->first()
                ?: 'data pengaturan tidak valid.';
            $this->notify('error', 'Pengaturan gagal disimpan. Alasan: ' . $message);

            throw $exception;
        } catch (\Throwable $exception) {
            $this->notify('error', 'Pengaturan gagal disimpan. Alasan: ' . $exception->getMessage());
        }
    }

    private function validatedSettingsPayload(): array
    {
        return $this->validate([
            'appName' => ['required', 'string', 'max:120'],
            'appLogo' => ['nullable', 'string', 'max:255'],
            'appFavicon' => ['nullable', 'string', 'max:255'],
            'appLogoEnabled' => ['boolean'],
            'logoUpload' => ['nullable', 'image', 'max:2048'],
            'faviconUpload' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,webp', 'max:1024'],
            'primaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'appTimezone' => ['required', Rule::in(array_keys(AppClock::timezoneOptions()))],
            'defaultServiceMinutes' => ['required', 'integer', 'min:1', 'max:240'],
            'dailyQuotaEnabled' => ['boolean'],
            'dailyQuotaLimit' => [$this->dailyQuotaEnabled ? 'required' : 'nullable', 'integer', 'min:1', 'max:100000'],
            'qrExpiryLimitEnabled' => ['boolean'],
            'qrExpiryLimitHours' => [$this->qrExpiryLimitEnabled ? 'required' : 'nullable', 'integer', 'min:1', 'max:24'],
            'qrAutoRegenerateEnabled' => ['boolean'],
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
            'dailyQuotaEnabled' => 'Quota Harian',
            'dailyQuotaLimit' => 'Total Quota Harian',
            'qrExpiryLimitEnabled' => 'Batas QR dan Kode Manual',
            'qrExpiryLimitHours' => 'Masa Berlaku QR',
            'qrAutoRegenerateEnabled' => 'QR dan Kode Otomatis Berubah',
        ]);
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
        $this->dailyQuotaEnabled = (bool) AppSetting::getValue('queue.daily_quota_enabled', true);
        $this->dailyQuotaLimit = max(1, (int) AppSetting::getValue('queue.daily_quota_limit', 200));
        $this->qrExpiryLimitEnabled = (bool) AppSetting::getValue('queue.qr_expiry_limit_enabled', false);
        $this->qrExpiryLimitHours = max(1, min(24, (int) AppSetting::getValue('queue.qr_expiry_limit_hours', 2)));
        $this->qrAutoRegenerateEnabled = (bool) AppSetting::getValue('queue.qr_auto_regenerate_enabled', true);
    }

    private function syncCurrentSessionDailyQuotas(bool $enabled, int $limit): void
    {
        if (! $enabled) {
            return;
        }

        $runtime = app(QueueRuntimeService::class);
        $session = $runtime->currentSession();

        QueueService::query()
            ->where('is_active', true)
            ->get()
            ->each(function (QueueService $service) use ($session, $limit, $runtime): void {
                $quota = ServiceDailyQuota::query()->firstOrNew([
                    'queue_session_id' => $session->id,
                    'queue_service_id' => $service->id,
                ]);

                $quota->max_daily_quota = $limit;
                $quota->is_open = $quota->exists ? $quota->is_open : true;
                $quota->save();

                $runtime->ensureAllocations($service, $session);
            });
    }

    private function notify(string $type, string $message): void
    {
        $this->dispatch('settings-notify', type: $type, message: $message);
    }

    public function render()
    {
        return view('livewire.pages.application-settings', [
            'timezoneOptions' => AppClock::timezoneOptions(),
        ]);
    }
}
