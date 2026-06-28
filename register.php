<?php
session_start();
require_once 'classes/Auth.php';
require_once 'classes/User.php';

if (Auth::isLoggedIn()) {
    header("Location: pages/dashboard.php");
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];

    if (empty($nama) || empty($username) || empty($password)) {
        $error = 'Semua field wajib diisi!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        $userModel = new User();
        if ($userModel->usernameExists($username)) {
            $error = 'Username sudah digunakan, pilih username lain!';
        } else {
            if ($userModel->create($nama, $username, $password, 'petugas')) {
                $success = true;
                $namaUser = $nama;
            } else {
                $error = 'Gagal mendaftar, coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrasi – Supermarket</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>

.login-wrap {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;

    /* === BACKGROUND BISA DIGANTI DI SINI === */
    background-image: url('assets/img/bg-registrasi.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    /* ======================================= */

    position: relative;
}

/* Overlay gelap */
.login-wrap::before {
    content: '';
    position: absolute;
    inset: 0;
    /* === GANTI OPACITY OVERLAY DI SINI (0.0 - 1.0) === */
    background: rgba(0, 0, 0, 0.45);
    /* =================================================== */
}

.login-card {
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.97);
    border-radius: 20px;
    padding: 44px 40px;
    width: 440px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.35);
}

.login-logo .icon { font-size: 52px; }
.login-logo h1 { font-size: 24px; font-weight: 700; color: #1e293b; margin-top: 10px; }
.login-logo p { color: #64748b; font-size: 13px; margin-top: 4px; }

.success-card { text-align: center; padding: 10px 0; }
.success-icon {
    width: 80px; height: 80px;
    background: linear-gradient(135deg, #16a34a, #22c55e);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    margin: 0 auto 20px;
    box-shadow: 0 8px 24px rgba(22,163,74,0.3);
    animation: pop 0.4s ease;
}
@keyframes pop {
    0% { transform: scale(0); opacity: 0; }
    70% { transform: scale(1.15); }
    100% { transform: scale(1); opacity: 1; }
}
.success-card h2 { font-size: 22px; color: #16a34a; margin-bottom: 8px; }
.success-card p { font-size: 14px; color: #64748b; margin-bottom: 6px; }
.username-badge {
    display: inline-block;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    margin: 10px 0 20px;
}
.info-box {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 13px;
    color: #1e40af;
    margin-bottom: 20px;
    text-align: left;
}
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

    <?php if ($success): ?>
    <!-- TAMPILAN SUKSES -->
    <div class="success-card">
      <div class="success-icon">✅</div>
      <h2>Registrasi Berhasil!</h2>
      <p>Selamat datang, <strong><?= htmlspecialchars($namaUser) ?></strong>!</p>
      <p>Akun kamu telah berhasil dibuat.</p>
      <div class="username-badge">👤 <?= htmlspecialchars($_POST['username']) ?></div>
      <div class="info-box">
        ℹ️ Akun kamu terdaftar sebagai <strong>Petugas</strong>. Hubungi admin jika ingin upgrade ke Admin.
      </div>
      <a href="index.php" class="btn btn-success" style="width:100%;justify-content:center;padding:12px;font-size:15px;display:flex;">
        🔐 Login Sekarang
      </a>
    </div>

    <?php else: ?>
    <!-- FORM REGISTRASI -->
    <div class="login-logo" style="text-align:center;margin-bottom:28px;">
      <div class="icon">📝</div>
      <h1>Daftar Akun Baru</h1>
      <p>Buat akun untuk mengakses sistem</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label">Nama Lengkap *</label>
        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap"
          value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">Username *</label>
        <input type="text" name="username" class="form-control" placeholder="Buat username unik"
          value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password *</label>
        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
      </div>
      <div class="form-group">
        <label class="form-label">Konfirmasi Password *</label>
        <input type="password" name="konfirmasi" class="form-control" placeholder="Ulangi password" required>
      </div>
      <div style="background:#f8fafc;border-radius:8px;padding:10px 12px;margin-bottom:16px;font-size:12px;color:#64748b;">
        ℹ️ Akun baru akan terdaftar sebagai <strong>Petugas</strong>.
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;font-size:15px;">
        📝 Daftar Sekarang
      </button>
    </form>

    <div class="login-divider">atau</div>

    <div style="text-align:center;">
      <p style="font-size:13px;color:#64748b;margin-bottom:10px;">Sudah punya akun?</p>
      <a href="index.php" class="btn btn-secondary" style="width:100%;justify-content:center;padding:11px;display:flex;">
        ← Kembali ke Login
      </a>
    </div>
    <?php endif; ?>

  </div>
</div>
</body>
</html>