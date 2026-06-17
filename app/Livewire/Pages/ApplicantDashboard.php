<?php

namespace App\Livewire\Pages;

use App\Models\QueueService;
use App\Models\QueueServiceDependency;
use App\Models\QueueSession;
use App\Models\QueueTicket;
use App\Models\AppSetting;
use App\Services\QueueRuntimeService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard Antrian')]
class ApplicantDashboard extends Component
{
    private const FALLBACK_ESTIMATE_MINUTES = 10;

    private const ACTIVE_STATUS_PRIORITY = [
        QueueTicket::STATUS_IN_PROGRESS => 1,
        QueueTicket::STATUS_CALLED => 2,
        QueueTicket::STATUS_WAITING => 3,
        QueueTicket::STATUS_NO_SHOW => 4,
    ];

    private const BLOCKING_QUEUE_STATUSES = [
        QueueTicket::STATUS_WAITING,
        QueueTicket::STATUS_CALLED,
        QueueTicket::STATUS_IN_PROGRESS,
        QueueTicket::STATUS_NO_SHOW,
    ];

    public ?int $selectedServiceId = null;
    public ?int $withdrawingTicketId = null;
    public ?int $blockedTooltipTicketId = null;
    public string $queue_code = '';
    public ?string $dashboardNotice = null;

    public function positionFor(QueueTicket $ticket): ?int
    {
        if ($ticket->status !== QueueTicket::STATUS_WAITING) {
            return null;
        }

        $query = QueueTicket::query()
            ->where('queue_service_id', $ticket->queue_service_id)
            ->whereDate('queue_date', $ticket->queue_date)
            ->where('status', QueueTicket::STATUS_WAITING)
            ->where(function ($query) use ($ticket) {
                $query->where('call_sequence', '<', $ticket->call_sequence)
                    ->orWhere(function ($query) use ($ticket) {
                        $query->where('call_sequence', $ticket->call_sequence)
                            ->where('id', '<=', $ticket->id);
                    });
            });

        if ($ticket->service_counter_id) {
            $query->where('service_counter_id', $ticket->service_counter_id);
        }

        return $query->count();
    }

    public function estimateFor(?QueueTicket $ticket, ?int $position): string
    {
        if (! $ticket) {
            return '-';
        }

        return match ($ticket->status) {
            QueueTicket::STATUS_IN_PROGRESS, QueueTicket::STATUS_CALLED => 'Sekarang',
            QueueTicket::STATUS_WAITING => $position
                ? ($this->estimatedMinutesForTicket($ticket) * max(1, $position)) . ' menit'
                : $this->estimatedMinutesForTicket($ticket) . ' menit',
            QueueTicket::STATUS_NO_SHOW => 'Lapor',
            default => '-',
        };
    }

    public function ticketBlocksQueue(QueueTicket $ticket): bool
    {
        return in_array($ticket->status, self::BLOCKING_QUEUE_STATUSES, true);
    }

    public function estimatedMinutesForTicket(QueueTicket $ticket): int
    {
        $completedTickets = QueueTicket::query()
            ->where('queue_service_id', $ticket->queue_service_id)
            ->whereDate('queue_date', $ticket->queue_date)
            ->where('status', QueueTicket::STATUS_COMPLETED)
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get(['started_at', 'completed_at']);

        $durations = $completedTickets
            ->map(function (QueueTicket $completedTicket): ?int {
                $seconds = $completedTicket->started_at?->diffInSeconds($completedTicket->completed_at, false);

                return $seconds && $seconds > 0 ? $seconds : null;
            })
            ->filter()
            ->values();

        if ($durations->isEmpty()) {
            return $this->defaultEstimateMinutes();
        }

        return max(1, (int) ceil($durations->avg() / 60));
    }

    private function defaultEstimateMinutes(): int
    {
        $value = AppSetting::getValue('queue.default_service_minutes', self::FALLBACK_ESTIMATE_MINUTES);

        return max(1, (int) $value);
    }

