<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    🛒 Supermarket
  </div>
  <div class="sidebar-user">
    <div class="name">👤 <?= htmlspecialchars($_SESSION['nama']) ?></div>
    <div class="role"><?= htmlspecialchars($_SESSION['role']) ?></div>
  </div>
  <nav>
    <div class="nav-section">Menu Utama</div>
    <a href="dashboard.php" class="nav-link <?= $current==='dashboard.php'?'active':'' ?>">
      <span class="icon">📊</span> Dashboard
    </a>
    <a href="barang.php" class="nav-link <?= $current==='barang.php'?'active':'' ?>">
      <span class="icon">📦</span> Data Barang
    </a>
    <a href="transaksi.php" class="nav-link <?= $current==='transaksi.php'?'active':'' ?>">
      <span class="icon">🔄</span> Transaksi
    </a>

    <div class="nav-section">Laporan</div>
    <a href="report.php" class="nav-link <?= $current==='report.php'?'active':'' ?>">
      <span class="icon">📋</span> Laporan
    </a>

    <?php if (Auth::isAdmin()): ?>
    <div class="nav-section">Admin</div>
    <a href="kategori.php" class="nav-link <?= $current==='kategori.php'?'active':'' ?>">
      <span class="icon">🏷️</span> Kategori
    </a>
    <a href="user.php" class="nav-link <?= $current==='user.php'?'active':'' ?>">
      <span class="icon">👥</span> Users
    </a>
    <?php endif; ?>

    <div class="nav-section">Akun</div>
    <a href="logout.php" class="nav-link" onclick="return confirm('Yakin ingin logout?')">
      <span class="icon">🚪</span> Logout
    </a>
  </nav>
</aside>
