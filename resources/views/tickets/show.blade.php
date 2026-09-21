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
    $pelaporLampirans = $pengaduan->lampirans->where('sumber', 'pelapor');
    $operatorLampirans = $pengaduan->lampirans->where('sumber', 'operator');
@endphp

<div class="ticket-detail-page">
    <header class="navbar">
        <a href="{{ $isAdminContext ?? false ? route('admin.dashboard') : (Auth::user()->role === 'operator' ? route('pengaduan.tickets') : route('dashboard')) }}" class="navbar-brand">
            <img src="{{ asset('images/logo-disdikbud.png') }}" alt="Logo DISDIKBUD Kota Banda Aceh">
            <div class="brand-text">
                <strong>DISDIKBUD</strong>
                <span>Kota Banda Aceh</span>
            </div>
        </a>

        <button class="mobile-menu-button" type="button" onclick="toggleDetailMenu()" aria-label="Buka menu">☰</button>

        <nav class="navbar-menu {{ $isAdminContext ?? false ? 'admin-nav-menu' : '' }}" id="detailNavbarMenu">
            @if($isAdminContext ?? false)
                <a href="{{ route('admin.dashboard') }}">Dashboard Admin</a>
                <a href="{{ route('admin.reports') }}" class="active">Laporan Pengaduan</a>
                <a href="{{ route('admin.users.create') }}">Tambah Admin / Operator</a>
            @elseif(Auth::user()->role !== 'operator')
                <a href="{{ route('dashboard') }}">Dashboard Layanan</a>
                <a href="{{ route('pengaduan.create') }}">Buat Pengaduan</a>
                <a href="{{ route('pengaduan.tickets') }}" class="active">Tiket Saya</a>
            @else
                <a href="{{ route('pengaduan.tickets') }}" class="active">Tiket Pengaduan</a>
            @endif
        </nav>

        <div class="navbar-auth">
            <div class="profile-menu">
                <button type="button" class="profile-avatar" title="Profil {{ Auth::user()->name }}" aria-label="Buka profil" onclick="toggleDetailProfileMenu()">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </button>
                <div class="profile-dropdown" id="detailProfileDropdown">
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

    <main class="ticket-detail-main">
        <div class="ticket-detail-heading">
            <a href="{{ $isAdminContext ?? false ? route('admin.reports') : route('pengaduan.tickets') }}" class="ticket-back" aria-label="Kembali ke {{ $isAdminContext ?? false ? 'laporan pengaduan' : 'tiket' }}">‹</a>
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
                    <h2>Foto / Dokumen Pendukung</h2>
                    @if($pelaporLampirans->isNotEmpty())
                        <div class="evidence-list">
                            @foreach($pelaporLampirans as $lampiran)
                                @if($lampiran->isImage())
                                    <button class="evidence-image-button" type="button" data-image-url="{{ route('pengaduan.attachment', $lampiran) }}" data-image-alt="{{ $lampiran->nama_asli }}" aria-label="Perbesar {{ $lampiran->nama_asli }}">
                                        <img class="evidence-preview" src="{{ route('pengaduan.attachment', $lampiran) }}" alt="{{ $lampiran->nama_asli }}">
                                    </button>
                                @else
                                    <a href="{{ route('pengaduan.attachment', $lampiran) }}" target="_blank" rel="noopener">{{ $lampiran->nama_asli }}</a>
                                @endif
                            @endforeach
                        </div>
                    @elseif($pengaduan->bukti_pendukung)
                        <button class="evidence-image-button" type="button" data-image-url="{{ route('pengaduan.evidence', $pengaduan) }}" data-image-alt="Bukti pendukung pengaduan {{ $pengaduan->nomor_tiket }}" aria-label="Perbesar bukti pendukung">
                            <img class="evidence-preview" src="{{ route('pengaduan.evidence', $pengaduan) }}" alt="Bukti pendukung pengaduan {{ $pengaduan->nomor_tiket }}">
                        </button>
                    @else
                        <p class="no-evidence">Tidak ada bukti pendukung.</p>
                    @endif
                </div>
            </div>

            @if($pengaduan->status === 'diproses' || $pengaduan->tanggapan_operator || $operatorLampirans->isNotEmpty() || $pengaduan->bukti_operator)
                <div class="operator-response">
                    <div>
                        <h2>Jawaban Operator</h2>
                        <p>{{ $pengaduan->tanggapan_operator ?? 'Tidak ada pesan tindak lanjut.' }}</p>
                    </div>
                    @if($operatorLampirans->isNotEmpty() || $pengaduan->bukti_operator)
                        <div class="operator-evidence">
                            <h2>Foto / Dokumen Operator</h2>
                            @if($operatorLampirans->isNotEmpty())
                                <div class="evidence-list">
                                    @foreach($operatorLampirans as $lampiran)
                                        @if($lampiran->isImage())
                                            <button class="evidence-image-button" type="button" data-image-url="{{ route('pengaduan.attachment', $lampiran) }}" data-image-alt="{{ $lampiran->nama_asli }}" aria-label="Perbesar {{ $lampiran->nama_asli }}">
                                                <img class="evidence-preview" src="{{ route('pengaduan.attachment', $lampiran) }}" alt="{{ $lampiran->nama_asli }}">
                                            </button>
                                        @else
                                            <a href="{{ route('pengaduan.attachment', $lampiran) }}" target="_blank" rel="noopener">{{ $lampiran->nama_asli }}</a>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <button class="evidence-image-button" type="button" data-image-url="{{ route('pengaduan.operator-evidence', $pengaduan) }}" data-image-alt="Foto pendukung dari operator untuk pengaduan {{ $pengaduan->nomor_tiket }}" aria-label="Perbesar foto pendukung operator">
                                    <img class="evidence-preview" src="{{ route('pengaduan.operator-evidence', $pengaduan) }}" alt="Foto pendukung dari operator untuk pengaduan {{ $pengaduan->nomor_tiket }}">
                                </button>
                            @endif
                        </div>
                    @endif
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

                    @if($pengaduan->status === 'diproses')
                        <form method="POST" action="{{ route('pengaduan.respond', $pengaduan) }}" class="ticket-response-form" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            <label for="tanggapan_operator">Pesan atau tanggapan</label>
                            <textarea id="tanggapan_operator" name="tanggapan_operator" rows="4" placeholder="Tulis pesan atau perkembangan pengaduan...">{{ old('tanggapan_operator') }}</textarea>
                            @error('tanggapan_operator')
                                <span class="ticket-form-error">{{ $message }}</span>
                            @enderror
                            <label for="bukti_tindak_lanjut">Foto / dokumen pendukung (opsional)</label>
                            <label for="bukti_tindak_lanjut" class="ticket-file-button">Pilih Foto / Dokumen</label>
                            <input id="bukti_tindak_lanjut" class="ticket-file-input" type="file" name="bukti_pendukung[]" accept="image/jpeg,image/png,image/webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" multiple>
                            <img id="bukti_tindak_lanjut_preview" class="operator-photo-preview" alt="Preview foto tindak lanjut" hidden>
                            @error('bukti_pendukung')
                                <span class="ticket-form-error">{{ $message }}</span>
                            @enderror
                            <button type="submit" class="ticket-action-button ticket-complete-button">Selesaikan Laporan</button>
                        </form>
                    @endif
                </div>
            @endif
        </section>
    </main>
