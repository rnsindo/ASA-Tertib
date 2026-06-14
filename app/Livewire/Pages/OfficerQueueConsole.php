<?php

namespace App\Livewire\Pages;

use App\Models\Applicant;
use App\Models\AttendanceCheckin;
use App\Models\QueueService;
use App\Models\QueueSessionQrCode;
use App\Models\QueueTicket;
use App\Models\ServiceCounter;
use App\Services\QueueRuntimeService;
use App\Support\AppClock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Konsol Petugas')]
class OfficerQueueConsole extends Component
{
    private const CALL_SEQUENCE_STEP = 1000;
    private const REQUEUE_AFTER_WAITING_COUNT = 2;
    private const APPLICANT_BATCH_SIZE = 5;
    private const APPLICANT_ROLE_NAMES = ['Pelanggan/Penanya', 'Pengguna', 'applicant', 'Pendaftar', 'Pelanggan'];
    private const ACTIVE_QUEUE_STATUSES = [
        QueueTicket::STATUS_WAITING,
        QueueTicket::STATUS_CALLED,
        QueueTicket::STATUS_IN_PROGRESS,
    ];

    public ?int $selectedCounterId = null;
    public ?int $transferTargetCounterId = null;
    public ?int $transferTicketId = null;
    public ?int $assigningApplicantId = null;
    public ?int $assigningServiceId = null;
    public string $search = '';
    public int $visibleApplicantCount = self::APPLICANT_BATCH_SIZE;
    public string $notes = '';
    public ?string $generatedCheckInUrl = null;
    public ?string $generatedCheckInCode = null;
    public ?string $generatedCheckInExpiresAt = null;

    public function mount(): void
    {
        $user = auth()->user();

        abort_unless(
            auth()->check()
            && ($user->can('petugas.konsol_antrian') || $user->hasAnyRole(['superadmin', 'admin', 'officer', 'Super Admin', 'Petugas'])),
            403,
        );

        $this->selectedCounterId = $this->accessibleCountersQuery()
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->value('id');
    }

    public function selectCounter(int $counterId): void
    {
        $counter = $this->accessibleCountersQuery()->find($counterId);

        if (! $counter) {
            $this->addError('selectedCounterId', 'Loket ini tidak termasuk tugas Anda.');

            return;
        }

        $this->selectedCounterId = $counterId;
        $this->transferTargetCounterId = null;
        $this->transferTicketId = null;
        $this->assigningApplicantId = null;
        $this->assigningServiceId = null;
    }

    public function updatedSearch(): void
    {
        $this->visibleApplicantCount = self::APPLICANT_BATCH_SIZE;
    }

    public function updatedTransferTargetCounterId(): void
    {
        $this->resetErrorBag('transferTargetCounterId');
    }

    public function updatedAssigningServiceId(): void
    {
        $this->resetErrorBag('assigningServiceId');
    }

    public function loadMoreApplicants(): void
    {
        $this->visibleApplicantCount += self::APPLICANT_BATCH_SIZE;
    }

    public function toggleSelectedCounterStatus(QueueRuntimeService $runtime): void
    {
        $counter = $this->selectedCounter();

        if (! $counter) {
            $this->addError('selectedCounterId', 'Tidak ada loket yang bisa dibuka atau ditutup.');

            return;
        }

        $counter->forceFill(['is_active' => ! $counter->is_active])->save();
        $runtime->ensureAllocations($counter->service);

        session()->flash('status', $counter->is_active
            ? 'Loket ' . $counter->code . ' sudah dibuka.'
            : 'Loket ' . $counter->code . ' sudah ditutup.');
    }

