<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\QueueSessionQrCode;
use App\Services\QueueRuntimeService;
use App\Support\AppClock;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class QueueQrPrintController extends Controller
{
    public function __invoke(Request $request, QueueRuntimeService $queueRuntime): View
    {
        $user = $request->user();

        abort_unless(
            $user
            && ($user->can('petugas.konsol_antrian') || $user->hasAnyRole(['superadmin', 'admin', 'officer', 'Super Admin', 'Petugas'])),
            403,
        );

        $currentSession = $queueRuntime->currentSession();
        $qrCode = QueueSessionQrCode::query()
            ->where('queue_session_id', $currentSession->id)
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->whereNull('revoked_at')
            ->latest()
            ->firstOrFail();

        $credential = $qrCode->manual_code ?: (string) $qrCode->id;
        $checkInUrl = route('queue.check-in', ['token' => $credential]);

        $renderer = new ImageRenderer(
            new RendererStyle(760, 3),
            new SvgImageBackEnd(),
        );
        $qrSvg = preg_replace('/<\?xml.*?\?>\s*/', '', (new Writer($renderer))->writeString($checkInUrl)) ?: '';

        return view('print.queue-qr', [
            'appName' => AppSetting::getValue('app.name', config('app.name', 'ASA-Tertib')),
            'sessionName' => $currentSession->name,
            'manualCode' => $qrCode->manual_code ?: '-',
            'checkInUrl' => $checkInUrl,
            'qrSvg' => $qrSvg,
            'startsAt' => $qrCode->starts_at,
            'expiresAt' => $qrCode->expires_at,
            'printedAt' => AppClock::now(),
            'timezone' => AppClock::timezone(),
        ]);
    }
}
