<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengaduanController extends Controller
{
    // Tampilkan form pengaduan
    public function create(): View
    {
        return view('layanan');
    }

    // Simpan data pengaduan
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nomor_telepon' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'sasaran_pengaduan' => 'required|string|max:255',
            'hal_diadukan' => 'required|string|max:5000',
        ]);

        do {
            $nomorTiket = 'TKT-'.now()->format('Ymd').'-'.random_int(100, 999);
        } while (Pengaduan::where('nomor_tiket', $nomorTiket)->exists());

        Pengaduan::create([
            'nomor_tiket' => $nomorTiket,
            ...$validated,
        ]);

        return redirect()->back()->with('success', 'Pengaduan berhasil dikirim! Nomor Tiket Anda: '.$nomorTiket);
    }
}
