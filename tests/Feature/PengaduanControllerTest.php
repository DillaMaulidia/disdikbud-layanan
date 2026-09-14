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

    public function test_dashboard_shows_profile_navigation_for_authenticated_users(): void
    {
        $user = User::factory()->create([
            'name' => 'Budi Santoso',
        ]);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('profile-avatar');
        $response->assertSee('Logout');
        $response->assertDontSee('>Login<');
        $response->assertDontSee('>Register<');
    }

    public function test_user_can_view_their_tickets_on_a_separate_page(): void
    {
        $user = User::factory()->create();

        Pengaduan::create([
            'user_id' => $user->id,
            'nomor_tiket' => '#SEK-2026-00125',
            'nama_lengkap' => $user->name,
            'nomor_telepon' => '081234567890',
            'email' => $user->email,
            'sasaran_pengaduan' => 'Sekretariat',
            'hal_diadukan' => 'Pengaduan yang ditampilkan di halaman tiket.',
        ]);

        $response = $this->actingAs($user)->get(route('pengaduan.tickets'));

        $response->assertStatus(200);
        $response->assertViewIs('tickets.index');
        $response->assertSee('#SEK-2026-00125');
        $response->assertSee('Pengaduan yang ditampilkan di halaman tiket.');
    }

    public function test_user_can_open_ticket_detail_from_the_action(): void
    {
        $user = User::factory()->create();
        $pengaduan = Pengaduan::create([
            'user_id' => $user->id,
            'nomor_tiket' => '#SD-2026-0042',
            'nama_lengkap' => $user->name,
            'nomor_telepon' => '081234567890',
            'email' => $user->email,
            'sasaran_pengaduan' => 'Bidang Pembinaan SD',
            'hal_diadukan' => 'Paving blok di halaman sekolah rusak.',
        ]);

        $response = $this->actingAs($user)->get(route('pengaduan.show', $pengaduan));

        $response->assertStatus(200);
        $response->assertViewIs('tickets.show');
        $response->assertSee('#SD-2026-0042');
        $response->assertSee('Paving blok di halaman sekolah rusak.');
    }

    public function test_user_cannot_open_another_users_ticket_detail(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $pengaduan = Pengaduan::create([
            'user_id' => $otherUser->id,
            'nomor_tiket' => '#SD-2026-0043',
            'nama_lengkap' => $otherUser->name,
            'nomor_telepon' => '081234567890',
            'email' => $otherUser->email,
            'sasaran_pengaduan' => 'Bidang Pembinaan SD',
            'hal_diadukan' => 'Pengaduan milik orang lain.',
        ]);

        $response = $this->actingAs($user)->get(route('pengaduan.show', $pengaduan));

        $response->assertNotFound();
    }

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

        $user = User::factory()->create();

        $response = $this->actingAs($user)
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
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $pengaduan = Pengaduan::query()->sole();

        $this->assertNotNull($pengaduan->bukti_pendukung);
        Storage::disk('local')->assertExists($pengaduan->bukti_pendukung);

        $this->assertMatchesRegularExpression(
            '/^#(SD|SMP|KBD|SEK)-\d{4}-\d{5}$/',
            $pengaduan->nomor_tiket,
        );
    }

    public function test_user_only_sees_their_own_complaints(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Pengaduan::create([
            'user_id' => $user->id,
            'nomor_tiket' => 'TKT-20260911-101',
            'nama_lengkap' => $user->name,
            'nomor_telepon' => '081234567890',
            'email' => $user->email,
            'sasaran_pengaduan' => 'Sekretariat',
            'hal_diadukan' => 'Pengaduan milik sendiri.',
        ]);
        Pengaduan::create([
            'user_id' => $otherUser->id,
            'nomor_tiket' => 'TKT-20260911-102',
            'nama_lengkap' => $otherUser->name,
            'nomor_telepon' => '081234567891',
            'email' => $otherUser->email,
            'sasaran_pengaduan' => 'Sekretariat',
            'hal_diadukan' => 'Pengaduan milik user lain.',
        ]);

        $response = $this->actingAs($user)->get(route('pengaduan.tickets'));

        $response->assertSee('TKT-20260911-101');
        $response->assertDontSee('TKT-20260911-102');
    }

    public function test_operator_only_sees_complaints_for_their_assigned_field(): void
    {
        $operator = User::factory()->create([
            'role' => User::ROLE_OPERATOR,
            'bidang' => 'Sekretariat',
        ]);

        foreach ([['TKT-20260911-201', 'Sekretariat'], ['TKT-20260911-202', 'Bidang Kebudayaan']] as [$ticket, $field]) {
            Pengaduan::create([
                'nomor_tiket' => $ticket,
                'nama_lengkap' => 'Pelapor',
                'nomor_telepon' => '081234567890',
                'email' => 'pelapor-'.$ticket.'@example.com',
                'sasaran_pengaduan' => $field,
                'hal_diadukan' => 'Isi pengaduan.',
            ]);
        }

        $response = $this->actingAs($operator)->get(route('pengaduan.tickets'));

        $response->assertSee('TKT-20260911-201');
        $response->assertDontSee('TKT-20260911-202');
    }

    public function test_operator_is_sent_directly_to_the_ticket_page(): void
    {
        $operator = User::factory()->create([
            'role' => User::ROLE_OPERATOR,
            'bidang' => 'Sekretariat',
        ]);

        $response = $this->actingAs($operator)->get(route('dashboard'));

        $response->assertRedirect(route('pengaduan.tickets', absolute: false));
    }

    public function test_operator_cannot_open_the_complaint_form(): void
    {
        $operator = User::factory()->create([
            'role' => User::ROLE_OPERATOR,
            'bidang' => 'Sekretariat',
        ]);

        $response = $this->actingAs($operator)->get(route('pengaduan.create'));

        $response->assertRedirect(route('pengaduan.tickets', absolute: false));
    }

    public function test_operator_can_verify_answer_and_complete_a_ticket(): void
    {
        $operator = User::factory()->create([
            'role' => User::ROLE_OPERATOR,
            'bidang' => 'Sekretariat',
        ]);
        $pengaduan = Pengaduan::create([
            'nomor_tiket' => '#SEK-2026-00999',
            'nama_lengkap' => 'Pelapor',
            'nomor_telepon' => '081234567890',
            'email' => 'pelapor@example.com',
            'sasaran_pengaduan' => 'Sekretariat',
            'hal_diadukan' => 'Permohonan tindak lanjut.',
        ]);

        $this->actingAs($operator)
            ->patch(route('pengaduan.status', $pengaduan), ['status' => 'diproses'])
            ->assertRedirect();
        $this->assertDatabaseHas('pengaduans', [
            'id' => $pengaduan->id,
            'status' => 'diproses',
            'operator_id' => $operator->id,
        ]);

        $this->actingAs($operator)
            ->patch(route('pengaduan.respond', $pengaduan), ['tanggapan_operator' => 'Pengaduan sedang kami tindak lanjuti.'])
            ->assertRedirect();
        $this->assertDatabaseHas('pengaduans', [
            'id' => $pengaduan->id,
            'tanggapan_operator' => 'Pengaduan sedang kami tindak lanjuti.',
        ]);

        $this->actingAs($operator)
            ->patch(route('pengaduan.status', $pengaduan), ['status' => 'selesai'])
            ->assertRedirect();
        $this->assertDatabaseHas('pengaduans', [
            'id' => $pengaduan->id,
            'status' => 'selesai',
        ]);
    }

    public function test_regular_user_cannot_update_ticket_status(): void
    {
        $user = User::factory()->create();
        $pengaduan = Pengaduan::create([
            'user_id' => $user->id,
            'nomor_tiket' => '#SEK-2026-01000',
            'nama_lengkap' => $user->name,
            'nomor_telepon' => '081234567890',
            'email' => $user->email,
            'sasaran_pengaduan' => 'Sekretariat',
            'hal_diadukan' => 'Pengaduan user.',
        ]);

        $response = $this->actingAs($user)
            ->patch(route('pengaduan.status', $pengaduan), ['status' => 'selesai']);

        $response->assertForbidden();
    }
}
