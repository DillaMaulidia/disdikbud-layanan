<!-- 1. Tambahkan action dan method pada tag <form> -->
<form action="{{ route('pengaduan.store') }}" method="POST">
    <!-- 2. Wajib tambahkan @csrf tepat di bawah tag form -->
    @csrf

    <!-- Pesan sukses jika berhasil kirim -->
    @if(session('success'))
        <div style="background: #d1e7dd; color: #0f5132; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
            {{ session('success') }}
        </div>
    @endif

    <!-- 3. Tambahkan atribut name="nama_lengkap" -->
    <label>Nama Lengkap</label>
    <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap Anda" required>

    <!-- 4. Tambahkan atribut name="nomor_telepon" -->
    <label>Nomor Telepon</label>
    <input type="text" name="nomor_telepon" placeholder="Masukkan nomor telepon Anda" required>

    <!-- 5. Tambahkan atribut name="email" -->
    <label>Email</label>
    <input type="email" name="email" placeholder="Masukkan alamat email Anda" required>

    <!-- 6. Tambahkan atribut name="sasaran_pengaduan" dan isi option-nya -->
    <label>Sasaran Pengaduan</label>
    <select name="sasaran_pengaduan" required>
        <option value="">Pilih bidang / unit yang dituju</option>
        <option value="Bidang Pembinaan SD">Bidang Pembinaan SD</option>
        <option value="Bidang Pembinaan SMP">Bidang Pembinaan SMP</option>
        <option value="Bidang Kebudayaan">Bidang Kebudayaan</option>
        <option value="Sekretariat">Sekretariat</option>
    </select>

    <!-- 7. Tambahkan atribut name="hal_diadukan" -->
    <label>Hal yang Diadukan</label>
    <textarea name="hal_diadukan" placeholder="Jelaskan pengaduan Anda secara lengkap dan jelas" required></textarea>

    <!-- 8. Pastikan tombol berupa type="submit" -->
    <button type="submit">Kirim Pengaduan</button>
</form>