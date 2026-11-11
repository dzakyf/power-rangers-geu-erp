<?php
session_start();
require_once 'db.php'; // Panggil koneksi database

// Jika user sudah login, langsung redirect ke index.php
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error_message = '';
$login_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        // Query ambil data user berdasarkan username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verifikasi password (mendukung hash bcrypt $2y$ dari Laravel)
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                // Set Session Berhasil Login
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['Name'];
                $_SESSION['position'] = $user['Position'];
                $_SESSION['location'] = $user['Location'];
                $_SESSION['role'] = $user['Position'] ?? 'ICT / Procurement';

                $login_success = true;
            } else {
                $error_message = 'Password yang Anda masukkan salah!';
            }
        } else {
            $error_message = 'Username tidak ditemukan di database!';
        }
    } catch (PDOException $e) {
        $error_message = 'Terjadi kesalahan sistem: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GEU Procurement System</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- FontAwesome untuk Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #3d1e58, #5e276b, #1e3a2b);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            padding: 30px 25px;
        }
        .brand-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .brand-logo-text {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 2px;
            background: linear-gradient(135deg, #4b286d, #2c4a3e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .form-label-group {
            position: relative;
            margin-bottom: 20px;
        }
        .form-label-group input {
            height: 52px;
            border-radius: 10px;
            border: 1px solid #ced4da;
            padding: 18px 45px 6px 15px;
            font-size: 15px;
        }
        .form-label-group input:focus {
            border-color: #5e276b;
            box-shadow: 0 0 0 0.2rem rgba(94, 39, 107, 0.25);
        }
        .form-label-group label {
            position: absolute;
            top: 0;
            left: 0;
            display: block;
            width: 100%;
            margin-bottom: 0;
            line-height: 1.5;
            color: #6c757d;
            border: 1px solid transparent;
            border-radius: .25rem;
            transition: all .1s ease-in-out;
            padding: 15px 15px;
            pointer-events: none;
        }
        .form-label-group input:focus ~ label,
        .form-label-group input:not(:placeholder-shown) ~ label {
            padding-top: 4px;
            padding-bottom: 4px;
            font-size: 11px;
            color: #4b286d;
            font-weight: 600;
        }
        .password-toggle-icon {
            position: absolute;
            right: 15px;
            top: 16px;
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }
        .password-toggle-icon:hover {
            color: #4b286d;
        }
        .btn-login {
            background: linear-gradient(135deg, #4b286d, #2c4a3e);
            color: white;
            border: none;
            height: 48px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            transition: opacity 0.2s;
        }
        .btn-login:hover {
            opacity: 0.95;
            color: white;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand-header">
        <div class="brand-logo-text">GEU</div>
        <small class="text-muted font-weight-bold">ERP KSP - Procurement System</small>
    </div>

    <form id="loginForm" action="login.php" method="POST">
        <div class="form-label-group">
            <input type="text" id="username" name="username" class="form-control" placeholder=" " required autofocus autocomplete="off">
            <label for="username"><i class="fas fa-user mr-1"></i> Username</label>
        </div>

        <div class="form-label-group">
            <input type="password" id="password" name="password" class="form-control" placeholder=" " required>
            <label for="password"><i class="fas fa-lock mr-1"></i> Password</label>
            <i class="fas fa-eye password-toggle-icon" id="togglePassword"></i>
        </div>

        <button class="btn btn-login btn-block shadow-sm mt-4" type="submit" id="submitBtn">
            <i class="fas fa-sign-in-alt mr-2"></i> Masuk Sistem
        </button>
    </form>

    <div class="text-center mt-4">
        <small class="text-muted">&copy; 2026 PT. Green Energi Utama</small>
    </div>
</div>

<!-- jQuery, Bootstrap JS, & SweetAlert2 JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Toggle Password Eye
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });

    // Event saat Form Disubmit (Munculkan Efek Loading Spinner Mutar-mutar)
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        Swal.fire({
            title: 'Memproses Login...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    });

    <?php if (!empty($error_message)): ?>
        // Jika Login Gagal (Username/Password salah)
        Swal.fire({
            icon: 'error',
            title: 'Login Gagal!',
            text: '<?= addslashes($error_message); ?>',
            confirmButtonColor: '#4b286d'
        });
    <?php endif; ?>

    <?php if ($login_success): ?>
        // Jika Login Berhasil
        Swal.fire({
            icon: 'success',
            title: 'Login Berhasil!',
            text: 'Selamat datang kembali, <?= addslashes($_SESSION['name'] ?? $_SESSION['username']); ?>',
            timer: 1500,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        }).then(() => {
            window.location.href = 'index.php';
        });
    <?php endif; ?>
</script>

</body>
</html>
