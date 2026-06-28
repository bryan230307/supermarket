<?php
session_start();
require_once '../classes/Auth.php';
require_once '../classes/Barang.php';
require_once '../classes/Kategori.php';

Auth::requireLogin();

$barangModel = new Barang();
$kategoriModel = new Kategori();
$message = '';
$messageType = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        if ($barangModel->create($_POST['nama_barang'], $_POST['harga'], $_POST['stok'], $_POST['id_kategori'])) {
            $message = 'Barang berhasil ditambahkan!';
            $messageType = 'success';
        } else {
            $message = 'Gagal menambahkan barang.';
            $messageType = 'danger';
        }
    } elseif ($action === 'update') {
        if ($barangModel->update($_POST['id_barang'], $_POST['nama_barang'], $_POST['harga'], $_POST['stok'], $_POST['id_kategori'])) {
            $message = 'Barang berhasil diupdate!';
            $messageType = 'success';
        } else {
            $message = 'Gagal mengupdate barang.';
            $messageType = 'danger';
        }
    } elseif ($action === 'delete') {
        Auth::requireAdmin();
        if ($barangModel->delete($_POST['id_barang'])) {
            $message = 'Barang berhasil dihapus!';
            $messageType = 'success';
        } else {
            $message = 'Gagal menghapus barang (mungkin ada transaksi terkait).';
            $messageType = 'danger';
        }
    }
}

$search = $_GET['search'] ?? '';
$dataBarang = $search ? $barangModel->search($search) : $barangModel->getAllWithKategori();
$dataKategori = $kategoriModel->getAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Barang – Supermarket</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <div class="topbar">
    <h2>📦 Data Barang</h2>
    <span style="font-size:13px;color:#64748b;"><?= count($dataBarang) ?> barang</span>
  </div>
  <div class="page-body">

    <?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>">
      <?= $messageType==='success' ? '✅' : '❌' ?> <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <span class="card-title">Daftar Barang</span>
        <div style="display:flex;gap:8px;align-items:center;">
          <!-- Search -->
          <form method="GET" class="search-bar">
            <input type="text" name="search" class="form-control" placeholder="Cari barang / kategori..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-secondary">🔍</button>
            <?php if ($search): ?>
            <a href="barang.php" class="btn btn-secondary">✕</a>
            <?php endif; ?>
          </form>
          <button class="btn btn-primary" onclick="openModal('modalTambah')">➕ Tambah</button>
        </div>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Barang</th>
              <th>Kategori</th>
              <th>Harga</th>
              <th>Stok</th>
              <th>Status Stok</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($dataBarang)): ?>
            <tr><td colspan="7" style="text-align:center;padding:30px;color:#64748b;">Tidak ada data ditemukan</td></tr>
          <?php else: ?>
            <?php foreach ($dataBarang as $i => $b): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td><strong><?= htmlspecialchars($b['nama_barang']) ?></strong></td>
              <td><span class="badge badge-info"><?= htmlspecialchars($b['nama_kategori'] ?? '-') ?></span></td>
              <td>Rp <?= number_format($b['harga'],0,',','.') ?></td>
              <td><?= number_format($b['stok']) ?></td>
              <td>
                <?php if ($b['stok'] <= 0): ?>
                  <span class="badge badge-danger">Habis</span>
                <?php elseif ($b['stok'] <= 10): ?>
                  <span class="badge badge-warning">Menipis</span>
                <?php else: ?>
                  <span class="badge badge-success">Aman</span>
                <?php endif; ?>
              </td>
              <td>
                <button class="btn btn-sm btn-warning"
                  onclick="editBarang(<?= htmlspecialchars(json_encode($b)) ?>)">✏️ Edit</button>
                <?php if (Auth::isAdmin()): ?>
                <button class="btn btn-sm btn-danger"
                  onclick="deleteBarang(<?= $b['id_barang'] ?>, '<?= htmlspecialchars($b['nama_barang']) ?>')">🗑️</button>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">➕ Tambah Barang</span>
      <button class="modal-close" onclick="closeModal('modalTambah')">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="create">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Nama Barang *</label>
          <input type="text" name="nama_barang" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Kategori *</label>
          <select name="id_kategori" class="form-control" required>
            <option value="">-- Pilih Kategori --</option>
            <?php foreach ($dataKategori as $k): ?>
            <option value="<?= $k['id_kategori'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label class="form-label">Harga (Rp) *</label>
            <input type="number" name="harga" class="form-control" min="0" required>
          </div>
          <div class="form-group">
            <label class="form-label">Stok Awal *</label>
            <input type="number" name="stok" class="form-control" min="0" required>
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

<!-- Modal Edit -->
<div class="modal-overlay" id="modalEdit">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">✏️ Edit Barang</span>
      <button class="modal-close" onclick="closeModal('modalEdit')">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id_barang" id="edit_id">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Nama Barang *</label>
          <input type="text" name="nama_barang" id="edit_nama" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Kategori *</label>
          <select name="id_kategori" id="edit_kategori" class="form-control" required>
            <?php foreach ($dataKategori as $k): ?>
            <option value="<?= $k['id_kategori'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label class="form-label">Harga (Rp) *</label>
            <input type="number" name="harga" id="edit_harga" class="form-control" min="0" required>
          </div>
          <div class="form-group">
            <label class="form-label">Stok *</label>
            <input type="number" name="stok" id="edit_stok" class="form-control" min="0" required>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalEdit')">Batal</button>
        <button type="submit" class="btn btn-warning">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Delete -->
<div class="modal-overlay" id="modalDelete">
  <div class="modal" style="width:380px;">
    <div class="modal-header">
      <span class="modal-title">🗑️ Hapus Barang</span>
      <button class="modal-close" onclick="closeModal('modalDelete')">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id_barang" id="delete_id">
      <div class="modal-body">
        <p>Yakin ingin menghapus barang <strong id="delete_nama"></strong>?</p>
        <p style="font-size:13px;color:#dc2626;margin-top:8px;">⚠️ Semua transaksi terkait akan ikut terhapus!</p>
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

function editBarang(data) {
  document.getElementById('edit_id').value = data.id_barang;
  document.getElementById('edit_nama').value = data.nama_barang;
  document.getElementById('edit_harga').value = data.harga;
  document.getElementById('edit_stok').value = data.stok;
  document.getElementById('edit_kategori').value = data.id_kategori;
  openModal('modalEdit');
}

function deleteBarang(id, nama) {
  document.getElementById('delete_id').value = id;
  document.getElementById('delete_nama').textContent = nama;
  openModal('modalDelete');
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', e => { if (e.target === el) el.classList.remove('show'); });
});
</script>
</body>
</html>
