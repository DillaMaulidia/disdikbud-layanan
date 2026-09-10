@extends('layouts.app')

@section('title', 'Tambah Admin / Operator')

@section('content')
<div class="dashboard-page">
    <header class="navbar">
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
        <div class="complaint-card" style="max-width: 620px;">
            <div class="complaint-heading">
                <h1>Tambah Admin / Operator</h1>
                <p>Gunakan form ini untuk membuat akun baru dengan role admin atau operator.</p>
            </div>

            @if(session('success'))
                <div class="form-alert form-alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="form-alert form-alert-error">Mohon periksa kembali data yang Anda masukkan.</div>
            @endif

            <form action="{{ route('admin.users.store') }}" method="POST" class="complaint-form">
                @csrf

                <div class="form-field">
                    <label for="name">Nama Lengkap</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required>
                </div>

                <div class="form-field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email" required>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" placeholder="Minimal 8 karakter" required>
                    </div>

                    <div class="form-field">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Ulangi password" required>
                    </div>
                </div>

                <div class="form-field">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="">Pilih role</option>
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="complaint-submit">Buat Akun <span>→</span></button>
            </form>
        </div>
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
