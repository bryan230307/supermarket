<?php
session_start();
require_once '../classes/Auth.php';
require_once '../classes/Transaksi.php';
require_once '../classes/Barang.php';

Auth::requireLogin();

$transaksiModel = new Transaksi();
$barangModel = new Barang();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        // Cek stok jika keluar
        if ($_POST['jenis'] === 'keluar') {
            $barang = $barangModel->getById($_POST['id_barang']);
            if ($barang && $barang['stok'] < $_POST['jumlah']) {
                $message = "Stok tidak cukup! Stok tersedia: {$barang['stok']}";
                $messageType = 'danger';
                goto skip_create;
            }
        }
        if ($transaksiModel->create($_POST['id_barang'], $_POST['tanggal'], $_POST['jumlah'], $_POST['jenis'])) {
            $message = 'Transaksi berhasil dicatat!';
            $messageType = 'success';
        } else {
            $message = 'Gagal menyimpan transaksi.';
            $messageType = 'danger';
        }
    } elseif ($action === 'delete') {
        Auth::requireAdmin();
        if ($transaksiModel->delete($_POST['id_transaksi'])) {
            $message = 'Transaksi berhasil dihapus dan stok dikembalikan!';
            $messageType = 'success';
        } else {
            $message = 'Gagal menghapus transaksi.';
            $messageType = 'danger';
        }
    }
    skip_create:
}

$search = $_GET['search'] ?? '';
$dataTrx = $search ? $transaksiModel->search($search) : $transaksiModel->getAllWithDetail();
$dataBarang = $barangModel->getAllWithKategori();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Transaksi – Supermarket</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <div class="topbar">
    <h2>🔄 Transaksi Barang</h2>
    <span style="font-size:13px;color:#64748b;"><?= count($dataTrx) ?> transaksi</span>
  </div>
  <div class="page-body">

    <?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>">
      <?= $messageType==='success' ? '✅' : '❌' ?> <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <span class="card-title">Data Transaksi</span>
        <div style="display:flex;gap:8px;align-items:center;">
          <form method="GET" class="search-bar">
            <input type="text" name="search" class="form-control" placeholder="Cari barang / jenis / tanggal..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-secondary">🔍</button>
            <?php if ($search): ?>
            <a href="transaksi.php" class="btn btn-secondary">✕</a>
            <?php endif; ?>
          </form>
          <button class="btn btn-primary" onclick="openModal('modalTambah')">➕ Tambah Transaksi</button>
        </div>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Barang</th>
              <th>Kategori</th>
              <th>Tanggal</th>
              <th>Jumlah</th>
              <th>Jenis</th>
              <th>Harga Satuan</th>
              <th>Total</th>
              <?php if (Auth::isAdmin()): ?><th>Aksi</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($dataTrx)): ?>
            <tr><td colspan="9" style="text-align:center;padding:30px;color:#64748b;">Tidak ada data</td></tr>
          <?php else: ?>
            <?php foreach ($dataTrx as $t): ?>
            <tr>
              <td>#<?= $t['id_transaksi'] ?></td>
              <td><strong><?= htmlspecialchars($t['nama_barang']) ?></strong></td>
              <td><span class="badge badge-info"><?= htmlspecialchars($t['nama_kategori'] ?? '-') ?></span></td>
              <td><?= date('d/m/Y', strtotime($t['tanggal'])) ?></td>
              <td><?= number_format($t['jumlah']) ?></td>
              <td>
                <?php if ($t['jenis']==='masuk'): ?>
                  <span class="badge badge-success">📥 Masuk</span>
                <?php else: ?>
                  <span class="badge badge-danger">📤 Keluar</span>
                <?php endif; ?>
              </td>
              <td>Rp <?= number_format($t['harga'],0,',','.') ?></td>
              <td><strong>Rp <?= number_format($t['total_harga'],0,',','.') ?></strong></td>
              <?php if (Auth::isAdmin()): ?>
              <td>
                <button class="btn btn-sm btn-danger"
                  onclick="deleteTrx(<?= $t['id_transaksi'] ?>)">🗑️</button>
              </td>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- Modal Tambah Transaksi -->
<div class="modal-overlay" id="modalTambah">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">➕ Tambah Transaksi</span>
      <button class="modal-close" onclick="closeModal('modalTambah')">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="create">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Barang *</label>
          <select name="id_barang" id="sel_barang" class="form-control" required onchange="updateInfo()">
            <option value="">-- Pilih Barang --</option>
            <?php foreach ($dataBarang as $b): ?>
            <option value="<?= $b['id_barang'] ?>"
              data-stok="<?= $b['stok'] ?>"
              data-harga="<?= $b['harga'] ?>"
              data-nama="<?= htmlspecialchars($b['nama_barang']) ?>">
              <?= htmlspecialchars($b['nama_barang']) ?> (Stok: <?= $b['stok'] ?>)
            </option>
            <?php endforeach; ?>
          </select>
          <div id="info_barang" style="margin-top:6px;font-size:12px;color:#64748b;"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Jenis Transaksi *</label>
          <select name="jenis" class="form-control" required>
            <option value="masuk">📥 Masuk (Stok bertambah)</option>
            <option value="keluar">📤 Keluar (Stok berkurang)</option>
          </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label class="form-label">Tanggal *</label>
            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Jumlah *</label>
            <input type="number" name="jumlah" class="form-control" min="1" required>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Delete -->
<div class="modal-overlay" id="modalDelete">
  <div class="modal" style="width:360px;">
    <div class="modal-header">
      <span class="modal-title">🗑️ Hapus Transaksi</span>
      <button class="modal-close" onclick="closeModal('modalDelete')">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id_transaksi" id="delete_id">
      <div class="modal-body">
        <p>Hapus transaksi <strong>#<span id="delete_id_display"></span></strong>?</p>
        <p style="font-size:13px;color:#d97706;margin-top:8px;">⚠️ Stok barang akan dikembalikan ke kondisi sebelumnya.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalDelete')">Batal</button>
        <button type="submit" class="btn btn-danger">Hapus</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function updateInfo() {
  const sel = document.getElementById('sel_barang');
  const opt = sel.options[sel.selectedIndex];
  const info = document.getElementById('info_barang');
  if (opt.value) {
    const stok = opt.dataset.stok;
    const harga = parseInt(opt.dataset.harga).toLocaleString('id-ID');
    info.innerHTML = `💰 Harga: Rp ${harga} &nbsp;|&nbsp; 📦 Stok saat ini: <strong>${stok}</strong>`;
  } else {
    info.textContent = '';
  }
}

function deleteTrx(id) {
  document.getElementById('delete_id').value = id;
  document.getElementById('delete_id_display').textContent = id;
  openModal('modalDelete');
}

document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', e => { if (e.target === el) el.classList.remove('show'); });
});
</script>
</body>
</html>
