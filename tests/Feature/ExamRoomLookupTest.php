<?php

namespace Tests\Feature;

use App\Livewire\Pages\ExamRoomLookup;
use App\Models\StudentRoomAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExamRoomLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_room_lookup_page_is_available_to_customer_officer_and_super_admin(): void
    {
        foreach (['Pelanggan/Penanya', 'Petugas', 'Super Admin'] as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $user = User::factory()->create(['email' => str_replace(['/', ' '], '-', strtolower($roleName)) . '@example.test']);
            $user->assignRole($role);

            $this->actingAs($user)
                ->get('/cek-ruangan')
                ->assertOk()
                ->assertSee('<title>Cek Ruangan Ujian - ASA-Tertib</title>', false)
                ->assertSee('Cek Ruangan Ujian')
                ->assertSee('Ruangan')
                ->assertSee('href="' . route('exam-room.lookup') . '"', false);
        }
    }

    public function test_exam_room_lookup_requires_birth_date_before_showing_room_and_username(): void
    {
        $user = User::factory()->create(['email' => 'room-lookup-user@example.test']);
        $user->assignRole(Role::firstOrCreate(['name' => 'Pelanggan/Penanya']));

        StudentRoomAssignment::create([
            'nisn' => '0111231231',
            'name' => 'PESERTA UJIAN',
            'junior_school' => 'SMP CONTOH',
            'birth_date' => '2026-06-24',
            'room' => 'RUA7',
        ]);

        Livewire::actingAs($user)
            ->test(ExamRoomLookup::class)
            ->set('nisn', '0111231231')
            ->call('searchParticipant')
            ->assertSee('PESERTA UJIAN')
            ->assertSee('0111231231')
            ->assertSee('SMP CONTOH')
            ->assertSee('Cek Ruangan')
            ->assertDontSee('RUA7')
            ->assertDontSee('smk0111231231')
            ->call('requestRoomCheck')
            ->assertSee('Tanggal Lahir')
            ->set('birthDate', '2026-06-23')
            ->call('verifyBirthDate')
            ->assertHasErrors(['birthDate'])
            ->assertDontSee('RUA7')
            ->set('birthDate', '2026-06-24')
            ->call('verifyBirthDate')
            ->assertHasNoErrors()
            ->assertSee('RUA7')
            ->assertSee('smk0111231231')
            ->assertSee('Akan diberikan saat sudah di ruang ujian.');
    }
}
