<div id="registerModal" class="modal" aria-hidden="true">
    <div class="modal-backdrop" onclick="closeRegisterModal()"></div>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="registerTitle">
        <button class="modal-close" aria-label="Tutup" onclick="closeRegisterModal()">✕</button>

        <h3 id="registerTitle">Buat Akun</h3>

        <div id="registerErrors" class="modal-errors" style="display:none"></div>

        <form id="registerModalForm" method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-row">
                <label for="modal_register_name">Nama</label>
                <input id="modal_register_name" name="name" type="text" required autocomplete="name">
            </div>

            <div class="form-row">
                <label for="modal_register_email">Email</label>
                <input id="modal_register_email" name="email" type="email" required autocomplete="username">
            </div>

            <div class="form-row">
                <label for="modal_register_password">Password</label>
                <input id="modal_register_password" name="password" type="password" required autocomplete="new-password">
            </div>

            <div class="form-row">
                <label for="modal_register_password_confirmation">Konfirmasi Password</label>
                <input id="modal_register_password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
            </div>

            <div class="form-row form-actions">
                <button type="submit" class="btn btn-primary">Daftar</button>
            </div>
        </form>
    </div>
</div>
