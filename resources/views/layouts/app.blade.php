<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'DISDIKBUD Kota Banda Aceh')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>

<body>

    @yield('content')

    @include('partials.login-modal')

    <script>
    (function () {
        function openLoginModal(e) {
            if (e) e.preventDefault();
            const modal = document.getElementById('loginModal');
            if (!modal) return;
            modal.setAttribute('aria-hidden', 'false');
            modal.classList.add('open');
            document.getElementById('modal_email').focus();
        }

        window.openLoginModal = openLoginModal;
        window.closeLoginModal = function () {
            const modal = document.getElementById('loginModal');
            if (!modal) return;
            modal.setAttribute('aria-hidden', 'true');
            modal.classList.remove('open');
            const errors = document.getElementById('loginErrors');
            if (errors) { errors.style.display = 'none'; errors.innerHTML = ''; }
            document.getElementById('loginModalForm').reset();
        };

        document.addEventListener('click', function (ev) {
            const t = ev.target.closest && ev.target.closest('.open-login');
            if (t) {
                openLoginModal(ev);
            }
        });

        // AJAX submit
        const form = document.getElementById('loginModalForm');
        if (form) {
            form.addEventListener('submit', function (ev) {
                ev.preventDefault();
                const data = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: data,
                    credentials: 'same-origin'
                }).then(async (res) => {
                    if (res.redirected) {
                        window.location.href = res.url;
                        return;
                    }

                    if (res.status === 422) {
                        const json = await res.json();
                        const errors = document.getElementById('loginErrors');
                        errors.style.display = 'block';
                        errors.innerHTML = Object.values(json.errors || {}).map(arr => '<div>' + arr.join('<br>') + '</div>').join('');
                        return;
                    }

                    if (res.ok) {
                        window.location.reload();
                        return;
                    }

                    // fallback: submit normally
                    form.submit();
                }).catch(() => form.submit());
            });
        }
    })();
    </script>

</body>

</html>   