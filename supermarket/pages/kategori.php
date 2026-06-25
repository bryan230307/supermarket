<?php
session_start();
require_once '../classes/Auth.php';
require_once '../classes/Kategori.php';

Auth::requireAdmin();

$kategoriModel = new Kategori();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        if ($kategoriModel->create(trim($_POST['nama_kategori']))) {
            $message = 'Kategori berhasil ditambahkan!';
            $messageType = 'success';
        } else { $message = 'Gagal menambahkan kategori.'; $messageType = 'danger'; }
    } elseif ($action === 'update') {
        if ($kategoriModel->update($_POST['id_kategori'], trim($_POST['nama_kategori']))) {
            $message = 'Kategori berhasil diupdate!';
            $messageType = 'success';
        } else { $message = 'Gagal mengupdate.'; $messageType = 'danger'; }
    } elseif ($action === 'delete') {
        if ($kategoriModel->delete($_POST['id_kategori'])) {
            $message = 'Kategori berhasil dihapus!';
            $messageType = 'success';
        } else { $message = 'Gagal menghapus (mungkin ada barang dalam kategori ini).'; $messageType = 'danger'; }
    }
}

$search = $_GET['search'] ?? '';
$data = $search ? $kategoriModel->search($search) : $kategoriModel->getAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kategori – Supermarket</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <div class="topbar">
    <h2>🏷️ Manajemen Kategori</h2>
  </div>
  <div class="page-body">

    <?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>">
      <?= $messageType==='success' ? '✅' : '❌' ?> <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <span class="card-title">Daftar Kategori</span>
        <div style="display:flex;gap:8px;">
          <form method="GET" class="search-bar">
            <input type="text" name="search" class="form-control" placeholder="Cari kategori..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-secondary">🔍</button>
            <?php if ($search): ?><a href="kategori.php" class="btn btn-secondary">✕</a><?php endif; ?>
          </form>
          <button class="btn btn-primary" onclick="openModal('modalTambah')">➕ Tambah</button>
        </div>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>No</th><th>ID</th><th>Nama Kategori</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php if (empty($data)): ?>
            <tr><td colspan="4" style="text-align:center;padding:30px;color:#64748b;">Tidak ada data</td></tr>
          <?php else: ?>
            <?php foreach ($data as $i => $k): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td><?= $k['id_kategori'] ?></td>
              <td><?= htmlspecialchars($k['nama_kategori']) ?></td>
              <td>
                <button class="btn btn-sm btn-warning"
                  onclick="editKategori(<?= $k['id_kategori'] ?>, '<?= htmlspecialchars($k['nama_kategori'], ENT_QUOTES) ?>')">✏️ Edit</button>
                <button class="btn btn-sm btn-danger"
                  onclick="delKategori(<?= $k['id_kategori'] ?>, '<?= htmlspecialchars($k['nama_kategori'], ENT_QUOTES) ?>')">🗑️</button>
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
  <div class="modal" style="width:380px;">
    <div class="modal-header">
      <span class="modal-title">➕ Tambah Kategori</span>
      <button class="modal-close" onclick="closeModal('modalTambah')">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="create">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Nama Kategori *</label>
          <input type="text" name="nama_kategori" class="form-control" required autofocus>
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
  <div class="modal" style="width:380px;">
    <div class="modal-header">
      <span class="modal-title">✏️ Edit Kategori</span>
      <button class="modal-close" onclick="closeModal('modalEdit')">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id_kategori" id="edit_id">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Nama Kategori *</label>
          <input type="text" name="nama_kategori" id="edit_nama" class="form-control" required>
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
  <div class="modal" style="width:360px;">
    <div class="modal-header">
      <span class="modal-title">🗑️ Hapus Kategori</span>
      <button class="modal-close" onclick="closeModal('modalDelete')">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id_kategori" id="del_id">
      <div class="modal-body">
        <p>Yakin hapus kategori <strong id="del_nama"></strong>?</p>
        <p style="font-size:13px;color:#dc2626;margin-top:8px;">⚠️ Tidak bisa dihapus jika masih ada barang di kategori ini!</p>
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
function editKategori(id, nama) {
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_nama').value = nama;
  openModal('modalEdit');
}
function delKategori(id, nama) {
  document.getElementById('del_id').value = id;
  document.getElementById('del_nama').textContent = nama;
  openModal('modalDelete');
}
document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', e => { if (e.target === el) el.classList.remove('show'); });
});
</script>
</body>
</html>
