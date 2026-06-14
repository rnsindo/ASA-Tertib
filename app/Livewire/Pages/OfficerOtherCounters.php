<?php

namespace App\Livewire\Pages;

use App\Models\QueueTicket;
use App\Models\ServiceCounter;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Daftar Loket Lain')]
class OfficerOtherCounters extends Component
{
    public function mount(): void
    {
        $user = auth()->user();

        abort_unless(
            auth()->check()
            && ($user->can('petugas.konsol_antrian') || $user->hasAnyRole(['superadmin', 'admin', 'officer', 'Super Admin', 'Petugas'])),
            403,
        );
    }

    public function render()
    {
        $assignedCounterIds = ServiceCounter::query()
            ->where('assigned_user_id', auth()->id())
            ->pluck('id');

        $canManageAllCounters = $this->canManageAllCounters();

        $counters = ServiceCounter::query()
            ->with(['service', 'assignedOfficer'])
            ->when(! $canManageAllCounters && $assignedCounterIds->isNotEmpty(), fn (Builder $query) => $query->whereKeyNot($assignedCounterIds))
            ->withCount([
                'tickets as waiting_count' => fn (Builder $query) => $query->whereDate('queue_date', today())->where('status', QueueTicket::STATUS_WAITING),
                'tickets as completed_count' => fn (Builder $query) => $query->whereDate('queue_date', today())->where('status', QueueTicket::STATUS_COMPLETED),
                'tickets as no_show_count' => fn (Builder $query) => $query->whereDate('queue_date', today())->where('status', QueueTicket::STATUS_NO_SHOW),
            ])
            ->orderBy('queue_service_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('livewire.pages.officer-other-counters', [
            'counters' => $counters,
            'canManageAllCounters' => $canManageAllCounters,
        ]);
    }

    private function canManageAllCounters(): bool
    {
        $user = auth()->user();

        return (bool) (
            $user
            && ($user->can('admin.manajemen_layanan') || $user->hasAnyRole(['superadmin', 'admin', 'Super Admin']))
        );
    }
}
