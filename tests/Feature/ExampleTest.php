<?php

namespace Tests\Feature;

use App\Livewire\Pages\LoginPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_guest_is_redirected_to_login_from_home(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_login_page_is_available(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('<title>Masuk - ASA-Tertib</title>', false);
        $response->assertSee('class="app-shell is-guest"', false);
        $response->assertDontSee('class="app-header"', false);
        $response->assertDontSee('class="bottom-nav"', false);
        $response->assertDontSee('id="sideDrawer"', false);
    }

    public function test_authenticated_user_is_redirected_away_from_login_and_google_redirect(): void
    {
        $user = User::factory()->create([
            'email' => 'already-authenticated@example.test',
            'password' => 'password123',
        ]);

        $this->actingAs($user)->get('/login')->assertRedirect(route('dashboard'));
        $this->actingAs($user)->get('/auth/google/redirect')->assertRedirect(route('dashboard'));
    }

    public function test_disabled_user_cannot_login_with_password(): void
    {
        $user = User::factory()->create([
            'email' => 'disabled-password@example.test',
            'password' => 'password123',
            'is_active' => false,
        ]);

        Livewire::test(LoginPage::class)
            ->set('email', $user->email)
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_disabled_authenticated_user_is_logged_out_by_middleware(): void
    {
        $user = User::factory()->create([
            'email' => 'disabled-session@example.test',
            'password' => 'password123',
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Akun Anda sedang dinonaktifkan. Silakan hubungi admin.');

        $this->assertGuest();
    }

    public function test_disabled_user_cannot_login_with_google(): void
    {
        User::factory()->create([
            'email' => 'disabled-google@example.test',
            'password' => 'password123',
            'is_active' => false,
        ]);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn(new class {
                public function user(): object
                {
                    return new class {
                        public function getId(): string
                        {
                            return 'google-disabled-id';
                        }

                        public function getEmail(): string
                        {
                            return 'disabled-google@example.test';
                        }

                        public function getAvatar(): ?string
                        {
                            return null;
                        }

                        public function getName(): string
                        {
                            return 'Disabled Google';
                        }

                        public function getNickname(): ?string
                        {
                            return null;
                        }
                    };
                }
            });

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Akun Anda sedang dinonaktifkan. Silakan hubungi admin.');

        $this->assertGuest();
    }

    public function test_authenticated_google_invalid_state_returns_to_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'google-invalid-authenticated@example.test',
            'password' => 'password123',
        ]);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn(new class {
                public function user(): void
                {
                    throw new InvalidStateException();
                }
            });

        $this->actingAs($user)
            ->get('/auth/google/callback')
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('status', 'Sesi Google sebelumnya sudah tidak aktif. Anda sudah masuk ke aplikasi.');
    }

    public function test_guest_google_invalid_state_returns_to_login_without_authenticated_chrome(): void
    {
        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn(new class {
                public function user(): void
                {
                    throw new InvalidStateException();
                }
            });

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Sesi login Google sudah kedaluwarsa. Silakan klik Masuk dengan Google kembali.');

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('class="app-shell is-guest"', false);
        $response->assertDontSee('class="app-header"', false);
        $response->assertDontSee('class="bottom-nav"', false);
        $response->assertDontSee('id="sideDrawer"', false);
    }

    public function test_mobile_design_page_is_available(): void
    {
        $response = $this->get('/design');

        $response->assertOk();
        $response->assertSee('id="openDrawer"', false);
        $response->assertSee('id="closeDrawer"', false);
        $response->assertSee('id="sideDrawer"', false);
        $response->assertSee('aria-hidden="true"', false);
        $response->assertDontSee('phone-shell');
        $response->assertDontSee('width: min(430px', false);
        $response->assertDontSee('margin: 0 auto', false);
        $response->assertDontSee('transform: translateX(-50%)', false);
        $response->assertSee('Pelayanan 24/7');
        $response->assertSee('Informasi Pendaftaran');
        $response->assertSee('Panduan Lengkap');
        $response->assertSee('Keluar / Logout');
        $response->assertSee('Welcome, [User Name]');
        $response->assertSee('A-023');
        $response->assertSee('Scan QR');
        $response->assertSee('Home');
        $response->assertSee('Riwayat');
        $response->assertSee('Profil');

        $content = $response->getContent();
        $this->assertLessThan(
            strpos($content, 'Pelayanan 24/7'),
            strpos($content, 'Keluar / Logout'),
        );
    }

    public function test_authenticated_application_pages_use_mobile_master_layout(): void
    {
        $user = User::factory()->create([
            'email' => 'layout-user@example.test',
            'password' => 'password123',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('class="app-shell is-authenticated"', false);
        $response->assertSee('class="app-header"', false);
        $response->assertSee('id="headerLiveClock"', false);
        $response->assertSee('data-timezone="Asia/Jakarta"', false);
        $response->assertSee('class="bottom-nav"', false);
        $response->assertSee('id="sideDrawer"', false);
        $response->assertSee('aria-hidden="true"', false);
        $response->assertSee('padding: 16px 14px 132px', false);
        $response->assertSee('left: 0;', false);
        $response->assertSee('right: 0;', false);
        $response->assertDontSee('phone-shell');
        $response->assertDontSee('width: min(430px', false);
        $response->assertDontSee('transform: translateX(-50%)', false);
    }

    public function test_guest_regular_blade_page_extends_master_without_authenticated_chrome(): void
    {
        $response = $this->get('/test');

        $response->assertOk();
        $response->assertSee('Halaman Test');
        $response->assertSee('<title>Halaman Test - ASA-Tertib</title>', false);
        $response->assertSee('class="app-shell is-guest"', false);
        $response->assertDontSee('class="app-header"', false);
        $response->assertDontSee('class="bottom-nav"', false);
        $response->assertDontSee('id="sideDrawer"', false);
    }

    public function test_not_found_error_page_uses_standalone_layout(): void
    {
        $response = $this->get('/halaman-tidak-ada');

        $response->assertNotFound();
        $response->assertSee('<title>404 - ASA-Tertib</title>', false);
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('class="error-brand"', false);
        $response->assertSee('ASA-Tertib');
        $response->assertSee('Dashboard');
        $response->assertDontSee('class="app-header"', false);
        $response->assertDontSee('class="bottom-nav"', false);
        $response->assertDontSee('id="sideDrawer"', false);
    }

    public function test_error_preview_route_is_removed_and_csrf_error_uses_standalone_layout(): void
    {
        $this->get('/contoh-error/419')->assertNotFound();

        $response = $this->get('/testing-error/419');

        $response->assertStatus(419);
        $response->assertSee('<title>419 - ASA-Tertib</title>', false);
        $response->assertSee('Sesi Sudah Berakhir');
        $response->assertSee('class="error-brand"', false);
        $response->assertSee('Dashboard');
        $response->assertDontSee('class="app-header"', false);
        $response->assertDontSee('class="bottom-nav"', false);
        $response->assertDontSee('id="sideDrawer"', false);
    }

    public function test_forbidden_error_page_uses_standalone_layout(): void
    {
        $user = User::factory()->create([
            'email' => 'forbidden-user@example.test',
            'password' => 'password123',
        ]);

        $response = $this->actingAs($user)->get('/manajemen-user');

        $response->assertForbidden();
        $response->assertSee('<title>403 - ASA-Tertib</title>', false);
        $response->assertSee('Akses Tidak Diizinkan');
        $response->assertSee('class="error-brand"', false);
        $response->assertSee('Dashboard');
        $response->assertDontSee('class="app-header"', false);
        $response->assertDontSee('class="bottom-nav"', false);
        $response->assertDontSee('id="sideDrawer"', false);
    }

    public function test_error_dashboard_button_uses_officer_default_dashboard(): void
    {
        Permission::firstOrCreate(['name' => 'petugas.konsol_antrian']);

        $user = User::factory()->create([
            'email' => 'error-officer@example.test',
            'password' => 'password123',
        ]);
        $user->givePermissionTo('petugas.konsol_antrian');

        $response = $this->actingAs($user)->get('/manajemen-user');

        $response->assertForbidden();
        $response->assertSee('href="' . route('officer.console') . '"', false);
        $response->assertDontSee('class="app-header"', false);
        $response->assertDontSee('class="bottom-nav"', false);
        $response->assertDontSee('id="sideDrawer"', false);
    }
}