    public function assignToSelectedCounter(int $applicantId): void
    {
        $counter = $this->selectedCounter();
        $queueRuntime = app(QueueRuntimeService::class);

        if (! $counter) {
            $this->addError('selectedCounterId', 'Pilih loket terlebih dahulu.');

            return;
        }

        if (! $counter->is_active) {
            $this->addError('selectedCounterId', 'Loket sedang ditutup. Buka loket terlebih dahulu sebelum memasukkan pendaftar.');

            return;
        }

        $applicant = Applicant::findOrFail($applicantId);

        [$canCreate, $message] = $queueRuntime->canCreateTicket($applicant, $counter->service);

        if (! $canCreate) {
            $this->addError('search', $message);

            return;
        }

        $queueRuntime->createTicket($applicant, $counter->service, $counter, auth()->user(), null, $this->notes ?: null);
        $this->notes = '';
    }

    public function openAssignServiceModal(int $applicantId): void
    {
        $applicant = Applicant::findOrFail($applicantId);
        $currentSession = app(QueueRuntimeService::class)->currentSession();

        $hasActiveTicket = QueueTicket::query()
            ->where('applicant_id', $applicant->id)
            ->whereIn('status', self::ACTIVE_QUEUE_STATUSES)
            ->where(function (Builder $query) use ($currentSession) {
                $query->where('queue_session_id', $currentSession->id)
                    ->orWhereDate('queue_date', $currentSession->session_date);
            })
            ->exists();

        if ($hasActiveTicket) {
            $this->addError('search', 'Pendaftar ini sedang berada dalam antrian aktif.');

            return;
        }

        $this->assigningApplicantId = $applicant->id;
        $this->assigningServiceId = null;
        $this->resetErrorBag('assigningServiceId');
    }

    public function closeAssignServiceModal(): void
    {
        $this->assigningApplicantId = null;
        $this->assigningServiceId = null;
        $this->resetErrorBag('assigningServiceId');
    }

    public function confirmAssignApplicantToService(): void
    {
        if (! $this->assigningApplicantId) {
            $this->addError('assigningServiceId', 'Pilih pendaftar terlebih dahulu.');

            return;
        }

        if (! $this->assigningServiceId) {
            $this->addError('assigningServiceId', 'Pilih layanan yang akan diambil pendaftar.');

            return;
        }

        $queueRuntime = app(QueueRuntimeService::class);
        $currentSession = $queueRuntime->currentSession();
        $applicant = Applicant::findOrFail($this->assigningApplicantId);
        $service = QueueService::query()
            ->where('is_active', true)
            ->find($this->assigningServiceId);

        if (! $service) {
            $this->addError('assigningServiceId', 'Layanan tidak tersedia atau sedang dinonaktifkan.');

            return;
        }

        [$canCreate, $message] = $queueRuntime->canCreateTicket($applicant, $service, $currentSession);

        if (! $canCreate) {
            $this->addError('assigningServiceId', $message);

            return;
        }

        $counter = $queueRuntime->recommendedCounter($service, null, $currentSession);

        if (! $counter) {
            $this->addError('assigningServiceId', 'Belum ada loket yang buka untuk layanan ' . $service->name . '. Buka minimal satu loket terlebih dahulu.');

            return;
        }

        $ticket = $queueRuntime->createTicket(
            $applicant,
            $service,
            $counter,
            auth()->user(),
            null,
            $this->notes ?: null,
            $currentSession,
        );

        session()->flash('status', 'Pendaftar dimasukkan ke ' . $counter->name . ' untuk layanan ' . $service->name . ' dengan nomor ' . $ticket->ticket_code . '.');

        $this->notes = '';
        $this->closeAssignServiceModal();
    }

    public function confirmApplicantPresence(int $applicantId): void
    {
        $applicant = Applicant::findOrFail($applicantId);

        app(QueueRuntimeService::class)->confirmPresenceByOfficer($applicant, auth()->user());
    }

