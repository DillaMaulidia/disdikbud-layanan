@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="dashboard-page admin-dashboard-page">
    <header class="navbar admin-navbar">
        <a href="{{ route('dashboard') }}" class="navbar-brand">
            <img src="{{ asset('images/logo-disdikbud.png') }}" alt="Logo DISDIKBUD Kota Banda Aceh">
            <div class="brand-text">
                <strong>DISDIKBUD</strong>
                <span>Kota Banda Aceh</span>
            </div>
        </a>

        <div class="navbar-auth">
            <div class="profile-menu">
                <button type="button" class="profile-avatar" title="Profil {{ Auth::user()->name }}" aria-label="Buka profil" onclick="toggleProfileMenu()">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </button>

                <div class="profile-dropdown" id="profileDropdown">
                    <a href="{{ route('admin.users.create') }}">Tambah Admin / Operator</a>
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
        <section class="admin-summary">
            <div class="summary-card summary-primary">
                <span>Total Pelapor</span>
                <strong>{{ $totalPelapor }}</strong>
            </div>
            <div class="summary-card summary-success">
                <span>Pending</span>
                <strong>{{ $stats['pending'] }}</strong>
            </div>
            <div class="summary-card summary-info">
                <span>Diproses</span>
                <strong>{{ $stats['diproses'] }}</strong>
            </div>
            <div class="summary-card summary-warning">
                <span>Selesai</span>
                <strong>{{ $stats['selesai'] }}</strong>
            </div>
        </section>

        <section class="admin-grid">
            <div class="panel-panel">
                <div class="panel-header">
                    <h2>Operator Aktif</h2>
                </div>

                <div class="operator-list">
                    @forelse($operators as $operator)
                        <div class="operator-item">
                            <div class="operator-avatar">{{ strtoupper(substr($operator->name, 0, 1)) }}</div>
                            <div class="operator-meta">
                                <strong>{{ $operator->name }}</strong>
                                <span>{{ $operator->email }}</span>
                            </div>
                            <span class="status-badge online">Aktif</span>
                        </div>
                    @empty
                        <p class="empty-state">Belum ada operator yang dibuat.</p>
                    @endforelse
                </div>
            </div>

            <div class="panel-panel">
                <div class="panel-header">
                    <h2>Statistik Pengaduan</h2>
                </div>

                <div class="chart-box">
                    <div class="bar-row">
                        <span>Pending</span>
                        <div class="bar-track"><div class="bar bar-pending" style="width: {{ $totalPelapor > 0 ? ($stats['pending'] / $totalPelapor) * 100 : 0 }}%"></div></div>
                        <strong>{{ $stats['pending'] }}</strong>
                    </div>
                    <div class="bar-row">
                        <span>Diproses</span>
                        <div class="bar-track"><div class="bar bar-process" style="width: {{ $totalPelapor > 0 ? ($stats['diproses'] / $totalPelapor) * 100 : 0 }}%"></div></div>
                        <strong>{{ $stats['diproses'] }}</strong>
                    </div>
                    <div class="bar-row">
                        <span>Selesai</span>
                        <div class="bar-track"><div class="bar bar-success" style="width: {{ $totalPelapor > 0 ? ($stats['selesai'] / $totalPelapor) * 100 : 0 }}%"></div></div>
                        <strong>{{ $stats['selesai'] }}</strong>
                    </div>
                    <div class="bar-row">
                        <span>Ditolak</span>
                        <div class="bar-track"><div class="bar bar-reject" style="width: {{ $totalPelapor > 0 ? ($stats['ditolak'] / $totalPelapor) * 100 : 0 }}%"></div></div>
                        <strong>{{ $stats['ditolak'] }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="table-panel">
            <div class="panel-header">
                <h2>Data Pelapor</h2>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Sasaran</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $pengaduans = \App\Models\Pengaduan::latest()->take(8)->get(); @endphp
                        @forelse($pengaduans as $pengaduan)
                            <tr>
                                <td>{{ $pengaduan->nama_lengkap }}</td>
                                <td>{{ $pengaduan->email }}</td>
                                <td>{{ $pengaduan->sasaran_pengaduan }}</td>
                                <td><span class="status-badge {{ $pengaduan->status }}">{{ $pengaduan->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-state">Belum ada data pelapor.</td>
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
    const dropdown = document.getElementById('profileDropdown');
    dropdown.classList.toggle('show');
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
