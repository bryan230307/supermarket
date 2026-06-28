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
    <a href="#" class="nav-link" onclick="document.getElementById('modalLogout').classList.add('show');return false;">
      <span class="icon">🚪</span> Logout
    </a>
  </nav>
</aside>

<!-- Modal Logout -->
<div class="modal-overlay" id="modalLogout" style="z-index:9999;">
  <div class="modal" style="width:360px;text-align:center;">
    <div class="modal-body" style="padding:32px 24px 20px;">
      <div style="font-size:52px;margin-bottom:12px;">👋</div>
      <h3 style="font-size:18px;color:#1e293b;margin-bottom:8px;">Yakin ingin keluar?</h3>
      <p style="font-size:13px;color:#64748b;">Sesi kamu akan diakhiri dan kamu perlu login kembali untuk mengakses sistem.</p>
    </div>
    <div style="display:flex;gap:10px;padding:0 20px 24px;">
      <button onclick="document.getElementById('modalLogout').classList.remove('show')"
        class="btn btn-secondary" style="flex:1;justify-content:center;padding:11px;">
        Batal
      </button>
      <a href="logout.php" class="btn btn-danger" style="flex:1;justify-content:center;padding:11px;">
        🚪 Ya, Logout
      </a>
    </div>
  </div>
</div>

<script>
document.getElementById('modalLogout').addEventListener('click', function(e) {
  if (e.target === this) this.classList.remove('show');
});
</script>
