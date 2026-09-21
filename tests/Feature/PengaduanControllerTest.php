<?php

namespace Tests\Feature;

use App\Models\Pengaduan;
use App\Models\PengaduanLampiran;
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
        $response->assertSeeInOrder(['Dashboard Layanan', 'Buat Pengaduan', 'Tiket Saya']);

        foreach (User::bidang() as $bidang) {
            $response->assertSee($bidang);
        }

        $response->assertSee('Bidang GTK')
            ->assertSee('Subbagian Umum, Kepegawaian, dan Aset')
            ->assertDontSee('Bidang Umum')
            ->assertDontSee('Bidang Ketenagaan');
        $response->assertDontSee('>Sekretariat<');
    }

    public function test_authenticated_user_can_view_the_developer_profiles(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Profil Developer')
            ->assertSee('M. Hilmy Helsinky')
            ->assertSee('yduta21@gmail.com')
            ->assertSee('Dilla Maulidia')
            ->assertSee('dilla.maulidia03@gmail.com')
            ->assertSee('Siti Zahara')
            ->assertSee('sitizahara20042005@gmail.com')
            ->assertSee('Tim Developer terdiri dari tiga mahasiswa');
    }

    public function test_authenticated_user_sees_create_complaint_button_on_dashboard(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Buat Pengaduan')
            ->assertSee(route('pengaduan.create', absolute: false));
    }

    public function test_user_can_submit_a_complaint_to_a_new_field(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/layanan-permohonan', [
                'nama_lengkap' => 'Budi Santoso',
                'nomor_telepon' => '081234567890',
                'email' => 'budi-paud@example.com',
                'sasaran_pengaduan' => 'Bidang Pembinaan PAUD dan Pendidikan Non Formal',
                'hal_diadukan' => 'Permohonan informasi pendidikan non formal.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pengaduans', [
            'email' => 'budi-paud@example.com',
            'sasaran_pengaduan' => 'Bidang Pembinaan PAUD dan Pendidikan Non Formal',
        ]);

        $pengaduan = Pengaduan::query()->where('email', 'budi-paud@example.com')->sole();
        $this->assertStringStartsWith('#PAUD-', $pengaduan->nomor_tiket);
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
                'bukti_pendukung' => [
                    UploadedFile::fake()->image('bukti.jpg'),
                    UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
                ],
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
        $this->assertDatabaseCount('pengaduan_lampirans', 2);
        $this->assertSame(2, $pengaduan->lampirans()->count());

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

    public function test_operator_sees_tickets_separated_by_processing_status(): void
    {
        $operator = User::factory()->create([
            'role' => User::ROLE_OPERATOR,
            'bidang' => 'Sekretariat',
        ]);

        Pengaduan::create([
            'nomor_tiket' => 'TKT-20260911-301',
            'nama_lengkap' => 'Pelapor Belum Diproses',
            'nomor_telepon' => '081234567890',
            'email' => 'belum@example.com',
            'sasaran_pengaduan' => 'Sekretariat',
            'hal_diadukan' => 'Belum diproses.',
            'status' => 'pending',
        ]);
        Pengaduan::create([
            'nomor_tiket' => 'TKT-20260911-302',
            'nama_lengkap' => 'Pelapor Sudah Diproses',
            'nomor_telepon' => '081234567891',
            'email' => 'sudah@example.com',
            'sasaran_pengaduan' => 'Sekretariat',
            'hal_diadukan' => 'Sudah diproses.',
            'status' => 'diproses',
        ]);
        Pengaduan::create([
            'nomor_tiket' => 'TKT-20260911-303',
            'nama_lengkap' => 'Pelapor Selesai',
            'nomor_telepon' => '081234567892',
            'email' => 'selesai@example.com',
            'sasaran_pengaduan' => 'Sekretariat',
            'hal_diadukan' => 'Sudah selesai.',
            'status' => 'selesai',
        ]);

        $this->actingAs($operator)
            ->get(route('pengaduan.tickets'))
            ->assertOk()
            ->assertSee('Tiket Pengaduan')
            ->assertSee('Masih Diproses (2)')
            ->assertSee('Tiket Sudah Selesai (1)')
            ->assertDontSee('Belum Diproses')
            ->assertSeeInOrder(['TKT-20260911-301', 'TKT-20260911-302', 'TKT-20260911-303']);
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
        Storage::fake('local');

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
            ->get(route('pengaduan.show', $pengaduan))
            ->assertOk()
            ->assertSee('Verifikasi Tiket')
            ->assertDontSee('Pilih Foto')
            ->assertDontSee('Kirim Jawaban');

        $this->actingAs($operator)
            ->patch(route('pengaduan.status', $pengaduan), ['status' => 'diproses'])
            ->assertRedirect();
        $this->assertDatabaseHas('pengaduans', [
            'id' => $pengaduan->id,
            'status' => 'diproses',
            'operator_id' => $operator->id,
        ]);
        $pengaduan->refresh();

        $this->actingAs($operator)
            ->get(route('pengaduan.show', $pengaduan))
            ->assertOk()
            ->assertSee('Foto / Dokumen Pendukung')
            ->assertSee('Tidak ada pesan tindak lanjut.');

        $this->actingAs($operator)
            ->patch(route('pengaduan.respond', $pengaduan), ['tanggapan_operator' => 'Pengaduan sedang kami tindak lanjuti.'])
            ->assertRedirect();
        $this->assertDatabaseHas('pengaduans', [
            'id' => $pengaduan->id,
            'tanggapan_operator' => 'Pengaduan sedang kami tindak lanjuti.',
        ]);

        $pengaduan->refresh();
        $this->actingAs($operator)
            ->get(route('pengaduan.show', $pengaduan))
            ->assertOk()
            ->assertSee('Jawaban Operator')
            ->assertSee('Pengaduan sedang kami tindak lanjuti.')
            ->assertDontSee('Selesaikan Laporan');

        $this->actingAs($operator)
            ->patch(route('pengaduan.respond', $pengaduan), [
                'tanggapan_operator' => 'Laporan sudah ditindaklanjuti dan diselesaikan.',
                'bukti_pendukung' => [
                    UploadedFile::fake()->image('bukti-tindak-lanjut.jpg'),
                    UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf'),
                ],
            ])
            ->assertRedirect();
        $pengaduan->refresh();
        $this->assertSame('Laporan sudah ditindaklanjuti dan diselesaikan.', $pengaduan->tanggapan_operator);
        $this->assertSame('selesai', $pengaduan->status);
        $this->assertTrue(Storage::disk('local')->exists($pengaduan->bukti_operator));
        $this->assertSame(2, $pengaduan->lampirans()->where('sumber', PengaduanLampiran::SOURCE_OPERATOR)->count());
    }

    public function test_operator_can_delete_a_ticket_from_their_assigned_field(): void
    {
        Storage::fake('local');

        $operator = User::factory()->create([
            'role' => User::ROLE_OPERATOR,
            'bidang' => 'Sekretariat',
        ]);
        $pengaduan = Pengaduan::create([
            'nomor_tiket' => '#SEK-2026-01000',
            'nama_lengkap' => 'Pelapor Hapus',
            'nomor_telepon' => '081234567890',
            'email' => 'hapus@example.com',
            'sasaran_pengaduan' => 'Sekretariat',
            'hal_diadukan' => 'Laporan yang akan dihapus.',
            'bukti_pendukung' => 'pengaduan/bukti.jpg',
        ]);
        Storage::disk('local')->put($pengaduan->bukti_pendukung, 'bukti');

        $this->actingAs($operator)
            ->delete(route('pengaduan.destroy', $pengaduan))
            ->assertRedirect(route('pengaduan.tickets', absolute: false));

        $this->assertDatabaseMissing('pengaduans', ['id' => $pengaduan->id]);
        Storage::disk('local')->assertMissing('pengaduan/bukti.jpg');
    }

    public function test_non_operator_cannot_delete_a_ticket(): void
    {
        $user = User::factory()->create();
        $pengaduan = Pengaduan::create([
            'nomor_tiket' => '#SEK-2026-01001',
            'nama_lengkap' => 'Pelapor',
            'nomor_telepon' => '081234567890',
            'email' => 'pelapor@example.com',
            'sasaran_pengaduan' => 'Sekretariat',
            'hal_diadukan' => 'Laporan aman.',
        ]);

        $this->actingAs($user)
            ->delete(route('pengaduan.destroy', $pengaduan))
            ->assertForbidden();

        $this->assertDatabaseHas('pengaduans', ['id' => $pengaduan->id]);
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
