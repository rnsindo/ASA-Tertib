<?php

namespace Tests\Feature;

use App\Livewire\Pages\OfficerQueueConsole;
use App\Models\Applicant;
use App\Models\QueueCallEvent;
use App\Models\QueueService;
use App\Models\QueueTicket;
use App\Models\ServiceCounter;
use App\Models\User;
use App\Services\QueueRuntimeService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QueueDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_call_creates_queue_call_event_for_display(): void
    {
        Role::firstOrCreate(['name' => 'officer']);

        $officer = User::factory()->create([
            'email' => 'display-officer@example.test',
        ]);
        $officer->assignRole('officer');

        $session = app(QueueRuntimeService::class)->currentSession();
        $service = QueueService::create([
            'name' => 'Layanan Display',
            'slug' => 'layanan-display',
            'code' => 'LD',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $counter = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'assigned_user_id' => $officer->id,
            'name' => 'Loket Display 1',
            'code' => 'LD-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $applicantUser = User::factory()->create([
            'email' => 'display-applicant@example.test',
        ]);
        $applicant = Applicant::create([
            'user_id' => $applicantUser->id,
            'full_name' => 'PENDAFTAR DISPLAY',
            'school_origin' => 'SMP DISPLAY',
            'nisn' => '9900112233',
            'whatsapp' => '089900112233',
            'status' => 'registered',
        ]);

        $ticket = QueueTicket::create([
            'applicant_id' => $applicant->id,
            'queue_session_id' => $session->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $counter->id,
            'queue_date' => $session->session_date,
            'queue_number' => 1,
            'call_sequence' => 1000,
            'ticket_code' => 'LD-001',
            'status' => QueueTicket::STATUS_WAITING,
            'assigned_at' => now(),
        ]);

        Livewire::actingAs($officer)
            ->test(OfficerQueueConsole::class)
            ->set('selectedCounterId', $counter->id)
            ->call('callTicket', $ticket->id)
            ->assertHasNoErrors();

        $this->assertSame(QueueTicket::STATUS_CALLED, $ticket->refresh()->status);

        $this->assertDatabaseHas('queue_call_events', [
            'queue_session_id' => $session->id,
            'queue_ticket_id' => $ticket->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $counter->id,
            'called_by' => $officer->id,
            'ticket_code' => 'LD-001',
            'service_name' => 'Layanan Display',
            'counter_name' => 'Loket Display 1',
            'applicant_name' => 'PENDAFTAR DISPLAY',
            'announcement_text' => 'Nomor antrian LD-001, menuju Loket Display 1, layanan Layanan Display.',
        ]);
    }

    public function test_display_event_endpoint_returns_new_calls_in_order(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create([
            'email' => 'display-admin@example.test',
        ]);
        $admin->assignRole('Super Admin');

        $session = app(QueueRuntimeService::class)->currentSession();
        $service = QueueService::create([
            'name' => 'Layanan Polling',
            'slug' => 'layanan-polling',
            'code' => 'LP',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $counterOne = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'name' => 'Loket Polling 1',
            'code' => 'LP-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $counterTwo = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'name' => 'Loket Polling 2',
            'code' => 'LP-2',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $eventOne = QueueCallEvent::create([
            'queue_session_id' => $session->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $counterOne->id,
            'called_by' => $admin->id,
            'ticket_code' => 'LP-001',
            'service_name' => 'Layanan Polling',
            'counter_name' => 'Loket Polling 1',
            'announcement_text' => 'Nomor antrian LP-001, menuju Loket Polling 1, layanan Layanan Polling.',
            'called_at' => now()->subMinute(),
        ]);

        $eventTwo = QueueCallEvent::create([
            'queue_session_id' => $session->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $counterTwo->id,
            'called_by' => $admin->id,
            'ticket_code' => 'LP-002',
            'service_name' => 'Layanan Polling',
            'counter_name' => 'Loket Polling 2',
            'announcement_text' => 'Nomor antrian LP-002, menuju Loket Polling 2, layanan Layanan Polling.',
            'called_at' => now(),
        ]);

        $this->actingAs($admin)
            ->getJson(route('display.queue.events', ['after_id' => $eventOne->id]))
            ->assertOk()
            ->assertJsonPath('latest_call.ticket_code', 'LP-002')
            ->assertJsonPath('new_events.0.id', $eventTwo->id)
            ->assertJsonPath('new_events.0.ticket_code', 'LP-002')
            ->assertJsonPath('last_event_id', $eventTwo->id)
            ->assertJsonCount(1, 'new_events')
            ->assertJsonCount(2, 'counter_statuses');

        $this->actingAs($admin)
            ->getJson(route('display.queue.events', ['initial' => 1]))
            ->assertOk()
            ->assertJsonPath('latest_call.ticket_code', 'LP-002')
            ->assertJsonPath('last_event_id', $eventTwo->id)
            ->assertJsonCount(0, 'new_events');
    }

    public function test_display_page_is_standalone_and_permission_protected(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create([
            'email' => 'display-access-admin@example.test',
        ]);
        $admin->assignRole('Super Admin');

        $officer = User::factory()->create([
            'email' => 'display-access-officer@example.test',
        ]);
        $officer->assignRole('Petugas');

        $this->actingAs($officer)
            ->get(route('display.queue'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('display.queue'))
            ->assertOk()
            ->assertSee('Display Panggilan Antrian')
            ->assertSee('Aktifkan Suara')
            ->assertDontSee('app-header', false)
            ->assertDontSee('bottom-nav', false);
    }
}
