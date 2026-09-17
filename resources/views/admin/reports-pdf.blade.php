<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengaduan - {{ $statusLabel }}</title>
    <style>
        @page { margin: 18px 22px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #111827; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        p { margin: 0; color: #4b5563; }
        table { width: 100%; table-layout: fixed; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #d1d5db; padding: 4px; text-align: left; vertical-align: top; word-wrap: break-word; }
        th { background: #e5e7eb; font-size: 8px; }
        .number-column { width: 4%; }
        .ticket-column { width: 11%; }
        .time-column { width: 10%; }
        .reporter-column { width: 13%; }
        .field-column { width: 9%; }
        .content-column { width: 20%; }
        .status-column { width: 8%; }
        .response-column { width: 15%; }
        .evidence-column { width: 10%; }
        .evidence-image { max-width: 58px; max-height: 48px; }
    </style>
</head>
<body>
    <h1>Laporan Pengaduan: {{ $statusLabel }}</h1>
    <p>Dicetak pada {{ now()->format('d-m-Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th class="number-column">No</th>
                <th class="ticket-column">Nomor Tiket</th>
                <th class="time-column">Waktu Pelaporan</th>
                <th class="reporter-column">Pelapor</th>
                <th class="field-column">Bidang</th>
                <th class="content-column">Isi Pengaduan</th>
                <th class="status-column">Status</th>
                <th class="response-column">Tanggapan</th>
                <th class="evidence-column">Foto Bukti</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pengaduans as $index => $pengaduan)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $pengaduan->nomor_tiket }}</td>
                    <td>{{ $pengaduan->created_at?->format('d-m-Y H:i') }}</td>
                    <td>{{ $pengaduan->nama_lengkap }}<br>{{ $pengaduan->email }}</td>
                    <td>{{ $pengaduan->sasaran_pengaduan }}</td>
                    <td>{{ $pengaduan->hal_diadukan }}</td>
                    <td>{{ ucfirst($pengaduan->status) }}</td>
                    <td>{{ $pengaduan->tanggapan_operator ?? 'Belum ada tanggapan' }}</td>
                    <td>
                        @if ($buktiPendukung->get($pengaduan->getKey()))
                            <img class="evidence-image" src="{{ $buktiPendukung->get($pengaduan->getKey()) }}" alt="Bukti pendukung">
                        @else
                            Tidak ada foto
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="9">Belum ada laporan pada kelompok ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>