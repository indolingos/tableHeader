<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Master Product</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-card {
        width: 100%;
        max-width: 380px;
        border: none;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0,0,0,.25);
    }
    .login-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #0d6efd;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 1rem;
    }
    .btn-primary { background-color: #0d6efd; border: none; }
    #loginAlert { display: none; }
</style>
</head>
<body>

<div class="card login-card">
    <div class="card-body p-4 p-sm-5">
        <div class="login-icon"><i class="bi bi-box-seam"></i></div>
        <h4 class="text-center mb-1">Master Product</h4>
        <p class="text-center text-muted mb-4">Silakan login untuk melanjutkan</p>

        <div id="loginAlert" class="alert alert-danger py-2"></div>

        <form id="loginForm" novalidate>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="username" name="username" autocomplete="username" required autofocus>
                </div>
                <div class="invalid-feedback">Username wajib diisi.</div>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                    <button type="button" class="btn btn-outline-secondary" id="btnTogglePassword"><i class="bi bi-eye"></i></button>
                </div>
                <div class="invalid-feedback">Password wajib diisi.</div>
            </div>
            <button type="submit" class="btn btn-primary w-100" id="btnLogin">
                <span class="spinner-border spinner-border-sm d-none me-1" id="loginSpinner"></span>Login
            </button>
            <p class="text-center text-muted mt-3 mb-0">
                Belum punya akun? <a href="<?= site_url('auth/signup'); ?>">Sign Up</a>
            </p>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function () {
    var LOGIN_URL = '<?= site_url('auth/login'); ?>';

    $('#btnTogglePassword').on('click', function () {
        var $pwd = $('#password');
        var isPwd = $pwd.attr('type') === 'password';
        $pwd.attr('type', isPwd ? 'text' : 'password');
        $(this).find('i').toggleClass('bi-eye bi-eye-slash');
    });

    function showError(message) {
        $('#loginAlert').text(message).show();
    }

    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        $('#loginAlert').hide();
        $('#loginForm .is-invalid').removeClass('is-invalid');

        var username = $('#username').val().trim();
        var password = $('#password').val();
        var valid = true;

        if (username === '') { $('#username').addClass('is-invalid'); valid = false; }
        if (password === '') { $('#password').addClass('is-invalid'); valid = false; }
        if (!valid) return;

        $('#btnLogin').prop('disabled', true);
        $('#loginSpinner').removeClass('d-none');

        $.ajax({
            url: LOGIN_URL,
            method: 'POST',
            data: { username: username, password: password },
            dataType: 'json'
        }).done(function (res) {
            if (res.status) {
                window.location.href = res.redirect;
            } else {
                showError(res.message || 'Login gagal.');
            }
        }).fail(function (xhr) {
            var msg = 'Terjadi kesalahan, silakan coba lagi.';
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            showError(msg);
        }).always(function () {
            $('#btnLogin').prop('disabled', false);
            $('#loginSpinner').addClass('d-none');
        });
    });
});
</script>
</body>
</html>
