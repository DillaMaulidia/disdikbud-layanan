@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="dashboard-page">

    <!-- ================= NAVBAR ================= -->
    <header class="navbar">

        <!-- LOGO -->
        <a href="{{ route('dashboard') }}" class="navbar-brand">

            <img
                src="{{ asset('images/logo-disdikbud.png') }}"
                alt="Logo DISDIKBUD Kota Banda Aceh"
            >

            <div class="brand-text">
                <strong>DISDIKBUD</strong>
                <span>Kota Banda Aceh</span>
            </div>

        </a>


        <!-- MOBILE MENU BUTTON -->
        <button class="mobile-menu-button" type="button" onclick="toggleMenu()">
            ☰
        </button>


        <!-- MENU -->
        <nav class="navbar-menu" id="navbarMenu">

            <a
                href="{{ route('dashboard') }}"
                class="active"
            >
                Dashboard Layanan
            </a>

        </nav>


        <!-- LOGIN / USER -->
        <div class="navbar-auth">

            @auth
                <div class="profile-menu">
                    <button type="button" class="profile-avatar" title="Profil {{ Auth::user()->name }}" aria-label="Buka profil" onclick="toggleProfileMenu()">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </button>

                    <div class="profile-dropdown" id="profileDropdown">
                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('admin.users.create') }}">Tambah Admin / Operator</a>
                        @endif
                        <a href="{{ route('profile.edit') }}">Profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="logout-button">Logout</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="open-login">
                    Login
                </a>

                <a href="{{ route('register') }}">
                    Register
                </a>
            @endauth

        </div>

    </header>


    <!-- ================= HERO ================= -->
    <main class="hero">

        <!-- DECORATION -->
        <div class="hero-decoration decoration-one"></div>
        <div class="hero-decoration decoration-two"></div>


        <!-- CONTENT -->
        <div class="hero-content">

            <h1>

                <span class="title-normal">
                    Layanan
                </span>

                <span class="title-blue">
                    E-GOVERNMENT
                </span>

                <span class="title-normal">
                    DISDIKBUD
                </span>

                <span class="title-normal">
                    Kota Banda Aceh
                </span>

            </h1>


            <p>

                Sampaikan pengaduan, keluhan, dan aspirasi Anda terkait
                layanan pendidikan dan kebudayaan secara mudah dan online.
                Setiap pengaduan akan diteruskan kepada bidang terkait
                untuk ditindaklanjuti.

            </p>


            <!-- BUTTON -->
            <div class="hero-buttons">

                @auth
                    <a href="{{ route('pengaduan.create') }}" class="btn btn-primary">

                        <span class="btn-icon">
                            ↗
                        </span>

                        Buat Pengaduan

                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary open-login">

                        <span class="btn-icon">
                            ↗
                        </span>

                        Buat Pengaduan

                    </a>
                @endauth



            </div>

        </div>


        <!-- ================= ILLUSTRATION ================= -->

        <div class="hero-illustration">

            <img
                src="{{ asset('images/dashboard-hero.png') }}"
                alt="Ilustrasi Layanan E-Government DISDIKBUD"
            >

        </div>


        <!-- ================= FEATURE ================= -->

        <div class="feature-wrapper">

            <!-- MUDAH -->
            <div class="feature-card">

                <div class="feature-icon">
                    ↗
                </div>

                <div class="feature-content">

                    <h3>
                        Mudah
                    </h3>

                    <p>
                        Sampaikan pengaduan
                        dengan mudah dan cepat
                    </p>

                </div>

            </div>


            <!-- TRANSPARAN -->
            <div class="feature-card">

                <div class="feature-icon">
                    ⌕
                </div>

                <div class="feature-content">

                    <h3>
                        Transparan
                    </h3>

                    <p>
                        Pantau status pengaduan
                        secara real time
                    </p>

                </div>

            </div>


            <!-- AMAN -->
            <div class="feature-card">

                <div class="feature-icon">
                    🔒
                </div>

                <div class="feature-content">

                    <h3>
                        Aman
                    </h3>

                    <p>
                        Data Anda aman
                        dan terlindungi
                    </p>

                </div>

            </div>

        </div>

    </main>

</div>


<script>

function toggleMenu() {

    const menu = document.getElementById('navbarMenu');

    menu.classList.toggle('show');

}

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