    public function generateCheckInQr(): void
    {
        if (! $this->canManageQueueQr()) {
            $this->addError('selectedCounterId', 'Anda tidak memiliki izin untuk membuat atau mengganti QR & kode ambil antrian.');

            return;
        }

        $result = app(QueueRuntimeService::class)->createCheckInQr(auth()->user());

        $this->generatedCheckInUrl = $result['url'];
        $this->generatedCheckInCode = $result['manualCode'];
        $this->generatedCheckInExpiresAt = AppClock::format($result['qrCode']->expires_at, 'd/m/Y H:i');
    }

    public function callTicket(int $ticketId): void
    {
        $ticket = QueueTicket::with('counter')->findOrFail($ticketId);
        $this->authorizeTicketCounter($ticket);

        if ($ticket->status !== QueueTicket::STATUS_WAITING || ! $ticket->counter) {
            $this->addError('notes', 'Hanya tiket yang masih menunggu yang bisa dipanggil.');

            return;
        }

        $firstWaitingTicketId = $this->waitingTicketsForCounter($ticket->counter)->value('id');

        if ((int) $firstWaitingTicketId !== (int) $ticket->id) {
            $this->addError('notes', 'Tombol panggil hanya berlaku untuk antrian paling awal pada loket ini.');

            return;
        }

        $ticket->update([
            'status' => QueueTicket::STATUS_CALLED,
            'called_at' => now(),
            'handled_by' => auth()->id(),
        ]);
    }

    public function startTicket(int $ticketId): void
    {
        $ticket = QueueTicket::findOrFail($ticketId);
        $this->authorizeTicketCounter($ticket);

        $ticket->update([
            'status' => QueueTicket::STATUS_IN_PROGRESS,
            'started_at' => $ticket->started_at ?: now(),
            'handled_by' => auth()->id(),
        ]);
    }

    public function completeTicket(int $ticketId): void
    {
        $ticket = QueueTicket::findOrFail($ticketId);
        $this->authorizeTicketCounter($ticket);

        $ticket->update([
            'status' => QueueTicket::STATUS_COMPLETED,
            'completed_at' => now(),
            'handled_by' => auth()->id(),
        ]);
    }

    public function cancelTicket(int $ticketId): void
    {
        $ticket = QueueTicket::findOrFail($ticketId);
        $this->authorizeTicketCounter($ticket);

        $ticket->update([
            'status' => QueueTicket::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'handled_by' => auth()->id(),
        ]);
    }

    public function markNoShow(int $ticketId): void
    {
        $ticket = QueueTicket::findOrFail($ticketId);
        $this->authorizeTicketCounter($ticket);

        if (! in_array($ticket->status, [QueueTicket::STATUS_CALLED, QueueTicket::STATUS_WAITING], true)) {
            $this->addError('notes', 'Hanya tiket antrian atau dipanggil yang bisa ditandai tidak di tempat.');

            return;
        }

        $ticket->update([
            'status' => QueueTicket::STATUS_NO_SHOW,
            'no_show_at' => now(),
            'no_show_count' => ((int) $ticket->no_show_count) + 1,
            'handled_by' => auth()->id(),
            'notes' => $this->appendTicketNote($ticket, 'Tidak di tempat pada ' . now()->format('H:i') . '. Jika hadir kembali, masukkan ulang ke posisi ke-3.'),
        ]);
    }

    public function requeueNoShow(int $ticketId): void
    {
        DB::transaction(function () use ($ticketId) {
            $ticket = QueueTicket::with(['counter.service'])->lockForUpdate()->findOrFail($ticketId);
            $this->authorizeTicketCounter($ticket);

            if ($ticket->status !== QueueTicket::STATUS_NO_SHOW || ! $ticket->counter) {
                $this->addError('notes', 'Tiket ini tidak berada pada status Tidak di Tempat.');

                return;
            }

            $ticket->update([
                'status' => QueueTicket::STATUS_WAITING,
                'call_sequence' => $this->callSequenceForRequeue($ticket->counter, $ticket->id),
                'called_at' => null,
                'started_at' => null,
                'requeued_at' => now(),
                'handled_by' => auth()->id(),
                'notes' => $this->appendTicketNote($ticket, 'Masuk ulang pada ' . now()->format('H:i') . ' ke posisi ke-3 loket.'),
            ]);
        });
    }

