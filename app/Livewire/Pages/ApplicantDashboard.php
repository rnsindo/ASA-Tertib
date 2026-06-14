<?php

namespace App\Livewire\Pages;

use App\Models\QueueService;
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

    public ?int $selectedServiceId = null;
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
                ? ($this->estimatedMinutesForTicket($ticket) * max(1, $position)) . 'm'
                : $this->estimatedMinutesForTicket($ticket) . 'm',
            QueueTicket::STATUS_NO_SHOW => 'Lapor',
            default => '-',
        };
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

        if (! $service) {
            $this->dashboardNotice = 'Layanan tidak ditemukan atau sudah tidak aktif.';
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
        $this->selectedServiceId = $service->id;
        $this->queue_code = '';
        $this->resetErrorBag('queue_code');
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

        $tickets = $applicant
            ? $applicant->queueTickets()
                ->with(['service', 'counter'])
                ->latest()
                ->limit(20)
                ->get()
            : new Collection();

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

        $queueLogs = $tickets->take(5);
        $checkin = $applicant ? $queueRuntime->checkinFor($applicant, $currentSession) : null;
        $serviceStatuses = $services->mapWithKeys(function (QueueService $service) use ($queueRuntime, $currentSession, $applicant, $checkin) {
            $quota = $queueRuntime->quotaStatus($service, $currentSession);
            $dependencyError = $applicant ? $queueRuntime->dependencyError($applicant, $service, $currentSession) : null;

            return [
                $service->id => [
                    'quota' => $quota,
                    'dependency_error' => $dependencyError,
                    'can_queue' => ! $quota['is_full'] && ! $dependencyError,
                ],
            ];
        });
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
            'queueLogs' => $queueLogs,
        ]);
    }
}
