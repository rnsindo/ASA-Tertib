<?php

namespace Tests\Feature;

use App\Livewire\Pages\StudentRoomData;
use App\Models\StudentRoomAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentRoomDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_student_room_data_page_and_download_template(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->get('/data-peserta-ruangan')
            ->assertOk()
            ->assertSee('<title>Data Peserta Ruangan - ASA-Tertib</title>', false)
            ->assertSee('Download Template')
            ->assertSee('Proses Upload');

        $this->actingAs($user)
            ->get('/data-peserta-ruangan/template')
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_student_room_data_menu_and_routes_are_protected_by_permission(): void
    {
        Permission::firstOrCreate(['name' => 'admin.data_peserta_ruangan']);

        $userWithoutPermission = User::factory()->create(['email' => 'room-no-access@example.test']);
        $userWithoutPermission->assignRole(Role::firstOrCreate(['name' => 'Petugas']));

        $userWithPermission = User::factory()->create(['email' => 'room-direct-access@example.test']);
        $userWithPermission->givePermissionTo('admin.data_peserta_ruangan');

        $this->actingAs($userWithoutPermission)
            ->get('/petugas')
            ->assertOk()
            ->assertDontSee('Data Peserta Ruangan');

        $this->actingAs($userWithoutPermission)
            ->get('/data-peserta-ruangan')
            ->assertForbidden();

        $this->actingAs($userWithoutPermission)
            ->get('/data-peserta-ruangan/template')
            ->assertForbidden();

        $this->actingAs($userWithPermission)
            ->get('/data-peserta-ruangan')
            ->assertOk()
            ->assertSee('Data Peserta Ruangan')
            ->assertSee('href="' . route('student-room-data.index') . '"', false);

        $this->actingAs($userWithPermission)
            ->get('/data-peserta-ruangan/template')
            ->assertOk();
    }

    public function test_super_admin_can_upload_excel_template_to_create_and_update_room_data(): void
    {
        $user = $this->superAdmin();

        StudentRoomAssignment::create([
            'nisn' => '1234567890',
            'name' => 'NAMA LAMA',
            'junior_school' => 'SMP LAMA',
            'birth_date' => '2010-01-01',
            'room' => 'RUA0',
        ]);

        $file = $this->makeTemplateUpload([
            ['NISN', 'Nama', 'SMP', 'Tanggal Lahir', 'Ruangan'],
            ['1234567890', 'nama baru', 'smp baru', '2010-05-21', 'RUA1'],
            ['9998887776', 'peserta baru', 'smp asal', '22/06/2010', 'RUA2'],
            ['ABC', 'nisn salah', 'smp salah', '2010-01-01', 'RUA3'],
        ]);

        Livewire::actingAs($user)
            ->test(StudentRoomData::class)
            ->set('templateFile', $file)
            ->call('uploadTemplate')
            ->assertHasNoErrors()
            ->assertSee('1 data baru')
            ->assertSee('1 data diperbarui')
            ->assertSee('1 baris dilewati');

        $updated = StudentRoomAssignment::where('nisn', '1234567890')->firstOrFail();
        $created = StudentRoomAssignment::where('nisn', '9998887776')->firstOrFail();

        $this->assertSame('NAMA BARU', $updated->name);
        $this->assertSame('SMP BARU', $updated->junior_school);
        $this->assertSame('2010-05-21', $updated->birth_date->toDateString());
        $this->assertSame('RUA1', $updated->room);
        $this->assertSame($user->id, $updated->imported_by);

        $this->assertSame('PESERTA BARU', $created->name);
        $this->assertSame('SMP ASAL', $created->junior_school);
        $this->assertSame('2010-06-22', $created->birth_date->toDateString());
        $this->assertSame('RUA2', $created->room);
        $this->assertSame($user->id, $created->imported_by);

        $this->assertDatabaseMissing('student_room_assignments', [
            'nisn' => 'ABC',
        ]);
    }

    private function superAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin']);
        $user = User::factory()->create(['email' => 'room-admin@example.test']);
        $user->assignRole($role);

        return $user;
    }

    private function makeTemplateUpload(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows);

        $path = tempnam(sys_get_temp_dir(), 'student-room-template-');
        $xlsxPath = $path . '.xlsx';
        (new Xlsx($spreadsheet))->save($xlsxPath);

        return UploadedFile::fake()->createWithContent(
            'template-data-peserta-ruangan.xlsx',
            file_get_contents($xlsxPath),
        );
    }
}
