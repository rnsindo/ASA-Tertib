<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\AppSetting;
use App\Models\CounterDailyAllocation;
use App\Models\AttendanceCheckin;
use App\Models\QueueServiceDependency;
use App\Models\QueueTicket;
use App\Models\QueueService;
use App\Models\QueueSession;
use App\Models\ServiceCounter;
use App\Models\ServiceDailyQuota;
use App\Models\User;
use App\Livewire\Pages\ApplicantDashboard;
use App\Livewire\Pages\ApplicationSettings;
use App\Livewire\Pages\OfficerQueueConsole;
use App\Livewire\Pages\CompleteRegistration;
use App\Livewire\Pages\ServiceManagement;
use App\Livewire\Pages\UserManagement;
use App\Services\QueueRuntimeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QueuePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_applicant_dashboard_is_available_to_applicant(): void
    {
        Role::firstOrCreate(['name' => 'applicant']);

        $user = User::factory()->create([
            'email' => 'queue-applicant@example.test',
            'password' => 'password123',
        ]);
        $user->assignRole('applicant');

        Applicant::create([
            'user_id' => $user->id,
            'full_name' => 'Queue Applicant',
            'school_origin' => 'SMP Test',
            'nisn' => '9911223344',
            'whatsapp' => '081111222233',
            'status' => 'registered',
        ]);

        $service = QueueService::create([
            'name' => 'Layanan Dashboard',
            'slug' => 'layanan-dashboard',
            'code' => 'LD',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        ServiceCounter::create([
            'queue_service_id' => $service->id,
            'name' => 'Loket Dashboard',
            'code' => 'LD-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('<title>Dashboard Antrian - ASA-Tertib</title>', false);
        $response->assertSee('Dashboard Antrian');
        $response->assertSee('Ambil Antrian');
        $response->assertDontSee('Konfirmasi Kehadiran');
        $response->assertDontSee('Wajib scan QR');
    }

    public function test_applicant_dashboard_keeps_registration_but_blocks_queue_when_daily_quota_is_full(): void
    {
        Role::firstOrCreate(['name' => 'applicant']);

        $user = User::factory()->create([
            'email' => 'quota-dashboard-applicant@example.test',
            'password' => 'password123',
        ]);
        $user->assignRole('applicant');

        Applicant::create([
            'user_id' => $user->id,
            'full_name' => 'Pendaftar Dashboard Quota',
            'school_origin' => 'SMP Quota',
            'nisn' => '9988776655',
            'whatsapp' => '089988776655',
            'status' => 'registered',
        ]);

        $runtime = app(QueueRuntimeService::class);
        $session = $runtime->currentSession();

        $service = QueueService::create([
            'name' => 'Layanan Dashboard Penuh',
            'slug' => 'layanan-dashboard-penuh',
            'code' => 'LDP',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $counter = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'name' => 'Loket Dashboard Penuh',
            'code' => 'LDP-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        ServiceDailyQuota::create([
            'queue_session_id' => $session->id,
            'queue_service_id' => $service->id,
            'max_daily_quota' => 1,
            'is_open' => true,
        ]);

        $existingUser = User::factory()->create(['email' => 'quota-dashboard-existing@example.test']);
        $existingApplicant = Applicant::create([
            'user_id' => $existingUser->id,
            'full_name' => 'Pendaftar Existing Dashboard',
            'school_origin' => 'SMP Existing',
            'nisn' => '8877665544',
            'whatsapp' => '088877665544',
            'status' => 'registered',
        ]);

        QueueTicket::create([
            'applicant_id' => $existingApplicant->id,
            'queue_session_id' => $session->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $counter->id,
            'queue_date' => today(),
            'queue_number' => 1,
            'call_sequence' => 1000,
            'ticket_code' => 'LDP-001',
            'status' => QueueTicket::STATUS_COMPLETED,
            'assigned_at' => now(),
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Antrian Penuh');
        $response->assertSee('Antrian layanan Layanan Dashboard Penuh sudah penuh untuk hari ini.');
        $response->assertSee('Registrasi Anda tetap berhasil tersimpan.');

        Livewire::actingAs($user)
            ->test(ApplicantDashboard::class)
            ->call('openQueueScanner', $service->id)
            ->assertSet('selectedServiceId', null)
            ->assertSee('Silakan hubungi petugas atau kembali pada jadwal layanan berikutnya.')
            ->assertDontSee('Kode Manual');
    }

    public function test_officer_console_is_available_to_officer(): void
    {
        Role::firstOrCreate(['name' => 'officer']);

        $user = User::factory()->create([
            'email' => 'queue-officer@example.test',
            'password' => 'password123',
        ]);
        $user->assignRole('officer');

        $service = QueueService::create([
            'name' => 'Layanan Test',
            'slug' => 'layanan-test',
            'code' => 'LT',
            'sort_order' => 99,
            'is_active' => true,
        ]);

        ServiceCounter::create([
            'queue_service_id' => $service->id,
            'name' => 'Loket Test',
            'code' => 'LT-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/petugas');

        $response->assertOk();
        $response->assertSee('<title>Konsol Petugas - ASA-Tertib</title>', false);
        $response->assertSee('Dashboard Petugas');
        $response->assertSee('Anda belum ditugaskan ke loket tertentu.');
    }

    public function test_officer_applicant_direction_list_uses_mobile_cards_and_lazy_batches(): void
    {
        Role::firstOrCreate(['name' => 'officer']);
        $customerRole = Role::firstOrCreate(['name' => 'Pelanggan/Penanya']);

        $officer = User::factory()->create([
            'email' => 'mobile-list-officer@example.test',
            'password' => 'password123',
        ]);
        $officer->assignRole('officer');

        $service = QueueService::create([
            'name' => 'Layanan Mobile List',
            'slug' => 'layanan-mobile-list',
            'code' => 'LML',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        ServiceCounter::create([
            'queue_service_id' => $service->id,
            'assigned_user_id' => $officer->id,
            'name' => 'Loket Mobile List',
            'code' => 'LML-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        foreach (range(1, 7) as $number) {
            $user = User::factory()->create([
                'email' => sprintf('mobile-list-applicant-%02d@example.test', $number),
                'password' => 'password123',
            ]);
            $user->assignRole($customerRole);

            Applicant::create([
                'user_id' => $user->id,
                'full_name' => sprintf('Pendaftar Mobile %02d', $number),
                'school_origin' => 'SMP Mobile',
                'nisn' => sprintf('55112233%02d', $number),
                'whatsapp' => sprintf('0855112233%02d', $number),
                'status' => 'registered',
                'created_at' => now()->addMinutes($number),
                'updated_at' => now()->addMinutes($number),
            ]);
        }

        Livewire::actingAs($officer)
            ->test(OfficerQueueConsole::class)
            ->assertSet('visibleApplicantCount', 5)
            ->assertSee('Pencarian Cepat')
            ->assertSee('Menampilkan 5 dari 7 data hari ini')
            ->assertSee('Pendaftar Mobile 01')
            ->assertDontSee('Pendaftar Mobile 06')
            ->call('loadMoreApplicants')
            ->assertSet('visibleApplicantCount', 10)
            ->assertSee('Pendaftar Mobile 06')
            ->set('search', 'Mobile 07')
            ->assertSet('visibleApplicantCount', 5)
            ->assertSee('Pendaftar Mobile 07')
            ->assertDontSee('Pendaftar Mobile 01');
    }

    public function test_officer_direction_list_filters_customer_roles_and_places_active_queue_after_waiting_applicants(): void
    {
        Role::firstOrCreate(['name' => 'officer']);
        $customerRole = Role::firstOrCreate(['name' => 'Pelanggan/Penanya']);

        $officer = User::factory()->create([
            'email' => 'direction-order-officer@example.test',
            'password' => 'password123',
        ]);
        $officer->assignRole('officer');

        $service = QueueService::create([
            'name' => 'Layanan Urutan',
            'slug' => 'layanan-urutan',
            'code' => 'LU',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $counter = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'assigned_user_id' => $officer->id,
            'name' => 'Loket Urutan',
            'code' => 'LU-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $session = app(QueueRuntimeService::class)->currentSession();

        $queuedUser = User::factory()->create([
            'email' => 'sudah-antri@example.test',
            'password' => 'password123',
        ]);
        $queuedUser->assignRole($customerRole);
        $queuedApplicant = Applicant::create([
            'user_id' => $queuedUser->id,
            'full_name' => 'Pendaftar Sudah Antri',
            'school_origin' => 'SMP Urutan',
            'nisn' => '7711000001',
            'whatsapp' => '087711000001',
            'status' => 'registered',
            'created_at' => now()->subMinutes(20),
            'updated_at' => now()->subMinutes(20),
        ]);

        AttendanceCheckin::create([
            'queue_session_id' => $session->id,
            'applicant_id' => $queuedApplicant->id,
            'presence_status' => AttendanceCheckin::STATUS_CHECKED_IN,
            'presence_confirmed_at' => today()->setTime(8, 0),
            'presence_method' => AttendanceCheckin::METHOD_OFFICER,
            'presence_location_code' => today()->toDateString(),
        ]);

        QueueTicket::create([
            'applicant_id' => $queuedApplicant->id,
            'queue_session_id' => $session->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $counter->id,
            'queue_date' => $session->session_date,
            'queue_number' => 1,
            'call_sequence' => 1000,
            'ticket_code' => 'LU-001',
            'status' => QueueTicket::STATUS_WAITING,
            'assigned_at' => now()->subMinutes(10),
        ]);

        $waitingUser = User::factory()->create([
            'email' => 'belum-antri@example.test',
            'password' => 'password123',
        ]);
        $waitingUser->assignRole($customerRole);
        $waitingApplicant = Applicant::create([
            'user_id' => $waitingUser->id,
            'full_name' => 'Pendaftar Belum Antri',
            'school_origin' => 'SMP Urutan',
            'nisn' => '7711000002',
            'whatsapp' => '087711000002',
            'status' => 'registered',
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);

        AttendanceCheckin::create([
            'queue_session_id' => $session->id,
            'applicant_id' => $waitingApplicant->id,
            'presence_status' => AttendanceCheckin::STATUS_CHECKED_IN,
            'presence_confirmed_at' => today()->setTime(8, 30),
            'presence_method' => AttendanceCheckin::METHOD_OFFICER,
            'presence_location_code' => today()->toDateString(),
        ]);

        $nonCustomerUser = User::factory()->create([
            'email' => 'bukan-pelanggan@example.test',
            'password' => 'password123',
        ]);
        Applicant::create([
            'user_id' => $nonCustomerUser->id,
            'full_name' => 'Bukan Pelanggan',
            'school_origin' => 'SMP Urutan',
            'nisn' => '7711000003',
            'whatsapp' => '087711000003',
            'status' => 'registered',
            'created_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(5),
        ]);

        $component = Livewire::actingAs($officer)->test(OfficerQueueConsole::class);
        $applicants = $component->viewData('applicants');

        $this->assertSame([
            'Pendaftar Belum Antri',
            'Pendaftar Sudah Antri',
        ], $applicants->pluck('full_name')->all());

        $component
            ->assertSee('Sedang antri di Loket Urutan')
            ->assertSee('Tombol Masukkan disembunyikan')
            ->assertDontSee('Bukan Pelanggan');
    }

    public function test_officer_assigns_applicant_through_service_modal_to_lightest_counter(): void
    {
        Role::firstOrCreate(['name' => 'officer']);
        $customerRole = Role::firstOrCreate(['name' => 'Pelanggan/Penanya']);

        $officer = User::factory()->create([
            'email' => 'assign-modal-officer@example.test',
            'password' => 'password123',
        ]);
        $officer->assignRole('officer');

        $runtime = app(QueueRuntimeService::class);
        $session = $runtime->currentSession();

        $service = QueueService::create([
            'name' => 'Layanan Modal',
            'slug' => 'layanan-modal',
            'code' => 'LM',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $busyCounter = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'assigned_user_id' => $officer->id,
            'name' => 'Loket Modal Ramai',
            'code' => 'LM-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $lightCounter = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'name' => 'Loket Modal Ringan',
            'code' => 'LM-2',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        ServiceDailyQuota::create([
            'queue_session_id' => $session->id,
            'queue_service_id' => $service->id,
            'max_daily_quota' => 20,
            'is_open' => true,
        ]);

        $existingUser = User::factory()->create(['email' => 'assign-modal-existing@example.test']);
        $existingApplicant = Applicant::create([
            'user_id' => $existingUser->id,
            'full_name' => 'Pendaftar Modal Existing',
            'school_origin' => 'SMP Modal',
            'nisn' => '7711223301',
            'whatsapp' => '087711223301',
            'status' => 'registered',
        ]);

        QueueTicket::create([
            'applicant_id' => $existingApplicant->id,
            'queue_session_id' => $session->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $busyCounter->id,
            'queue_date' => $session->session_date,
            'queue_number' => 1,
            'call_sequence' => 1000,
            'ticket_code' => 'LM-001',
            'status' => QueueTicket::STATUS_WAITING,
            'assigned_at' => now()->subMinutes(10),
        ]);

        $user = User::factory()->create(['email' => 'assign-modal-applicant@example.test']);
        $user->assignRole($customerRole);
        $applicant = Applicant::create([
            'user_id' => $user->id,
            'full_name' => 'Pendaftar Modal Baru',
            'school_origin' => 'SMP Modal',
            'nisn' => '7711223302',
            'whatsapp' => '087711223302',
            'status' => 'registered',
        ]);

        $runtime->confirmPresenceByOfficer($applicant, $officer);

        Livewire::actingAs($officer)
            ->test(OfficerQueueConsole::class)
            ->call('openAssignServiceModal', $applicant->id)
            ->assertSet('assigningApplicantId', $applicant->id)
            ->assertSee('Masukkan ke Layanan')
            ->assertSee('Layanan Modal - Kuota 1 / 20')
            ->assertDontSee('rekomendasi')
            ->set('assigningServiceId', $service->id)
            ->assertSee('Sistem akan memilih loket yang buka dengan beban paling ringan')
            ->call('confirmAssignApplicantToService')
            ->assertHasNoErrors()
            ->assertSet('assigningApplicantId', null);

        $this->assertDatabaseHas('queue_tickets', [
            'applicant_id' => $applicant->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $lightCounter->id,
            'status' => QueueTicket::STATUS_WAITING,
        ]);
    }

    public function test_officer_can_only_call_first_waiting_ticket_and_transfer_uses_modal_target(): void
    {
        Role::firstOrCreate(['name' => 'officer']);

        $officer = User::factory()->create([
            'email' => 'call-transfer-officer@example.test',
            'password' => 'password123',
        ]);
        $officer->assignRole('officer');

        $service = QueueService::create([
            'name' => 'Layanan Panggil',
            'slug' => 'layanan-panggil',
            'code' => 'LP',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $counterOne = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'assigned_user_id' => $officer->id,
            'name' => 'Loket Panggil 1',
            'code' => 'LP-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $counterTwo = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'name' => 'Loket Panggil 2',
            'code' => 'LP-2',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $session = app(QueueRuntimeService::class)->currentSession();

        $tickets = collect(range(1, 2))->map(function (int $number) use ($service, $counterOne, $session): QueueTicket {
            $user = User::factory()->create([
                'email' => "call-transfer-applicant-{$number}@example.test",
                'password' => 'password123',
            ]);

            $applicant = Applicant::create([
                'user_id' => $user->id,
                'full_name' => "Pendaftar Panggil {$number}",
                'school_origin' => 'SMP Panggil',
                'nisn' => '882200000' . $number,
                'whatsapp' => '08882200000' . $number,
                'status' => 'registered',
            ]);

            AttendanceCheckin::create([
                'queue_session_id' => $session->id,
                'applicant_id' => $applicant->id,
                'presence_status' => AttendanceCheckin::STATUS_CHECKED_IN,
                'presence_confirmed_at' => today()->setTime(9, $number),
                'presence_method' => AttendanceCheckin::METHOD_OFFICER,
                'presence_location_code' => today()->toDateString(),
            ]);

            return QueueTicket::create([
                'applicant_id' => $applicant->id,
                'queue_session_id' => $session->id,
                'queue_service_id' => $service->id,
                'service_counter_id' => $counterOne->id,
                'queue_date' => $session->session_date,
                'queue_number' => $number,
                'call_sequence' => $number * 1000,
                'ticket_code' => 'LP-' . str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                'status' => QueueTicket::STATUS_WAITING,
                'assigned_at' => now()->addMinutes($number),
            ]);
        })->values();

        Livewire::actingAs($officer)
            ->test(OfficerQueueConsole::class)
            ->call('callTicket', $tickets[1]->id)
            ->assertHasErrors(['notes']);

        $this->assertSame(QueueTicket::STATUS_WAITING, $tickets[1]->refresh()->status);

        Livewire::actingAs($officer)
            ->test(OfficerQueueConsole::class)
            ->call('callTicket', $tickets[0]->id)
            ->assertHasNoErrors();

        $this->assertSame(QueueTicket::STATUS_CALLED, $tickets[0]->refresh()->status);

        Livewire::actingAs($officer)
            ->test(OfficerQueueConsole::class)
            ->call('openTransferModal', $tickets[1]->id)
            ->assertSet('transferTicketId', $tickets[1]->id)
            ->assertSee('Pindah Loket')
            ->set('transferTargetCounterId', $counterOne->id)
            ->call('confirmTransferTicket')
            ->assertHasErrors(['transferTargetCounterId'])
            ->set('transferTargetCounterId', $counterTwo->id)
            ->call('confirmTransferTicket')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('queue_tickets', [
            'applicant_id' => $tickets[1]->applicant_id,
            'service_counter_id' => $counterTwo->id,
            'status' => QueueTicket::STATUS_WAITING,
        ]);
    }

    public function test_officer_can_print_active_queue_qr_code(): void
    {
        $this->seed();

        $officer = User::where('email', 'petugas@example.test')->firstOrFail();
        $qr = app(QueueRuntimeService::class)->createCheckInQr($officer);

        $response = $this->actingAs($officer)->get('/petugas');

        $response->assertOk();
        $response->assertSee('Dashboard Petugas');
        $response->assertSee('Download QR');
        $response->assertSee(route('officer.queue-qr.print'), false);

        $response = $this->actingAs($officer)->get(route('officer.queue-qr.print'));

        $response->assertOk();
        $response->assertSee('<title>QR Ambil Antrian - ASA-Tertib</title>', false);
        $response->assertSee('Kode alternatif jika QR tidak terbaca');
        $response->assertSee($qr['manualCode']);
        $response->assertSee('Berlaku sampai');
        $response->assertSee('Zona waktu: Asia/Jakarta');
        $response->assertSee('Cetak / Simpan PDF');
        $response->assertSee('<svg', false);
        $response->assertSee(route('queue.check-in', ['token' => $qr['manualCode']]), false);
        $response->assertDontSee('class="app-header"', false);
        $response->assertDontSee('class="bottom-nav"', false);
    }

    public function test_queue_qr_generation_requires_manual_officer_permission(): void
    {
        Permission::firstOrCreate(['name' => 'petugas.konsol_antrian']);
        $permission = Permission::firstOrCreate(['name' => 'petugas.kelola_qr_antrian']);
        $officerRole = Role::firstOrCreate(['name' => 'Petugas']);
        $officerRole->givePermissionTo('petugas.konsol_antrian');

        $officer = User::factory()->create([
            'email' => 'qr-limited-officer@example.test',
            'password' => 'password123',
        ]);
        $officer->assignRole($officerRole);

        $service = QueueService::create([
            'name' => 'Layanan QR Terbatas',
            'slug' => 'layanan-qr-terbatas',
            'code' => 'LQT',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        ServiceCounter::create([
            'queue_service_id' => $service->id,
            'assigned_user_id' => $officer->id,
            'name' => 'Loket QR Terbatas',
            'code' => 'LQT-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Livewire::actingAs($officer)
            ->test(OfficerQueueConsole::class)
            ->assertDontSee('Buat/Ganti QR & Kode', false)
            ->call('generateCheckInQr')
            ->assertHasErrors(['selectedCounterId']);

        $this->assertDatabaseCount('queue_session_qr_codes', 0);

        $officer->givePermissionTo($permission);

        $component = Livewire::actingAs($officer->fresh())
            ->test(OfficerQueueConsole::class)
            ->assertSee('Buat/Ganti QR & Kode', false)
            ->call('generateCheckInQr')
            ->assertHasNoErrors();

        $this->assertNotEmpty($component->get('generatedCheckInCode'));
        $this->assertDatabaseCount('queue_session_qr_codes', 1);
    }

    public function test_admin_route_is_removed(): void
    {
        $response = $this->get('/admin');

        $response->assertNotFound();
    }

    public function test_database_seeder_prepares_first_installation_baseline(): void
    {
        $this->seed();

        $superAdmin = User::where('email', 'superadmin@asa-link.cloud')->first();

        $this->assertNotNull($superAdmin);
        $this->assertTrue($superAdmin->is_active);
        $this->assertTrue($superAdmin->hasRole('Super Admin'));
        $this->assertTrue($superAdmin->can('petugas.konsol_antrian'));
        $this->assertTrue($superAdmin->can('petugas.kelola_qr_antrian'));
        $this->assertTrue($superAdmin->can('admin.pengaturan_aplikasi'));
        $this->assertTrue($superAdmin->can('admin.manajemen_layanan'));
        $this->assertTrue($superAdmin->can('admin.manajemen_user'));
        $this->assertTrue($superAdmin->can('admin.reset_password_user'));
        $this->assertTrue($superAdmin->can('admin.login_sebagai_user'));

        $this->assertDatabaseHas('roles', ['name' => 'Super Admin']);
        $this->assertDatabaseHas('roles', ['name' => 'Petugas']);
        $this->assertDatabaseHas('roles', ['name' => 'Pelanggan/Penanya']);
        $this->assertDatabaseHas('permissions', ['name' => 'admin.pengaturan_aplikasi']);
        $this->assertDatabaseHas('permissions', ['name' => 'admin.manajemen_layanan']);
        $this->assertDatabaseHas('permissions', ['name' => 'admin.manajemen_user']);
        $this->assertDatabaseHas('permissions', ['name' => 'admin.reset_password_user']);
        $this->assertDatabaseHas('permissions', ['name' => 'admin.login_sebagai_user']);
        $this->assertDatabaseHas('permissions', ['name' => 'petugas.beranda']);
        $this->assertDatabaseHas('permissions', ['name' => 'petugas.konsol_antrian']);
        $this->assertDatabaseHas('permissions', ['name' => 'petugas.kelola_qr_antrian']);
        $this->assertDatabaseHas('permissions', ['name' => 'pelanggan.beranda']);
        $this->assertDatabaseHas('permissions', ['name' => 'pelanggan.dashboard_antrian']);
        $this->assertDatabaseHas('permissions', ['name' => 'pelanggan.status_antrian']);
        $this->assertDatabaseHas('permissions', ['name' => 'pelanggan.scan_qr']);
        $this->assertDatabaseHas('permissions', ['name' => 'pelanggan.riwayat']);
        $this->assertDatabaseHas('permissions', ['name' => 'pelanggan.profil']);
        $this->assertDatabaseMissing('permissions', ['name' => 'page.app-settings']);
        $this->assertDatabaseMissing('permissions', ['name' => 'page.user-management']);
        $this->assertDatabaseMissing('permissions', ['name' => 'user.reset-password']);
        $this->assertDatabaseMissing('permissions', ['name' => 'user.impersonate']);
        $this->assertDatabaseMissing('permissions', ['name' => 'menu.konsol-petugas']);
        $this->assertDatabaseMissing('permissions', ['name' => 'menu.app-settings']);
        $this->assertDatabaseMissing('permissions', ['name' => 'menu.user-management']);
        $this->assertDatabaseMissing('permissions', ['name' => 'menu.pelayanan-24-7']);
        $this->assertDatabaseMissing('permissions', ['name' => 'menu.informasi-pendaftaran']);
        $this->assertDatabaseMissing('permissions', ['name' => 'menu.panduan-lengkap']);

        $this->assertSame('ASA-Tertib', AppSetting::getValue('app.name'));
        $this->assertDatabaseHas('app_settings', ['key' => 'app.favicon']);
        $this->assertSame('Asia/Jakarta', AppSetting::getValue('app.timezone'));
        $this->assertTrue(AppSetting::getValue('queue.daily_quota_enabled'));
        $this->assertSame(200, AppSetting::getValue('queue.daily_quota_limit'));

        $this->assertTrue(Role::findByName('Petugas')->hasPermissionTo('petugas.konsol_antrian'));
        $this->assertFalse(Role::findByName('Petugas')->hasPermissionTo('petugas.kelola_qr_antrian'));
        $this->assertFalse(Role::findByName('Petugas')->hasPermissionTo('admin.pengaturan_aplikasi'));
        $this->assertFalse(Role::findByName('Petugas')->hasPermissionTo('admin.manajemen_layanan'));
        $this->assertFalse(Role::findByName('Petugas')->hasPermissionTo('admin.manajemen_user'));
        $this->assertFalse(Role::findByName('Pelanggan/Penanya')->hasPermissionTo('petugas.konsol_antrian'));
        $this->assertFalse(Role::findByName('Pelanggan/Penanya')->hasPermissionTo('admin.pengaturan_aplikasi'));
        $this->assertFalse(Role::findByName('Pelanggan/Penanya')->hasPermissionTo('admin.manajemen_layanan'));
        $this->assertFalse(Role::findByName('Pelanggan/Penanya')->hasPermissionTo('admin.manajemen_user'));
        $this->assertTrue(Role::findByName('Pelanggan/Penanya')->hasPermissionTo('pelanggan.status_antrian'));
        $this->assertSame(10, AppSetting::getValue('queue.default_service_minutes'));

        AppSetting::putValue('queue.daily_quota_limit', 123, [
            'group' => 'queue',
            'label' => 'Total Quota Harian',
            'type' => AppSetting::TYPE_INTEGER,
            'is_public' => false,
            'sort_order' => 3,
        ]);

        Role::firstOrCreate(['name' => 'applicant']);
        $this->seed();
        $this->assertSame(123, AppSetting::getValue('queue.daily_quota_limit'));
        config(['seed.sync_mode' => 'sync']);
        $this->seed();
        $this->assertSame(200, AppSetting::getValue('queue.daily_quota_limit'));
        $this->assertTrue(Role::findByName('applicant')->hasPermissionTo('pelanggan.status_antrian'));
        $this->assertTrue(Role::findByName('applicant')->hasPermissionTo('pelanggan.dashboard_antrian'));

        $verification = QueueService::where('slug', 'verifikasi-berkas')->first();
        $interview = QueueService::where('slug', 'wawancara')->first();

        $this->assertNotNull($verification);
        $this->assertNotNull($interview);

        $this->assertDatabaseHas('queue_service_dependencies', [
            'queue_service_id' => $interview->id,
            'required_queue_service_id' => $verification->id,
            'required_status_mode' => QueueServiceDependency::MODE_COMPLETED,
            'is_active' => true,
        ]);

        $this->assertSame(4, ServiceCounter::count());
        $this->assertDatabaseHas('service_counters', [
            'code' => 'VB-1',
            'assigned_user_id' => User::where('email', 'petugas@example.test')->value('id'),
        ]);
        $this->assertSame(1, QueueSession::count());
        $this->assertSame(2, ServiceDailyQuota::count());
        $this->assertSame(4, CounterDailyAllocation::count());

        $response = $this->actingAs($superAdmin)->get('/petugas');

        $response->assertOk();
        $response->assertSee('Konsol Petugas');
        $response->assertSee('Pengaturan Aplikasi');
        $response->assertSee('Manajemen Layanan');
        $response->assertSee('Manajemen User');
        $response->assertDontSee('Pelayanan 24/7');
        $response->assertDontSee('Informasi Pendaftaran');
        $response->assertDontSee('Panduan Lengkap');

        $response = $this->actingAs($superAdmin)->get('/pengaturan-aplikasi');

        $response->assertOk();
        $response->assertSee('<title>Pengaturan Aplikasi - ASA-Tertib</title>', false);
        $response->assertDontSee('Atur identitas ASA-Tertib dan perilaku dasar yang dibutuhkan pengguna HP.');
        $response->assertSee('Nama Aplikasi');
        $response->assertSee('Tampilkan Logo');
        $response->assertSee('Ikon Browser');
        $response->assertSee('Aktifkan Quota Harian');
        $response->assertSee('Total Quota Harian');
        $response->assertDontSee('app.name');
        $response->assertDontSee('<table>', false);

        $applicantUser = User::where('email', 'pendaftar@example.test')->firstOrFail();

        $response = $this->actingAs($applicantUser)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Pelayanan 24/7');
        $response->assertDontSee('Informasi Pendaftaran');
        $response->assertDontSee('Panduan Lengkap');
        $response->assertDontSee('Konsol Petugas');
        $response->assertDontSee('Manajemen User');
        $response->assertDontSee('Pengaturan Aplikasi');

        $this->actingAs($applicantUser)->get('/pengaturan-aplikasi')->assertForbidden();
        $this->actingAs($applicantUser)->get('/manajemen-layanan')->assertForbidden();
        $this->actingAs($applicantUser)->get('/manajemen-user')->assertForbidden();
    }

    public function test_super_admin_can_manage_services_and_counters(): void
    {
        $this->seed();

        $superAdmin = User::where('email', 'superadmin@asa-link.cloud')->firstOrFail();
        $officer = User::where('email', 'petugas@example.test')->firstOrFail();

        $response = $this->actingAs($superAdmin)->get('/manajemen-layanan');

        $response->assertOk();
        $response->assertSee('<title>Manajemen Layanan - ASA-Tertib</title>', false);
        $response->assertSee('Manajemen Layanan');
        $response->assertSee('Tambah Layanan');
        $response->assertSee('Verifikasi Berkas');

        Livewire::actingAs($superAdmin)
            ->test(ServiceManagement::class)
            ->call('openServiceModal')
            ->assertSet('isServiceModalOpen', true)
            ->set('serviceName', 'Tes Administrasi')
            ->set('serviceDescription', 'Layanan test administrasi')
            ->call('createService')
            ->assertHasNoErrors()
            ->assertSet('isServiceModalOpen', false)
            ->assertSet('selectedServiceId', QueueService::where('code', 'TA')->value('id'))
            ->assertSee('Tambah Loket')
            ->call('openCounterModal')
            ->assertSet('isCounterModalOpen', true)
            ->set('counterName', 'Loket Administrasi 1')
            ->set('counterOfficerId', (string) $officer->id)
            ->call('addCounter')
            ->assertHasNoErrors()
            ->assertSet('isCounterModalOpen', false)
            ->assertSee('Loket Administrasi 1');

        $service = QueueService::where('code', 'TA')->firstOrFail();
        $counter = ServiceCounter::where('code', 'LA')->firstOrFail();

        $this->assertSame('Tes Administrasi', $service->name);
        $this->assertSame('TA', $service->code);
        $this->assertSame($service->id, $counter->queue_service_id);
        $this->assertSame($officer->id, $counter->assigned_user_id);

        $this->assertDatabaseHas('service_daily_quotas', [
            'queue_service_id' => $service->id,
            'max_daily_quota' => 200,
            'is_open' => true,
        ]);

        $this->assertDatabaseHas('counter_daily_allocations', [
            'queue_service_id' => $service->id,
            'service_counter_id' => $counter->id,
            'target_quota' => 200,
        ]);

        Livewire::actingAs($superAdmin)
            ->test(ServiceManagement::class)
            ->call('openCounters', $service->id)
            ->call('openCounterModal')
            ->set('counterName', 'Loket Administrasi 1')
            ->call('addCounter')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('service_counters', [
            'queue_service_id' => $service->id,
            'name' => 'Loket Administrasi 1',
            'code' => 'LA2',
        ]);

        Livewire::actingAs($superAdmin)
            ->test(ServiceManagement::class)
            ->call('openServiceModal')
            ->set('serviceName', 'Tes Akhir')
            ->call('createService')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('queue_services', [
            'name' => 'Tes Akhir',
            'code' => 'TA2',
        ]);

        $verification = QueueService::where('slug', 'verifikasi-berkas')->firstOrFail();

        Livewire::actingAs($superAdmin)
            ->test(ServiceManagement::class)
            ->call('openEditServiceModal', $service->id)
            ->assertSet('editingServiceId', $service->id)
            ->assertSet('editingServiceCode', 'TA')
            ->assertSet('serviceRequiresPrevious', false)
            ->set('serviceName', 'Tes Administrasi Revisi')
            ->set('serviceDescription', 'Deskripsi sudah direvisi')
            ->set('serviceIsActive', false)
            ->set('serviceRequiresPrevious', true)
            ->set('requiredServiceId', (string) $verification->id)
            ->set('requiredStatusMode', QueueServiceDependency::MODE_COMPLETED)
            ->call('updateService')
            ->assertHasNoErrors()
            ->assertSet('isServiceModalOpen', false)
            ->assertSet('selectedServiceId', $service->id);

        $service->refresh();

        $this->assertSame('Tes Administrasi Revisi', $service->name);
        $this->assertSame('TA', $service->code);
        $this->assertSame('Deskripsi sudah direvisi', $service->description);
        $this->assertFalse($service->is_active);
        $this->assertDatabaseHas('queue_service_dependencies', [
            'queue_service_id' => $service->id,
            'required_queue_service_id' => $verification->id,
            'required_status_mode' => QueueServiceDependency::MODE_COMPLETED,
            'is_active' => true,
        ]);

        Livewire::actingAs($superAdmin)
            ->test(ServiceManagement::class)
            ->call('openCounters', $service->id)
            ->assertSee('Syarat Ambil Antrian')
            ->assertSee('Verifikasi Berkas')
            ->assertSee('sudah selesai');

        Livewire::actingAs($superAdmin)
            ->test(ServiceManagement::class)
            ->call('openEditServiceModal', $verification->id)
            ->set('serviceRequiresPrevious', true)
            ->set('requiredServiceId', (string) $service->id)
            ->set('requiredStatusMode', QueueServiceDependency::MODE_COMPLETED)
            ->call('updateService')
            ->assertHasErrors(['requiredServiceId']);

        $secondOfficer = User::factory()->create([
            'name' => 'Petugas Revisi',
            'email' => 'petugas-revisi@example.test',
        ]);
        $secondOfficer->assignRole('Petugas');

        Livewire::actingAs($superAdmin)
            ->test(ServiceManagement::class)
            ->call('openEditCounterModal', $counter->id)
            ->assertSet('editingCounterId', $counter->id)
            ->assertSet('editingCounterCode', 'LA')
            ->set('counterName', 'Loket Administrasi Revisi')
            ->set('counterOfficerId', (string) $secondOfficer->id)
            ->set('counterIsActive', false)
            ->call('updateCounter')
            ->assertHasNoErrors()
            ->assertSet('isCounterModalOpen', false)
            ->assertSet('selectedServiceId', $service->id);

        $counter->refresh();

        $this->assertSame('Loket Administrasi Revisi', $counter->name);
        $this->assertSame('LA', $counter->code);
        $this->assertSame($secondOfficer->id, $counter->assigned_user_id);
        $this->assertFalse($counter->is_active);
    }

    public function test_super_admin_can_manage_users_reset_password_and_impersonate(): void
    {
        $this->seed();

        $superAdmin = User::where('email', 'superadmin@asa-link.cloud')->firstOrFail();
        $target = User::where('email', 'pendaftar@example.test')->firstOrFail();
        collect(range(1, 7))->each(function (int $number): void {
            User::factory()->create([
                'name' => sprintf('ZZZ User %02d', $number),
                'email' => sprintf('zzz-user-%02d@example.test', $number),
            ]);
        });

        $response = $this->actingAs($superAdmin)->get('/manajemen-user');

        $response->assertOk();
        $response->assertSee('<title>Manajemen User - ASA-Tertib</title>', false);
        $response->assertSee('Reset Password');
        $response->assertSee('Login As');
        $response->assertSee('Aktif');
        $response->assertSee($target->email);
        $response->assertSee('ZZZ User 02');
        $response->assertDontSee('ZZZ User 03');

        $component = Livewire::actingAs($superAdmin)
            ->test(UserManagement::class)
            ->assertSet('visibleCount', 5)
            ->assertSee('ZZZ User 02')
            ->assertDontSee('ZZZ User 03')
            ->call('loadMore')
            ->assertSet('visibleCount', 10)
            ->assertSee('ZZZ User 03')
            ->call('resetPassword', $target->id)
            ->assertHasNoErrors()
            ->assertSee('Password baru')
            ->assertSee('Copy detail akun');

        $newPassword = $component->get('resetPasswords')[$target->id] ?? null;

        $this->assertNotEmpty($newPassword);
        $this->assertSame(5, strlen($newPassword));
        $this->assertMatchesRegularExpression('/^[ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789]{5}$/', $newPassword);
        $this->assertTrue(Hash::check($newPassword, $target->refresh()->password));

        Livewire::actingAs($superAdmin)
            ->test(UserManagement::class)
            ->call('openPermissionModal', $target->id)
            ->assertSet('isPermissionModalOpen', true)
            ->assertSet('editingUserIsActive', true)
            ->assertSee('Bawaan role')
            ->assertSee('Role User')
            ->assertSee('Status Akun')
            ->assertSee('pelanggan.status_antrian')
            ->set('selectedRoleNames', ['Pelanggan/Penanya', 'Petugas'])
            ->set('selectedDirectPermissions', ['admin.pengaturan_aplikasi'])
            ->call('savePermissions')
            ->assertHasNoErrors()
            ->assertSet('isPermissionModalOpen', false);

        $target->refresh();

        $this->assertTrue($target->hasDirectPermission('admin.pengaturan_aplikasi'));
        $this->assertTrue($target->hasRole('Petugas'));
        $this->assertTrue($target->can('pelanggan.status_antrian'));
        $this->assertTrue($target->can('petugas.konsol_antrian'));

        Livewire::actingAs($superAdmin)
            ->test(UserManagement::class)
            ->call('openPermissionModal', $target->id)
            ->set('selectedRoleNames', ['Pelanggan/Penanya'])
            ->set('selectedDirectPermissions', ['admin.pengaturan_aplikasi'])
            ->call('savePermissions')
            ->assertHasNoErrors();

        $target->refresh();

        $this->assertFalse($target->hasRole('Petugas'));
        $this->assertFalse($target->can('petugas.konsol_antrian'));

        Livewire::actingAs($superAdmin)
            ->test(UserManagement::class)
            ->call('openPermissionModal', $superAdmin->id)
            ->set('selectedRoleNames', [])
            ->call('savePermissions')
            ->assertHasNoErrors();

        $this->assertTrue($superAdmin->refresh()->hasRole('Super Admin'));

        Livewire::actingAs($superAdmin)
            ->test(UserManagement::class)
            ->call('openPermissionModal', $target->id)
            ->set('editingUserIsActive', false)
            ->set('selectedRoleNames', ['Pelanggan/Penanya'])
            ->set('selectedDirectPermissions', ['admin.pengaturan_aplikasi'])
            ->call('savePermissions')
            ->assertHasNoErrors();

        $this->assertFalse($target->refresh()->is_active);

        $this->actingAs($superAdmin)
            ->post(route('users.impersonate.take', $target))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($target);
        $this->assertSame($superAdmin->id, session('impersonator_id'));

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Sedang Login As');

        $this->post(route('users.impersonate.leave'))
            ->assertRedirect(route('users.management'));

        $this->assertAuthenticatedAs($superAdmin);
        $this->assertNull(session('impersonator_id'));

        Livewire::actingAs($superAdmin)
            ->test(UserManagement::class)
            ->call('openPermissionModal', $superAdmin->id)
            ->set('editingUserIsActive', false)
            ->call('savePermissions')
            ->assertHasNoErrors();

        $this->assertTrue($superAdmin->refresh()->is_active);

        Livewire::actingAs($superAdmin)
            ->test(UserManagement::class)
            ->call('openPermissionModal', $target->id)
            ->set('editingUserIsActive', true)
            ->set('selectedRoleNames', ['Pelanggan/Penanya'])
            ->set('selectedDirectPermissions', ['admin.pengaturan_aplikasi'])
            ->call('savePermissions')
            ->assertHasNoErrors();

        $this->assertTrue($target->refresh()->is_active);

        $this->actingAs($superAdmin)
            ->post(route('users.impersonate.take', $target))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($target);
        $this->assertSame($superAdmin->id, session('impersonator_id'));

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Sedang Login As');
        $response->assertSee('Kembali ke Akun Asli');
        $response->assertSee($superAdmin->email);

        $this->post(route('users.impersonate.leave'))
            ->assertRedirect(route('users.management'));

        $this->assertAuthenticatedAs($superAdmin);
        $this->assertNull(session('impersonator_id'));
    }

    public function test_super_admin_can_update_application_settings(): void
    {
        $this->seed();
        Storage::fake('public');

        $superAdmin = User::where('email', 'superadmin@asa-link.cloud')->firstOrFail();
        $logo = UploadedFile::fake()->image('logo.png', 120, 120);
        $favicon = UploadedFile::fake()->image('favicon.png', 32, 32);

        Livewire::actingAs($superAdmin)
            ->test(ApplicationSettings::class)
            ->set('appName', 'ASA-Tertib Test')
            ->set('logoUpload', $logo)
            ->set('faviconUpload', $favicon)
            ->set('appLogoEnabled', false)
            ->set('primaryColor', '#123abc')
            ->set('appTimezone', 'Asia/Makassar')
            ->set('defaultServiceMinutes', 12)
            ->set('dailyQuotaEnabled', true)
            ->set('dailyQuotaLimit', 150)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('ASA-Tertib Test', AppSetting::getValue('app.name'));
        $this->assertStringStartsWith('storage/logos/', AppSetting::getValue('app.logo'));
        Storage::disk('public')->assertExists(str_replace('storage/', '', AppSetting::getValue('app.logo')));
        $this->assertStringStartsWith('storage/favicons/', AppSetting::getValue('app.favicon'));
        Storage::disk('public')->assertExists(str_replace('storage/', '', AppSetting::getValue('app.favicon')));
        $this->assertFalse(AppSetting::getValue('app.logo_enabled'));
        $this->assertSame('#123abc', AppSetting::getValue('app.primary_color'));
        $this->assertSame('Asia/Makassar', AppSetting::getValue('app.timezone'));
        $this->assertSame(12, AppSetting::getValue('queue.default_service_minutes'));
        $this->assertTrue(AppSetting::getValue('queue.daily_quota_enabled'));
        $this->assertSame(150, AppSetting::getValue('queue.daily_quota_limit'));

        $this->assertDatabaseHas('service_daily_quotas', [
            'max_daily_quota' => 150,
        ]);
    }

    public function test_applicant_dashboard_estimate_uses_default_then_average_completed_service_time(): void
    {
        Role::firstOrCreate(['name' => 'applicant']);

        $service = QueueService::create([
            'name' => 'Layanan Estimasi',
            'slug' => 'layanan-estimasi',
            'code' => 'LE',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $counter = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'name' => 'Loket Estimasi',
            'code' => 'LE-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $waitingAheadUser = User::factory()->create(['email' => 'estimate-ahead@example.test']);
        $waitingAheadApplicant = Applicant::create([
            'user_id' => $waitingAheadUser->id,
            'full_name' => 'Pendaftar Depan',
            'school_origin' => 'SMP Estimasi',
            'nisn' => '7711000001',
            'whatsapp' => '087711000001',
            'status' => 'registered',
        ]);

        $user = User::factory()->create([
            'email' => 'estimate-applicant@example.test',
            'password' => 'password123',
        ]);
        $user->assignRole('applicant');

        $applicant = Applicant::create([
            'user_id' => $user->id,
            'full_name' => 'Pendaftar Estimasi',
            'school_origin' => 'SMP Estimasi',
            'nisn' => '7711000002',
            'whatsapp' => '087711000002',
            'status' => 'registered',
        ]);

        QueueTicket::create([
            'applicant_id' => $waitingAheadApplicant->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $counter->id,
            'queue_date' => today(),
            'queue_number' => 1,
            'call_sequence' => 1000,
            'ticket_code' => 'LE-001',
            'status' => QueueTicket::STATUS_WAITING,
            'assigned_at' => now(),
        ]);

        QueueTicket::create([
            'applicant_id' => $applicant->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $counter->id,
            'queue_date' => today(),
            'queue_number' => 2,
            'call_sequence' => 2000,
            'ticket_code' => 'LE-002',
            'status' => QueueTicket::STATUS_WAITING,
            'assigned_at' => now(),
        ]);

        AppSetting::putValue('queue.default_service_minutes', 10, [
            'group' => 'queue',
            'label' => 'Estimasi Awal Pelayanan Per Pendaftar',
            'type' => AppSetting::TYPE_INTEGER,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('<strong>20m</strong>', false);

        $completedUserOne = User::factory()->create(['email' => 'estimate-done-one@example.test']);
        $completedApplicantOne = Applicant::create([
            'user_id' => $completedUserOne->id,
            'full_name' => 'Pendaftar Selesai Satu',
            'school_origin' => 'SMP Estimasi',
            'nisn' => '7711000003',
            'whatsapp' => '087711000003',
            'status' => 'registered',
        ]);

        $completedUserTwo = User::factory()->create(['email' => 'estimate-done-two@example.test']);
        $completedApplicantTwo = Applicant::create([
            'user_id' => $completedUserTwo->id,
            'full_name' => 'Pendaftar Selesai Dua',
            'school_origin' => 'SMP Estimasi',
            'nisn' => '7711000004',
            'whatsapp' => '087711000004',
            'status' => 'registered',
        ]);

        QueueTicket::create([
            'applicant_id' => $completedApplicantOne->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $counter->id,
            'queue_date' => today(),
            'queue_number' => 3,
            'call_sequence' => 3000,
            'ticket_code' => 'LE-003',
            'status' => QueueTicket::STATUS_COMPLETED,
            'assigned_at' => now(),
            'started_at' => today()->setTime(8, 0),
            'completed_at' => today()->setTime(8, 4),
        ]);

        QueueTicket::create([
            'applicant_id' => $completedApplicantTwo->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $counter->id,
            'queue_date' => today(),
            'queue_number' => 4,
            'call_sequence' => 4000,
            'ticket_code' => 'LE-004',
            'status' => QueueTicket::STATUS_COMPLETED,
            'assigned_at' => now(),
            'started_at' => today()->setTime(8, 10),
            'completed_at' => today()->setTime(8, 16),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('<strong>10m</strong>', false);
        $response->assertDontSee('<strong>20m</strong>', false);
    }

    public function test_complete_registration_name_is_editable_and_validation_is_indonesian(): void
    {
        $this->withSession([
            'google_registration' => [
                'google_id' => 'google-test-id',
                'name' => 'Nama Google',
                'email' => 'google-registration@example.test',
                'avatar_url' => null,
            ],
        ]);

        $response = $this->get('/register/complete');

        $response->assertOk();
        $response->assertSee('<title>Formulir Pendaftaran Lanjutan - ASA-Tertib</title>', false);
        $response->assertSee('class="app-shell is-guest"', false);
        $response->assertDontSee('class="app-header"', false);
        $response->assertDontSee('class="bottom-nav"', false);
        $response->assertDontSee('id="sideDrawer"', false);
        $response->assertSee('id="name"', false);
        $response->assertDontSee('id="name" class="input" type="text" wire:model="name" readonly', false);

        Livewire::test(CompleteRegistration::class)
            ->set('name', '')
            ->set('email', 'google-registration@example.test')
            ->call('complete')
            ->assertSee('Nama Lengkap wajib diisi.');
    }

    public function test_complete_registration_ignores_admin_intended_url_after_google_registration(): void
    {
        $this->withSession([
            'url.intended' => route('users.management'),
            'google_registration' => [
                'google_id' => 'google-intended-test-id',
                'name' => 'Pendaftar Intended',
                'email' => 'google-intended@example.test',
                'avatar_url' => null,
            ],
        ]);

        Livewire::test(CompleteRegistration::class)
            ->set('name', 'Pendaftar Intended')
            ->set('email', 'google-intended@example.test')
            ->set('school_origin', 'SMP Intended')
            ->set('nisn', '9911002200')
            ->set('whatsapp', '081234567891')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('complete')
            ->assertRedirect(route('dashboard'));

        $user = User::where('email', 'google-intended@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasRole('Pelanggan/Penanya'));
        $this->assertTrue($user->can('pelanggan.dashboard_antrian'));
        $this->assertNull(session('url.intended'));
    }

    public function test_no_show_requeue_returns_ticket_to_third_waiting_position_repeatedly(): void
    {
        Role::firstOrCreate(['name' => 'officer']);

        $officer = User::factory()->create([
            'email' => 'repeat-officer@example.test',
            'password' => 'password123',
        ]);
        $officer->assignRole('officer');

        $service = QueueService::create([
            'name' => 'Layanan Ulang',
            'slug' => 'layanan-ulang',
            'code' => 'LU',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $counter = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'assigned_user_id' => $officer->id,
            'name' => 'Loket Ulang',
            'code' => 'LU-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $tickets = collect(range(1, 5))->map(function (int $number) use ($service, $counter): QueueTicket {
            $user = User::factory()->create([
                'email' => "repeat-applicant-{$number}@example.test",
                'password' => 'password123',
            ]);

            $applicant = Applicant::create([
                'user_id' => $user->id,
                'full_name' => "Pendaftar {$number}",
                'school_origin' => 'SMP Test',
                'nisn' => '77889900' . $number,
                'whatsapp' => '0811112222' . $number,
                'status' => 'registered',
            ]);

            return QueueTicket::create([
                'applicant_id' => $applicant->id,
                'queue_service_id' => $service->id,
                'service_counter_id' => $counter->id,
                'queue_date' => today(),
                'queue_number' => $number,
                'call_sequence' => $number * 1000,
                'ticket_code' => 'LU-' . str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                'status' => $number === 1 ? QueueTicket::STATUS_CALLED : QueueTicket::STATUS_WAITING,
                'assigned_at' => now(),
                'called_at' => $number === 1 ? now() : null,
            ]);
        })->values();

        Livewire::actingAs($officer)
            ->test(OfficerQueueConsole::class)
            ->call('markNoShow', $tickets[0]->id)
            ->call('requeueNoShow', $tickets[0]->id);

        $this->assertSame([
            $tickets[1]->id,
            $tickets[2]->id,
            $tickets[0]->id,
            $tickets[3]->id,
            $tickets[4]->id,
        ], $this->waitingTicketIds($counter));

        $this->assertSame(1, $tickets[0]->refresh()->no_show_count);

        $tickets[0]->update([
            'status' => QueueTicket::STATUS_CALLED,
            'called_at' => now(),
        ]);

        Livewire::actingAs($officer)
            ->test(OfficerQueueConsole::class)
            ->call('markNoShow', $tickets[0]->id)
            ->call('requeueNoShow', $tickets[0]->id);

        $this->assertSame([
            $tickets[1]->id,
            $tickets[2]->id,
            $tickets[0]->id,
            $tickets[3]->id,
            $tickets[4]->id,
        ], $this->waitingTicketIds($counter));

        $this->assertSame(2, $tickets[0]->refresh()->no_show_count);
    }

    public function test_service_dependency_can_require_previous_service_completion(): void
    {
        Role::firstOrCreate(['name' => 'officer']);

        $officer = User::factory()->create([
            'email' => 'dependency-officer@example.test',
            'password' => 'password123',
        ]);
        $officer->assignRole('officer');

        $applicantUser = User::factory()->create([
            'email' => 'dependency-applicant@example.test',
            'password' => 'password123',
        ]);

        $applicant = Applicant::create([
            'user_id' => $applicantUser->id,
            'full_name' => 'Pendaftar Dependensi',
            'school_origin' => 'SMP Dependensi',
            'nisn' => '4455667788',
            'whatsapp' => '081244556677',
            'status' => 'registered',
        ]);

        $verification = QueueService::create([
            'name' => 'Layanan Awal',
            'slug' => 'layanan-awal',
            'code' => 'LA',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $interview = QueueService::create([
            'name' => 'Layanan Lanjutan',
            'slug' => 'layanan-lanjutan',
            'code' => 'LL',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $verificationCounter = ServiceCounter::create([
            'queue_service_id' => $verification->id,
            'assigned_user_id' => $officer->id,
            'name' => 'Loket Awal',
            'code' => 'LA-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $interviewCounter = ServiceCounter::create([
            'queue_service_id' => $interview->id,
            'assigned_user_id' => $officer->id,
            'name' => 'Loket Lanjutan',
            'code' => 'LL-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        QueueServiceDependency::create([
            'queue_service_id' => $interview->id,
            'required_queue_service_id' => $verification->id,
            'required_status_mode' => QueueServiceDependency::MODE_COMPLETED,
            'is_active' => true,
        ]);

        $verificationTicket = QueueTicket::create([
            'applicant_id' => $applicant->id,
            'queue_service_id' => $verification->id,
            'service_counter_id' => $verificationCounter->id,
            'queue_date' => today(),
            'queue_number' => 1,
            'call_sequence' => 1000,
            'ticket_code' => 'LA-001',
            'status' => QueueTicket::STATUS_WAITING,
            'assigned_at' => now(),
        ]);

        app(QueueRuntimeService::class)->confirmPresenceByOfficer($applicant, $officer);

        Livewire::actingAs($officer)
            ->test(OfficerQueueConsole::class)
            ->set('selectedCounterId', $interviewCounter->id)
            ->call('assignToSelectedCounter', $applicant->id)
            ->assertHasErrors(['search']);

        $this->assertDatabaseMissing('queue_tickets', [
            'applicant_id' => $applicant->id,
            'queue_service_id' => $interview->id,
        ]);

        $verificationTicket->update([
            'status' => QueueTicket::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        Livewire::actingAs($officer)
            ->test(OfficerQueueConsole::class)
            ->set('selectedCounterId', $interviewCounter->id)
            ->call('assignToSelectedCounter', $applicant->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('queue_tickets', [
            'applicant_id' => $applicant->id,
            'queue_service_id' => $interview->id,
            'service_counter_id' => $interviewCounter->id,
            'status' => QueueTicket::STATUS_WAITING,
        ]);
    }

    public function test_application_settings_store_dynamic_values(): void
    {
        AppSetting::putValue('app.name', 'ASA-Tertib', [
            'group' => 'identity',
            'label' => 'Nama Aplikasi',
            'type' => AppSetting::TYPE_STRING,
            'is_public' => true,
            'sort_order' => 1,
        ]);

        AppSetting::putValue('app.logo', 'logos/asa-tertib.png', [
            'group' => 'identity',
            'label' => 'Logo Aplikasi',
            'type' => AppSetting::TYPE_IMAGE,
            'is_public' => true,
            'sort_order' => 2,
        ]);

        $this->assertSame('ASA-Tertib', AppSetting::getValue('app.name'));
        $this->assertSame('logos/asa-tertib.png', AppSetting::getValue('app.logo'));

        $this->assertDatabaseHas('app_settings', [
            'key' => 'app.name',
            'value' => 'ASA-Tertib',
        ]);
    }

    public function test_qr_check_in_confirms_applicant_presence(): void
    {
        Role::firstOrCreate(['name' => 'applicant']);

        $user = User::factory()->create([
            'email' => 'qr-applicant@example.test',
            'password' => 'password123',
        ]);
        $user->assignRole('applicant');

        $applicant = Applicant::create([
            'user_id' => $user->id,
            'full_name' => 'Pendaftar QR',
            'school_origin' => 'SMP QR',
            'nisn' => '1111222233',
            'whatsapp' => '081111222233',
            'status' => 'registered',
        ]);

        $qr = app(QueueRuntimeService::class)->createCheckInQr();
        $path = parse_url($qr['url'], PHP_URL_PATH);

        $response = $this->actingAs($user)->get($path);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('status', 'Kehadiran Anda sudah dikonfirmasi. Silakan menunggu arahan petugas.');

        $this->assertDatabaseHas('attendance_checkins', [
            'applicant_id' => $applicant->id,
            'presence_method' => AttendanceCheckin::METHOD_QR,
            'presence_status' => AttendanceCheckin::STATUS_CHECKED_IN,
        ]);
    }

    public function test_applicant_can_take_queue_with_manual_code(): void
    {
        Role::firstOrCreate(['name' => 'applicant']);

        $user = User::factory()->create([
            'email' => 'manual-code-applicant@example.test',
            'password' => 'password123',
        ]);
        $user->assignRole('applicant');

        $applicant = Applicant::create([
            'user_id' => $user->id,
            'full_name' => 'Pendaftar Kode Manual',
            'school_origin' => 'SMP Manual',
            'nisn' => '1212121212',
            'whatsapp' => '081212121212',
            'status' => 'registered',
        ]);

        $service = QueueService::create([
            'name' => 'Layanan Kode',
            'slug' => 'layanan-kode',
            'code' => 'LK',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $counter = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'name' => 'Loket Kode',
            'code' => 'LK-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $qr = app(QueueRuntimeService::class)->createCheckInQr();

        $this->assertNotEmpty($qr['manualCode']);
        $this->assertTrue($qr['qrCode']->expires_at->greaterThan(now()->addMinutes(119)));
        $this->assertTrue($qr['qrCode']->expires_at->lessThan(now()->addMinutes(121)));

        Livewire::actingAs($user)
            ->test(ApplicantDashboard::class)
            ->call('openQueueScanner', $service->id)
            ->set('queue_code', $qr['manualCode'])
            ->call('claimSelectedService')
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('attendance_checkins', [
            'applicant_id' => $applicant->id,
            'presence_method' => AttendanceCheckin::METHOD_QR,
        ]);

        $this->assertDatabaseHas('queue_tickets', [
            'applicant_id' => $applicant->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $counter->id,
            'status' => QueueTicket::STATUS_WAITING,
        ]);
    }

    public function test_officer_cannot_queue_applicant_before_presence_confirmation(): void
    {
        Role::firstOrCreate(['name' => 'officer']);

        $officer = User::factory()->create([
            'email' => 'presence-officer@example.test',
            'password' => 'password123',
        ]);
        $officer->assignRole('officer');

        $user = User::factory()->create([
            'email' => 'presence-applicant@example.test',
            'password' => 'password123',
        ]);

        $applicant = Applicant::create([
            'user_id' => $user->id,
            'full_name' => 'Pendaftar Hadir',
            'school_origin' => 'SMP Hadir',
            'nisn' => '2222333344',
            'whatsapp' => '082222333344',
            'status' => 'registered',
        ]);

        $service = QueueService::create([
            'name' => 'Layanan Hadir',
            'slug' => 'layanan-hadir',
            'code' => 'LH',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $counter = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'assigned_user_id' => $officer->id,
            'name' => 'Loket Hadir',
            'code' => 'LH-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Livewire::actingAs($officer)
            ->test(OfficerQueueConsole::class)
            ->set('selectedCounterId', $counter->id)
            ->call('assignToSelectedCounter', $applicant->id)
            ->assertHasErrors(['search']);

        $this->assertDatabaseMissing('queue_tickets', [
            'applicant_id' => $applicant->id,
            'queue_service_id' => $service->id,
        ]);

        Livewire::actingAs($officer)
            ->test(OfficerQueueConsole::class)
            ->set('selectedCounterId', $counter->id)
            ->call('confirmApplicantPresence', $applicant->id)
            ->call('assignToSelectedCounter', $applicant->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('queue_tickets', [
            'applicant_id' => $applicant->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $counter->id,
            'status' => QueueTicket::STATUS_WAITING,
        ]);
    }

    public function test_full_quota_blocks_only_that_service(): void
    {
        Role::firstOrCreate(['name' => 'officer']);

        $officer = User::factory()->create([
            'email' => 'quota-officer@example.test',
            'password' => 'password123',
        ]);
        $officer->assignRole('officer');

        $runtime = app(QueueRuntimeService::class);
        $session = $runtime->currentSession();

        $fullService = QueueService::create([
            'name' => 'Layanan Penuh',
            'slug' => 'layanan-penuh',
            'code' => 'LP',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $openService = QueueService::create([
            'name' => 'Layanan Tersedia',
            'slug' => 'layanan-tersedia',
            'code' => 'LS',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $fullCounter = ServiceCounter::create([
            'queue_service_id' => $fullService->id,
            'assigned_user_id' => $officer->id,
            'name' => 'Loket Penuh',
            'code' => 'LP-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $openCounter = ServiceCounter::create([
            'queue_service_id' => $openService->id,
            'assigned_user_id' => $officer->id,
            'name' => 'Loket Tersedia',
            'code' => 'LS-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        ServiceDailyQuota::create([
            'queue_session_id' => $session->id,
            'queue_service_id' => $fullService->id,
            'max_daily_quota' => 1,
            'is_open' => true,
        ]);

        ServiceDailyQuota::create([
            'queue_session_id' => $session->id,
            'queue_service_id' => $openService->id,
            'max_daily_quota' => 2,
            'is_open' => true,
        ]);

        $existingUser = User::factory()->create(['email' => 'quota-existing@example.test']);
        $existingApplicant = Applicant::create([
            'user_id' => $existingUser->id,
            'full_name' => 'Pendaftar Existing',
            'school_origin' => 'SMP Existing',
            'nisn' => '3333444455',
            'whatsapp' => '083333444455',
            'status' => 'registered',
        ]);

        QueueTicket::create([
            'applicant_id' => $existingApplicant->id,
            'queue_session_id' => $session->id,
            'queue_service_id' => $fullService->id,
            'service_counter_id' => $fullCounter->id,
            'queue_date' => today(),
            'queue_number' => 1,
            'call_sequence' => 1000,
            'ticket_code' => 'LP-001',
            'status' => QueueTicket::STATUS_COMPLETED,
            'assigned_at' => now(),
            'completed_at' => now(),
        ]);

        $user = User::factory()->create(['email' => 'quota-applicant@example.test']);
        $applicant = Applicant::create([
            'user_id' => $user->id,
            'full_name' => 'Pendaftar Kuota',
            'school_origin' => 'SMP Kuota',
            'nisn' => '4444555566',
            'whatsapp' => '084444555566',
            'status' => 'registered',
        ]);

        $runtime->confirmPresenceByOfficer($applicant, $officer);

        Livewire::actingAs($officer)
            ->test(OfficerQueueConsole::class)
            ->set('selectedCounterId', $fullCounter->id)
            ->call('assignToSelectedCounter', $applicant->id)
            ->assertHasErrors(['search']);

        Livewire::actingAs($officer)
            ->test(OfficerQueueConsole::class)
            ->set('selectedCounterId', $openCounter->id)
            ->call('assignToSelectedCounter', $applicant->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('queue_tickets', [
            'applicant_id' => $applicant->id,
            'queue_service_id' => $openService->id,
            'service_counter_id' => $openCounter->id,
        ]);
    }

    public function test_disabled_daily_quota_allows_queue_even_when_service_quota_record_is_full(): void
    {
        AppSetting::putValue('queue.daily_quota_enabled', false, [
            'group' => 'queue',
            'label' => 'Aktifkan Quota Harian',
            'type' => AppSetting::TYPE_BOOLEAN,
            'is_public' => false,
            'sort_order' => 2,
        ]);

        $officer = User::factory()->create([
            'email' => 'quota-disabled-officer@example.test',
            'password' => 'password123',
        ]);

        $runtime = app(QueueRuntimeService::class);
        $session = $runtime->currentSession();

        $service = QueueService::create([
            'name' => 'Layanan Quota Nonaktif',
            'slug' => 'layanan-quota-nonaktif',
            'code' => 'LQN',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $counter = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'assigned_user_id' => $officer->id,
            'name' => 'Loket Quota Nonaktif',
            'code' => 'LQN-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        ServiceDailyQuota::create([
            'queue_session_id' => $session->id,
            'queue_service_id' => $service->id,
            'max_daily_quota' => 1,
            'is_open' => true,
        ]);

        $existingUser = User::factory()->create(['email' => 'quota-disabled-existing@example.test']);
        $existingApplicant = Applicant::create([
            'user_id' => $existingUser->id,
            'full_name' => 'Pendaftar Quota Existing',
            'school_origin' => 'SMP Quota',
            'nisn' => '7777888899',
            'whatsapp' => '087777888899',
            'status' => 'registered',
        ]);

        QueueTicket::create([
            'applicant_id' => $existingApplicant->id,
            'queue_session_id' => $session->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $counter->id,
            'queue_date' => today(),
            'queue_number' => 1,
            'call_sequence' => 1000,
            'ticket_code' => 'LQN-001',
            'status' => QueueTicket::STATUS_COMPLETED,
            'assigned_at' => now(),
            'completed_at' => now(),
        ]);

        $user = User::factory()->create(['email' => 'quota-disabled-applicant@example.test']);
        $applicant = Applicant::create([
            'user_id' => $user->id,
            'full_name' => 'Pendaftar Quota Nonaktif',
            'school_origin' => 'SMP Quota',
            'nisn' => '8888999900',
            'whatsapp' => '088888999900',
            'status' => 'registered',
        ]);

        $runtime->confirmPresenceByOfficer($applicant, $officer);

        $quotaStatus = $runtime->quotaStatus($service, $session);

        $this->assertNull($quotaStatus['max']);
        $this->assertFalse($quotaStatus['is_full']);

        $runtime->createTicket($applicant, $service, $counter, $officer, null, null, $session);

        $this->assertDatabaseHas('queue_tickets', [
            'applicant_id' => $applicant->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $counter->id,
        ]);
    }

    public function test_counter_allocation_recommends_under_target_counter(): void
    {
        Role::firstOrCreate(['name' => 'officer']);

        $officer = User::factory()->create([
            'email' => 'allocation-officer@example.test',
            'password' => 'password123',
        ]);
        $officer->assignRole('officer');

        $runtime = app(QueueRuntimeService::class);
        $session = $runtime->currentSession();

        $service = QueueService::create([
            'name' => 'Layanan Alokasi',
            'slug' => 'layanan-alokasi',
            'code' => 'AL',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $counterOne = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'assigned_user_id' => $officer->id,
            'name' => 'Loket Alokasi 1',
            'code' => 'AL-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $counterTwo = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'assigned_user_id' => $officer->id,
            'name' => 'Loket Alokasi 2',
            'code' => 'AL-2',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $counterClosed = ServiceCounter::create([
            'queue_service_id' => $service->id,
            'assigned_user_id' => null,
            'name' => 'Loket Alokasi Tutup',
            'code' => 'AL-3',
            'sort_order' => 3,
            'is_active' => false,
        ]);

        ServiceDailyQuota::create([
            'queue_session_id' => $session->id,
            'queue_service_id' => $service->id,
            'max_daily_quota' => 3,
            'is_open' => true,
        ]);

        $existingUser = User::factory()->create(['email' => 'allocation-existing@example.test']);
        $existingApplicant = Applicant::create([
            'user_id' => $existingUser->id,
            'full_name' => 'Pendaftar Alokasi Existing',
            'school_origin' => 'SMP Alokasi',
            'nisn' => '5555666677',
            'whatsapp' => '085555666677',
            'status' => 'registered',
        ]);

        QueueTicket::create([
            'applicant_id' => $existingApplicant->id,
            'queue_session_id' => $session->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $counterOne->id,
            'queue_date' => today(),
            'queue_number' => 1,
            'call_sequence' => 1000,
            'ticket_code' => 'AL-001',
            'status' => QueueTicket::STATUS_COMPLETED,
            'assigned_at' => now(),
            'completed_at' => now(),
        ]);

        $user = User::factory()->create(['email' => 'allocation-applicant@example.test']);
        $applicant = Applicant::create([
            'user_id' => $user->id,
            'full_name' => 'Pendaftar Alokasi',
            'school_origin' => 'SMP Alokasi',
            'nisn' => '6666777788',
            'whatsapp' => '086666777788',
            'status' => 'registered',
        ]);

        $runtime->confirmPresenceByOfficer($applicant, $officer);

        Livewire::actingAs($officer)
            ->test(OfficerQueueConsole::class)
            ->set('selectedCounterId', $counterOne->id)
            ->call('assignToSelectedCounter', $applicant->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('queue_tickets', [
            'applicant_id' => $applicant->id,
            'queue_service_id' => $service->id,
            'service_counter_id' => $counterTwo->id,
        ]);

        foreach ([$counterOne, $counterTwo, $counterClosed] as $counter) {
            $this->assertDatabaseHas('counter_daily_allocations', [
                'queue_session_id' => $session->id,
                'service_counter_id' => $counter->id,
                'target_quota' => 1,
            ]);
        }
    }

    private function waitingTicketIds(ServiceCounter $counter): array
    {
        return QueueTicket::query()
            ->where('service_counter_id', $counter->id)
            ->where('status', QueueTicket::STATUS_WAITING)
            ->orderBy('call_sequence')
            ->orderBy('id')
            ->pluck('id')
            ->all();
    }
}
