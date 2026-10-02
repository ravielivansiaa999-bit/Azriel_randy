
<?php
require '../config/database.php';
require '../includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("
        SELECT id, username, password, role, anggota_id
        FROM users
        WHERE username = ?
    ");

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'anggota_id' => $user['anggota_id'],
        ];

        header("Location: " . BASE . "/dashboard.php");
        exit;
    }

    $error = "Username atau password salah.";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Perpustakaan</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;

            background:
                radial-gradient(circle at 10% 10%, rgba(129,140,248,.35), transparent 35%),
                radial-gradient(circle at 90% 90%, rgba(99,102,241,.35), transparent 35%),
                linear-gradient(135deg, #312e81, #4338ca, #6366f1);

            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* LOGIN CONTAINER */
        .login-container {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border-radius: 22px;
            padding: 38px;
            box-shadow: 0 25px 70px rgba(15, 23, 42, .25);
            animation: muncul .5s ease;
        }

        @keyframes muncul {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* LOGO */
        .login-logo {
            width: 75px;
            height: 75px;
            margin: 0 auto 18px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 22px;
            background: linear-gradient(135deg, #4f46e5, #818cf8);

            font-size: 38px;
            box-shadow: 0 10px 20px rgba(79,70,229,.22);
        }

        /* HEADER */
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #1e1b4b;
            font-size: 25px;
            font-weight: 750;
            margin: 0 0 9px;
        }

        .login-header p {
            color: #94a3b8;
            font-size: 13px;
            margin: 0;
            line-height: 1.6;
        }

        /* FORM */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 9px;
            color: #334155;
            font-size: 13px;
            font-weight: 650;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 17px;
            color: #818cf8;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 14px 45px;
            border: 1px solid #e2e8f0;
            border-radius: 11px;
            background: #f8fafc;
            font-size: 14px;
            color: #1e293b;
            outline: none;
            transition: .2s;
        }

        .form-control::placeholder {
            color: #a1a1aa;
        }

        .form-control:focus {
            background: #ffffff;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99,102,241,.12);
        }

        /* SHOW PASSWORD */
        .toggle-password {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            font-size: 17px;
            padding: 5px;
        }

        .toggle-password:hover {
            color: #4f46e5;
        }

        /* ERROR */
        .error-message {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 12px;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        /* BUTTON */
        .btn-login {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 11px;

            background: linear-gradient(120deg, #4f46e5, #6366f1);
            color: white;

            font-size: 14px;
            font-weight: 700;
            cursor: pointer;

            box-shadow: 0 7px 16px rgba(79,70,229,.22);
            transition: .2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(79,70,229,.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* FOOTER */
        .login-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }

        .login-footer p {
            color: #94a3b8;
            font-size: 11px;
            margin: 0;
        }

        .login-footer strong {
            color: #6366f1;
        }

        /* RESPONSIVE */
        @media (max-width: 480px) {
            .login-container {
                padding: 28px 22px;
                border-radius: 18px;
            }

            .login-header h1 {
                font-size: 22px;
            }

            .login-logo {
                width: 65px;
                height: 65px;
                font-size: 32px;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">

        <!-- LOGO -->
        <div class="login-logo">
            📚
        </div>

        <!-- HEADER -->
        <div class="login-header">
            <h1>Selamat Datang!</h1>

            <p>
                Sistem Informasi Perpustakaan<br>
                Silakan masuk ke akun Anda.
            </p>
        </div>

        <!-- ERROR -->
        <?php if ($error): ?>
            <div class="error-message">
                <span>⚠️</span>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- FORM LOGIN -->
        <form method="post">

            <div class="form-group">

                <label for="username">
                    Username
                </label>

                <div class="input-wrapper">

                    <span class="input-icon">👤</span>

                    <input
                        class="form-control"
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Masukkan username"
                        value="<?= e($_POST['username'] ?? '') ?>"
                        autocomplete="username"
                        required
                        autofocus
                    >

                </div>

            </div>

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <div class="input-wrapper">

                    <span class="input-icon">🔒</span>

                    <input
                        class="form-control"
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        type="button"
                        class="toggle-password"
                        id="togglePassword"
                        aria-label="Tampilkan password"
                    >
                        👁
                    </button>

                </div>

            </div>

            <button type="submit" class="btn-login">
                Masuk ke Dashboard →
            </button>

        </form>

        <!-- FOOTER -->
        <div class="login-footer">
            <p>
                © <?= date('Y') ?>
                <strong>Perpustakaan Digital</strong>
                <br>
                Sistem Informasi Perpustakaan
            </p>
        </div>

    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');

        toggleButton.addEventListener('click', function () {

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = '🙈';
                toggleButton.setAttribute('aria-label', 'Sembunyikan password');
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = '👁';
                toggleButton.setAttribute('aria-label', 'Tampilkan password');
            }

        });
    </script>

</body>
</html>