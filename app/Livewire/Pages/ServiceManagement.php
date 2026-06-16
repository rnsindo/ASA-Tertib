<?php

namespace App\Livewire\Pages;

use App\Models\QueueService;
use App\Models\QueueServiceDependency;
use App\Models\ServiceCounter;
use App\Models\ServiceDailyQuota;
use App\Models\AppSetting;
use App\Models\User;
use App\Services\QueueRuntimeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Layanan')]
class ServiceManagement extends Component
{
    public string $serviceName = '';
    public string $serviceDescription = '';
    public string $serviceSortOrder = '0';
    public bool $serviceEnforceCallOrder = true;
    public bool $serviceIsActive = true;
    public bool $serviceRequiresPrevious = false;
    public string $requiredServiceId = '';
    public string $requiredStatusMode = QueueServiceDependency::MODE_COMPLETED;
    public bool $isServiceModalOpen = false;
    public ?int $editingServiceId = null;
    public string $editingServiceCode = '';
    public ?int $selectedServiceId = null;
    public string $counterName = '';
    public string $counterOfficerId = '';
    public bool $counterIsActive = true;
    public bool $isCounterModalOpen = false;
    public ?int $editingCounterId = null;
    public string $editingCounterCode = '';

    public function mount(): void
    {
        $user = auth()->user();

        abort_unless(
            auth()->check()
            && ($user->can('admin.manajemen_layanan') || $user->hasAnyRole(['superadmin', 'Super Admin'])),
            403,
        );
    }

    public function createService(QueueRuntimeService $runtime): void
    {
        $this->authorizeServiceManagement();

        $validated = $this->validateServicePayload();

        try {
            $service = DB::transaction(function () use ($validated, $runtime): QueueService {
                $service = QueueService::query()->create([
                    'name' => trim($validated['serviceName']),
                    'slug' => $this->uniqueServiceSlug($validated['serviceName']),
                    'code' => $this->uniqueServiceCode($validated['serviceName']),
                    'description' => trim((string) $validated['serviceDescription']) ?: null,
                    'sort_order' => (int) $validated['serviceSortOrder'],
                    'enforce_call_order' => (bool) $validated['serviceEnforceCallOrder'],
                    'is_active' => (bool) $validated['serviceIsActive'],
                ]);

                ServiceDailyQuota::query()->firstOrCreate(
                    [
                        'queue_session_id' => $runtime->currentSession()->id,
                        'queue_service_id' => $service->id,
                    ],
                    [
                        'max_daily_quota' => max(1, (int) AppSetting::getValue('queue.daily_quota_limit', 200)),
                        'is_open' => true,
                    ],
                );

                $this->saveServiceDependency($service, $validated);

                return $service;
            });

            $this->resetServiceForm();
            $this->isServiceModalOpen = false;
            $this->selectedServiceId = $service->id;
            $this->notify('success', 'Layanan berhasil ditambahkan.');
        } catch (\Throwable $exception) {
            $this->notify('error', 'Layanan gagal ditambahkan. Alasan: ' . $exception->getMessage());
        }
    }

    public function updateService(): void
    {
        $this->authorizeServiceManagement();

        $service = $this->editingServiceId
            ? QueueService::query()->findOrFail($this->editingServiceId)
            : null;

        if (! $service) {
            $this->notify('error', 'Layanan yang akan diedit tidak ditemukan.');

            return;
        }

        $validated = $this->validateServicePayload($service->id);

        try {
            DB::transaction(function () use ($service, $validated): void {
                $service->forceFill([
                    'name' => trim($validated['serviceName']),
                    'description' => trim((string) $validated['serviceDescription']) ?: null,
                    'sort_order' => (int) $validated['serviceSortOrder'],
                    'enforce_call_order' => (bool) $validated['serviceEnforceCallOrder'],
                    'is_active' => (bool) $validated['serviceIsActive'],
                ])->save();

                $this->saveServiceDependency($service, $validated);
            });

            $this->selectedServiceId = $service->id;
            $this->isServiceModalOpen = false;
            $this->resetServiceForm();
            $this->notify('success', 'Layanan berhasil diperbarui.');
        } catch (\Throwable $exception) {
            $this->notify('error', 'Layanan gagal diperbarui. Alasan: ' . $exception->getMessage());
        }
    }

