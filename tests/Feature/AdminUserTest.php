<?php

namespace Tests\Feature;

use App\Exports\PengaduanExport;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_a_field_to_an_operator(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Operator GTK',
            'email' => 'operator@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_OPERATOR,
            'bidang' => 'Bidang GTK',
        ]);

        $response->assertRedirect(route('admin.users.create', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'operator@example.com',
            'role' => User::ROLE_OPERATOR,
            'bidang' => 'Bidang GTK',
        ]);
    }

    public function test_operator_must_have_an_assigned_field(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Operator Tanpa Bidang',
            'email' => 'operator-tanpa-bidang@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_OPERATOR,
        ]);

        $response->assertSessionHasErrors('bidang');
        $this->assertDatabaseMissing('users', ['email' => 'operator-tanpa-bidang@example.com']);
    }

    public function test_admin_can_view_the_updated_operator_fields(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->get('/admin/users/create');

        $response->assertOk()
            ->assertSee('Bidang GTK')
            ->assertSee('Subbagian Umum, Kepegawaian, dan Aset')
            ->assertDontSee('Bidang Umum')
            ->assertDontSee('Bidang Ketenagaan');
    }

    public function test_admin_can_view_and_export_reports_by_status(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Pengaduan::create([
            'nomor_tiket' => 'TKT-SELESAI-001',
            'nama_lengkap' => 'Pelapor Selesai',
            'nomor_telepon' => '081234567890',
            'email' => 'selesai@example.com',
            'sasaran_pengaduan' => 'Sekretariat',
            'hal_diadukan' => 'Pengaduan selesai',
            'status' => 'selesai',
        ]);
        Pengaduan::create([
            'nomor_tiket' => 'TKT-PROSES-001',
            'nama_lengkap' => 'Pelapor Proses',
            'nomor_telepon' => '081234567891',
            'email' => 'proses@example.com',
            'sasaran_pengaduan' => 'Sekretariat',
            'hal_diadukan' => 'Pengaduan diproses',
            'status' => 'diproses',
        ]);
        Pengaduan::create([
            'nomor_tiket' => 'TKT-BARU-001',
            'nama_lengkap' => 'Pelapor Baru',
            'nomor_telepon' => '081234567892',
            'email' => 'baru@example.com',
            'sasaran_pengaduan' => 'Sekretariat',
            'hal_diadukan' => 'Pengaduan baru belum diproses.',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports'))
            ->assertOk()
            ->assertSee('TKT-SELESAI-001')
            ->assertSee('TKT-PROSES-001')
            ->assertSee('TKT-BARU-001');

        $this->actingAs($admin)
            ->get(route('admin.reports', ['status' => 'proses']))
            ->assertOk()
            ->assertSee('TKT-PROSES-001')
            ->assertSee('TKT-BARU-001')
            ->assertDontSee('TKT-SELESAI-001');

        $this->actingAs($admin)
            ->get(route('admin.reports.show', Pengaduan::query()->where('nomor_tiket', 'TKT-SELESAI-001')->firstOrFail()))
            ->assertOk()
            ->assertSee('Dashboard Admin')
            ->assertSee('Laporan Pengaduan')
            ->assertSee('Tambah Admin / Operator')
            ->assertSee('Detail Pengaduan')
            ->assertSee('TKT-SELESAI-001')
            ->assertDontSee('Tiket Saya');

        $this->actingAs($admin)
            ->get(route('admin.reports.export', ['selesai', 'excel']))
            ->assertDownload('laporan-pengaduan-selesai.xlsx');

        $this->actingAs($admin)
            ->get(route('admin.reports.export', ['proses', 'pdf']))
            ->assertDownload('laporan-pengaduan-proses.pdf');

        $export = new PengaduanExport(Pengaduan::query()->get());

        $this->assertSame([
            'No',
            'Nomor Tiket',
            'Waktu Pelaporan',
            'Pelapor',
            'Bidang',
            'Isi Pengaduan',
            'Status',
            'Tanggapan Operator',
            'Foto Bukti',
        ], $export->headings());
        $this->assertSame(1, $export->collection()->first()[0]);
        $this->assertSame('TKT-SELESAI-001', $export->collection()->first()[1]);
    }

    public function test_admin_can_filter_reports_by_date_and_export_all(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $dalamRange = Pengaduan::create([
            'nomor_tiket' => 'TKT-DALAM-001',
            'nama_lengkap' => 'Pelapor Dalam Range',
            'nomor_telepon' => '081234567892',
            'email' => 'dalam@example.com',
            'sasaran_pengaduan' => 'Sekretariat',
            'hal_diadukan' => 'Dalam range',
            'status' => 'pending',
        ]);
        $dalamRange->timestamps = false;
        $dalamRange->created_at = '2026-09-10 10:00:00';
        $dalamRange->updated_at = '2026-09-10 10:00:00';
        $dalamRange->save();

        $luarRange = Pengaduan::create([
            'nomor_tiket' => 'TKT-LUAR-001',
            'nama_lengkap' => 'Pelapor Di Luar Range',
            'nomor_telepon' => '081234567893',
            'email' => 'luar@example.com',
            'sasaran_pengaduan' => 'Sekretariat',
            'hal_diadukan' => 'Di luar range',
            'status' => 'selesai',
        ]);
        $luarRange->timestamps = false;
        $luarRange->created_at = '2026-09-14 10:00:00';
        $luarRange->updated_at = '2026-09-14 10:00:00';
        $luarRange->save();

        $this->actingAs($admin)
            ->get(route('admin.reports', ['status' => 'semua', 'from' => '2026-09-10', 'to' => '2026-09-10']))
            ->assertOk()
            ->assertSee('TKT-DALAM-001')
            ->assertDontSee('TKT-LUAR-001');

        $this->actingAs($admin)
            ->get(route('admin.reports.export', ['semua', 'excel', 'from' => '2026-09-10', 'to' => '2026-09-10']))
            ->assertDownload('laporan-pengaduan-semua.xlsx');
    }

    public function test_non_admin_cannot_access_report_exports(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->get(route('admin.reports'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.reports.export', ['selesai', 'pdf']))
            ->assertForbidden();
    }
}
