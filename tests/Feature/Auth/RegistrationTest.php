<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertSame('user', auth()->user()->role);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_admin_and_operator_roles_are_supported(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $operator = User::factory()->create(['role' => 'operator']);

        $this->assertSame('admin', $admin->role);
        $this->assertSame('operator', $operator->role);
    }

    public function test_admin_can_create_new_admin_or_operator_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'Operator Baru',
                'email' => 'operatorbaru@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'operator',
            ]);

        $response->assertRedirect(route('admin.users.create', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'operatorbaru@example.com',
            'role' => 'operator',
        ]);
    }

    public function test_non_admin_users_cannot_access_admin_user_management(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->get('/admin/users/create');

        $response->assertStatus(403);
    }

    public function test_admin_dashboard_shows_active_operators_and_statistics(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Utama']);
        $operator = User::factory()->create(['role' => 'operator', 'name' => 'Operator Aktif']);

        \App\Models\Pengaduan::create([
            'nomor_tiket' => 'TKT-20260910-001',
            'nama_lengkap' => 'Pelapor Satu',
            'nomor_telepon' => '081234567890',
            'email' => 'pelapor1@example.com',
            'sasaran_pengaduan' => 'Sekretariat',
            'hal_diadukan' => 'Laporan satu.',
            'status' => 'pending',
        ]);

        \App\Models\Pengaduan::create([
            'nomor_tiket' => 'TKT-20260910-002',
            'nama_lengkap' => 'Pelapor Dua',
            'nomor_telepon' => '081234567891',
            'email' => 'pelapor2@example.com',
            'sasaran_pengaduan' => 'Bidang Kebudayaan',
            'hal_diadukan' => 'Laporan dua.',
            'status' => 'selesai',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertSee('Operator Aktif');
        $response->assertSee('Statistik Pengaduan');
        $response->assertSee('pending');
        $response->assertSee('selesai');
    }
}