    public function openServiceModal(): void
    {
        $this->authorizeServiceManagement();
        $this->resetServiceForm();
        $this->serviceSortOrder = (string) $this->nextServiceSortOrder();
        $this->isServiceModalOpen = true;
    }

    public function openEditServiceModal(int $serviceId): void
    {
        $this->authorizeServiceManagement();
        $service = QueueService::query()
            ->with(['dependencies.requiredService'])
            ->findOrFail($serviceId);
        $dependency = $this->globalDependencyFor($service);

        $this->editingServiceId = $service->id;
        $this->editingServiceCode = $service->code;
        $this->serviceName = $service->name;
        $this->serviceDescription = (string) $service->description;
        $this->serviceSortOrder = (string) $service->sort_order;
        $this->serviceEnforceCallOrder = (bool) $service->enforce_call_order;
        $this->serviceIsActive = (bool) $service->is_active;
        $this->serviceRequiresPrevious = (bool) $dependency;
        $this->requiredServiceId = $dependency ? (string) $dependency->required_queue_service_id : '';
        $this->requiredStatusMode = $dependency?->required_status_mode ?: QueueServiceDependency::MODE_COMPLETED;
        $this->isServiceModalOpen = true;
        $this->resetErrorBag(['serviceName', 'serviceDescription', 'serviceSortOrder', 'requiredServiceId', 'requiredStatusMode']);
    }

    public function closeServiceModal(): void
    {
        $this->isServiceModalOpen = false;
        $this->resetServiceForm();
    }

    public function openCounters(int $serviceId): void
    {
        $this->authorizeServiceManagement();
        QueueService::query()->findOrFail($serviceId);

        $this->selectedServiceId = $serviceId;
        $this->resetCounterForm();
    }

    public function closeCounters(): void
    {
        $this->selectedServiceId = null;
        $this->resetCounterForm();
    }

    public function openCounterModal(): void
    {
        $this->authorizeServiceManagement();

        if (! $this->selectedService()) {
            $this->notify('error', 'Loket gagal ditambahkan. Alasan: pilih layanan terlebih dahulu.');

            return;
        }

        $this->resetCounterForm();
        $this->isCounterModalOpen = true;
    }

    public function openEditCounterModal(int $counterId): void
    {
        $this->authorizeServiceManagement();
        $counter = ServiceCounter::query()->findOrFail($counterId);

        $this->editingCounterId = $counter->id;
        $this->editingCounterCode = $counter->code;
        $this->counterName = $counter->name;
        $this->counterOfficerId = $counter->assigned_user_id ? (string) $counter->assigned_user_id : '';
        $this->counterIsActive = (bool) $counter->is_active;
        $this->selectedServiceId = $counter->queue_service_id;
        $this->isCounterModalOpen = true;
        $this->resetErrorBag(['counterName', 'counterOfficerId']);
    }

    public function closeCounterModal(): void
    {
        $this->isCounterModalOpen = false;
        $this->resetCounterForm();
    }

    public function addCounter(QueueRuntimeService $runtime): void
    {
        $this->authorizeServiceManagement();
        $service = $this->selectedService();

        if (! $service) {
            $this->notify('error', 'Loket gagal ditambahkan. Alasan: pilih layanan terlebih dahulu.');

            return;
        }

        $validated = $this->validateCounterPayload('ditambahkan');

        try {
            ServiceCounter::query()->create([
                'queue_service_id' => $service->id,
                'assigned_user_id' => $validated['counterOfficerId'] !== '' ? (int) $validated['counterOfficerId'] : null,
                'name' => trim($validated['counterName']),
                'code' => $this->uniqueCounterCode($validated['counterName']),
                'sort_order' => ((int) $service->counters()->max('sort_order')) + 1,
                'is_active' => (bool) $validated['counterIsActive'],
            ]);

            $runtime->ensureAllocations($service->refresh());
            $this->resetCounterForm();
            $this->isCounterModalOpen = false;
            $this->notify('success', 'Loket berhasil ditambahkan.');
        } catch (\Throwable $exception) {
            $this->notify('error', 'Loket gagal ditambahkan. Alasan: ' . $exception->getMessage());
        }
    }

