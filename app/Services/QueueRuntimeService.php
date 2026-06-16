<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\ApplicantServiceRecord;
use App\Models\AppSetting;
use App\Models\AttendanceCheckin;
use App\Models\CounterDailyAllocation;
use App\Models\QueueService;
use App\Models\QueueServiceDependency;
use App\Models\QueueSession;
use App\Models\QueueSessionQrCode;
use App\Models\QueueTicket;
use App\Models\ServiceCounter;
use App\Models\ServiceDailyQuota;
use App\Models\User;
use App\Support\AppClock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QueueRuntimeService
{
    private const CALL_SEQUENCE_STEP = 1000;

    public function currentSession(): QueueSession
    {
        $session = QueueSession::query()
            ->whereDate('session_date', today())
            ->first();

        if ($session) {
            return $session;
        }

        return QueueSession::query()->create([
            'name' => 'Antrian ' . today()->format('d/m/Y'),
            'session_date' => today(),
            'starts_at' => today()->startOfDay(),
            'ends_at' => today()->endOfDay(),
            'is_active' => true,
        ]);
    }

    public function createCheckInQr(?User $creator = null, ?Carbon $expiresAt = null, ?string $label = null): array
    {
        $session = $this->currentSession();
        $token = Str::random(56);
        $manualCode = $this->generateManualCode();
        $now = AppClock::now();
        $expiresAt = $this->resolveCheckInQrExpiresAt($expiresAt);

        return DB::transaction(function () use ($session, $creator, $expiresAt, $label, $token, $manualCode, $now): array {
            QueueSessionQrCode::query()
                ->where('queue_session_id', $session->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'revoked_at' => now(),
                    'revoked_by' => $creator?->id,
                ]);

            $qrCode = QueueSessionQrCode::query()->create([
                'queue_session_id' => $session->id,
                'token_hash' => $this->hashToken($token),
                'manual_code' => $manualCode,
                'label' => $label ?: 'QR Ambil Antrian ' . AppClock::format($now, 'd/m/Y H:i'),
                'starts_at' => $now,
                'expires_at' => $expiresAt,
                'is_active' => true,
                'created_by' => $creator?->id,
            ]);

            return [
                'qrCode' => $qrCode,
                'token' => $token,
                'manualCode' => $manualCode,
                'url' => route('queue.check-in', ['token' => $token]),
            ];
        });
    }

    public function activeCheckInQr(?User $autoCreator = null): ?QueueSessionQrCode
    {
        $session = $this->currentSession();
        $now = AppClock::now();
        $activeQrCode = $this->activeCheckInQrQuery($session, $now)->first();

        if ($activeQrCode) {
            return $activeQrCode;
        }

        if (! $this->qrExpiryLimitEnabled() || ! $this->qrAutoRegenerateEnabled()) {
            return null;
        }

        if ($now->greaterThan($now->copy()->setTime(23, 0, 0))) {
            return null;
        }

        $expiredQrCode = QueueSessionQrCode::query()
            ->where('queue_session_id', $session->id)
            ->where('is_active', true)
            ->whereNull('revoked_at')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->latest('expires_at')
            ->latest('id')
            ->first();

        if (! $expiredQrCode) {
            return null;
        }

        return $this->createCheckInQr(
            $autoCreator,
            label: 'QR Ambil Antrian Otomatis ' . AppClock::format($now, 'd/m/Y H:i'),
        )['qrCode'];
    }

    private function activeCheckInQrQuery(QueueSession $session, Carbon $now)
    {
        return QueueSessionQrCode::query()
            ->where('queue_session_id', $session->id)
            ->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', $now);
            })
            ->whereNull('revoked_at')
            ->latest();
    }

    private function resolveCheckInQrExpiresAt(?Carbon $expiresAt = null): Carbon
    {
        $now = AppClock::now();
        $sameDayLimit = $now->copy()->setTime(23, 0, 0);

        if ($expiresAt) {
            $candidate = $expiresAt->copy()->timezone(AppClock::timezone());
        } elseif ($this->qrExpiryLimitEnabled()) {
            $candidate = $now->copy()->addHours($this->qrExpiryLimitHours());
        } else {
            $candidate = $sameDayLimit;
        }

        if (! $candidate->isSameDay($now) || $candidate->greaterThan($sameDayLimit)) {
            return $sameDayLimit;
        }

        return $candidate;
    }

    public function checkInWithQr(User $user, string $token): array
    {
        $applicant = $user->applicant;

        if (! $applicant) {
            return [false, 'Akun ini belum terhubung dengan data pendaftar.'];
        }

        $qrCode = $this->findValidCredential($token);

        if (! $qrCode) {
            return [false, 'QR ambil antrian tidak valid atau sudah kedaluwarsa. Silakan scan QR yang tersedia di lokasi layanan.'];
        }

        $this->confirmPresence($applicant, $qrCode->session, AttendanceCheckin::METHOD_QR, null, $qrCode);

        return [true, 'Kehadiran Anda sudah dikonfirmasi. Silakan menunggu arahan petugas.'];
    }

    public function takeQueueWithCredential(User $user, QueueService $service, string $credential): array
    {
        $applicant = $user->applicant;

        if (! $applicant) {
            return [false, 'Akun ini belum terhubung dengan data pendaftar.', null];
        }

        $qrCode = $this->findValidCredential($credential);

        if (! $qrCode) {
            return [false, 'QR atau kode ambil antrian tidak valid atau sudah kedaluwarsa.', null];
        }

        $this->confirmPresence($applicant, $qrCode->session, AttendanceCheckin::METHOD_QR, null, $qrCode);

        [$canCreate, $message] = $this->canCreateTicket($applicant, $service, $qrCode->session);

        if (! $canCreate) {
            return [false, $message, null];
        }

        $ticket = $this->createTicket($applicant, $service, null, null, null, null, $qrCode->session);

        return [true, 'Nomor antrian ' . $ticket->ticket_code . ' berhasil diambil untuk layanan ' . $service->name . '.', $ticket];
    }

    public function confirmPresenceByOfficer(Applicant $applicant, User $officer): AttendanceCheckin
    {
        return $this->confirmPresence(
            $applicant,
            $this->currentSession(),
            AttendanceCheckin::METHOD_OFFICER,
            $officer,
            null,
        );
    }

    public function checkinFor(Applicant $applicant, ?QueueSession $session = null): ?AttendanceCheckin
    {
        $session ??= $this->currentSession();

        return AttendanceCheckin::query()
            ->where('queue_session_id', $session->id)
            ->where('applicant_id', $applicant->id)
            ->first();
    }

    public function hasPresence(Applicant $applicant, ?QueueSession $session = null): bool
    {
        return (bool) $this->checkinFor($applicant, $session);
    }

    public function quotaStatus(QueueService $service, ?QueueSession $session = null): array
    {
        $session ??= $this->currentSession();
        $dailyQuotaEnabled = $this->dailyQuotaEnabled();
        $quota = ServiceDailyQuota::query()
            ->where('queue_session_id', $session->id)
            ->where('queue_service_id', $service->id)
            ->first();

        $used = $this->serviceTicketCount($service, $session);
        $max = $dailyQuotaEnabled ? ($quota?->max_daily_quota ?? $this->defaultDailyQuotaLimit()) : null;
        $isOpen = $quota?->is_open ?? true;
        $isFull = $max !== null && $used >= $max;

        return [
            'quota' => $quota,
            'used' => $used,
            'max' => $max,
            'remaining' => $max === null ? null : max(0, $max - $used),
            'is_open' => $isOpen,
            'is_full' => $dailyQuotaEnabled && (! $isOpen || $isFull),
            'label' => $max === null ? 'Tanpa batas' : $used . ' / ' . $max,
            'is_enabled' => $dailyQuotaEnabled,
        ];
    }

    public function serviceTicketCount(QueueService $service, ?QueueSession $session = null): int
    {
        $session ??= $this->currentSession();

        return QueueTicket::query()
            ->where('queue_service_id', $service->id)
            ->whereDate('queue_date', $session->session_date)
            ->count();
    }

    public function allocationStatus(ServiceCounter $counter, ?QueueSession $session = null): array
    {
        $session ??= $this->currentSession();
        $dailyQuotaEnabled = $this->dailyQuotaEnabled();
        $allocation = CounterDailyAllocation::query()
            ->where('queue_session_id', $session->id)
            ->where('service_counter_id', $counter->id)
            ->first();

        $used = QueueTicket::query()
            ->where('service_counter_id', $counter->id)
            ->whereDate('queue_date', $session->session_date)
            ->count();

        return [
            'allocation' => $allocation,
            'used' => $used,
            'target' => $dailyQuotaEnabled ? $allocation?->target_quota : null,
            'is_at_target' => $dailyQuotaEnabled && $allocation?->target_quota !== null && $used >= $allocation->target_quota,
            'label' => $dailyQuotaEnabled && $allocation ? $used . ' / ' . $allocation->target_quota : (string) $used,
        ];
    }

    public function ensureAllocations(QueueService $service, ?QueueSession $session = null): void
    {
        $session ??= $this->currentSession();

        if (! $this->dailyQuotaEnabled()) {
            return;
        }

        $quotaStatus = $this->quotaStatus($service, $session);
        $max = $quotaStatus['max'];

        if ($max === null) {
            return;
        }

        $counters = ServiceCounter::query()
            ->where('queue_service_id', $service->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($counters->isEmpty()) {
            return;
        }

        $base = intdiv($max, $counters->count());
        $remainder = $max % $counters->count();

        foreach ($counters as $index => $counter) {
            CounterDailyAllocation::query()->updateOrCreate(
                [
                    'queue_session_id' => $session->id,
                    'service_counter_id' => $counter->id,
                ],
                [
                    'queue_service_id' => $service->id,
                    'target_quota' => $base + ($index < $remainder ? 1 : 0),
                    'manual_overflow_allowed' => true,
                ],
            );
        }
    }

    public function recommendedCounter(QueueService $service, ?ServiceCounter $preferredCounter = null, ?QueueSession $session = null): ?ServiceCounter
    {
        $session ??= $this->currentSession();
        $this->ensureAllocations($service, $session);

        $counters = ServiceCounter::query()
            ->where('queue_service_id', $service->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($counters->isEmpty()) {
            return null;
        }

        $ranked = $counters->map(function (ServiceCounter $counter) use ($session): array {
            $status = $this->allocationStatus($counter, $session);
            $target = $status['target'] ?: 1;

            return [
                'counter' => $counter,
                'used' => $status['used'],
                'target' => $status['target'],
                'ratio' => $status['used'] / $target,
                'is_under_target' => $status['target'] === null || $status['used'] < $status['target'],
            ];
        })->sortBy([
            ['is_under_target', 'desc'],
            ['ratio', 'asc'],
            ['used', 'asc'],
        ])->values();

        $best = $ranked->first()['counter'] ?? null;

        if ($preferredCounter && $preferredCounter->queue_service_id === $service->id) {
            $preferredStatus = $this->allocationStatus($preferredCounter, $session);

            if ($preferredStatus['target'] === null || $preferredStatus['used'] < $preferredStatus['target']) {
                return $preferredCounter;
            }
        }

        return $best;
    }

    public function canCreateTicket(Applicant $applicant, QueueService $service, ?QueueSession $session = null, ?int $ignoreTicketId = null, bool $ignoreQuota = false): array
    {
        $session ??= $this->currentSession();

        if (! $this->hasPresence($applicant, $session)) {
            return [false, 'Pendaftar wajib konfirmasi hadir di lokasi sebelum masuk antrian.'];
        }

        $hasActiveTicket = QueueTicket::query()
            ->where('applicant_id', $applicant->id)
            ->where('queue_service_id', $service->id)
            ->whereDate('queue_date', $session->session_date)
            ->when($ignoreTicketId, fn ($query) => $query->whereKeyNot($ignoreTicketId))
            ->whereIn('status', [
                QueueTicket::STATUS_WAITING,
                QueueTicket::STATUS_CALLED,
                QueueTicket::STATUS_IN_PROGRESS,
                QueueTicket::STATUS_NO_SHOW,
            ])
            ->exists();

        if ($hasActiveTicket) {
            return [false, 'Pendaftar ini masih punya antrian aktif atau terlewat pada layanan yang sama.'];
        }

        $otherActiveTicket = QueueTicket::query()
            ->with('service')
            ->where('applicant_id', $applicant->id)
            ->where('queue_service_id', '!=', $service->id)
            ->whereDate('queue_date', $session->session_date)
            ->when($ignoreTicketId, fn ($query) => $query->whereKeyNot($ignoreTicketId))
            ->whereIn('status', [
                QueueTicket::STATUS_WAITING,
                QueueTicket::STATUS_CALLED,
                QueueTicket::STATUS_IN_PROGRESS,
                QueueTicket::STATUS_NO_SHOW,
            ])
            ->latest('assigned_at')
            ->latest('id')
            ->first();

        if ($otherActiveTicket) {
            return [false, 'Anda masih memiliki antrian aktif atau terlewat di layanan ' . ($otherActiveTicket->service?->name ?? 'lain') . '. Silakan selesaikan dahulu sebelum mengambil antrian layanan lain.'];
        }

        if (! $ignoreQuota) {
            $quotaStatus = $this->quotaStatus($service, $session);

            if ($quotaStatus['is_full']) {
                return [false, 'Antrian layanan ' . $service->name . ' sudah penuh untuk hari ini. Registrasi pendaftar tetap tersimpan, tetapi belum bisa mengambil antrian layanan ini. Silakan hubungi petugas atau kembali pada jadwal layanan berikutnya.'];
            }
        }

        if ($dependencyMessage = $this->dependencyError($applicant, $service, $session)) {
            return [false, $dependencyMessage];
        }

        return [true, null];
    }

    public function createTicket(
        Applicant $applicant,
        QueueService $service,
        ?ServiceCounter $preferredCounter = null,
        ?User $assignedBy = null,
        ?int $fromCounterId = null,
        ?string $notes = null,
        ?QueueSession $session = null,
        bool $forcePreferredCounter = false,
    ): QueueTicket {
        $session ??= $this->currentSession();

        return DB::transaction(function () use ($applicant, $service, $preferredCounter, $assignedBy, $fromCounterId, $notes, $session, $forcePreferredCounter): QueueTicket {
            $counter = null;

            if ($forcePreferredCounter) {
                if (! $preferredCounter || $preferredCounter->queue_service_id !== $service->id) {
                    throw new \RuntimeException('Loket tujuan tidak tersedia atau tidak sesuai layanan.');
                }

                $counter = $preferredCounter;
            }

            $counter ??= $this->recommendedCounter($service, $preferredCounter, $session);

            if (! $counter) {
                throw new \RuntimeException('Belum ada loket aktif untuk layanan ' . $service->name . '.');
            }

            $nextNumber = ((int) QueueTicket::query()
                ->where('queue_service_id', $service->id)
                ->whereDate('queue_date', $session->session_date)
                ->lockForUpdate()
                ->max('queue_number')) + 1;

            ApplicantServiceRecord::query()->firstOrCreate(
                [
                    'queue_session_id' => $session->id,
                    'applicant_id' => $applicant->id,
                    'queue_service_id' => $service->id,
                ],
                ['service_status' => 'queued'],
            );

            return QueueTicket::query()->create([
                'applicant_id' => $applicant->id,
                'queue_session_id' => $session->id,
                'queue_service_id' => $service->id,
                'service_counter_id' => $counter->id,
                'transferred_from_counter_id' => $fromCounterId,
                'assigned_by' => $assignedBy?->id,
                'queue_date' => $session->session_date,
                'queue_number' => $nextNumber,
                'call_sequence' => $this->nextCallSequenceForCounter($counter, $session),
                'ticket_code' => $service->code . '-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT),
                'status' => QueueTicket::STATUS_WAITING,
                'assigned_at' => now(),
                'notes' => $notes,
            ]);
        });
    }

    public function dependencyError(Applicant $applicant, QueueService $service, ?QueueSession $session = null): ?string
    {
        $session ??= $this->currentSession();

        $dependencies = QueueServiceDependency::query()
            ->with('requiredService')
            ->where('queue_service_id', $service->id)
            ->where('is_active', true)
            ->where(function ($query) use ($session) {
                $query->whereNull('queue_session_id')
                    ->orWhere('queue_session_id', $session->id);
            })
            ->get();

        foreach ($dependencies as $dependency) {
            $requiredServiceName = $dependency->requiredService?->name ?? 'layanan prasyarat';

            if (! $this->hasRequiredServiceTicket($applicant, $dependency, $session)) {
                return match ($dependency->required_status_mode) {
                    QueueServiceDependency::MODE_COMPLETED => 'Layanan ' . $service->name . ' baru bisa diambil setelah layanan ' . $requiredServiceName . ' selesai.',
                    QueueServiceDependency::MODE_IN_PROGRESS => 'Layanan ' . $service->name . ' baru bisa diambil setelah layanan ' . $requiredServiceName . ' sedang atau sudah diproses.',
                    QueueServiceDependency::MODE_CALLED => 'Layanan ' . $service->name . ' baru bisa diambil setelah nomor layanan ' . $requiredServiceName . ' sudah dipanggil.',
                    default => 'Layanan ' . $service->name . ' baru bisa diambil setelah pendaftar masuk antrian layanan ' . $requiredServiceName . '.',
                };
            }
        }

        return null;
    }

    private function confirmPresence(
        Applicant $applicant,
        QueueSession $session,
        string $method,
        ?User $confirmedBy = null,
        ?QueueSessionQrCode $qrCode = null,
    ): AttendanceCheckin {
        return AttendanceCheckin::query()->updateOrCreate(
            [
                'queue_session_id' => $session->id,
                'applicant_id' => $applicant->id,
            ],
            [
                'queue_session_qr_code_id' => $qrCode?->id,
                'presence_status' => AttendanceCheckin::STATUS_CHECKED_IN,
                'presence_confirmed_at' => now(),
                'presence_confirmed_by' => $confirmedBy?->id,
                'presence_method' => $method,
                'presence_location_code' => $session->session_date->toDateString(),
            ],
        );
    }

    private function qrIsValid(QueueSessionQrCode $qrCode): bool
    {
        $now = now();

        if (! $qrCode->session?->is_active) {
            return false;
        }

        if (! $qrCode->session->session_date?->isSameDay(AppClock::now())) {
            return false;
        }

        if ($qrCode->starts_at && $qrCode->starts_at->gt($now)) {
            return false;
        }

        if ($qrCode->expires_at && $qrCode->expires_at->lt($now)) {
            return false;
        }

        return $qrCode->revoked_at === null;
    }

    private function findValidCredential(string $credential): ?QueueSessionQrCode
    {
        $normalized = trim($credential);

        if ($normalized === '') {
            return null;
        }

        $token = $this->extractToken($normalized);
        $manualCode = strtoupper($normalized);
        $extractedManualCode = strtoupper($token);

        $qrCode = QueueSessionQrCode::query()
            ->with('session')
            ->where('is_active', true)
            ->where(function ($query) use ($token, $manualCode, $extractedManualCode) {
                $query->where('token_hash', $this->hashToken($token))
                    ->orWhere('manual_code', $manualCode)
                    ->orWhere('manual_code', $extractedManualCode);
            })
            ->first();

        if (! $qrCode || ! $this->qrIsValid($qrCode)) {
            return null;
        }

        return $qrCode;
    }

    private function extractToken(string $raw): string
    {
        $path = parse_url($raw, PHP_URL_PATH);

        if ($path && str_contains($path, '/check-in/')) {
            return basename($path);
        }

        return trim($raw);
    }

    private function nextCallSequenceForCounter(ServiceCounter $counter, QueueSession $session): float
    {
        $maxSequence = QueueTicket::query()
            ->where('service_counter_id', $counter->id)
            ->whereDate('queue_date', $session->session_date)
            ->max('call_sequence');

        return ((float) ($maxSequence ?: 0)) + self::CALL_SEQUENCE_STEP;
    }

    private function hasRequiredServiceTicket(Applicant $applicant, QueueServiceDependency $dependency, QueueSession $session): bool
    {
        $query = QueueTicket::query()
            ->where('applicant_id', $applicant->id)
            ->where('queue_service_id', $dependency->required_queue_service_id)
            ->whereDate('queue_date', $session->session_date);

        return match ($dependency->required_status_mode) {
            QueueServiceDependency::MODE_COMPLETED => $query
                ->where('status', QueueTicket::STATUS_COMPLETED)
                ->exists(),
            QueueServiceDependency::MODE_IN_PROGRESS => $query
                ->whereIn('status', [
                    QueueTicket::STATUS_IN_PROGRESS,
                    QueueTicket::STATUS_COMPLETED,
                ])
                ->exists(),
            QueueServiceDependency::MODE_CALLED => $query
                ->whereIn('status', [
                    QueueTicket::STATUS_CALLED,
                    QueueTicket::STATUS_IN_PROGRESS,
                    QueueTicket::STATUS_COMPLETED,
                ])
                ->exists(),
            default => $query
                ->whereIn('status', [
                    QueueTicket::STATUS_WAITING,
                    QueueTicket::STATUS_CALLED,
                    QueueTicket::STATUS_IN_PROGRESS,
                    QueueTicket::STATUS_NO_SHOW,
                    QueueTicket::STATUS_TRANSFERRED,
                    QueueTicket::STATUS_COMPLETED,
                ])
                ->exists(),
        };
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    private function generateManualCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (QueueSessionQrCode::query()->where('manual_code', $code)->where('is_active', true)->exists());

        return $code;
    }

    private function dailyQuotaEnabled(): bool
    {
        return (bool) AppSetting::getValue('queue.daily_quota_enabled', true);
    }

    private function defaultDailyQuotaLimit(): int
    {
        return max(1, (int) AppSetting::getValue('queue.daily_quota_limit', 200));
    }

    private function qrExpiryLimitEnabled(): bool
    {
        return (bool) AppSetting::getValue('queue.qr_expiry_limit_enabled', false);
    }

    private function qrExpiryLimitHours(): int
    {
        return max(1, min(24, (int) AppSetting::getValue('queue.qr_expiry_limit_hours', 2)));
    }

    private function qrAutoRegenerateEnabled(): bool
    {
        return (bool) AppSetting::getValue('queue.qr_auto_regenerate_enabled', true);
    }
}
