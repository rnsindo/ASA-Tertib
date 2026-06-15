<div class="panel stack" style="max-width: 760px; margin: 0 auto;">
    <div>
        <h1 class="title">Formulir Pendaftaran Lanjutan</h1>
        <p class="subtitle">Email diambil dari Google. Isi ulang Nama Lengkap sesuai identitas pendaftar sebelum menyelesaikan pendaftaran.</p>
    </div>

    <form class="stack" wire:submit="complete">
        <div class="grid grid-2">
            <div class="field">
                <label for="name">Nama Lengkap</label>
                <input id="name" class="input" type="text" wire:model="name" autocomplete="name" required>
                @error('name') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="email">Email Google</label>
                <input id="email" class="input" type="email" wire:model="email" readonly>
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-2">
            <div class="field">
                <label for="school_origin">Sekolah SMP</label>
                <input id="school_origin" class="input" type="text" wire:model="school_origin" required>
                @error('school_origin') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="nisn">NISN</label>
                <input id="nisn" class="input" type="text" wire:model="nisn" inputmode="numeric" required>
                @error('nisn') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="field">
            <label for="whatsapp">Nomor WhatsApp Aktif</label>
            <input id="whatsapp" class="input" type="text" wire:model="whatsapp" inputmode="tel" placeholder="081234567890" required>
            @error('whatsapp') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-2">
            <div class="field">
                <label for="password">Password Baru</label>
                <input id="password" class="input" type="password" wire:model="password" autocomplete="new-password" required>
                @error('password') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input id="password_confirmation" class="input" type="password" wire:model="password_confirmation" autocomplete="new-password" required>
            </div>
        </div>

        <button class="btn btn-primary" type="submit" wire:loading.attr="disabled" wire:target="complete">
            <span class="button-row" wire:loading.remove wire:target="complete">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                Selesaikan Pendaftaran
            </span>
            <span class="button-row" wire:loading wire:target="complete">
                <span class="spinner" aria-hidden="true"></span>
                Memproses Registrasi
            </span>
        </button>
    </form>
</div>
