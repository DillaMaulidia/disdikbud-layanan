<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use App\Models\PengaduanLampiran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PengaduanController extends Controller
{
    // Tampilkan form pengaduan
    public function create(): View|RedirectResponse
    {
        if (Auth::user()->role === User::ROLE_OPERATOR) {
            return redirect()->route('pengaduan.tickets');
        }

        return view('layanan');
    }

    public function tickets(): View
    {
        $pengaduans = Pengaduan::query()
            ->visibleTo(Auth::user())
            ->latest()
            ->get();

        $masihDiproses = $pengaduans->whereIn('status', ['pending', 'diproses']);
        $sudahSelesai = $pengaduans->whereNotIn('status', ['pending', 'diproses']);

        return view('tickets.index', compact('pengaduans', 'masihDiproses', 'sudahSelesai'));
    }

    public function showTicket(Pengaduan $pengaduan): View
    {
        $pengaduan = Pengaduan::query()
            ->visibleTo(Auth::user())
            ->whereKey($pengaduan->getKey())
            ->firstOrFail();

        return view('tickets.show', compact('pengaduan'));
    }

    public function evidence(Pengaduan $pengaduan): BinaryFileResponse
    {
        $pengaduan = Pengaduan::query()
            ->visibleTo(Auth::user())
            ->whereKey($pengaduan->getKey())
            ->firstOrFail();

        abort_unless($pengaduan->bukti_pendukung && Storage::disk('local')->exists($pengaduan->bukti_pendukung), 404);

        return response()->file(Storage::disk('local')->path($pengaduan->bukti_pendukung));
    }

    public function operatorEvidence(Pengaduan $pengaduan): BinaryFileResponse
    {
        $pengaduan = Pengaduan::query()
            ->visibleTo(Auth::user())
            ->whereKey($pengaduan->getKey())
            ->firstOrFail();

        abort_unless($pengaduan->bukti_operator && Storage::disk('local')->exists($pengaduan->bukti_operator), 404);

        return response()->file(Storage::disk('local')->path($pengaduan->bukti_operator));
    }

    public function attachment(PengaduanLampiran $lampiran): BinaryFileResponse
    {
        $pengaduan = Pengaduan::query()
            ->visibleTo(Auth::user())
            ->whereKey($lampiran->pengaduan_id)
            ->firstOrFail();

        abort_unless(Storage::disk('local')->exists($lampiran->path), 404);

        return response()->file(Storage::disk('local')->path($lampiran->path));
    }

    public function updateStatus(Request $request, Pengaduan $pengaduan): RedirectResponse
    {
        abort_unless(Auth::user()->role === User::ROLE_OPERATOR, 403);

        $pengaduan = $this->operatorTicket($pengaduan);
        $validated = $request->validate([
            'status' => ['required', Rule::in(['diproses', 'selesai'])],
        ]);

        $pengaduan->update([
            'status' => $validated['status'],
            'operator_id' => Auth::id(),
        ]);

        return back()->with('success', 'Status tiket berhasil diperbarui.');
    }

    public function respond(Request $request, Pengaduan $pengaduan): RedirectResponse
    {
        abort_unless(Auth::user()->role === User::ROLE_OPERATOR, 403);

        $pengaduan = $this->operatorTicket($pengaduan);
        $validated = $request->validate([
            'tanggapan_operator' => ['nullable', 'string', 'max:5000', 'required_without:bukti_pendukung'],
            'bukti_pendukung' => ['nullable', 'array', 'max:10'],
            'bukti_pendukung.*' => ['file', 'mimes:jpeg,jpg,png,webp,pdf,doc,docx', 'max:5120'],
        ]);

        $lampiranPaths = $this->storeAttachments(
            $pengaduan,
            $request->file('bukti_pendukung', []),
            PengaduanLampiran::SOURCE_OPERATOR,
            'pengaduan/operator',
        );

        if ($lampiranPaths !== []) {
            $validated['bukti_operator'] = $lampiranPaths[0];
        }

        $pengaduan->update([
            'tanggapan_operator' => $validated['tanggapan_operator'] ?? $pengaduan->tanggapan_operator,
            'status' => 'selesai',
            'operator_id' => Auth::id(),
            'bukti_operator' => $validated['bukti_operator'] ?? $pengaduan->bukti_operator,
        ]);

        return back()->with('success', 'Jawaban berhasil dikirim kepada pelapor.');
    }

    public function destroy(Pengaduan $pengaduan): RedirectResponse
    {
        abort_unless(Auth::user()->role === User::ROLE_OPERATOR, 403);

        $pengaduan = $this->operatorTicket($pengaduan);

        if ($pengaduan->bukti_pendukung) {
            Storage::disk('local')->delete($pengaduan->bukti_pendukung);
        }

        if ($pengaduan->bukti_operator) {
            Storage::disk('local')->delete($pengaduan->bukti_operator);
        }

        foreach ($pengaduan->lampirans as $lampiran) {
            Storage::disk('local')->delete($lampiran->path);
        }

        $pengaduan->lampirans()->delete();

        $pengaduan->delete();

        return redirect()->route('pengaduan.tickets')->with('success', 'Laporan pengaduan berhasil dihapus.');
    }

    private function operatorTicket(Pengaduan $pengaduan): Pengaduan
    {
        return Pengaduan::query()
            ->visibleTo(Auth::user())
            ->whereKey($pengaduan->getKey())
            ->firstOrFail();
    }

    // Simpan data pengaduan
    public function store(Request $request): RedirectResponse
    {
        abort_if(Auth::user()->role === User::ROLE_OPERATOR, 403);

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nomor_telepon' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'sasaran_pengaduan' => ['required', 'string', Rule::in(User::validBidang())],
            'hal_diadukan' => 'required|string|max:5000',
            'bukti_pendukung' => ['nullable', 'array', 'max:10'],
            'bukti_pendukung.*' => ['file', 'mimes:jpeg,jpg,png,webp,pdf,doc,docx', 'max:5120'],
        ]);

        $lampiranPaths = $this->storeAttachments(
            null,
            $request->file('bukti_pendukung', []),
            PengaduanLampiran::SOURCE_PELAPOR,
            'pengaduan',
        );

        if ($lampiranPaths !== []) {
            $validated['bukti_pendukung'] = $lampiranPaths[0];
        }

        $bidangCode = User::bidangCode($validated['sasaran_pengaduan']);

        do {
            $nomorTiket = '#'.$bidangCode.'-'.now()->format('Y').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Pengaduan::where('nomor_tiket', $nomorTiket)->exists());

        $pengaduan = Pengaduan::create([
            'nomor_tiket' => $nomorTiket,
            'user_id' => Auth::id(),
            ...$validated,
        ]);

        $this->storeAttachments(
            $pengaduan,
            $request->file('bukti_pendukung', []),
            PengaduanLampiran::SOURCE_PELAPOR,
            'pengaduan',
            $lampiranPaths,
        );

        return redirect()->back()->with('success', [
            'nomor_tiket' => $nomorTiket,
            'sasaran_pengaduan' => $validated['sasaran_pengaduan'],
        ]);
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @param  array<int, string>|null  $existingPaths
     * @return array<int, string>
     */
    private function storeAttachments(
        ?Pengaduan $pengaduan,
        array $files,
        string $source,
        string $directory,
        ?array $existingPaths = null,
    ): array {
        $paths = $existingPaths ?? [];

        foreach ($files as $index => $file) {
            $path = $paths[$index] ?? $file->store($directory, 'local');
            $paths[$index] = $path;

            if ($pengaduan === null) {
                continue;
            }

            $pengaduan->lampirans()->create([
                'sumber' => $source,
                'path' => $path,
                'nama_asli' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            ]);
        }

        return $paths;
    }
}
