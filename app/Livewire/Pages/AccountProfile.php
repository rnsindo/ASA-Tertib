<?php

namespace App\Livewire\Pages;

use App\Models\Applicant;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Profil Akun')]
class AccountProfile extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $schoolOrigin = '';
    public string $nisn = '';
    public string $whatsapp = '';
    public bool $hasApplicantProfile = false;

    public function mount(): void
    {
        $user = auth()->user();

        abort_unless($this->canOpenProfile($user), 403);

        $this->loadProfile($user);
    }

    public function save(): void
    {
        $user = auth()->user();

        abort_unless($this->canOpenProfile($user), 403);

        $applicant = $user->applicant;
        $this->hasApplicantProfile = (bool) $applicant;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'regex:/^\+?[0-9]{10,16}$/'],
            'schoolOrigin' => [$applicant ? 'required' : 'nullable', 'string', 'max:255'],
            'nisn' => [
                $applicant ? 'required' : 'nullable',
                'string',
                'max:32',
                $applicant
                    ? Rule::unique('applicants', 'nisn')->ignore($applicant->id)
                    : Rule::unique('applicants', 'nisn'),
            ],
            'whatsapp' => [$applicant ? 'required' : 'nullable', 'regex:/^\+?[0-9]{10,16}$/'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'phone.regex' => 'Nomor telepon harus angka 10-16 digit, boleh diawali +.',
            'schoolOrigin.required' => 'Nama sekolah wajib diisi.',
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.unique' => 'NISN sudah digunakan oleh pendaftar lain.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.regex' => 'Nomor WhatsApp harus angka 10-16 digit, boleh diawali +.',
        ], [
            'name' => 'Nama Lengkap',
            'phone' => 'Nomor Telepon',
            'schoolOrigin' => 'Nama Sekolah',
            'nisn' => 'NISN',
            'whatsapp' => 'Nomor WhatsApp',
        ]);

        $normalizedName = $this->normalizeUppercase($validated['name']);
        $normalizedSchool = $this->normalizeUppercase((string) ($validated['schoolOrigin'] ?? ''));
        $phone = trim((string) ($validated['phone'] ?? ''));
        $whatsapp = trim((string) ($validated['whatsapp'] ?? ''));

        $user->forceFill([
            'name' => $normalizedName,
            'phone' => $phone !== '' ? $phone : null,
        ])->save();

        if ($applicant) {
            $applicant->forceFill([
                'full_name' => $normalizedName,
                'school_origin' => $normalizedSchool,
                'nisn' => trim((string) ($validated['nisn'] ?? '')),
                'whatsapp' => $whatsapp,
            ])->save();

            if ($phone === '') {
                $user->forceFill(['phone' => $whatsapp])->save();
            }
        }

        session()->flash('status', 'Profil akun berhasil diperbarui.');
        $this->loadProfile($user->fresh(['applicant']));
    }

    public function render()
    {
        return view('livewire.pages.account-profile');
    }

    private function loadProfile(User $user): void
    {
        $applicant = $user->applicant;

        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->phone = (string) ($user->phone ?? '');
        $this->hasApplicantProfile = (bool) $applicant;
        $this->schoolOrigin = (string) ($applicant?->school_origin ?? '');
        $this->nisn = (string) ($applicant?->nisn ?? '');
        $this->whatsapp = (string) ($applicant?->whatsapp ?? '');
    }

    private function canOpenProfile(?User $user): bool
    {
        return (bool) $user
            && (
                $user->can('pengguna.profil')
                || $user->can('pelanggan.profil')
                || $user->hasAnyRole([
                    'Super Admin',
                    'Petugas',
                    'Pelanggan/Penanya',
                    'superadmin',
                    'admin',
                    'officer',
                    'applicant',
                    'Pengguna',
                    'Pendaftar',
                    'Pelanggan',
                ])
            );
    }

    private function normalizeUppercase(string $value): string
    {
        return mb_strtoupper(trim(preg_replace('/\s+/', ' ', $value) ?? $value));
    }
}