    public function updateCounter(QueueRuntimeService $runtime): void
    {
        $this->authorizeServiceManagement();
        $counter = $this->editingCounterId
            ? ServiceCounter::query()->with('service')->findOrFail($this->editingCounterId)
            : null;

        if (! $counter) {
            $this->notify('error', 'Loket gagal diperbarui. Alasan: data loket tidak ditemukan.');

            return;
        }

        $validated = $this->validateCounterPayload('diperbarui');

        try {
            $counter->forceFill([
                'assigned_user_id' => $validated['counterOfficerId'] !== '' ? (int) $validated['counterOfficerId'] : null,
                'name' => trim($validated['counterName']),
                'is_active' => (bool) $validated['counterIsActive'],
            ])->save();

            if ($counter->service) {
                $runtime->ensureAllocations($counter->service);
            }

            $this->selectedServiceId = $counter->queue_service_id;
            $this->resetCounterForm();
            $this->isCounterModalOpen = false;
            $this->notify('success', 'Loket berhasil diperbarui.');
        } catch (\Throwable $exception) {
            $this->notify('error', 'Loket gagal diperbarui. Alasan: ' . $exception->getMessage());
        }
    }

    public function toggleService(int $serviceId): void
    {
        $this->authorizeServiceManagement();
        $service = QueueService::query()->findOrFail($serviceId);
        $service->forceFill(['is_active' => ! $service->is_active])->save();

        $this->notify('success', 'Status layanan berhasil diperbarui.');
    }

    public function toggleCounter(int $counterId, QueueRuntimeService $runtime): void
    {
        $this->authorizeServiceManagement();
        $counter = ServiceCounter::query()->with('service')->findOrFail($counterId);
        $counter->forceFill(['is_active' => ! $counter->is_active])->save();

        if ($counter->service) {
            $runtime->ensureAllocations($counter->service);
        }

        $this->notify('success', 'Status loket berhasil diperbarui.');
    }

    public function reorderServices(array $orderedServiceIds): void
    {
        $this->authorizeServiceManagement();

        $orderedIds = collect($orderedServiceIds)
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        $existingIds = QueueService::query()
            ->whereIn('id', $orderedIds)
            ->pluck('id')
            ->map(fn (int $id): int => $id)
            ->values();

        if ($orderedIds->count() === 0 || $orderedIds->count() !== $existingIds->count()) {
            $this->notify('error', 'Urutan layanan gagal disimpan. Alasan: data layanan tidak valid.');

            return;
        }

        DB::transaction(function () use ($orderedIds): void {
            $orderedIds->each(function (int $serviceId, int $index): void {
                QueueService::query()
                    ->whereKey($serviceId)
                    ->update(['sort_order' => $index + 1]);
            });
        });

        $this->selectedServiceId = null;
        $this->notify('success', 'Urutan tampil layanan berhasil disimpan.');
    }

    public function render()
    {
        $services = QueueService::query()
            ->with(['dependencies.requiredService'])
            ->withCount([
                'counters',
                'counters as active_counters_count' => fn ($query) => $query->where('is_active', true),
                'tickets',
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('livewire.pages.service-management', [
            'services' => $services,
            'selectedService' => $this->selectedService(),
            'officerUsers' => $this->officerUsers(),
            'serviceOptions' => QueueService::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'code']),
            'dependencyModes' => $this->dependencyModeLabels(),
        ]);
    }

    private function selectedService(): ?QueueService
    {
        if (! $this->selectedServiceId) {
            return null;
        }

        return QueueService::query()
            ->with([
                'counters' => fn ($query) => $query->with('assignedOfficer')->orderBy('sort_order')->orderBy('id'),
                'dependencies.requiredService',
            ])
            ->withCount([
                'counters',
                'counters as active_counters_count' => fn ($query) => $query->where('is_active', true),
            ])
            ->find($this->selectedServiceId);
    }

    private function resetServiceForm(): void
    {
        $this->serviceName = '';
        $this->serviceDescription = '';
        $this->serviceSortOrder = '0';
        $this->serviceEnforceCallOrder = true;
        $this->serviceIsActive = true;
        $this->serviceRequiresPrevious = false;
        $this->requiredServiceId = '';
        $this->requiredStatusMode = QueueServiceDependency::MODE_COMPLETED;
        $this->editingServiceId = null;
        $this->editingServiceCode = '';
        $this->resetErrorBag(['serviceName', 'serviceDescription', 'serviceSortOrder', 'requiredServiceId', 'requiredStatusMode']);
    }

