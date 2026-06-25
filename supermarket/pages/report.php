<?php
session_start();
require_once '../classes/Auth.php';
require_once '../classes/Transaksi.php';

Auth::requireLogin();

$transaksiModel = new Transaksi();

$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$year = $_GET['year'] ?? date('Y');

$data = $transaksiModel->getReportByDate($dari, $sampai);
$summary = $transaksiModel->getSummary();
$topBarang = $transaksiModel->getTopBarang(10);
$monthly = $transaksiModel->getMonthlyReport($year);

// Hitung total periode ini
$totalMasuk = 0; $totalKeluar = 0; $qtyMasuk = 0; $qtyKeluar = 0;
foreach ($data as $d) {
    if ($d['jenis'] === 'masuk') { $totalMasuk += $d['total_harga']; $qtyMasuk += $d['jumlah']; }
    else { $totalKeluar += $d['total_harga']; $qtyKeluar += $d['jumlah']; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan – Supermarket</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <div class="topbar no-print">
    <h2>📋 Laporan Transaksi</h2>
    <button class="btn btn-secondary" onclick="window.print()">🖨️ Print</button>
  </div>
  <div class="page-body">

    <!-- Filter -->
    <div class="report-filter no-print">
      <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
        <div class="form-group" style="margin:0;">
          <label class="form-label">Dari Tanggal</label>
          <input type="date" name="dari" class="form-control" value="<?= htmlspecialchars($dari) ?>">
        </div>
        <div class="form-group" style="margin:0;">
          <label class="form-label">Sampai Tanggal</label>
          <input type="date" name="sampai" class="form-control" value="<?= htmlspecialchars($sampai) ?>">
        </div>
        <button type="submit" class="btn btn-primary">🔍 Filter</button>
        <a href="report.php" class="btn btn-secondary">Reset</a>
      </form>
    </div>

    <!-- Print Header -->
    <div style="display:none;" class="print-only">
      <h2 style="text-align:center;margin-bottom:4px;">🛒 SUPERMARKET</h2>
      <h3 style="text-align:center;font-weight:normal;color:#64748b;">Laporan Transaksi</h3>
      <p style="text-align:center;font-size:13px;">Periode: <?= date('d/m/Y', strtotime($dari)) ?> – <?= date('d/m/Y', strtotime($sampai)) ?></p>
      <hr style="margin:12px 0;">
    </div>

    <!-- Summary Cards -->
    <div class="summary-grid">
      <div class="summary-card">
        <div class="value"><?= count($data) ?></div>
        <div class="label">Total Transaksi</div>
      </div>
      <div class="summary-card green">
        <div class="value"><?= number_format($qtyMasuk) ?></div>
        <div class="label">📥 Qty Masuk</div>
      </div>
      <div class="summary-card red">
        <div class="value"><?= number_format($qtyKeluar) ?></div>
        <div class="label">📤 Qty Keluar</div>
      </div>
      <div class="summary-card green">
        <div class="value" style="font-size:16px;">Rp <?= number_format($totalMasuk,0,',','.') ?></div>
        <div class="label">Nilai Masuk</div>
      </div>
      <div class="summary-card red">
        <div class="value" style="font-size:16px;">Rp <?= number_format($totalKeluar,0,',','.') ?></div>
        <div class="label">Nilai Keluar</div>
      </div>
    </div>

    <!-- Tabel Detail -->
    <div class="card" style="margin-bottom:20px;">
      <div class="card-header">
        <span class="card-title">Detail Transaksi: <?= date('d/m/Y', strtotime($dari)) ?> – <?= date('d/m/Y', strtotime($sampai)) ?></span>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>ID</th><th>Tanggal</th><th>Barang</th><th>Kategori</th><th>Jenis</th><th>Qty</th><th>Harga</th><th>Total</th></tr>
          </thead>
          <tbody>
          <?php if (empty($data)): ?>
            <tr><td colspan="8" style="text-align:center;padding:30px;color:#64748b;">Tidak ada transaksi pada periode ini</td></tr>
          <?php else: ?>
            <?php foreach ($data as $d): ?>
            <tr>
              <td>#<?= $d['id_transaksi'] ?></td>
              <td><?= date('d/m/Y', strtotime($d['tanggal'])) ?></td>
              <td><?= htmlspecialchars($d['nama_barang']) ?></td>
              <td><span class="badge badge-info"><?= htmlspecialchars($d['nama_kategori'] ?? '-') ?></span></td>
              <td>
                <?php if ($d['jenis']==='masuk'): ?>
                  <span class="badge badge-success">📥 Masuk</span>
                <?php else: ?>
                  <span class="badge badge-danger">📤 Keluar</span>
                <?php endif; ?>
              </td>
              <td><?= number_format($d['jumlah']) ?></td>
              <td>Rp <?= number_format($d['harga'],0,',','.') ?></td>
              <td><strong>Rp <?= number_format($d['total_harga'],0,',','.') ?></strong></td>
            </tr>
            <?php endforeach; ?>
            <tr style="background:#f8fafc;font-weight:600;">
              <td colspan="5" style="text-align:right;">TOTAL</td>
              <td><?= number_format($qtyMasuk + $qtyKeluar) ?></td>
              <td></td>
              <td>Rp <?= number_format($totalMasuk + $totalKeluar,0,',','.') ?></td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Top Barang -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="no-print">
      <div class="card">
        <div class="card-header"><span class="card-title">🏆 Top 10 Barang Terjual</span></div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Rank</th><th>Barang</th><th>Qty</th><th>Nilai</th></tr></thead>
            <tbody>
            <?php if (empty($topBarang)): ?>
              <tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b;">Belum ada data</td></tr>
            <?php else: ?>
              <?php foreach ($topBarang as $i => $b): ?>
              <tr>
                <td><span class="badge badge-<?= $i<3 ? 'warning' : 'secondary' ?>"><?= $i+1 ?></span></td>
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

      <!-- Laporan Bulanan -->
      <div class="card">
        <div class="card-header">
          <span class="card-title">📅 Rekap Bulanan <?= $year ?></span>
          <form method="GET" style="display:flex;gap:6px;align-items:center;">
            <input type="hidden" name="dari" value="<?= $dari ?>">
            <input type="hidden" name="sampai" value="<?= $sampai ?>">
            <select name="year" class="form-control" style="width:auto;padding:5px 8px;font-size:13px;" onchange="this.form.submit()">
              <?php for ($y = date('Y'); $y >= date('Y')-5; $y--): ?>
              <option value="<?= $y ?>" <?= $y==$year ? 'selected' : '' ?>><?= $y ?></option>
              <?php endfor; ?>
            </select>
          </form>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Bulan</th><th>Jenis</th><th>Nilai</th></tr></thead>
            <tbody>
            <?php if (empty($monthly)): ?>
              <tr><td colspan="3" style="text-align:center;padding:20px;color:#64748b;">Belum ada data</td></tr>
            <?php else: ?>
              <?php foreach ($monthly as $m): ?>
              <tr>
                <td><?= $m['nama_bulan'] ?></td>
                <td>
                  <?php if ($m['jenis']==='masuk'): ?>
                    <span class="badge badge-success">📥 Masuk</span>
                  <?php else: ?>
                    <span class="badge badge-danger">📤 Keluar</span>
                  <?php endif; ?>
                </td>
                <td>Rp <?= number_format($m['total_nilai'],0,',','.') ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<style>
@media print {
  .print-only { display: block !important; }
  .no-print { display: none !important; }
}
</style>
</body>
</html>