    private function serviceUnavailableMessage(QueueService $service): ?string
    {
        $queueRuntime = app(QueueRuntimeService::class);
        $currentSession = $queueRuntime->currentSession();
        $applicant = auth()->user()?->applicant()->first();
        $quota = $queueRuntime->quotaStatus($service, $currentSession);

        if ($quota['is_full'] ?? false) {
            return 'Antrian layanan ' . $service->name . ' sudah penuh untuk hari ini. Registrasi Anda tetap berhasil tersimpan. Silakan hubungi petugas atau kembali pada jadwal layanan berikutnya.';
        }

        if ($applicant && $dependencyMessage = $queueRuntime->dependencyError($applicant, $service, $currentSession)) {
            return $dependencyMessage;
        }

        return null;
    }

    public function openQueueScanner(int $serviceId): void
    {
        $service = QueueService::find($serviceId);
        $queueRuntime = app(QueueRuntimeService::class);
        $currentSession = $queueRuntime->currentSession();
        $applicant = auth()->user()?->applicant()->first();

        if (! $service) {
            $this->dashboardNotice = 'Layanan tidak ditemukan atau sudah tidak aktif.';
            $this->selectedServiceId = null;
            $this->dispatch('queue-scanner-stop');

            return;
        }

        if ($applicant && $activeTicket = $this->activeBlockingTicket($applicant->id, $currentSession, $service->id)) {
            $this->showActiveQueueMessage($activeTicket->id);
            $this->selectedServiceId = null;
            $this->dispatch('queue-scanner-stop');

            return;
        }

        if ($message = $this->serviceUnavailableMessage($service)) {
            $this->dashboardNotice = $message;
            $this->selectedServiceId = null;
            $this->dispatch('queue-scanner-stop');

            return;
        }

        $this->dashboardNotice = null;
        $this->blockedTooltipTicketId = null;
        $this->selectedServiceId = $service->id;
        $this->queue_code = '';
        $this->resetErrorBag('queue_code');
    }

    public function showActiveQueueMessage(int $ticketId): void
    {
        $ticket = $this->ownedTicketQuery()
            ->with('service')
            ->whereKey($ticketId)
            ->first();

        $this->blockedTooltipTicketId = $ticket?->id;
        $this->selectedServiceId = null;
        $this->dispatch('queue-scanner-stop');
        $this->dashboardNotice = $this->activeBlockingMessageFor($ticket);
    }

    public function openWithdrawQueueModal(int $ticketId): void
    {
        $ticket = $this->ownedTicketQuery()
            ->with('service')
            ->whereKey($ticketId)
            ->where('status', QueueTicket::STATUS_WAITING)
            ->first();

        if (! $ticket) {
            $this->dashboardNotice = 'Antrian ini tidak bisa dicabut. Silakan hubungi petugas jika nomor sudah dipanggil atau sedang diproses.';

            return;
        }

        $this->withdrawingTicketId = $ticket->id;
    }

    public function closeWithdrawQueueModal(): void
    {
        $this->withdrawingTicketId = null;
    }

    public function withdrawQueue(): void
    {
        $ticket = $this->withdrawingTicketId
            ? $this->ownedTicketQuery()->whereKey($this->withdrawingTicketId)->first()
            : null;

        if (! $ticket || $ticket->status !== QueueTicket::STATUS_WAITING) {
            $this->dashboardNotice = 'Antrian ini tidak bisa dicabut. Silakan hubungi petugas jika nomor sudah dipanggil atau sedang diproses.';
            $this->withdrawingTicketId = null;

            return;
        }

        $ticket->update([
            'status' => QueueTicket::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'notes' => $this->appendTicketNote($ticket, 'Antrian dicabut oleh pendaftar pada ' . now()->format('H:i') . '.'),
        ]);

        $this->dashboardNotice = 'Antrian ' . $ticket->ticket_code . ' berhasil dicabut. Jika mengambil antrian ulang, nomor baru akan masuk di urutan terakhir.';
        $this->withdrawingTicketId = null;
        $this->blockedTooltipTicketId = null;
    }

