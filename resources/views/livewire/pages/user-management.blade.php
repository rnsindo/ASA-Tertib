<div class="stack user-management-page">
    <style>
        .user-management-page {
            gap: 14px;
        }

        .user-filter-card,
        .user-card {
            display: grid;
            gap: 12px;
        }

        .user-filter-grid {
            display: grid;
            grid-template-columns: 1fr 180px;
            gap: 10px;
        }

        .user-card-head {
            display: grid;
            grid-template-columns: 48px 1fr;
            gap: 12px;
            align-items: center;
        }

        .user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: var(--primary-soft);
            color: var(--primary-deep);
            font-weight: 900;
            border: 1px solid #bfdbfe;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-title {
            min-width: 0;
        }

        .user-title strong {
            display: block;
            color: var(--primary-deep);
            font-size: 15px;
            line-height: 1.25;
        }

        .user-title span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
            word-break: break-word;
        }

        .role-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .user-actions {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .inline-form {
            display: contents;
        }

        .card-reset-result {
            display: grid;
            gap: 8px;
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: var(--success);
            padding: 10px;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
        }

        .password-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 8px 10px;
            border-radius: 8px;
            background: #fff;
            border: 1px solid #bbf7d0;
            color: #14532d;
            font-weight: 900;
            letter-spacing: 0;
        }

        .reset-copy-row {
            display: grid;
            grid-template-columns: 1fr 44px;
            gap: 8px;
            align-items: center;
        }

        .copy-account-button {
            width: 44px;
            height: 40px;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            background: #fff;
            color: var(--success);
            display: grid;
            place-items: center;
            cursor: pointer;
        }

        .copy-account-button[data-copied="1"] {
            background: var(--success);
            color: #fff;
        }

        .copy-feedback {
            display: none;
            color: var(--success);
            font-size: 12px;
            font-weight: 700;
        }

        .copy-feedback.is-visible {
            display: block;
        }

        .load-more-sentinel {
            min-height: 54px;
            display: grid;
            place-items: center;
            color: var(--muted);
            font-size: 12px;
        }

        .user-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: grid;
            align-items: end;
            background: rgba(8, 31, 67, .44);
            padding: 18px 12px;
        }

        .user-modal-card {
            width: min(720px, 100%);
            max-height: min(84vh, 760px);
            margin: 0 auto;
            overflow: auto;
            background: #fff;
            border: 1px solid #d8e5f7;
            border-radius: 18px 18px 8px 8px;
            padding: 16px;
            box-shadow: 0 24px 60px rgba(8, 31, 67, .28);
            display: grid;
            gap: 14px;
        }

        .modal-head {
            display: grid;
            grid-template-columns: 1fr 40px;
            gap: 10px;
            align-items: start;
        }

        .modal-head strong {
            display: block;
            color: var(--primary-deep);
            font-size: 18px;
        }

        .modal-head span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
            word-break: break-word;
        }

        .icon-only {
            width: 40px;
            height: 40px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #fff;
            color: var(--primary-deep);
            display: grid;
            place-items: center;
            cursor: pointer;
        }

        .permission-groups {
            display: grid;
            gap: 12px;
        }

        .account-status-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: center;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fbff;
        }

        .account-status-row strong {
            display: block;
            color: var(--primary-deep);
            font-size: 14px;
        }

        .account-status-row span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
        }

        .switch {
            position: relative;
            display: inline-grid;
            width: 56px;
            height: 32px;
            cursor: pointer;
        }

        .switch input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .switch-track {
            border-radius: 999px;
            background: #cbd5e1;
            border: 1px solid #94a3b8;
            transition: background .18s ease, border-color .18s ease;
        }

        .switch-track::after {
            content: "";
            position: absolute;
            width: 24px;
            height: 24px;
            left: 4px;
            top: 4px;
            border-radius: 999px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(15, 23, 42, .22);
            transition: transform .18s ease;
        }

        .switch input:checked + .switch-track {
            background: var(--primary);
            border-color: var(--primary);
        }

        .switch input:checked + .switch-track::after {
            transform: translateX(24px);
        }

        .role-check-grid {
            display: grid;
            gap: 8px;
        }

        .role-check-row {
            display: grid;
            grid-template-columns: 26px 1fr;
            gap: 8px;
            align-items: start;
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fbff;
        }

        .role-check-row input {
            margin-top: 3px;
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        .role-check-row strong {
            display: block;
            color: var(--primary-deep);
            font-size: 14px;
        }

        .role-check-row span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
        }

        .permission-group {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fbff;
            overflow: hidden;
        }

        .permission-group-title {
            padding: 10px 12px;
            border-bottom: 1px solid var(--line);
            color: var(--primary-deep);
            font-weight: 900;
            background: #eef6ff;
        }

        .permission-list {
            display: grid;
        }

        .permission-row {
            display: grid;
            grid-template-columns: 26px 1fr;
            gap: 8px;
            align-items: start;
            padding: 10px 12px;
            border-bottom: 1px solid var(--line);
        }

        .permission-row:last-child {
            border-bottom: 0;
        }

        .permission-row input {
            margin-top: 3px;
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        .permission-name {
            color: var(--primary-deep);
            font-weight: 800;
            font-size: 13px;
            word-break: break-word;
        }

        .permission-note {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 5px;
        }

        .permission-tag {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 3px 7px;
            font-size: 11px;
            font-weight: 800;
            background: #eaf2ff;
            color: var(--primary-deep);
        }

        .permission-tag.role-default {
            background: #dcfce7;
            color: #166534;
        }

        .user-toast {
            position: fixed;
            left: 14px;
            right: 14px;
            bottom: 98px;
            z-index: 90;
            display: none;
            border-radius: 8px;
            padding: 12px 14px;
            color: #fff;
            font-weight: 800;
            box-shadow: 0 16px 34px rgba(15, 23, 42, .24);
        }

        .user-toast.is-visible { display: block; }
        .user-toast.success { background: var(--success); }
        .user-toast.error { background: var(--danger); }

        @media (max-width: 640px) {
            .user-filter-grid,
            .user-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div id="userToast" class="user-toast" role="status" aria-live="polite"></div>

    <section class="panel user-filter-card">
        <div class="user-filter-grid">
            <div class="field">
                <label for="search">Cari User</label>
                <input id="search" class="input" type="search" wire:model.live.debounce.400ms="search" placeholder="Nama, email, atau no HP">
            </div>
            <div class="field">
                <label for="roleFilter">Role</label>
                <select id="roleFilter" class="select" wire:model.live="roleFilter">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}">{{ $role }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <div class="stack">
        @forelse($users as $user)
            <section class="panel user-card" wire:key="user-{{ $user->id }}">
                <div class="user-card-head">
                    <div class="user-avatar">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="Avatar {{ $user->name }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">
                            <span style="display: none;">{{ mb_substr($user->name ?: 'U', 0, 1) }}</span>
                        @else
                            <span>{{ mb_substr($user->name ?: 'U', 0, 1) }}</span>
                        @endif
                    </div>
                    <div class="user-title">
                        <strong>{{ $user->name }}</strong>
                        <span>{{ $user->email }}</span>
                    </div>
                </div>

                <div class="role-list">
                    <span class="badge {{ $user->is_active ? '' : 'badge-danger' }}">
                        {{ $user->is_active ? 'Aktif' : 'Disable' }}
                    </span>
                    @forelse($user->roles as $role)
                        <span class="badge">{{ $role->name }}</span>
                    @empty
                        <span class="badge">Tanpa Role</span>
                    @endforelse
                </div>

                @if(isset($resetPasswords[$user->id]))
                    @php
                        $accountCopyText = "Nama: {$user->name}\nEmail: {$user->email}\nPassword: {$resetPasswords[$user->id]}";
                    @endphp
                    <div class="card-reset-result">
                        <strong>Password baru</strong>
                        <div class="reset-copy-row">
                            <span class="password-chip">{{ $resetPasswords[$user->id] }}</span>
                            <button
                                class="copy-account-button"
                                type="button"
                                title="Copy detail akun"
                                aria-label="Copy detail akun {{ $user->name }}"
                                onclick="window.copyAccountDetails(@js($accountCopyText), this)"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 8h10v12H8z"/><path d="M6 16H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                        <span class="copy-feedback" aria-live="polite">Data berhasil di-copy</span>
                        <span style="font-size: 12px;">Berikan password ini ke user terkait.</span>
                    </div>
                @endif

                <div class="user-actions">
                    <button class="btn btn-outline" type="button" wire:click="openPermissionModal({{ $user->id }})">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4 7v6c0 5 3.5 7.5 8 8 4.5-.5 8-3 8-8V7z"/><path d="m9 12 2 2 4-4"/></svg>
                        Edit Permission
                    </button>

                    <button class="btn btn-outline" type="button" wire:click="resetPassword({{ $user->id }})" wire:loading.attr="disabled" wire:target="resetPassword({{ $user->id }})">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 7a4 4 0 1 1-3.46 6"/><path d="M14 14 4 24"/><path d="m9 19 2 2"/><path d="m12 16 2 2"/></svg>
                        <span wire:loading.remove wire:target="resetPassword({{ $user->id }})">Reset Password</span>
                        <span wire:loading wire:target="resetPassword({{ $user->id }})">Memproses...</span>
                    </button>

                    @if(auth()->id() !== $user->id && ! session()->has('impersonator_id'))
                        <form class="inline-form" method="POST" action="{{ route('users.impersonate.take', $user) }}">
                            @csrf
                            <button class="btn btn-primary" type="submit">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v4"/><path d="M10 14 21 3"/><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/></svg>
                                Login As
                            </button>
                        </form>
                    @else
                        <button class="btn" type="button" disabled>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 20a6 6 0 0 0-12 0"/><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
                            Akun Aktif
                        </button>
                    @endif
                </div>
            </section>
        @empty
            <div class="empty">Belum ada user yang sesuai dengan filter.</div>
        @endforelse

        @if($hasMoreUsers)
            <div
                class="load-more-sentinel"
                wire:key="user-load-more-{{ $visibleCount }}-{{ md5($search . '|' . $roleFilter) }}"
                wire:poll.visible.750ms="loadMore"
            >
                Memuat data berikutnya...
            </div>
        @else
            <div class="load-more-sentinel">Semua data sudah ditampilkan.</div>
        @endif
    </div>

    @if($isPermissionModalOpen)
        <div class="user-modal-backdrop" wire:key="permission-modal-{{ $editingPermissionUserId }}">
            <section class="user-modal-card">
                <div class="modal-head">
                    <div>
                        <strong>Edit Permission</strong>
                        <span>{{ $editingPermissionUserName }} - {{ $editingPermissionUserEmail }}</span>
                    </div>
                    <button class="icon-only" type="button" wire:click="closePermissionModal" aria-label="Tutup modal permission">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="role-list">
                    @forelse($editingPermissionUserRoles as $roleName)
                        <span class="badge">{{ $roleName }}</span>
                    @empty
                        <span class="badge">Tanpa Role</span>
                    @endforelse
                </div>

                <div class="alert">
                    Role menentukan permission default. Permission bertanda <strong>Bawaan role</strong> otomatis aktif dari role user. Sistem mencegah Super Admin terakhir terhapus, akun sendiri dinonaktifkan, atau akun sendiri kehilangan akses Super Admin.
                </div>

                <div class="account-status-row">
                    <span>
                        <strong>Status Akun</strong>
                        <span>{{ $editingUserIsActive ? 'Aktif dan bisa login.' : 'Disable dan tidak bisa login.' }}</span>
                    </span>
                    <label class="switch" aria-label="Status akun">
                        <input type="checkbox" wire:model.live="editingUserIsActive">
                        <span class="switch-track"></span>
                    </label>
                </div>

                <div class="permission-group">
                    <div class="permission-group-title">Role User</div>
                    <div class="role-check-grid" style="padding: 10px;">
                        @foreach($allRoles as $role)
                            @php
                                $rolePermissionCount = $role->permissions->count();
                                $isSelfSuperAdminRole = $editingPermissionUserId === auth()->id() && $role->name === 'Super Admin' && in_array('Super Admin', $editingPermissionUserRoles, true);
                            @endphp
                            <label class="role-check-row" wire:key="role-check-{{ $role->id }}">
                                <input
                                    type="checkbox"
                                    value="{{ $role->name }}"
                                    wire:model="selectedRoleNames"
                                    @disabled($isSelfSuperAdminRole)
                                >
                                <span>
                                    <strong>{{ $role->name }}</strong>
                                    <span>{{ $rolePermissionCount }} permission bawaan role</span>
                                    @if($isSelfSuperAdminRole)
                                        <span class="permission-tag role-default" style="margin-top: 6px;">Dikunci untuk akun sendiri</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                @error('selectedRoleNames') <div class="alert alert-danger">{{ $message }}</div> @enderror
                @error('selectedDirectPermissions') <div class="alert alert-danger">{{ $message }}</div> @enderror

                <div class="permission-groups">
                    @foreach($permissionsByGroup as $groupName => $permissions)
                        <div class="permission-group" wire:key="permission-group-{{ $groupName }}">
                            <div class="permission-group-title">{{ $groupName }}</div>
                            <div class="permission-list">
                                @foreach($permissions as $permission)
                                    @php
                                        $isRoleDefault = in_array($permission->name, $roleDefaultPermissionNames, true);
                                        $isDirect = in_array($permission->name, $selectedDirectPermissions, true);
                                    @endphp
                                    <label class="permission-row" wire:key="permission-{{ $permission->id }}">
                                        <input
                                            type="checkbox"
                                            value="{{ $permission->name }}"
                                            wire:model="selectedDirectPermissions"
                                            @checked($isRoleDefault || $isDirect)
                                            @disabled($isRoleDefault)
                                        >
                                        <span>
                                            <span class="permission-name">{{ $permission->name }}</span>
                                            <span class="permission-note">
                                                @if($isRoleDefault)
                                                    <span class="permission-tag role-default">Bawaan role</span>
                                                @endif
                                                @if($isDirect && ! $isRoleDefault)
                                                    <span class="permission-tag">Tambahan user</span>
                                                @endif
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <button class="btn btn-primary" type="button" wire:click="savePermissions" wire:loading.attr="disabled" wire:target="savePermissions">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
                    <span wire:loading.remove wire:target="savePermissions">Simpan Role & Permission</span>
                    <span wire:loading wire:target="savePermissions">Menyimpan...</span>
                </button>
            </section>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('user-notify', (payload) => {
                const event = Array.isArray(payload) ? payload[0] : payload;
                const toast = document.getElementById('userToast');

                if (! toast || ! event) {
                    return;
                }

                toast.textContent = event.message || 'Proses selesai.';
                toast.className = 'user-toast is-visible ' + (event.type === 'error' ? 'error' : 'success');

                window.clearTimeout(window.__userToastTimer);
                window.__userToastTimer = window.setTimeout(() => {
                    toast.className = 'user-toast';
                }, 2400);
            });
        });

        window.copyAccountDetails = function (text, button) {
            const done = () => {
                if (! button) {
                    return;
                }

                button.dataset.copied = '1';
                const feedback = button.closest('.card-reset-result')?.querySelector('.copy-feedback');

                if (feedback) {
                    feedback.classList.add('is-visible');
                }

                window.setTimeout(() => {
                    button.dataset.copied = '0';

                    if (feedback) {
                        feedback.classList.remove('is-visible');
                    }
                }, 1200);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done).catch(() => fallbackCopy(text, done));

                return;
            }

            fallbackCopy(text, done);
        };

        function fallbackCopy(text, done) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', 'readonly');
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            done();
        }
    </script>
</div>
