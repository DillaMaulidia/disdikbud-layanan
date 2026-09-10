@extends('layouts.app')

@section('title', 'Layanan Permohonan')

@section('content')
<div class="complaint-page">
    <header class="navbar">
        <a href="{{ route('dashboard') }}" class="navbar-brand">
            <img src="{{ asset('images/logo-disdikbud.png') }}" alt="Logo DISDIKBUD Kota Banda Aceh">
            <div class="brand-text">
                <strong>DISDIKBUD</strong>
                <span>Kota Banda Aceh</span>
            </div>
        </a>

        <button class="mobile-menu-button" type="button" onclick="toggleComplaintMenu()" aria-label="Buka menu">☰</button>

        <nav class="navbar-menu" id="complaintNavbarMenu">
            <a href="{{ route('dashboard') }}">Dashboard Layanan</a>
        </nav>

        <div class="navbar-auth">
            <div class="profile-menu">
                <button type="button" class="profile-avatar" title="Profil {{ Auth::user()->name }}" aria-label="Buka profil" onclick="toggleComplaintProfileMenu()">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </button>

                <div class="profile-dropdown" id="complaintProfileDropdown">
                    <a href="{{ route('profile.edit') }}">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-button">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="complaint-main">
        <div class="complaint-card">
            <div class="complaint-heading">
                <h1>Sampaikan Pengaduan Anda</h1>
                <p>Silakan lengkapi formulir berikut untuk menyampaikan keluhan,<br class="desktop-only"> pengaduan, atau aspirasi Anda kepada Dinas Pendidikan dan Kebudayaan Kota Banda Aceh.</p>
            </div>

            @if(session('success'))
                <div class="form-alert form-alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="form-alert form-alert-error">Mohon periksa kembali data yang Anda masukkan.</div>
            @endif

            <form action="{{ route('pengaduan.store') }}" method="POST" enctype="multipart/form-data" class="complaint-form">
                @csrf

                <div class="form-grid">
                    <div class="form-field">
                        <label for="nama_lengkap">Nama Lengkap</label>
                        <input id="nama_lengkap" type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" placeholder="Masukkan nama lengkap Anda" required>
                    </div>

                    <div class="form-field">
                        <label for="nomor_telepon">Nomor Telepon</label>
                        <input id="nomor_telepon" type="text" name="nomor_telepon" value="{{ old('nomor_telepon') }}" placeholder="Masukkan nomor telepon Anda" required>
                    </div>
                </div>

                <div class="form-field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Masukkan alamat email Anda" required>
                </div>

                <div class="form-field">
                    <label for="sasaran_pengaduan">Sasaran Pengaduan</label>
                    <select id="sasaran_pengaduan" name="sasaran_pengaduan" required>
                        <option value="">Pilih bidang / unit yang dituju</option>
                        <option value="Bidang Pembinaan SD" @selected(old('sasaran_pengaduan') === 'Bidang Pembinaan SD')>Bidang Pembinaan SD</option>
                        <option value="Bidang Pembinaan SMP" @selected(old('sasaran_pengaduan') === 'Bidang Pembinaan SMP')>Bidang Pembinaan SMP</option>
                        <option value="Bidang Kebudayaan" @selected(old('sasaran_pengaduan') === 'Bidang Kebudayaan')>Bidang Kebudayaan</option>
                        <option value="Sekretariat" @selected(old('sasaran_pengaduan') === 'Sekretariat')>Sekretariat</option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="hal_diadukan">Hal yang Diadukan</label>
                    <textarea id="hal_diadukan" name="hal_diadukan" placeholder="Jelaskan pengaduan Anda secara lengkap dan jelas" required>{{ old('hal_diadukan') }}</textarea>
                </div>

                <div class="form-field">
                    <label for="bukti_pendukung">Foto / Bukti Pendukung <span>(opsional)</span></label>
                    <label class="file-upload" for="bukti_pendukung">
                        <span class="file-upload-icon" aria-hidden="true">↑</span>
                        <span>
                            <strong>Pilih foto atau bukti pendukung</strong>
                            <small id="file-name">JPG, JPEG, PNG, atau WEBP maksimal 5 MB</small>
                        </span>
                    </label>
                    <input id="bukti_pendukung" class="file-input" type="file" name="bukti_pendukung" accept="image/jpeg,image/png,image/webp">
                </div>

                <button type="submit" class="complaint-submit">Kirim Pengaduan <span>→</span></button>
            </form>
        </div>
    </main>
</div>

<script>
function toggleComplaintMenu() {
    document.getElementById('complaintNavbarMenu').classList.toggle('show');
}

function toggleComplaintProfileMenu() {
    const dropdown = document.getElementById('complaintProfileDropdown');
    dropdown.classList.toggle('show');
}

document.addEventListener('click', function (event) {
    const profileMenu = document.querySelector('.profile-menu');
    const dropdown = document.getElementById('complaintProfileDropdown');

    if (profileMenu && !profileMenu.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});

document.getElementById('bukti_pendukung').addEventListener('change', function () {
    document.getElementById('file-name').textContent = this.files[0]?.name || 'JPG, JPEG, PNG, atau WEBP maksimal 5 MB';
});
</script>
@endsection