    public function showServiceUnavailableMessage(int $serviceId): void
    {
        $service = QueueService::find($serviceId);

        $this->dashboardNotice = $service
            ? ($this->serviceUnavailableMessage($service) ?: 'Layanan masih tersedia. Silakan tekan Ambil Antrian.')
            : 'Layanan tidak ditemukan atau sudah tidak aktif.';
        $this->selectedServiceId = null;
        $this->dispatch('queue-scanner-stop');
    }

    public function closeQueueScanner(): void
    {
        $this->selectedServiceId = null;
        $this->withdrawingTicketId = null;
        $this->blockedTooltipTicketId = null;
        $this->queue_code = '';
        $this->resetErrorBag('queue_code');
        $this->dispatch('queue-scanner-stop');
    }

    public function claimSelectedService()
    {
        $this->validate([
            'queue_code' => ['required', 'string', 'max:255'],
        ], [
            'queue_code.required' => 'Scan QR atau isi kode ambil antrian terlebih dahulu.',
            'queue_code.max' => 'Kode ambil antrian tidak valid.',
        ]);

        $service = $this->selectedServiceId ? QueueService::find($this->selectedServiceId) : null;

        if (! $service) {
            $this->addError('queue_code', 'Pilih layanan terlebih dahulu.');

            return null;
        }

        [$success, $message] = app(QueueRuntimeService::class)
            ->takeQueueWithCredential(auth()->user(), $service, $this->queue_code);

        if (! $success) {
            $this->addError('queue_code', $message);

            return null;
        }

        session()->flash('status', $message);

        return $this->redirectRoute('dashboard');
    }

    public function claimScannedCredential(string $credential)
    {
        $this->queue_code = trim($credential);

        return $this->claimSelectedService();
    }

    public function render()
    {
        $queueRuntime = app(QueueRuntimeService::class);
        $currentSession = $queueRuntime->currentSession();
        $applicant = auth()->user()?->applicant()->first();
        $services = QueueService::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $allTickets = $applicant
            ? $applicant->queueTickets()
                ->with(['service', 'counter'])
                ->latest()
                ->limit(20)
                ->get()
            : new Collection();

        $tickets = $allTickets
            ->filter(fn (QueueTicket $ticket): bool => $this->ticketBelongsToSession($ticket, $currentSession))
            ->values();

        $activeTicket = $tickets
            ->filter(fn (QueueTicket $ticket): bool => array_key_exists($ticket->status, self::ACTIVE_STATUS_PRIORITY))
            ->sortBy(fn (QueueTicket $ticket): string => sprintf(
                '%02d-%012.3f-%012d',
                self::ACTIVE_STATUS_PRIORITY[$ticket->status],
                $ticket->call_sequence ?? PHP_INT_MAX,
                $ticket->id,
            ))
            ->first();

        $activePosition = $activeTicket ? $this->positionFor($activeTicket) : null;

        $queueLogs = $allTickets->take(5);
        $checkin = $applicant ? $queueRuntime->checkinFor($applicant, $currentSession) : null;
        $activeBlockingTicket = $applicant ? $this->activeBlockingTicket($applicant->id, $currentSession) : null;
        $serviceStatuses = $services->mapWithKeys(function (QueueService $service) use ($queueRuntime, $currentSession, $applicant, $activeBlockingTicket) {
            $quota = $queueRuntime->quotaStatus($service, $currentSession);
            $dependencyError = $applicant ? $queueRuntime->dependencyError($applicant, $service, $currentSession) : null;
            $blockedByActiveTicket = $activeBlockingTicket && (int) $activeBlockingTicket->queue_service_id !== (int) $service->id;

            return [
                $service->id => [
                    'quota' => $quota,
                    'dependency_error' => $dependencyError,
                    'active_blocking_ticket' => $blockedByActiveTicket ? $activeBlockingTicket : null,
                    'active_blocking_message' => $blockedByActiveTicket
                        ? $this->activeBlockingMessageFor($activeBlockingTicket)
                        : null,
                    'can_queue' => ! $quota['is_full'] && ! $dependencyError && ! $blockedByActiveTicket,
                ],
            ];
        });
        $services = $this->sortServicesForDashboard($services, $serviceStatuses, $tickets, $currentSession);
        $selectedService = $this->selectedServiceId
            ? $services->firstWhere('id', $this->selectedServiceId)
            : null;

        return view('livewire.pages.applicant-dashboard', [
            'currentSession' => $currentSession,
            'applicant' => $applicant,
            'services' => $services,
            'tickets' => $tickets,
            'checkin' => $checkin,
            'serviceStatuses' => $serviceStatuses,
            'selectedService' => $selectedService,
            'activeTicket' => $activeTicket,
            'activePosition' => $activePosition,
            'activeEstimate' => $this->estimateFor($activeTicket, $activePosition),
            'withdrawTicket' => $this->withdrawingTicketId
                ? $this->ownedTicketQuery()->with(['service', 'counter'])->whereKey($this->withdrawingTicketId)->first()
                : null,
            'queueLogs' => $queueLogs,
        ]);
    }

