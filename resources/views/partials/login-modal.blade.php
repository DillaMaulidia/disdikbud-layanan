<div id="loginModal" class="modal" aria-hidden="true">
    <div class="modal-backdrop" onclick="closeLoginModal()"></div>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="loginTitle">
        <button class="modal-close" aria-label="Tutup" onclick="closeLoginModal()">✕</button>

        <h3 id="loginTitle">Masuk ke Akun</h3>

        <div id="loginErrors" class="modal-errors" style="display:none"></div>

        <form id="loginModalForm" method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-row">
                <label for="modal_email">Email</label>
                <input id="modal_email" name="email" type="email" required autocomplete="username">
            </div>

            <div class="form-row">
                <label for="modal_password">Password</label>
                <input id="modal_password" name="password" type="password" required autocomplete="current-password">
            </div>

            <div class="form-row form-actions">
                <label class="remember-inline">
                    <input type="checkbox" name="remember"> Ingat saya
                </label>

                <button type="submit" class="btn btn-primary">Masuk</button>
            </div>

            <div class="form-row small">
                <a href="{{ route('password.request') }}" class="link">Lupa password?</a>
            </div>
        </form>
    </div>
</div>
