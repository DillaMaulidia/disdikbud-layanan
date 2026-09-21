@extends('layouts.app')

@section('title', Auth::user()->role === 'operator' ? 'Tiket Pengaduan' : 'Tiket Saya')

@section('content')
<div class="complaint-page">
    <header class="navbar">
        <a href="{{ Auth::user()->role === 'operator' ? route('pengaduan.tickets') : route('dashboard') }}" class="navbar-brand">
            <img src="{{ asset('images/logo-disdikbud.png') }}" alt="Logo DISDIKBUD Kota Banda Aceh">
            <div class="brand-text">
                <strong>DISDIKBUD</strong>
                <span>Kota Banda Aceh</span>
            </div>
        </a>

        <button class="mobile-menu-button" type="button" onclick="toggleTicketMenu()" aria-label="Buka menu">☰</button>

        <nav class="navbar-menu" id="ticketNavbarMenu">
            @if(Auth::user()->role !== 'operator')
                <a href="{{ route('dashboard') }}">Dashboard Layanan</a>
                <a href="{{ route('pengaduan.create') }}">Buat Pengaduan</a>
            @endif
            <a href="{{ route('pengaduan.tickets') }}" class="active">{{ Auth::user()->role === 'operator' ? 'Tiket Pengaduan' : 'Tiket Saya' }}</a>
        </nav>

        <div class="navbar-auth">
            <div class="profile-menu">
                <button type="button" class="profile-avatar" title="Profil {{ Auth::user()->name }}" aria-label="Buka profil" onclick="toggleTicketProfileMenu()">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </button>

                <div class="profile-dropdown" id="ticketProfileDropdown">
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

    <main class="complaint-main">
        <section class="table-panel ticket-page-panel">
            <div class="panel-header">
                <h1>{{ Auth::user()->role === 'operator' ? 'Tiket Untuk Ditindaklanjuti' : 'Tiket Pengaduan Saya' }}</h1>
                <p class="ticket-page-description">{{ Auth::user()->role === 'operator' ? 'Tindak lanjuti pengaduan yang masuk ke bidang Anda.' : 'Lihat perkembangan pengaduan berdasarkan nomor tiket yang Anda terima.' }}</p>
            </div>

            @php
                $ticketGroups = Auth::user()->role === 'operator'
                    ? [
                        ['title' => 'Masih Diproses', 'items' => $masihDiproses],
                        ['title' => 'Tiket Sudah Selesai', 'items' => $sudahSelesai],
                    ]
                    : [['title' => null, 'items' => $pengaduans]];
            @endphp

            @foreach($ticketGroups as $group)
                @if($group['title'])
                    <h2 class="ticket-group-title">{{ $group['title'] }} ({{ $group['items']->count() }})</h2>
                @endif
                <div class="table-wrap">
                    <table>
                    <thead>
                        <tr>
                            <th>Nomor Tiket</th>
                            <th>Nama Pelapor</th>
                            <th>Sasaran</th>
                            <th>Pengaduan</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                        <tbody>
                        @forelse($group['items'] as $pengaduan)
                            <tr>
                                <td><strong>{{ $pengaduan->nomor_tiket }}</strong></td>
                                <td>{{ $pengaduan->nama_lengkap }}</td>
                                <td>{{ $pengaduan->sasaran_pengaduan }}</td>
                                <td>{{ $pengaduan->hal_diadukan }}</td>
                                <td><span class="status-badge {{ $pengaduan->status }}">{{ $pengaduan->status }}</span></td>
                                <td>{{ $pengaduan->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('pengaduan.show', $pengaduan) }}" class="ticket-action" title="Lihat detail pengaduan" aria-label="Lihat detail pengaduan {{ $pengaduan->nomor_tiket }}">
                                        <span aria-hidden="true">&#128065;</span>
                                    </a>
                                    @if(Auth::user()->role === 'operator')
                                        <form method="POST" action="{{ route('pengaduan.destroy', $pengaduan) }}" class="ticket-action-form" onsubmit="return confirm('Hapus laporan pengaduan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ticket-action ticket-action-delete" title="Hapus laporan pengaduan" aria-label="Hapus laporan pengaduan {{ $pengaduan->nomor_tiket }}">
                                                <span aria-hidden="true">&#128465;</span>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">Belum ada tiket pengaduan.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach
        </section>
    </main>
</div>

<script>
function toggleTicketMenu() {
    document.getElementById('ticketNavbarMenu').classList.toggle('show');
}

function toggleTicketProfileMenu() {
    document.getElementById('ticketProfileDropdown').classList.toggle('show');
}

document.addEventListener('click', function (event) {
    const profileMenu = document.querySelector('.profile-menu');
    const dropdown = document.getElementById('ticketProfileDropdown');

    if (profileMenu && !profileMenu.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});
</script>
@endsection
