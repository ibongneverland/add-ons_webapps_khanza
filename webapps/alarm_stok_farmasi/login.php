<?php
// login.php
require_once 'auth.php';
require_once 'koneksi.php';

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Zero Trust CSRF Protection (Aturan .antigravityrules #0)
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        header('HTTP/1.1 403 Forbidden');
        die('Akses ditolak: Invalid CSRF Token.');
    }

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Semua kolom wajib diisi!";
    } else {
        try {
            // 1. Cek tabel Admin
            // Menggunakan AES_DECRYPT dengan key standar Khanza
            $sqlAdmin = "SELECT usere FROM admin WHERE AES_DECRYPT(usere, 'nur') = :uname AND AES_DECRYPT(passworde, 'windi') = :pass LIMIT 1";
            $stmtA = $pdo->prepare($sqlAdmin);
            $stmtA->execute([':uname' => $username, ':pass' => $password]);
            $rowA = $stmtA->fetch();

            if ($rowA) {
                session_regenerate_id(true); // Hindari Session Fixation
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'admin';
                header("Location: index.php");
                exit;
            } else {
                // 2. Cek tabel User
                $sqlUser = "SELECT id_user FROM user WHERE AES_DECRYPT(id_user, 'nur') = :uname AND AES_DECRYPT(password, 'windi') = :pass LIMIT 1";
                $stmtU = $pdo->prepare($sqlUser);
                $stmtU->execute([':uname' => $username, ':pass' => $password]);
                $rowU = $stmtU->fetch();

                if ($rowU) {
                    session_regenerate_id(true);
                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = 'user';
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Kredensial tidak valid. Akses ditolak.";
                }
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan server saat memvalidasi akun.";
            // Untuk log internal bisa echo $e->getMessage() ke file
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dashboard Farmasi</title>
    <!-- Fonts & Bootstrap 5 -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a; /* Slate 900 */
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: radial-gradient(circle at 15% 50%, rgba(30, 58, 138, 0.3), transparent 25%),
                              radial-gradient(circle at 85% 30%, rgba(15, 118, 110, 0.3), transparent 25%);
        }
        .login-card {
            background: rgba(30, 41, 59, 0.7); /* Glassmorphism background */
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 3rem 2rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }
        .form-control {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f8fafc;
            border-radius: 10px;
            padding: 0.8rem 1rem;
        }
        .form-control:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: #3b82f6;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        .form-floating label {
            color: #94a3b8;
        }
        .btn-login {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            border-radius: 10px;
            padding: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }
        .login-logo i {
            font-size: 3rem;
            color: #3b82f6;
            filter: drop-shadow(0 0 10px rgba(59,130,246,0.5));
        }
    </style>
</head>
<body>

    <div class="container d-flex justify-content-center">
        <div class="login-card">
            <div class="text-center mb-4 login-logo">
                <i class="bi bi-shield-lock-fill"></i>
                <h4 class="mt-3 fw-bold text-white">Stock System</h4>
                <p class="text-secondary small">Masukkan otentikasi Khanza Anda</p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger py-2 small fw-bold text-center border-0 bg-danger bg-opacity-25 text-danger border-start border-danger border-4 rounded-end">
                    <i class="bi bi-exclamation-octagon me-1"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required autofocus>
                    <label for="username"><i class="bi bi-person me-1"></i> Username</label>
                </div>
                
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password"><i class="bi bi-key me-1"></i> Password</label>
                </div>

                <button type="submit" class="btn btn-login btn-primary w-100 text-white">
                    LOGIN <i class="bi bi-box-arrow-in-right ms-1"></i>
                </button>
            </form>
        </div>
    </div>

</body>
</html>
