@extends('layouts.app')

@section('title', 'Detail Pengaduan')

@section('content')
@php
    $steps = [
        ['key' => 'laporan', 'title' => 'Tulis Laporan', 'description' => 'Laporan diterima'],
        ['key' => 'verifikasi', 'title' => 'Verifikasi', 'description' => 'Sedang berlangsung'],
        ['key' => 'tindak_lanjut', 'title' => 'Tindak Lanjut', 'description' => 'Belum diproses'],
        ['key' => 'selesai', 'title' => 'Selesai', 'description' => 'Belum selesai'],
    ];
    $statusStep = match ($pengaduan->status) {
        'diproses' => 2,
        'selesai' => 4,
        'ditolak' => 2,
        default => 1,
    };
@endphp

<div class="ticket-detail-page">
    <header class="navbar">
        <a href="{{ Auth::user()->role === 'operator' ? route('pengaduan.tickets') : route('dashboard') }}" class="navbar-brand">
            <img src="{{ asset('images/logo-disdikbud.png') }}" alt="Logo DISDIKBUD Kota Banda Aceh">
            <div class="brand-text">
                <strong>DISDIKBUD</strong>
                <span>Kota Banda Aceh</span>
            </div>
        </a>

        <button class="mobile-menu-button" type="button" onclick="toggleDetailMenu()" aria-label="Buka menu">☰</button>

        <nav class="navbar-menu" id="detailNavbarMenu">
            @if(Auth::user()->role !== 'operator')
                <a href="{{ route('dashboard') }}">Dashboard Layanan</a>
            @endif
            <a href="{{ route('pengaduan.tickets') }}" class="active">Tiket Saya</a>
        </nav>

        <div class="navbar-auth">
            <div class="profile-menu">
                <button type="button" class="profile-avatar" title="Profil {{ Auth::user()->name }}" aria-label="Buka profil" onclick="toggleDetailProfileMenu()">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </button>
                <div class="profile-dropdown" id="detailProfileDropdown">
                    <a href="{{ route('profile.edit') }}">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-button">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="ticket-detail-main">
        <div class="ticket-detail-heading">
            <a href="{{ route('pengaduan.tickets') }}" class="ticket-back" aria-label="Kembali ke tiket">‹</a>
            <h1>Detail Pengaduan</h1>
            <p>Pantau status dan perkembangan pengaduan Anda.</p>
        </div>

        <section class="ticket-detail-card">
            <div class="ticket-detail-top">
                <div>
                    <span class="detail-label">Nomor Tiket</span>
                    <strong class="detail-ticket-number">{{ $pengaduan->nomor_tiket }}</strong>
                </div>
            </div>

            <div class="detail-summary">
                <div>
                    <span class="detail-label">Tanggal Pengaduan</span>
                    <strong>{{ $pengaduan->created_at->format('d F Y') }}</strong>
                    <small>{{ $pengaduan->created_at->format('H:i') }} WIB</small>
                </div>
                <div>
                    <span class="detail-label">Sasaran Pengaduan</span>
                    <strong>{{ $pengaduan->sasaran_pengaduan }}</strong>
                </div>
                <div>
                    <span class="detail-label">Pelapor</span>
                    <strong>{{ $pengaduan->nama_lengkap }}</strong>
                </div>
                <div>
                    <span class="detail-label">Status</span>
                    <strong>{{ $pengaduan->status === 'pending' ? 'Menunggu Verifikasi' : ucfirst($pengaduan->status) }}</strong>
                </div>
            </div>

            <div class="ticket-timeline" aria-label="Perkembangan pengaduan">
                @foreach($steps as $index => $step)
                    @php
                        $stepNumber = $index + 1;
                        $stepClass = $stepNumber < $statusStep ? 'is-complete' : ($stepNumber === $statusStep ? 'is-current' : '');
                    @endphp
                    <div class="timeline-step {{ $stepClass }}">
                        <span class="timeline-dot"></span>
                        <strong>{{ $step['title'] }}</strong>
                        <small>{{ $stepNumber === $statusStep && $pengaduan->status === 'pending' ? 'Sedang berlangsung' : $step['description'] }}</small>
                    </div>
                @endforeach
            </div>

            <div class="ticket-detail-content">
                <div>
                    <h2>Hal yang diadukan</h2>
                    <p>{{ $pengaduan->hal_diadukan }}</p>
                </div>
                <div>
                    <h2>Foto / Bukti Pendukung</h2>
                    @if($pengaduan->bukti_pendukung)
                        <img class="evidence-preview" src="{{ route('pengaduan.evidence', $pengaduan) }}" alt="Bukti pendukung pengaduan {{ $pengaduan->nomor_tiket }}">
                    @else
                        <p class="no-evidence">Tidak ada bukti pendukung.</p>
                    @endif
                </div>
            </div>

            @if($pengaduan->tanggapan_operator)
                <div class="operator-response">
                    <h2>Jawaban Operator</h2>
                    <p>{{ $pengaduan->tanggapan_operator }}</p>
                </div>
            @endif

            @if(Auth::user()->role === 'operator')
                <div class="ticket-operator-actions">
                    <h2>Tindak Lanjut Operator</h2>

                    @if($pengaduan->status === 'pending')
                        <form method="POST" action="{{ route('pengaduan.status', $pengaduan) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="diproses">
                            <button type="submit" class="ticket-action-button ticket-verify-button">Verifikasi Tiket</button>
                        </form>
                    @endif

                    @if($pengaduan->status !== 'selesai')
                        <form method="POST" action="{{ route('pengaduan.respond', $pengaduan) }}" class="ticket-response-form">
                            @csrf
                            @method('PATCH')
                            <label for="tanggapan_operator">Jawaban untuk Pelapor</label>
                            <textarea id="tanggapan_operator" name="tanggapan_operator" rows="4" placeholder="Tulis jawaban atau perkembangan pengaduan..." required>{{ old('tanggapan_operator', $pengaduan->tanggapan_operator) }}</textarea>
                            @error('tanggapan_operator')
                                <span class="ticket-form-error">{{ $message }}</span>
                            @enderror
                            <button type="submit" class="ticket-action-button">Kirim Jawaban</button>
                        </form>
                    @endif

                    @if($pengaduan->status === 'diproses')
                        <form method="POST" action="{{ route('pengaduan.status', $pengaduan) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="selesai">
                            <button type="submit" class="ticket-action-button ticket-complete-button">Selesaikan Tiket</button>
                        </form>
                    @endif
                </div>
            @endif
        </section>
    </main>
</div>

<script>
function toggleDetailMenu() {
    document.getElementById('detailNavbarMenu').classList.toggle('show');
}

function toggleDetailProfileMenu() {
    document.getElementById('detailProfileDropdown').classList.toggle('show');
}

document.addEventListener('click', function (event) {
    const profileMenu = document.querySelector('.profile-menu');
    const dropdown = document.getElementById('detailProfileDropdown');

    if (profileMenu && !profileMenu.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});
</script>
@endsection
