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
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo">
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
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:11px;">
        🔐 Login
      </button>
    </form>

    <p style="text-align:center;margin-top:16px;font-size:12px;color:#94a3b8;">
      Demo: admin / admin123 &nbsp;|&nbsp; petugas / petugas123
    </p>
  </div>
</div>
</body>
</html>
