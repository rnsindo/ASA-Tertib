<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\QueueCallEvent;
use App\Models\QueueTicket;
use App\Models\ServiceCounter;
use App\Services\QueueAnnouncementAudioService;
use App\Services\QueueRuntimeService;
use App\Support\AppClock;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class QueueDisplayController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeDisplay($request);

        return view('display.queue-call', [
            'appName' => $this->appName(),
            'appLogo' => $this->appLogo(),
            'primaryColor' => $this->primaryColor(),
            'timezone' => AppClock::timezone(),
            'eventsUrl' => route('display.queue.events'),
            'activationAudioUrl' => asset('audio/queue/system/bell.wav'),
        ]);
    }

    public function events(Request $request, QueueRuntimeService $runtime): JsonResponse
    {
        $this->authorizeDisplay($request);

        $session = $runtime->currentSession();
        $afterId = max(0, (int) $request->integer('after_id'));
        $initial = $request->boolean('initial');

        $latestCall = QueueCallEvent::query()
            ->where('queue_session_id', $session->id)
            ->latest('id')
            ->first();

        $newEvents = collect();

        if (! $initial) {
            $newEvents = QueueCallEvent::query()
                ->where('queue_session_id', $session->id)
                ->where('id', '>', $afterId)
                ->orderBy('id')
                ->limit(20)
                ->get();
        }

        $recentCalls = QueueCallEvent::query()
            ->where('queue_session_id', $session->id)
            ->latest('id')
            ->limit(8)
            ->get()
            ->reverse()
            ->values();

        $lastEventId = max(
            $afterId,
            (int) ($latestCall?->id ?? 0),
            (int) ($newEvents->max('id') ?? 0),
        );

        return response()->json([
            'latest_call' => $latestCall ? $this->eventPayload($latestCall) : null,
            'new_events' => $newEvents->map(fn (QueueCallEvent $event): array => $this->eventPayload($event))->values(),
            'recent_calls' => $recentCalls->map(fn (QueueCallEvent $event): array => $this->eventPayload($event))->values(),
            'counter_statuses' => $this->counterStatuses($session->id, $session->session_date),
            'last_event_id' => $lastEventId,
            'server_time' => AppClock::format(AppClock::now(), 'd/m/Y H:i:s'),
            'server_time_iso' => AppClock::isoNow(),
        ]);
    }

    private function authorizeDisplay(Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user
            && (
                $user->can('petugas.display_antrian')
                || $user->hasAnyRole(['superadmin', 'admin', 'Super Admin'])
            ),
            403,
        );
    }

    private function counterStatuses(int $sessionId, mixed $sessionDate): array
    {
        $activeTicketsByCounter = QueueTicket::query()
            ->where(function (Builder $query) use ($sessionId, $sessionDate): void {
                $query->where('queue_session_id', $sessionId)
                    ->orWhereDate('queue_date', $sessionDate);
            })
            ->whereIn('status', [QueueTicket::STATUS_CALLED, QueueTicket::STATUS_IN_PROGRESS])
            ->orderByRaw("case status when 'in_progress' then 1 when 'called' then 2 else 3 end")
            ->orderByDesc('called_at')
            ->orderByDesc('id')
            ->get()
            ->unique('service_counter_id')
            ->keyBy('service_counter_id');

        $lastCallsByCounter = QueueCallEvent::query()
            ->where('queue_session_id', $sessionId)
            ->latest('id')
            ->limit(500)
            ->get()
            ->unique('service_counter_id')
            ->keyBy('service_counter_id');

        return ServiceCounter::query()
            ->with('service')
            ->withCount(['tickets as waiting_count' => function (Builder $query) use ($sessionId, $sessionDate): void {
                $query->where(function (Builder $query) use ($sessionId, $sessionDate): void {
                    $query->where('queue_session_id', $sessionId)
                        ->orWhereDate('queue_date', $sessionDate);
                })->where('status', QueueTicket::STATUS_WAITING);
            }])
            ->orderBy('queue_service_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (ServiceCounter $counter) use ($activeTicketsByCounter, $lastCallsByCounter): array {
                $activeTicket = $activeTicketsByCounter->get($counter->id);
                $lastCall = $lastCallsByCounter->get($counter->id);

                return [
                    'id' => $counter->id,
                    'service_name' => $counter->service?->name ?? 'Layanan',
                    'counter_name' => $counter->name,
                    'counter_code' => $counter->code,
                    'is_active' => (bool) $counter->is_active,
                    'waiting_count' => (int) $counter->waiting_count,
                    'current_ticket_code' => $activeTicket?->ticket_code,
                    'current_status' => $activeTicket?->status,
                    'current_status_label' => $activeTicket?->status_label,
                    'last_ticket_code' => $lastCall?->ticket_code,
                    'last_called_at' => $lastCall?->called_at ? AppClock::format($lastCall->called_at, 'H:i') : null,
                ];
            })
            ->values()
            ->all();
    }

    private function eventPayload(QueueCallEvent $event): array
    {
        return [
            'id' => $event->id,
            'ticket_code' => $event->ticket_code,
            'service_name' => $event->service_name,
            'counter_name' => $event->counter_name,
            'announcement_text' => $event->announcement_text,
            'audio_urls' => app(QueueAnnouncementAudioService::class)->eventAudioUrls($event),
            'called_at' => AppClock::format($event->called_at, 'd/m/Y H:i:s'),
            'called_at_time' => AppClock::format($event->called_at, 'H:i'),
            'called_at_iso' => $event->called_at?->copy()->timezone(AppClock::timezone())->toIso8601String(),
        ];
    }

    private function appName(): string
    {
        return $this->setting('app.name', config('app.name', 'ASA-Tertib'));
    }

    private function appLogo(): ?string
    {
        $logoEnabled = (bool) $this->setting('app.logo_enabled', true);

        if (! $logoEnabled) {
            return null;
        }

        $logo = $this->setting('app.logo', null);

        if (! is_string($logo) || trim($logo) === '') {
            return null;
        }

        $logo = trim($logo);

        return str_starts_with($logo, 'http://')
            || str_starts_with($logo, 'https://')
            || str_starts_with($logo, '/')
            || str_starts_with($logo, 'data:')
                ? $logo
                : asset($logo);
    }

    private function primaryColor(): string
    {
        $color = $this->setting('app.primary_color', '#1d4ed8');

        return is_string($color) && preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? $color : '#1d4ed8';
    }

    private function setting(string $key, mixed $fallback): mixed
    {
        if (! Schema::hasTable('app_settings')) {
            return $fallback;
        }

        return AppSetting::getValue($key, $fallback);
    }
}