</div>

<div class="image-lightbox" id="imageLightbox" aria-hidden="true">
    <div class="image-lightbox-backdrop" data-lightbox-close></div>
    <div class="image-lightbox-content" role="dialog" aria-modal="true" aria-labelledby="imageLightboxTitle">
        <button class="image-lightbox-close" type="button" data-lightbox-close aria-label="Tutup foto">&times;</button>
        <p class="image-lightbox-title" id="imageLightboxTitle">Pratinjau foto</p>
        <img id="imageLightboxImage" src="" alt="">
    </div>
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

document.querySelectorAll('input[type="file"]').forEach(function (input) {
    input.addEventListener('change', function () {
        const preview = document.getElementById(`${input.id}_preview`);

        if (!preview || !input.files[0]) {
            return;
        }

        preview.src = URL.createObjectURL(input.files[0]);
        preview.hidden = false;
    });
});

const imageLightbox = document.getElementById('imageLightbox');
const imageLightboxImage = document.getElementById('imageLightboxImage');

function closeImageLightbox() {
    imageLightbox.classList.remove('open');
    imageLightbox.setAttribute('aria-hidden', 'true');
    imageLightboxImage.src = '';
    document.body.classList.remove('lightbox-open');
}

document.querySelectorAll('[data-image-url]').forEach(function (button) {
    button.addEventListener('click', function () {
        imageLightboxImage.src = button.dataset.imageUrl;
        imageLightboxImage.alt = button.dataset.imageAlt || 'Pratinjau foto';
        imageLightbox.classList.add('open');
        imageLightbox.setAttribute('aria-hidden', 'false');
        document.body.classList.add('lightbox-open');
    });
});

document.querySelectorAll('[data-lightbox-close]').forEach(function (element) {
    element.addEventListener('click', closeImageLightbox);
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && imageLightbox.classList.contains('open')) {
        closeImageLightbox();
    }
});
</script>
@endsection
