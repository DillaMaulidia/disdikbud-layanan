<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PengaduanExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(private readonly Collection $pengaduans) {}

    public function collection(): Collection
    {
        return $this->pengaduans->values()->map(fn ($pengaduan, int $index): array => [
            $index + 1,
            $pengaduan->nomor_tiket,
            $pengaduan->created_at?->format('d-m-Y H:i'),
            $pengaduan->nama_lengkap.'\n'.$pengaduan->email,
            $pengaduan->sasaran_pengaduan,
            $pengaduan->hal_diadukan,
            ucfirst($pengaduan->status),
            $pengaduan->tanggapan_operator ?? 'Belum ada tanggapan',
            $pengaduan->bukti_pendukung ? 'Ada foto' : 'Tidak ada foto',
        ]);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor Tiket',
            'Waktu Pelaporan',
            'Pelapor',
            'Bidang',
            'Isi Pengaduan',
            'Status',
            'Tanggapan Operator',
            'Foto Bukti',
        ];
    }
}
