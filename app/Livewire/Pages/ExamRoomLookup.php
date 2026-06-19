<?php

namespace App\Livewire\Pages;

use App\Models\StudentRoomAssignment;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cek Ruangan Ujian')]
class ExamRoomLookup extends Component
{
    public string $nisn = '';

    public string $birthDate = '';

    public ?int $assignmentId = null;

    public ?array $foundParticipant = null;

    public bool $showBirthDateForm = false;

    public bool $roomRevealed = false;

    public ?string $notice = null;

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function updatedNisn(): void
    {
        $this->resetLookupResult();
    }

    public function searchParticipant(): void
    {
        $this->resetErrorBag();
        $this->resetLookupResult(false);

        $validated = $this->validate([
            'nisn' => ['required', 'regex:/^[0-9]{5,20}$/'],
        ], [
            'nisn.required' => 'Masukkan NISN terlebih dahulu.',
            'nisn.regex' => 'NISN harus berupa angka 5 sampai 20 digit.',
        ]);

        $assignment = StudentRoomAssignment::query()
            ->where('nisn', trim($validated['nisn']))
            ->first();

        if (! $assignment) {
            $this->notice = 'Data NISN tidak ditemukan. Pastikan NISN yang dimasukkan sudah benar.';

            return;
        }

        $this->assignmentId = $assignment->id;
        $this->foundParticipant = [
            'nisn' => $assignment->nisn,
            'name' => $assignment->name,
            'junior_school' => $assignment->junior_school,
        ];
        $this->notice = null;
    }

    public function requestRoomCheck(): void
    {
        if (! $this->assignmentId || ! $this->foundParticipant) {
            $this->notice = 'Cari NISN terlebih dahulu sebelum cek ruangan.';

            return;
        }

        $this->showBirthDateForm = true;
        $this->roomRevealed = false;
        $this->birthDate = '';
    }

    public function verifyBirthDate(): void
    {
        $this->resetErrorBag();

        $this->validate([
            'birthDate' => ['required', 'date'],
        ], [
            'birthDate.required' => 'Masukkan tanggal lahir terlebih dahulu.',
            'birthDate.date' => 'Format tanggal lahir tidak valid.',
        ]);

        $assignment = $this->currentAssignment();

        if (! $assignment) {
            $this->notice = 'Data peserta tidak ditemukan. Silakan cari NISN kembali.';
            $this->resetLookupResult();

            return;
        }

        if (! $assignment->birth_date || $assignment->birth_date->toDateString() !== Carbon::parse($this->birthDate)->toDateString()) {
            $this->addError('birthDate', 'Tanggal lahir tidak sesuai dengan data peserta.');

            return;
        }

        $this->roomRevealed = true;
        $this->showBirthDateForm = false;
        $this->notice = null;
    }

    public function getRoomProperty(): ?string
    {
        return $this->roomRevealed ? $this->currentAssignment()?->room : null;
    }

    public function getUsernameProperty(): ?string
    {
        return $this->roomRevealed && $this->foundParticipant
            ? 'smk' . $this->foundParticipant['nisn']
            : null;
    }

    public function render(): mixed
    {
        return view('livewire.pages.exam-room-lookup');
    }

    private function currentAssignment(): ?StudentRoomAssignment
    {
        if (! $this->assignmentId) {
            return null;
        }

        return StudentRoomAssignment::query()->find($this->assignmentId);
    }

    private function resetLookupResult(bool $clearNotice = true): void
    {
        $this->assignmentId = null;
        $this->foundParticipant = null;
        $this->showBirthDateForm = false;
        $this->roomRevealed = false;
        $this->birthDate = '';

        if ($clearNotice) {
            $this->notice = null;
        }
    }
}
