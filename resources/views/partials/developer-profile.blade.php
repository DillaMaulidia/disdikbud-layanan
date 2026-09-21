<button type="button" class="developer-profile-link" onclick="toggleDeveloperProfile()" aria-expanded="false" aria-controls="developerProfilePanel">
    <span aria-hidden="true">ID</span>
    Profil Developer
</button>

<div class="developer-profile-panel" id="developerProfilePanel" hidden>
    <div class="developer-profile-heading">
        <span class="developer-profile-eyebrow">Tim Pengembang</span>
        <h2>Profil Developer</h2>
    </div>

    <div class="developer-profile-list">
        @foreach([
            ['name' => 'M. Hilmy Helsinky', 'email' => 'yduta21@gmail.com', 'initials' => 'MH', 'photo' => 'developer-hilmy.jpg'],
            ['name' => 'Dilla Maulidia', 'email' => 'dilla.maulidia03@gmail.com', 'initials' => 'DM', 'photo' => 'developer-dilla.jpg'],
            ['name' => 'Siti Zahara', 'email' => 'sitizahara20042005@gmail.com', 'initials' => 'SZ', 'photo' => 'developer-siti.jpg'],
        ] as $developer)
            <article class="developer-card">
                <div class="developer-photo-wrap">
                    <span class="developer-initials">{{ $developer['initials'] }}</span>
                    <img class="developer-photo" src="{{ asset('images/'.$developer['photo']) }}" alt="Foto {{ $developer['name'] }}" onerror="this.hidden=true">
                </div>
                <div>
                    <h3>{{ $developer['name'] }}</h3>
                    <a href="mailto:{{ $developer['email'] }}">{{ $developer['email'] }}</a>
                </div>
            </article>
        @endforeach
    </div>

    <p class="developer-biography">Tim Developer terdiri dari tiga mahasiswa Program Studi Teknologi Informasi UIN Ar-Raniry Banda Aceh, yaitu <strong>Dilla Maulidia, M. Hilmy Helsinky, dan Siti Zahara</strong>. Pada tahun 2026, ketiganya melaksanakan kegiatan magang di <strong>Dinas Pendidikan dan Kebudayaan Kota Banda Aceh</strong> sekaligus terlibat dalam pengembangan <strong>Aplikasi Layanan Pengaduan Dinas Pendidikan dan Kebudayaan Kota Banda Aceh</strong>. Dalam proses pengembangan aplikasi, tim berperan dalam perancangan, pengembangan antarmuka, pengelolaan sistem, pengujian, serta penyempurnaan fitur agar aplikasi dapat memudahkan masyarakat dalam menyampaikan pengaduan dan membantu pihak dinas dalam mengelola serta menindaklanjuti pengaduan secara lebih efektif.</p>
</div>

<script>
function toggleDeveloperProfile() {
    const panel = document.getElementById('developerProfilePanel');
    const button = document.querySelector('.developer-profile-link');
    const isOpen = !panel.hidden;

    panel.hidden = isOpen;
    button.setAttribute('aria-expanded', String(!isOpen));
}

document.addEventListener('click', function (event) {
    const panel = document.getElementById('developerProfilePanel');
    const button = document.querySelector('.developer-profile-link');

    if (panel && button && !panel.contains(event.target) && !button.contains(event.target)) {
        panel.hidden = true;
        button.setAttribute('aria-expanded', 'false');
    }
});
</script>
