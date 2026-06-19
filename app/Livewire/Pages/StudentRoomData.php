<?php

namespace App\Livewire\Pages;

use App\Imports\StudentRoomAssignmentImport;
use App\Models\StudentRoomAssignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.app')]
#[Title('Data Peserta Ruangan')]
class StudentRoomData extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public mixed $templateFile = null;

    public ?array $importSummary = null;

    public function mount(): void
    {
        $user = auth()->user();

        abort_unless(
            $user && ($user->can('admin.data_peserta_ruangan') || $user->hasAnyRole(['superadmin', 'Super Admin'])),
            403,
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function uploadTemplate(): void
    {
        $this->resetErrorBag();
        $this->importSummary = null;

        $this->validate([
            'templateFile' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'templateFile.required' => 'Pilih file template Excel terlebih dahulu.',
            'templateFile.file' => 'File template tidak valid.',
            'templateFile.mimes' => 'Format file harus xlsx, xls, atau csv.',
            'templateFile.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        try {
            if (! class_exists(Excel::class)) {
                throw new \RuntimeException('Package Excel belum tersedia di server. Jalankan composer install --no-dev --optimize-autoloader lalu php artisan optimize:clear.');
            }

            $import = new StudentRoomAssignmentImport(auth()->id());

            Excel::import($import, $this->templateFile->getRealPath());

            $this->importSummary = $import->summary();
            $this->reset('templateFile');
            $this->resetPage();

            $this->dispatch('student-room-notify', [
                'type' => 'success',
                'message' => 'Template berhasil diproses.',
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            $message = 'Upload template gagal. Alasan: ' . $exception->getMessage();
            $this->addError('templateFile', $message);
            $this->dispatch('student-room-notify', [
                'type' => 'error',
                'message' => $message,
            ]);
        }
    }

    public function render(): mixed
    {
        return view('livewire.pages.student-room-data', [
            'records' => $this->records(),
            'totalRecords' => StudentRoomAssignment::query()->count(),
            'totalRooms' => StudentRoomAssignment::query()->distinct('room')->count('room'),
        ]);
    }

    private function records(): LengthAwarePaginator
    {
        $search = trim($this->search);

        return StudentRoomAssignment::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('nisn', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('junior_school', 'like', '%' . $search . '%')
                        ->orWhere('room', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('room')
            ->orderBy('name')
            ->paginate(10);
    }
}
