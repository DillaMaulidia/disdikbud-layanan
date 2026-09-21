@extends('layouts.app')

@section('title', 'Laporan Pengaduan')

@section('content')
<div class="dashboard-page admin-dashboard-page">
    <header class="navbar admin-navbar">
        <a href="{{ route('admin.dashboard') }}" class="navbar-brand">
            <img src="{{ asset('images/logo-disdikbud.png') }}" alt="Logo DISDIKBUD Kota Banda Aceh">
            <div class="brand-text">
                <strong>DISDIKBUD</strong>
                <span>Kota Banda Aceh</span>
            </div>
        </a>

        <nav class="navbar-menu admin-nav-menu">
            <a href="{{ route('admin.dashboard') }}">Dashboard Admin</a>
            <a href="{{ route('admin.reports') }}" class="active">Laporan Pengaduan</a>
            <a href="{{ route('admin.users.create') }}">Tambah Admin / Operator</a>
        </nav>

        <div class="navbar-auth">
            <div class="profile-menu">
                <button type="button" class="profile-avatar" title="Profil {{ Auth::user()->name }}" aria-label="Buka profil" onclick="toggleProfileMenu()">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </button>
                <div class="profile-dropdown" id="profileDropdown">
                    <a href="{{ route('profile.edit') }}">Profil</a>
                    @include('partials.developer-profile')
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-button">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="admin-main">
        <section class="table-panel">
            <div class="panel-header">
                <h1>Laporan Pengaduan</h1>
                <p class="ticket-page-description">Pantau status verifikasi dan tindak lanjut seluruh pengaduan.</p>
            </div>

            <form method="GET" action="{{ route('admin.reports') }}" class="report-filter">
                <div class="report-filter-field">
                    <label for="status">Status laporan</label>
                    <select id="status" name="status">
                        <option value="semua" @selected($selectedStatus === 'semua')>Semua laporan</option>
                        <option value="selesai" @selected($selectedStatus === 'selesai')>Laporan selesai</option>
                        <option value="proses" @selected($selectedStatus === 'proses')>Masih diproses</option>
                    </select>
                </div>
                <div class="report-filter-field">
                    <label for="from">Dari tanggal</label>
                    <input id="from" type="date" name="from" value="{{ $dateFrom }}">
                </div>
                <div class="report-filter-field">
                    <label for="to">Sampai tanggal</label>
                    <input id="to" type="date" name="to" value="{{ $dateTo }}">
                </div>
                <button type="submit" class="report-filter-submit">
                    <span aria-hidden="true">&#128269;</span>
                    Tampilkan
                </button>
                <a href="{{ route('admin.reports') }}" class="report-filter-reset">Reset</a>
                @php
                    $exportQueryString = $dateFrom || $dateTo ? '?'.http_build_query(array_filter(['from' => $dateFrom, 'to' => $dateTo])) : '';
                @endphp
                <div class="report-filter-exports">
                    <span>Download semua:</span>
                    <a href="{{ route('admin.reports.export', ['semua', 'pdf']).$exportQueryString }}" class="report-download report-download-pdf" title="Download semua laporan PDF" aria-label="Download semua laporan dalam PDF">
                        <span class="export-file-icon" aria-hidden="true">PDF</span>
                        <span>PDF</span>
                    </a>
                    <a href="{{ route('admin.reports.export', ['semua', 'excel']).$exportQueryString }}" class="report-download report-download-excel" title="Download semua laporan Excel" aria-label="Download semua laporan dalam Excel">
                        <span class="export-file-icon" aria-hidden="true">XLS</span>
                        <span>Excel</span>
                    </a>
                </div>
            </form>

            @php
                $exportQuery = array_filter(['from' => $dateFrom, 'to' => $dateTo]);
                $groups = $selectedStatus === 'selesai'
                    ? [['key' => 'selesai', 'title' => 'Laporan Selesai', 'items' => $selesai]]
                    : ($selectedStatus === 'proses'
                        ? [['key' => 'proses', 'title' => 'Laporan Masih Diproses', 'items' => $diproses]]
                        : [['key' => 'selesai', 'title' => 'Laporan Selesai', 'items' => $selesai], ['key' => 'proses', 'title' => 'Laporan Masih Diproses', 'items' => $diproses]]);
            @endphp

            @foreach ($groups as $group)
                @php
                    $groupExportQueryString = $exportQuery ? '?'.http_build_query($exportQuery) : '';
                @endphp
                <div class="report-group">
                    <div class="report-group-header">
                        <h2>{{ $group['title'] }} ({{ $group['items']->count() }})</h2>
                        <div class="report-downloads">
                            <a href="{{ route('admin.reports.export', [$group['key'], 'pdf']).$groupExportQueryString }}" class="report-download report-download-pdf" title="Download PDF" aria-label="Download {{ $group['title'] }} dalam PDF">
                                <span class="export-file-icon" aria-hidden="true">PDF</span>
                                <span>PDF</span>
                            </a>
                            <a href="{{ route('admin.reports.export', [$group['key'], 'excel']).$groupExportQueryString }}" class="report-download report-download-excel" title="Download Excel" aria-label="Download {{ $group['title'] }} dalam Excel">
                                <span class="export-file-icon" aria-hidden="true">XLS</span>
                                <span>Excel</span>
                            </a>
                        </div>
                    </div>

                    <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Token</th>
                            <th>Pelapor</th>
                            <th>Bidang</th>
                            <th>Verifikasi</th>
                            <th>Tindak Lanjut</th>
                            <th>Operator</th>
                            <th>Jawaban</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($group['items'] as $pengaduan)
                            <tr>
                                <td><strong>{{ $pengaduan->nomor_tiket }}</strong></td>
                                <td>{{ $pengaduan->nama_lengkap }}</td>
                                <td>{{ $pengaduan->sasaran_pengaduan }}</td>
                                <td>
                                    <span class="status-badge {{ $pengaduan->status === 'pending' ? 'pending' : 'selesai' }}">
                                        {{ $pengaduan->status === 'pending' ? 'Belum' : 'Sudah' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge {{ $pengaduan->status === 'diproses' || $pengaduan->status === 'selesai' ? 'diproses' : 'pending' }}">
                                        {{ $pengaduan->status === 'diproses' || $pengaduan->status === 'selesai' ? 'Sudah' : 'Belum' }}
                                    </span>
                                </td>
                                <td>{{ $pengaduan->operator?->name ?? 'Belum ditugaskan' }}</td>
                                <td>{{ $pengaduan->tanggapan_operator ? 'Sudah dijawab' : 'Belum dijawab' }}</td>
                                <td>
                                    <a href="{{ route('admin.reports.show', $pengaduan) }}" class="ticket-action" title="Lihat detail pengaduan" aria-label="Lihat detail pengaduan {{ $pengaduan->nomor_tiket }}">
                                        <span aria-hidden="true">&#128065;</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-state">Belum ada laporan pada kelompok ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                    </div>
                </div>
            @endforeach
        </section>
    </main>
</div>

<script>
function toggleProfileMenu() {
    document.getElementById('profileDropdown').classList.toggle('show');
}

document.addEventListener('click', function (event) {
    const profileMenu = document.querySelector('.profile-menu');
    const dropdown = document.getElementById('profileDropdown');

    if (profileMenu && !profileMenu.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});
</script>
@endsection