    private function sortServicesForDashboard(Collection $services, Collection $serviceStatuses, Collection $tickets, QueueSession $currentSession): Collection
    {
        $serviceIds = $services->pluck('id')->map(fn ($id): int => (int) $id)->all();

        $dependencies = QueueServiceDependency::query()
            ->whereIn('queue_service_id', $serviceIds)
            ->where('is_active', true)
            ->where(function ($query) use ($currentSession) {
                $query->whereNull('queue_session_id')
                    ->orWhere('queue_session_id', $currentSession->id);
            })
            ->get(['queue_service_id', 'required_queue_service_id']);

        $requiredIdsByService = $dependencies
            ->groupBy('queue_service_id')
            ->map(fn (Collection $items): array => $items
                ->pluck('required_queue_service_id')
                ->filter()
                ->map(fn ($id): int => (int) $id)
                ->values()
                ->all())
            ->all();

        $blockedServiceIds = $serviceStatuses
            ->filter(fn (array $status): bool => filled($status['dependency_error'] ?? null))
            ->keys()
            ->map(fn ($id): int => (int) $id)
            ->all();

        $requiredByBlockedIds = collect($blockedServiceIds)
            ->flatMap(fn (int $serviceId): array => $this->collectRequiredServiceIds($serviceId, $requiredIdsByService))
            ->unique()
            ->values();

        $pastTicketServiceIds = $tickets
            ->reject(fn (QueueTicket $ticket): bool => $ticket->status === QueueTicket::STATUS_CANCELLED)
            ->pluck('queue_service_id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $activeTicketServiceIds = $tickets
            ->whereIn('status', self::BLOCKING_QUEUE_STATUSES)
            ->pluck('queue_service_id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $depthCache = [];

        return $services
            ->sortBy(function (QueueService $service) use ($serviceStatuses, $pastTicketServiceIds, $activeTicketServiceIds, $requiredByBlockedIds, $requiredIdsByService, &$depthCache): string {
                $status = $serviceStatuses->get($service->id, []);
                $hasPastTicket = $pastTicketServiceIds->contains((int) $service->id);
                $hasActiveTicket = $activeTicketServiceIds->contains((int) $service->id);
                $dependencyBlocked = filled($status['dependency_error'] ?? null);
                $activeQueueBlocked = filled($status['active_blocking_ticket'] ?? null);
                $canTakeQueue = ! $hasActiveTicket && ! $dependencyBlocked && ! ($status['quota']['is_full'] ?? false);
                $isRequiredByBlocked = $requiredByBlockedIds->contains((int) $service->id);
                $quotaFull = (bool) ($status['quota']['is_full'] ?? false);

                $priority = match (true) {
                    $canTakeQueue && ! $activeQueueBlocked && ! $hasPastTicket => 0,
                    $hasActiveTicket => 1,
                    $isRequiredByBlocked => 2,
                    $dependencyBlocked || $activeQueueBlocked => 3,
                    $canTakeQueue && $hasPastTicket => 4,
                    $quotaFull => 5,
                    default => 6,
                };

                return sprintf(
                    '%02d-%04d-%06d-%s',
                    $priority,
                    $this->serviceDependencyDepth((int) $service->id, $requiredIdsByService, $depthCache),
                    (int) $service->sort_order,
                    mb_strtolower($service->name),
                );
            })
            ->values();
    }

    private function collectRequiredServiceIds(int $serviceId, array $requiredIdsByService, array $visited = []): array
    {
        if (in_array($serviceId, $visited, true)) {
            return [];
        }

        $visited[] = $serviceId;
        $requiredIds = $requiredIdsByService[$serviceId] ?? [];

        return collect($requiredIds)
            ->flatMap(fn (int $requiredId): array => [
                $requiredId,
                ...$this->collectRequiredServiceIds($requiredId, $requiredIdsByService, $visited),
            ])
            ->unique()
            ->values()
            ->all();
    }

    private function serviceDependencyDepth(int $serviceId, array $requiredIdsByService, array &$depthCache, array $visited = []): int
    {
        if (isset($depthCache[$serviceId])) {
            return $depthCache[$serviceId];
        }

        if (in_array($serviceId, $visited, true)) {
            return 0;
        }

        $visited[] = $serviceId;
        $requiredIds = $requiredIdsByService[$serviceId] ?? [];

        if ($requiredIds === []) {
            return $depthCache[$serviceId] = 0;
        }

        return $depthCache[$serviceId] = collect($requiredIds)
            ->map(fn (int $requiredId): int => $this->serviceDependencyDepth($requiredId, $requiredIdsByService, $depthCache, $visited) + 1)
            ->max() ?? 0;
    }

    private function ownedTicketQuery()
    {
        $applicant = auth()->user()?->applicant()->first();

        return QueueTicket::query()
            ->where('applicant_id', $applicant?->id ?: 0)
            ->whereDate('queue_date', app(QueueRuntimeService::class)->currentSession()->session_date);
    }

    private function activeBlockingTicket(int $applicantId, QueueSession $currentSession, ?int $exceptServiceId = null): ?QueueTicket
    {
        return QueueTicket::query()
            ->with('service')
            ->where('applicant_id', $applicantId)
            ->whereDate('queue_date', $currentSession->session_date)
            ->when($exceptServiceId, fn ($query) => $query->where('queue_service_id', '!=', $exceptServiceId))
            ->whereIn('status', self::BLOCKING_QUEUE_STATUSES)
            ->latest('assigned_at')
            ->latest('id')
            ->first();
    }

    private function ticketBelongsToSession(QueueTicket $ticket, QueueSession $session): bool
    {
        if ($ticket->queue_session_id && (int) $ticket->queue_session_id === (int) $session->id) {
            return true;
        }

        return $ticket->queue_date?->isSameDay($session->session_date) ?? false;
    }

    private function activeBlockingMessageFor(?QueueTicket $ticket): string
    {
        if (! $ticket) {
            return 'Anda masih memiliki antrian aktif. Silakan selesaikan dahulu sebelum mengambil antrian layanan lain.';
        }

        $serviceName = $ticket->service?->name ?? 'lain';

        if ($ticket->status === QueueTicket::STATUS_NO_SHOW) {
            return 'Anda masih memiliki antrian terlewat di layanan ' . $serviceName . '. Silakan hubungi petugas atau selesaikan dahulu sebelum mengambil antrian layanan lain.';
        }

        return 'Anda sedang mengantri di layanan ' . $serviceName . '. Silakan selesaikan dahulu sebelum mengambil antrian layanan lain.';
    }

    private function appendTicketNote(QueueTicket $ticket, string $note): string
    {
        return trim(($ticket->notes ? $ticket->notes . PHP_EOL : '') . $note);
    }
}
