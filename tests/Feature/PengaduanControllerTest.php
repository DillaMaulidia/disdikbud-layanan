<?php

namespace Tests\Feature;

use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengaduanControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_the_complaint_form(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get('/layanan-permohonan');

        $response->assertStatus(200);
        $response->assertViewIs('layanan');
    }

    public function test_authenticated_user_can_submit_a_complaint(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->post('/layanan-permohonan', [
                'nama_lengkap' => 'Budi Santoso',
                'nomor_telepon' => '081234567890',
                'email' => 'budi@example.com',
                'sasaran_pengaduan' => 'Sekretariat',
                'hal_diadukan' => 'Permohonan informasi layanan.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pengaduans', [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'status' => 'pending',
        ]);

        $this->assertMatchesRegularExpression(
            '/^TKT-\d{8}-\d{3}$/',
            Pengaduan::query()->sole()->nomor_tiket,
        );
    }
}
