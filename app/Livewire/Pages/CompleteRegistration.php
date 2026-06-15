<?php

namespace App\Livewire\Pages;

use App\Models\Applicant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Formulir Pendaftaran Lanjutan')]
class CompleteRegistration extends Component
{
    public string $name = '';
    public string $email = '';
    public string $school_origin = '';
    public string $nisn = '';
    public string $whatsapp = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $googleUser = session('google_registration');

        if (! $googleUser) {
            $this->redirectRoute('login');

            return;
        }

        $this->name = '';
        $this->email = $googleUser['email'] ?? '';
    }

    public function complete()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'school_origin' => ['required', 'string', 'max:255'],
            'nisn' => ['required', 'string', 'max:32', 'unique:applicants,nisn'],
            'whatsapp' => ['required', 'regex:/^\+?[0-9]{10,16}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[A-Za-z]/', 'regex:/[0-9]/'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'unique' => ':attribute sudah terdaftar.',
            'password.min' => 'Password minimal :min karakter.',
            'password.regex' => 'Password harus memuat huruf dan angka.',
            'whatsapp.regex' => 'Nomor WhatsApp harus angka 10-16 digit, boleh diawali +.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ], [
            'name' => 'Nama Lengkap',
            'email' => 'Email Google',
            'school_origin' => 'Sekolah SMP',
            'nisn' => 'NISN',
            'whatsapp' => 'Nomor WhatsApp',
            'password' => 'Password',
        ]);

        $googleUser = session('google_registration');

        if (! $googleUser) {
            return $this->redirectRoute('login');
        }

        $validated['name'] = $this->normalizeUppercase($validated['name']);
        $validated['school_origin'] = $this->normalizeUppercase($validated['school_origin']);
        $validated['nisn'] = trim($validated['nisn']);
        $validated['whatsapp'] = trim($validated['whatsapp']);

        $user = DB::transaction(function () use ($validated, $googleUser) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'google_id' => $googleUser['google_id'] ?? null,
                'avatar_url' => null,
                'phone' => $validated['whatsapp'],
                'password' => $validated['password'],
            ]);

            $customerPermissions = [
                'pelanggan.beranda',
                'pelanggan.dashboard_antrian',
                'pelanggan.status_antrian',
                'pelanggan.scan_qr',
                'pelanggan.riwayat',
                'pelanggan.profil',
            ];

            collect($customerPermissions)->each(function (string $permission): void {
                Permission::firstOrCreate(['name' => $permission]);
            });

            $customerRole = Role::firstOrCreate(['name' => 'Pelanggan/Penanya']);
            $customerRole->syncPermissions($customerPermissions);
            $user->assignRole($customerRole);

            Applicant::create([
                'user_id' => $user->id,
                'full_name' => $validated['name'],
                'school_origin' => $validated['school_origin'],
                'nisn' => $validated['nisn'],
                'whatsapp' => $validated['whatsapp'],
                'status' => 'registered',
            ]);

            return $user;
        });

        Auth::login($user);
        session()->forget('google_registration');
        session()->forget('url.intended');
        session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.pages.complete-registration');
    }

    private function normalizeUppercase(string $value): string
    {
        return mb_strtoupper(trim(preg_replace('/\s+/', ' ', $value) ?? $value));
    }
}
