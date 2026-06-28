<?php
session_start();
require_once 'classes/Auth.php';

if (Auth::isLoggedIn()) {
    header("Location: pages/dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    if ($auth->login($_POST['username'], $_POST['password'])) {
        header("Location: pages/dashboard.php");
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login – Supermarket</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>

.login-wrap {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;

    /* === BACKGROUND BISA DIGANTI DI SINI === */
    background-image: url('assets/img/bg-login.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    /* ======================================= */

    position: relative;
}

/* Overlay gelap di atas background agar card tetap terbaca */
.login-wrap::before {
    content: '';
    position: absolute;
    inset: 0;
    /* === GANTI WARNA & OPACITY OVERLAY DI SINI === */
    background: rgba(0, 0, 0, 0.45);
    /* ============================================== */
}

.login-card {
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.97);
    border-radius: 20px;
    padding: 44px 40px;
    width: 420px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.35);
}

.login-logo .icon { font-size: 52px; }
.login-logo h1 { font-size: 24px; font-weight: 700; color: #1e293b; margin-top: 10px; }
.login-logo p { color: #64748b; font-size: 13px; margin-top: 4px; }

.login-divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 16px 0;
    color: #94a3b8;
    font-size: 12px;
}
.login-divider::before,
.login-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e2e8f0;
}
</style>
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo" style="text-align:center;margin-bottom:28px;">
      <div class="icon">🛒</div>
      <h1>Supermarket POS</h1>
      <p>Sistem Manajemen Inventori</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;font-size:15px;margin-top:4px;">
        🔐 Login
      </button>
    </form>

    <div class="login-divider">atau</div>

    <div style="text-align:center;">
      <p style="font-size:13px;color:#64748b;margin-bottom:10px;">Belum punya akun?</p>
      <a href="register.php" class="btn btn-secondary" style="width:100%;justify-content:center;padding:11px;display:flex;">
        📝 Daftar Akun Baru
      </a>
    </div>

  </div>
</div>
</body>
</html>