    public function openTransferModal(int $ticketId): void
    {
        $ticket = QueueTicket::findOrFail($ticketId);
        $this->authorizeTicketCounter($ticket);

        $this->transferTicketId = $ticket->id;
        $this->transferTargetCounterId = null;
        $this->resetErrorBag('transferTargetCounterId');
    }

    public function closeTransferModal(): void
    {
        $this->transferTicketId = null;
        $this->transferTargetCounterId = null;
    }

    public function confirmTransferTicket(): void
    {
        if (! $this->transferTicketId) {
            $this->addError('transferTargetCounterId', 'Pilih tiket yang akan dipindahkan.');

            return;
        }

        $this->transferTicket($this->transferTicketId);
    }

    public function transferTicket(int $ticketId): void
    {
        $targetCounter = ServiceCounter::with('service')->find($this->transferTargetCounterId);

        if (! $targetCounter) {
            $this->addError('transferTargetCounterId', 'Pilih loket tujuan.');

            return;
        }

        $queueRuntime = app(QueueRuntimeService::class);
        $sourceTicket = QueueTicket::with('applicant')->findOrFail($ticketId);
        $this->authorizeTicketCounter($sourceTicket);

        if ((int) $targetCounter->id === (int) $sourceTicket->service_counter_id) {
            $this->addError('transferTargetCounterId', 'Loket tujuan tidak boleh sama dengan loket antrian saat ini.');

            return;
        }

        if (! $targetCounter->is_active) {
            $this->addError('transferTargetCounterId', 'Loket tujuan sedang ditutup.');

            return;
        }

        [$canCreate, $message] = $queueRuntime->canCreateTicket(
            $sourceTicket->applicant,
            $targetCounter->service,
            null,
            $sourceTicket->id,
        );

        if (! $canCreate) {
            $this->addError('transferTargetCounterId', $message);

            return;
        }

        DB::transaction(function () use ($ticketId, $targetCounter) {
            $ticket = QueueTicket::with('applicant')->lockForUpdate()->findOrFail($ticketId);

            $ticket->update([
                'status' => QueueTicket::STATUS_TRANSFERRED,
                'transferred_from_counter_id' => $ticket->service_counter_id,
                'handled_by' => auth()->id(),
                'notes' => trim(($ticket->notes ? $ticket->notes . "\n" : '') . 'Dipindahkan ke ' . $targetCounter->code),
            ]);

            app(QueueRuntimeService::class)->createTicket(
                $ticket->applicant,
                $targetCounter->service,
                $targetCounter,
                auth()->user(),
                $ticket->service_counter_id,
                $this->notes ?: null,
            );
        });

        $this->notes = '';
        $this->transferTargetCounterId = null;
        $this->transferTicketId = null;
    }

    private function nextCallSequenceForCounter(ServiceCounter $counter): float
    {
        $maxSequence = QueueTicket::query()
            ->where('service_counter_id', $counter->id)
            ->whereDate('queue_date', today())
            ->max('call_sequence');

        return ((float) ($maxSequence ?: 0)) + self::CALL_SEQUENCE_STEP;
    }