    private function resetCounterForm(): void
    {
        $this->counterName = '';
        $this->counterOfficerId = '';
        $this->counterIsActive = true;
        $this->editingCounterId = null;
        $this->editingCounterCode = '';
        $this->resetErrorBag(['counterName', 'counterOfficerId']);
    }

    private function uniqueServiceSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'layanan';
        $slug = $base;
        $counter = 2;

        while (QueueService::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function uniqueServiceCode(string $name): string
    {
        $base = $this->serviceInitials($name);
        $code = $base;
        $counter = 2;

        while (QueueService::query()->where('code', $code)->exists()) {
            $code = $base . $counter;
            $counter++;
        }

        return $code;
    }

    private function serviceInitials(string $name): string
    {
        $words = Str::of($name)
            ->replaceMatches('/[^A-Za-z0-9\s]+/', ' ')
            ->squish()
            ->explode(' ')
            ->filter(fn (string $word): bool => preg_match('/[A-Za-z]/', $word) === 1)
            ->values();

        if ($words->isEmpty()) {
            return 'LYN';
        }

        $initials = $words
            ->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');

        if (strlen($initials) === 1) {
            $initials = Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $name) ?: 'LYN', 0, 3));
        }

        return Str::substr($initials ?: 'LYN', 0, 10);
    }

    private function validateCounterPayload(string $actionLabel): array
    {
        try {
            return $this->validate([
                'counterName' => ['required', 'string', 'max:120'],
                'counterOfficerId' => ['nullable', Rule::in($this->officerUserIds())],
                'counterIsActive' => ['boolean'],
            ], [
                'counterOfficerId.in' => 'Petugas loket yang dipilih tidak valid.',
            ], [
                'counterName' => 'nama loket',
                'counterOfficerId' => 'petugas loket',
            ]);
        } catch (ValidationException $exception) {
            $message = collect($exception->validator->errors()->all())->first()
                ?: 'data loket belum lengkap.';
            $this->notify('error', 'Loket gagal ' . $actionLabel . '. Alasan: ' . $message);

            throw $exception;
        }
    }

    private function validateServicePayload(?int $currentServiceId = null): array
    {
        $requiredServiceRules = ['nullable', 'integer', 'exists:queue_services,id'];

        if ($this->serviceRequiresPrevious) {
            $requiredServiceRules = ['required', 'integer', 'exists:queue_services,id'];
        }

        if ($currentServiceId) {
            $requiredServiceRules[] = Rule::notIn([$currentServiceId]);
        }

        $validated = $this->validate([
            'serviceName' => ['required', 'string', 'max:120'],
            'serviceDescription' => ['nullable', 'string', 'max:500'],
            'serviceSortOrder' => ['required', 'integer', 'min:0', 'max:65535'],
            'serviceEnforceCallOrder' => ['boolean'],
            'serviceIsActive' => ['boolean'],
            'serviceRequiresPrevious' => ['boolean'],
            'requiredServiceId' => $requiredServiceRules,
            'requiredStatusMode' => ['required', Rule::in(QueueServiceDependency::MODES)],
        ], [
            'requiredServiceId.required' => 'Pilih layanan prasyarat terlebih dahulu.',
            'requiredServiceId.not_in' => 'Layanan tidak boleh menjadikan dirinya sendiri sebagai prasyarat.',
        ], [
            'serviceName' => 'nama layanan',
            'serviceDescription' => 'deskripsi layanan',
            'serviceSortOrder' => 'urutan tampil pendaftar',
            'serviceEnforceCallOrder' => 'wajib panggil berurutan',
            'serviceRequiresPrevious' => 'syarat layanan sebelumnya',
            'requiredServiceId' => 'layanan prasyarat',
            'requiredStatusMode' => 'status prasyarat',
        ]);

        $validated['serviceRequiresPrevious'] = (bool) ($validated['serviceRequiresPrevious'] ?? false);

        if (! $validated['serviceRequiresPrevious']) {
            $validated['requiredServiceId'] = null;
            $validated['requiredStatusMode'] = QueueServiceDependency::MODE_COMPLETED;

            return $validated;
        }

        $validated['requiredServiceId'] = (int) $validated['requiredServiceId'];

        if (
            $currentServiceId
            && $this->wouldCreateDependencyCycle($currentServiceId, $validated['requiredServiceId'])
        ) {
            throw ValidationException::withMessages([
                'requiredServiceId' => 'Layanan prasyarat ini membuat alur layanan berputar. Pilih layanan lain.',
            ]);
        }

        return $validated;
    }

    private function nextServiceSortOrder(): int
    {
        return ((int) QueueService::query()->max('sort_order')) + 1;
    }

    private function saveServiceDependency(QueueService $service, array $validated): void
    {
        QueueServiceDependency::query()
            ->whereNull('queue_session_id')
            ->where('queue_service_id', $service->id)
            ->delete();

        if (! ($validated['serviceRequiresPrevious'] ?? false)) {
            return;
        }

        QueueServiceDependency::query()->create([
            'queue_session_id' => null,
            'queue_service_id' => $service->id,
            'required_queue_service_id' => (int) $validated['requiredServiceId'],
            'required_status_mode' => $validated['requiredStatusMode'],
            'is_active' => true,
        ]);
    }

    private function globalDependencyFor(QueueService $service): ?QueueServiceDependency
    {
        if ($service->relationLoaded('dependencies')) {
            return $service->dependencies
                ->whereNull('queue_session_id')
                ->where('is_active', true)
                ->first();
        }

        return $service->dependencies()
            ->with('requiredService')
            ->whereNull('queue_session_id')
            ->where('is_active', true)
            ->first();
    }

    private function wouldCreateDependencyCycle(int $serviceId, int $requiredServiceId): bool
    {
        $visited = [];
        $queue = [$requiredServiceId];

        while ($queue !== []) {
            $currentId = array_shift($queue);

            if ($currentId === $serviceId) {
                return true;
            }

            if (isset($visited[$currentId])) {
                continue;
            }

            $visited[$currentId] = true;

            QueueServiceDependency::query()
                ->whereNull('queue_session_id')
                ->where('is_active', true)
                ->where('queue_service_id', $currentId)
                ->pluck('required_queue_service_id')
                ->each(function (int $nextId) use (&$queue): void {
                    $queue[] = $nextId;
                });
        }

        return false;
    }

    private function dependencyModeLabels(): array
    {
        return [
            QueueServiceDependency::MODE_QUEUED => 'sudah masuk antrian',
            QueueServiceDependency::MODE_CALLED => 'nomornya sudah dipanggil',
            QueueServiceDependency::MODE_IN_PROGRESS => 'sedang atau sudah diproses',
            QueueServiceDependency::MODE_COMPLETED => 'sudah selesai',
        ];
    }

    private function uniqueCounterCode(string $name): string
    {
        $base = $this->counterInitials($name);
        $code = $base;
        $counter = 2;

        while (ServiceCounter::query()->where('code', $code)->exists()) {
            $code = $base . $counter;
            $counter++;
        }

        return $code;
    }

    private function counterInitials(string $name): string
    {
        $words = Str::of($name)
            ->replaceMatches('/[^A-Za-z0-9\s]+/', ' ')
            ->squish()
            ->explode(' ')
            ->filter(fn (string $word): bool => preg_match('/[A-Za-z]/', $word) === 1)
            ->values();

        if ($words->isEmpty()) {
            return 'LKT';
        }

        $initials = $words
            ->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');

        if (strlen($initials) === 1) {
            $initials = Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $name) ?: 'LKT', 0, 3));
        }

        return Str::substr($initials ?: 'LKT', 0, 18);
    }

    private function officerUserIds(): array
    {
        return $this->officerUserQuery()
            ->pluck('id')
            ->map(fn (int $id): string => (string) $id)
            ->all();
    }

    private function officerUsers()
    {
        return $this->officerUserQuery()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function officerUserQuery()
    {
        return User::query()
            ->where(function ($query): void {
                $query->whereHas('roles', function ($query): void {
                    $query->whereIn('name', ['Petugas', 'officer']);
                })
                    ->orWhereHas('roles.permissions', function ($query): void {
                        $query->where('name', 'petugas.konsol_antrian');
                    })
                    ->orWhereHas('permissions', function ($query): void {
                        $query->where('name', 'petugas.konsol_antrian');
                    });
            });
    }

    private function authorizeServiceManagement(): void
    {
        $user = auth()->user();

        abort_unless(
            $user && ($user->can('admin.manajemen_layanan') || $user->hasAnyRole(['superadmin', 'Super Admin'])),
            403,
        );
    }

    private function notify(string $type, string $message): void
    {
        $this->dispatch('service-notify', type: $type, message: $message);
    }
}
