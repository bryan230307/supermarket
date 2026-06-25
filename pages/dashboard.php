<?php
session_start();
require_once '../classes/Auth.php';
require_once '../classes/Barang.php';
require_once '../classes/Transaksi.php';
require_once '../classes/Kategori.php';

Auth::requireLogin();

$db = \Database::getInstance()->getConnection();
$barangModel = new Barang();
$transaksiModel = new Transaksi();

// Stats
$totalBarang = $db->query("SELECT COUNT(*) FROM barang")->fetchColumn();
$totalKategori = $db->query("SELECT COUNT(*) FROM kategori")->fetchColumn();
$totalTrxHariIni = $db->query("SELECT COUNT(*) FROM transaksi WHERE tanggal = CURDATE()")->fetchColumn();
$nilaiStok = $db->query("SELECT SUM(harga * stok) FROM barang")->fetchColumn();

$summary = $transaksiModel->getSummary();
$masuk = 0; $keluar = 0;
foreach ($summary as $s) {
    if ($s['jenis'] === 'masuk') $masuk = $s['total_nilai'];
    if ($s['jenis'] === 'keluar') $keluar = $s['total_nilai'];
}

$lowStock = $barangModel->getLowStock(5);
$topBarang = $transaksiModel->getTopBarang(5);
$recentTrx = array_slice($transaksiModel->getAllWithDetail(), 0, 8);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard – Supermarket</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <div class="topbar">
    <h2>📊 Dashboard</h2>
    <span style="font-size:13px;color:#64748b;">Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?> 👋</span>
  </div>
  <div class="page-body">

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon blue">📦</div>
        <div><div class="stat-label">Total Barang</div><div class="stat-value"><?= number_format($totalBarang) ?></div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">🏷️</div>
        <div><div class="stat-label">Kategori</div><div class="stat-value"><?= number_format($totalKategori) ?></div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange">🔄</div>
        <div><div class="stat-label">Transaksi Hari Ini</div><div class="stat-value"><?= number_format($totalTrxHariIni) ?></div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue">💰</div>
        <div><div class="stat-label">Nilai Stok</div><div class="stat-value" style="font-size:16px;">Rp <?= number_format($nilaiStok,0,',','.') ?></div></div>
      </div>
    </div>

    <div class="stats-grid" style="margin-bottom:24px;">
      <div class="stat-card">
        <div class="stat-icon green">📥</div>
        <div><div class="stat-label">Total Nilai Masuk</div><div class="stat-value" style="font-size:16px;color:#16a34a;">Rp <?= number_format($masuk,0,',','.') ?></div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red">📤</div>
        <div><div class="stat-label">Total Nilai Keluar</div><div class="stat-value" style="font-size:16px;color:#dc2626;">Rp <?= number_format($keluar,0,',','.') ?></div></div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
      <!-- Stok Menipis -->
      <div class="card">
        <div class="card-header">
          <span class="card-title">⚠️ Stok Menipis (≤10)</span>
          <a href="barang.php" class="btn btn-sm btn-secondary">Lihat Semua</a>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Barang</th><th>Stok</th></tr></thead>
            <tbody>
            <?php if (empty($lowStock)): ?>
              <tr><td colspan="2" style="text-align:center;color:#64748b;padding:20px;">✅ Semua stok aman</td></tr>
            <?php else: ?>
              <?php foreach ($lowStock as $b): ?>
              <tr>
                <td><?= htmlspecialchars($b['nama_barang']) ?></td>
                <td><span class="badge badge-danger"><?= $b['stok'] ?></span></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Top Terjual -->
      <div class="card">
        <div class="card-header"><span class="card-title">🏆 Top 5 Barang Keluar</span></div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Barang</th><th>Qty</th><th>Nilai</th></tr></thead>
            <tbody>
            <?php if (empty($topBarang)): ?>
              <tr><td colspan="3" style="text-align:center;color:#64748b;padding:20px;">Belum ada data</td></tr>
            <?php else: ?>
              <?php foreach ($topBarang as $b): ?>
              <tr>
                <td><?= htmlspecialchars($b['nama_barang']) ?></td>
                <td><?= number_format($b['total_qty']) ?></td>
                <td>Rp <?= number_format($b['total_nilai'],0,',','.') ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Transaksi Terbaru -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">🕒 Transaksi Terbaru</span>
        <a href="transaksi.php" class="btn btn-sm btn-primary">Lihat Semua</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>ID</th><th>Barang</th><th>Kategori</th><th>Tanggal</th><th>Qty</th><th>Jenis</th><th>Total</th></tr></thead>
          <tbody>
          <?php if (empty($recentTrx)): ?>
            <tr><td colspan="7" style="text-align:center;color:#64748b;padding:20px;">Belum ada transaksi</td></tr>
          <?php else: ?>
            <?php foreach ($recentTrx as $t): ?>
            <tr>
              <td>#<?= $t['id_transaksi'] ?></td>
              <td><?= htmlspecialchars($t['nama_barang']) ?></td>
              <td><span class="badge badge-info"><?= htmlspecialchars($t['nama_kategori']) ?></span></td>
              <td><?= date('d/m/Y', strtotime($t['tanggal'])) ?></td>
              <td><?= number_format($t['jumlah']) ?></td>
              <td>
                <?php if ($t['jenis']==='masuk'): ?>
                  <span class="badge badge-success">📥 Masuk</span>
                <?php else: ?>
                  <span class="badge badge-danger">📤 Keluar</span>
                <?php endif; ?>
              </td>
              <td>Rp <?= number_format($t['total_harga'],0,',','.') ?></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
</body>
</html>