    private function callSequenceForRequeue(ServiceCounter $counter, int $ticketId): float
    {
        $waitingTickets = $this->waitingTicketsForCounter($counter)
            ->whereKeyNot($ticketId)
            ->limit(self::REQUEUE_AFTER_WAITING_COUNT + 1)
            ->get(['id', 'call_sequence', 'queue_number']);

        if ($waitingTickets->isEmpty()) {
            return $this->nextCallSequenceForCounter($counter);
        }

        if ($waitingTickets->count() < self::REQUEUE_AFTER_WAITING_COUNT) {
            return ((float) $waitingTickets->last()->call_sequence) + self::CALL_SEQUENCE_STEP;
        }

        $secondTicket = $waitingTickets[self::REQUEUE_AFTER_WAITING_COUNT - 1];
        $thirdTicket = $waitingTickets[self::REQUEUE_AFTER_WAITING_COUNT] ?? null;
        $secondSequence = (float) $secondTicket->call_sequence;

        if (! $thirdTicket) {
            return $secondSequence + self::CALL_SEQUENCE_STEP;
        }

        $thirdSequence = (float) $thirdTicket->call_sequence;

        if ($thirdSequence <= $secondSequence) {
            return $secondSequence + self::CALL_SEQUENCE_STEP;
        }

        return ($secondSequence + $thirdSequence) / 2;
    }

    private function waitingTicketsForCounter(ServiceCounter $counter): Builder
    {
        return QueueTicket::query()
            ->where('service_counter_id', $counter->id)
            ->whereDate('queue_date', today())
            ->where('status', QueueTicket::STATUS_WAITING)
            ->orderBy('call_sequence')
            ->orderBy('id');
    }

    private function appendTicketNote(QueueTicket $ticket, string $note): string
    {
        return trim(($ticket->notes ? $ticket->notes . "\n" : '') . $note);
    }

    private function selectedCounter(): ?ServiceCounter
    {
        if (! $this->selectedCounterId) {
            return null;
        }

        return $this->accessibleCountersQuery()
            ->with(['service', 'assignedOfficer'])
            ->find($this->selectedCounterId);
    }

