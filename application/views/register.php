<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up - Master Product</title>
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
    #registerAlert { display: none; }
    .invalid-feedback { display: none; }
    .invalid-feedback.d-block { display: block; }
</style>
</head>
<body>

<div class="card login-card">
    <div class="card-body p-4 p-sm-5">
        <div class="login-icon"><i class="bi bi-person-plus"></i></div>
        <h4 class="text-center mb-1">Buat Akun</h4>

        <div id="registerAlert" class="alert alert-danger py-2"></div>

        <form id="registerForm" novalidate>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="username" name="username" autocomplete="username" required autofocus>
                </div>
                <div class="invalid-feedback" id="usernameFeedback">Username wajib diisi (min. 3 karakter).</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" required>
                    <button type="button" class="btn btn-outline-secondary" id="btnTogglePassword"><i class="bi bi-eye"></i></button>
                </div>
                <div class="invalid-feedback" id="passwordFeedback">Password wajib diisi (min. 6 karakter).</div>
            </div>
            <div class="mb-4">
                <label class="form-label">Konfirmasi Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" autocomplete="new-password" required>
                </div>
                <div class="invalid-feedback" id="confirmFeedback">Konfirmasi password tidak cocok.</div>
            </div>
            <button type="submit" class="btn btn-primary w-100" id="btnRegister">
                <span class="spinner-border spinner-border-sm d-none me-1" id="registerSpinner"></span>Sign Up
            </button>
            <p class="text-center text-muted mt-3 mb-0">
                Sudah punya akun? <a href="<?= site_url(); ?>">Login</a>
            </p>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function () {
    var REGISTER_URL = '<?= site_url('auth/register'); ?>';
    var LOGIN_URL     = '<?= site_url(); ?>';

    $('#btnTogglePassword').on('click', function () {
        var $pwd = $('#password');
        var isPwd = $pwd.attr('type') === 'password';
        $pwd.attr('type', isPwd ? 'text' : 'password');
        $(this).find('i').toggleClass('bi-eye bi-eye-slash');
    });

    function showError(message) {
        $('#registerAlert').removeClass('alert-success').addClass('alert-danger').text(message).show();
    }

    function showSuccess(message) {
        $('#registerAlert').removeClass('alert-danger').addClass('alert-success').text(message).show();
    }

    $('#registerForm').on('submit', function (e) {
        e.preventDefault();

        $('#registerAlert').hide();
        $('#registerForm .is-invalid').removeClass('is-invalid');
        $('#registerForm .invalid-feedback').removeClass('d-block');

        var username = $('#username').val().trim();
        var password = $('#password').val();
        var confirm  = $('#confirm_password').val();
        var valid = true;

        if (username.length < 3) {
            $('#username').addClass('is-invalid');
            $('#usernameFeedback').addClass('d-block');
            valid = false;
        }
        if (password.length < 6) {
            $('#password').addClass('is-invalid');
            $('#passwordFeedback').addClass('d-block');
            valid = false;
        }
        if (confirm !== password || confirm === '') {
            $('#confirm_password').addClass('is-invalid');
            $('#confirmFeedback').addClass('d-block');
            valid = false;
        }
        if (!valid) {
            showError('Periksa kembali data yang diisi.');
            return;
        }

        $('#btnRegister').prop('disabled', true);
        $('#registerSpinner').removeClass('d-none');

        $.ajax({
            url: REGISTER_URL,
            method: 'POST',
            data: { username: username, password: password, confirm_password: confirm },
            dataType: 'json'
        }).done(function (res) {
            if (res.status) {
                showSuccess(res.message || 'Akun berhasil dibuat.');
                setTimeout(function () { window.location.href = LOGIN_URL; }, 1200);
            } else {
                showError(res.message || 'Registrasi gagal.');
            }
        }).fail(function (xhr) {
            var msg = 'Terjadi kesalahan, silakan coba lagi.';
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            showError(msg);
        }).always(function () {
            $('#btnRegister').prop('disabled', false);
            $('#registerSpinner').addClass('d-none');
        });
    });
});
</script>
</body>
</html>
