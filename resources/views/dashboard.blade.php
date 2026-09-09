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

            <a href="{{ route('pengaduan.create') }}">
                Layanan Permohonan
            </a>

            <a href="#">
                Tiket
            </a>

        </nav>


        <!-- LOGIN -->
        <div class="navbar-auth">

            <a href="{{ route('login') }}">
                Login
            </a>

            <a href="{{ route('register') }}">
                Register
            </a>

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

                <a href="{{ route('pengaduan.create') }}" class="btn btn-primary">

                    <span class="btn-icon">
                        ↗
                    </span>

                    Buat Pengaduan

                </a>


                <a href="#" class="btn btn-outline">

                    <span class="btn-icon">
                        ⌕
                    </span>

                    Tiket Pengaduan

                </a>

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

</script>

@endsection