    public function render()
    {
        $queueRuntime = app(QueueRuntimeService::class);
        $currentSession = $queueRuntime->currentSession();

        $counters = $this->accessibleCountersQuery()
            ->with(['service', 'assignedOfficer'])
            ->orderBy('queue_service_id')
            ->orderBy('sort_order')
            ->get();

        $selectedCounter = $this->selectedCounter();

        $applicantQuery = Applicant::query()
            ->with('user')
            ->whereHas('user.roles', fn (Builder $query) => $query->whereIn('name', self::APPLICANT_ROLE_NAMES))
            ->where(function (Builder $query) use ($currentSession) {
                $query->whereDate('created_at', $currentSession->session_date)
                    ->orWhereHas('checkins', fn (Builder $query) => $query->where('queue_session_id', $currentSession->id))
                    ->orWhereHas('queueTickets', function (Builder $query) use ($currentSession) {
                        $query->where('queue_session_id', $currentSession->id)
                            ->orWhereDate('queue_date', $currentSession->session_date);
                    });
            })
            ->when(trim($this->search) !== '', function (Builder $query) {
                $term = '%' . trim($this->search) . '%';

                $query->where(function (Builder $query) use ($term) {
                    $query->where('full_name', 'like', $term)
                        ->orWhere('nisn', 'like', $term)
                        ->orWhere('whatsapp', 'like', $term)
                        ->orWhere('school_origin', 'like', $term)
                        ->orWhereHas('user', fn (Builder $query) => $query->where('email', 'like', $term));
                });
            })
            ->withExists(['queueTickets as has_active_queue_ticket' => function (Builder $query) use ($currentSession) {
                $query->whereIn('status', self::ACTIVE_QUEUE_STATUSES)
                    ->where(function (Builder $query) use ($currentSession) {
                        $query->where('queue_session_id', $currentSession->id)
                            ->orWhereDate('queue_date', $currentSession->session_date);
                    });
            }])
            ->withMin(['checkins as today_presence_confirmed_at' => fn (Builder $query) => $query->where('queue_session_id', $currentSession->id)], 'presence_confirmed_at')
            ->orderBy('has_active_queue_ticket')
            ->orderByRaw('case when today_presence_confirmed_at is null then 1 else 0 end')
            ->orderBy('today_presence_confirmed_at')
            ->orderBy('id');

        $totalApplicants = (clone $applicantQuery)->count();

        $applicants = $applicantQuery
            ->limit($this->visibleApplicantCount)
            ->get();

        $presenceByApplicantId = AttendanceCheckin::query()
            ->where('queue_session_id', $currentSession->id)
            ->whereIn('applicant_id', $applicants->pluck('id'))
            ->get()
            ->keyBy('applicant_id');

        $activeTicketByApplicantId = QueueTicket::query()
            ->with(['counter.service', 'service'])
            ->whereIn('applicant_id', $applicants->pluck('id'))
            ->whereIn('status', self::ACTIVE_QUEUE_STATUSES)
            ->where(function (Builder $query) use ($currentSession) {
                $query->where('queue_session_id', $currentSession->id)
                    ->orWhereDate('queue_date', $currentSession->session_date);
            })
            ->orderBy('assigned_at')
            ->orderBy('id')
            ->get()
            ->unique('applicant_id')
            ->keyBy('applicant_id');

        $now = AppClock::now();
        $activeQrCode = QueueSessionQrCode::query()
            ->where('queue_session_id', $currentSession->id)
            ->where('is_active', true)
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', $now);
            })
            ->whereNull('revoked_at')
            ->latest()
            ->first();

        $selectedServiceQuota = null;
        $selectedCounterAllocation = null;
        $recommendedCounter = null;

        if ($selectedCounter) {
            $selectedServiceQuota = $queueRuntime->quotaStatus($selectedCounter->service, $currentSession);
            $queueRuntime->ensureAllocations($selectedCounter->service, $currentSession);
            $selectedCounterAllocation = $queueRuntime->allocationStatus($selectedCounter, $currentSession);
            $recommendedCounter = $queueRuntime->recommendedCounter($selectedCounter->service, $selectedCounter, $currentSession);
        }

        $activeTickets = QueueTicket::query()
            ->with(['applicant.user', 'service', 'counter'])
            ->when($selectedCounter, fn (Builder $query) => $query->where('service_counter_id', $selectedCounter->id), fn (Builder $query) => $query->whereRaw('1 = 0'))
            ->whereDate('queue_date', today())
            ->whereIn('status', self::ACTIVE_QUEUE_STATUSES)
            ->orderByRaw("case status when 'called' then 1 when 'in_progress' then 2 when 'waiting' then 3 else 4 end")
            ->orderBy('call_sequence')
            ->orderBy('id')
            ->get();

        $firstWaitingTicketId = $activeTickets
            ->where('status', QueueTicket::STATUS_WAITING)
            ->sortBy([['call_sequence', 'asc'], ['id', 'asc']])
            ->first()?->id;

        $transferTicket = $this->transferTicketId
            ? QueueTicket::query()
                ->with(['applicant', 'service', 'counter.service'])
                ->find($this->transferTicketId)
            : null;

        $assigningApplicant = $this->assigningApplicantId
            ? Applicant::query()
                ->with('user')
                ->find($this->assigningApplicantId)
            : null;

        $assignmentServices = QueueService::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $assignmentServiceStatuses = $assignmentServices->mapWithKeys(function (QueueService $service) use ($queueRuntime, $currentSession, $assigningApplicant): array {
            $quota = $queueRuntime->quotaStatus($service, $currentSession);
            $dependencyError = $assigningApplicant ? $queueRuntime->dependencyError($assigningApplicant, $service, $currentSession) : null;
            $recommendedCounter = $queueRuntime->recommendedCounter($service, null, $currentSession);
            $unavailableMessage = null;

            if ($quota['is_full'] ?? false) {
                $unavailableMessage = 'Kuota layanan penuh.';
            } elseif ($dependencyError) {
                $unavailableMessage = $dependencyError;
            } elseif (! $recommendedCounter) {
                $unavailableMessage = 'Belum ada loket yang buka.';
            }

            return [
                $service->id => [
                    'quota' => $quota,
                    'dependency_error' => $dependencyError,
                    'recommended_counter' => $recommendedCounter,
                    'can_queue' => $unavailableMessage === null,
                    'unavailable_message' => $unavailableMessage,
                ],
            ];
        });

        $noShowTickets = QueueTicket::query()
            ->with(['applicant.user', 'service', 'counter'])
            ->when($selectedCounter, fn (Builder $query) => $query->where('service_counter_id', $selectedCounter->id), fn (Builder $query) => $query->whereRaw('1 = 0'))
            ->whereDate('queue_date', today())
            ->where('status', QueueTicket::STATUS_NO_SHOW)
            ->orderByDesc('no_show_at')
            ->orderByDesc('id')
            ->get();

        $completedCount = QueueTicket::query()
            ->when($selectedCounter, fn (Builder $query) => $query->where('service_counter_id', $selectedCounter->id), fn (Builder $query) => $query->whereRaw('1 = 0'))
            ->whereDate('queue_date', today())
            ->where('status', QueueTicket::STATUS_COMPLETED)
            ->count();

        return view('livewire.pages.officer-queue-console', [
            'currentSession' => $currentSession,
            'counters' => $counters,
            'transferCounters' => $this->transferTargetCounters(),
            'selectedCounter' => $selectedCounter,
            'isCounterManager' => $this->canManageAllCounters(),
            'canManageQueueQr' => $this->canManageQueueQr(),
            'assignedCountersCount' => $counters->count(),
            'waitingCount' => $activeTickets->where('status', QueueTicket::STATUS_WAITING)->count(),
            'completedCount' => $completedCount,
            'noShowCount' => $noShowTickets->count(),
            'applicants' => $applicants,
            'totalApplicants' => $totalApplicants,
            'hasMoreApplicants' => $applicants->count() < $totalApplicants,
            'presenceByApplicantId' => $presenceByApplicantId,
            'activeTicketByApplicantId' => $activeTicketByApplicantId,
            'activeQrCode' => $activeQrCode,
            'selectedServiceQuota' => $selectedServiceQuota,
            'selectedCounterAllocation' => $selectedCounterAllocation,
            'recommendedCounter' => $recommendedCounter,
            'activeTickets' => $activeTickets,
            'firstWaitingTicketId' => $firstWaitingTicketId,
            'transferTicket' => $transferTicket,
            'assigningApplicant' => $assigningApplicant,
            'assignmentServices' => $assignmentServices,
            'assignmentServiceStatuses' => $assignmentServiceStatuses,
            'noShowTickets' => $noShowTickets,
        ]);
    }

    private function accessibleCountersQuery(): Builder
    {
        $query = ServiceCounter::query();

        if (! $this->canManageAllCounters()) {
            $query->where('assigned_user_id', auth()->id());
        }

        return $query;
    }

    private function canManageAllCounters(): bool
    {
        $user = auth()->user();

        return (bool) (
            $user
            && ($user->can('admin.manajemen_layanan') || $user->hasAnyRole(['superadmin', 'admin', 'Super Admin']))
        );
    }

    private function canManageQueueQr(): bool
    {
        $user = auth()->user();

        return (bool) (
            $user
            && ($user->can('petugas.kelola_qr_antrian') || $user->hasAnyRole(['superadmin', 'admin', 'Super Admin']))
        );
    }

    private function transferTargetCounters()
    {
        return ServiceCounter::query()
            ->with('service')
            ->where('is_active', true)
            ->orderBy('queue_service_id')
            ->orderBy('sort_order')
            ->get();
    }

    private function authorizeTicketCounter(QueueTicket $ticket): void
    {
        if (! $ticket->service_counter_id || ! $this->accessibleCountersQuery()->whereKey($ticket->service_counter_id)->exists()) {
            abort(403);
        }
    }
}
