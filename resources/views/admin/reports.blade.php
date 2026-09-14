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
                        @forelse($pengaduans as $pengaduan)
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
                                    <a href="{{ route('pengaduan.show', $pengaduan) }}" class="ticket-action" title="Lihat detail pengaduan" aria-label="Lihat detail pengaduan {{ $pengaduan->nomor_tiket }}">
                                        <span aria-hidden="true">&#128065;</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-state">Belum ada laporan pengaduan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
