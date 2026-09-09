<?php

namespace Tests\Feature;

use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        Storage::fake('local');

        $response = $this->actingAs(User::factory()->create())
            ->post('/layanan-permohonan', [
                'nama_lengkap' => 'Budi Santoso',
                'nomor_telepon' => '081234567890',
                'email' => 'budi@example.com',
                'sasaran_pengaduan' => 'Sekretariat',
                'hal_diadukan' => 'Permohonan informasi layanan.',
                'bukti_pendukung' => UploadedFile::fake()->image('bukti.jpg'),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pengaduans', [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'status' => 'pending',
        ]);

        $pengaduan = Pengaduan::query()->sole();

        $this->assertNotNull($pengaduan->bukti_pendukung);
        Storage::disk('local')->assertExists($pengaduan->bukti_pendukung);

        $this->assertMatchesRegularExpression(
            '/^TKT-\d{8}-\d{3}$/',
            $pengaduan->nomor_tiket,
        );
    }
}
